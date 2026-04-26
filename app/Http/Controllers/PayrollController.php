<?php
namespace App\Http\Controllers;

use App\Models\PayrollPeriod;
use App\Models\Payslip;
use App\Models\SalaryStructure;
use App\Models\PayrollAdjustment;
use App\Models\Employee;
use App\Models\Department;
use App\Models\AuditLog;
use App\Services\PayrollService;
use App\Services\TaxCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\EmailService;

class PayrollController extends Controller
{
    // ── DASHBOARD ────────────────────────────────────────────
    public function index()
    {
        $companyId = auth()->user()->company_id;

        $periods = PayrollPeriod::where('company_id', $companyId)
            ->latest()->take(12)->get();

        $stats = [
            'total_paid_ytd'    => PayrollPeriod::where('company_id', $companyId)
                ->where('status', 'paid')->whereYear('created_at', now()->year)
                ->sum('total_net'),
            'pending_approval'  => PayrollPeriod::where('company_id', $companyId)
                ->whereIn('status', ['draft','processing'])->count(),
            'active_employees'  => Employee::where('company_id', $companyId)
                ->where('employment_status', 'active')
                ->whereHas('salaryStructure')->count(),
            'current_month_net' => PayrollPeriod::where('company_id', $companyId)
                ->where('month', now()->month)->where('year', now()->year)
                ->value('total_net') ?? 0,
        ];

        // Monthly trend (last 6 months)
        $trend = [];
        for ($i = 5; $i >= 0; $i--) {
            $d = now()->subMonths($i);
            $p = PayrollPeriod::where('company_id', $companyId)
                ->where('month', $d->month)->where('year', $d->year)->first();
            $trend[] = [
                'month'   => $d->format('M Y'),
                'gross'   => $p?->total_gross ?? 0,
                'net'     => $p?->total_net ?? 0,
                'tax'     => $p?->total_tax ?? 0,
            ];
        }

        return view('payroll.index', compact('periods', 'stats', 'trend'));
    }

    // ── CREATE PERIOD ────────────────────────────────────────
    public function createPeriod(Request $request)
    {
        $request->validate([
            'month'        => 'required|integer|min:1|max:12',
            'year'         => 'required|integer|min:2020|max:2030',
            'payment_date' => 'required|date',
            'notes'        => 'nullable|string',
        ]);

        $companyId = auth()->user()->company_id;

        $existing = PayrollPeriod::where('company_id', $companyId)
            ->where('month', $request->month)
            ->where('year', $request->year)->first();

        if ($existing) {
            return back()->with('error', 'A payroll period already exists for that month.');
        }

        $monthName = Carbon::create($request->year, $request->month, 1)->format('F Y');

        $period = PayrollPeriod::create([
            'company_id'   => $companyId,
            'title'        => "Payroll — {$monthName}",
            'month'        => $request->month,
            'year'         => $request->year,
            'payment_date' => $request->payment_date,
            'status'       => 'draft',
            'created_by'   => auth()->id(),
            'notes'        => $request->notes,
        ]);

        AuditLog::log('payroll_period_created', $period);
        return redirect()->route('payroll.period', $period)
            ->with('success', "Payroll period for {$monthName} created.");
    }

    // ── PERIOD DETAIL ────────────────────────────────────────
    public function period(PayrollPeriod $period)
    {
        $this->authorizePeriod($period);

        $period->load(['creator','approver']);

        $payslips = Payslip::with(['employee.department','employee.designation'])
            ->where('payroll_period_id', $period->id)
            ->orderBy('employee_id')
            ->get();

        $departments = Department::where('company_id', auth()->user()->company_id)->get();

        $adjustments = PayrollAdjustment::with(['employee','creator'])
            ->where('payroll_period_id', $period->id)->get();

        $employees = Employee::where('company_id', auth()->user()->company_id)
            ->where('employment_status', 'active')
            ->orderBy('first_name')->get();

        return view('payroll.period', compact(
            'period','payslips','departments','adjustments','employees'
        ));
    }

    // ── GENERATE PAYROLL ─────────────────────────────────────
    public function generate(PayrollPeriod $period)
    {
        $this->authorizePeriod($period);

        if ($period->status === 'paid') {
            return back()->with('error', 'Cannot regenerate a paid payroll.');
        }

        $period->update(['status' => 'processing']);
        $result = PayrollService::generatePayroll($period);
        $period->update(['status' => 'processing']);

        AuditLog::log('payroll_generated', $period);
        return back()->with('success',
            "Generated {$result['generated']} payslips. Skipped: {$result['skipped']}."
        );
    }

    // ── APPROVE PERIOD ───────────────────────────────────────
    public function approve(Request $request, PayrollPeriod $period)
    {
        $this->authorizePeriod($period);

        if ($period->status !== 'processing') {
            return back()->with('error', 'Only processing payrolls can be approved.');
        }

        DB::transaction(function () use ($period) {
            Payslip::where('payroll_period_id', $period->id)
                ->update(['status' => 'approved']);

            $period->update([
                'status'      => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);
        });

        AuditLog::log('payroll_approved', $period);
        return back()->with('success', 'Payroll approved successfully.');
    }

    // ── MARK PAID ────────────────────────────────────────────
    public function markPaid(PayrollPeriod $period)
    {
        $this->authorizePeriod($period);

        if ($period->status !== 'approved') {
            return back()->with('error', 'Only approved payrolls can be marked as paid.');
        }

        DB::transaction(function () use ($period) {
            Payslip::where('payroll_period_id', $period->id)
                ->update(['status' => 'paid']);
            $period->update(['status' => 'paid']);
        });

        AuditLog::log('payroll_paid', $period);
        EmailService::payrollPeriodPaid($period);
        return back()->with('success', 'Payroll marked as paid. Payslips are now accessible to employees.');
    }

    // ── ADD ADJUSTMENT ───────────────────────────────────────
    public function addAdjustment(Request $request, PayrollPeriod $period)
    {
        $this->authorizePeriod($period);

        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'type'        => 'required|string',
            'description' => 'required|string|max:200',
            'amount'      => 'required|numeric|min:0',
            'effect'      => 'required|in:add,deduct',
        ]);

        PayrollAdjustment::create([
            'payroll_period_id' => $period->id,
            'employee_id'       => $request->employee_id,
            'type'              => $request->type,
            'description'       => $request->description,
            'amount'            => $request->amount,
            'effect'            => $request->effect,
            'created_by'        => auth()->id(),
        ]);

        return back()->with('success', 'Adjustment added.');
    }

    // ── RECALCULATE SINGLE PAYSLIP ───────────────────────────
    public function recalculate(Payslip $payslip)
    {
        $period    = $payslip->period;
        $start     = Carbon::create($period->year, $period->month, 1)->startOfMonth();
        $end       = $start->copy()->endOfMonth();

        $workingDays = 0;
        $cur = $start->copy();
        while ($cur->lte($end)) {
            if (!$cur->isWeekend()) $workingDays++;
            $cur->addDay();
        }

        $structure = SalaryStructure::where('employee_id', $payslip->employee_id)
            ->where('is_current', true)->firstOrFail();

        $payslip->delete();

        PayrollService::generatePayslip(
            $period, $payslip->employee, $structure, $workingDays, $start, $end
        );

        PayrollService::recalculatePeriodTotals($period);
        return back()->with('success', 'Payslip recalculated.');
    }

    // ── VIEW PAYSLIP ─────────────────────────────────────────
    public function showPayslip(Payslip $payslip)
    {
        $this->authorizePayslip($payslip);
        $payslip->load(['employee.department','employee.designation',
                        'company','period']);
        return view('payroll.payslip', compact('payslip'));
    }

    // ── DOWNLOAD PDF ─────────────────────────────────────────
    public function downloadPdf(Payslip $payslip)
    {
        $this->authorizePayslip($payslip);
        $payslip->load(['employee.department','employee.designation','company','period']);

        $pdf = Pdf::loadView('payroll.payslip-pdf', compact('payslip'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont'  => 'DejaVu Sans',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => true,
            ]);

        $filename = "Payslip-{$payslip->payslip_number}.pdf";
        return $pdf->download($filename);
    }

    // ── SALARY STRUCTURES ────────────────────────────────────
    public function salaryStructures(Request $request)
    {
        $companyId   = auth()->user()->company_id;
        $departments = Department::where('company_id', $companyId)->get();

        $query = Employee::with(['salaryStructure','department','designation'])
            ->where('company_id', $companyId)
            ->where('employment_status', 'active');

        if ($request->filled('department'))
            $query->where('department_id', $request->department);

        $employees = $query->orderBy('first_name')->get();

        return view('payroll.salary-structures', compact('employees','departments'));
    }

    // ── SAVE SALARY STRUCTURE ────────────────────────────────
    public function saveSalaryStructure(Request $request, Employee $employee)
    {
        $request->validate([
            'basic_salary'   => 'required|numeric|min:0',
            'effective_from' => 'required|date',
        ]);

        // Close existing
        SalaryStructure::where('employee_id', $employee->id)
            ->where('is_current', true)
            ->update(['is_current' => false, 'effective_to' => now()]);

        $structure = SalaryStructure::create([
            'company_id'       => auth()->user()->company_id,
            'employee_id'      => $employee->id,
            'structure_name'   => $request->structure_name ?? 'Standard',
            'basic_salary'     => $request->basic_salary,
            'house_rent'       => $request->house_rent       ?? 0,
            'medical'          => $request->medical          ?? 0,
            'conveyance'       => $request->conveyance       ?? 0,
            'fuel'             => $request->fuel             ?? 0,
            'utility'          => $request->utility          ?? 0,
            'meal'             => $request->meal             ?? 0,
            'special_allowance'=> $request->special_allowance ?? 0,
            'other_allowance'  => $request->other_allowance  ?? 0,
            'loan_deduction'   => $request->loan_deduction   ?? 0,
            'other_deduction'  => $request->other_deduction  ?? 0,
            'tax_exempt'       => $request->boolean('tax_exempt'),
            'tax_rebate'       => $request->tax_rebate ?? 0,
            'effective_from'   => $request->effective_from,
            'notes'            => $request->notes,
            'is_current'       => true,
        ]);

        AuditLog::log('salary_structure_updated', $structure);
        return back()->with('success', "Salary structure updated for {$employee->full_name}.");
    }

    // ── TAX CALCULATOR PAGE ──────────────────────────────────
    public function taxCalculator()
    {
        $slabs = TaxCalculator::getSlabs();
        return view('payroll.tax-calculator', compact('slabs'));
    }

    // ── AJAX: CALCULATE TAX ──────────────────────────────────
    public function calculateTax(Request $request)
    {
        $gross       = (float) $request->gross_monthly;
        $annualGross = $gross * 12;
        $eobi        = TaxCalculator::calculateEOBI($gross * 0.6); // estimate basic as 60%
        $taxable     = $annualGross - ($eobi['employee'] * 12);
        $result      = TaxCalculator::calculateAnnualTax(max(0, $taxable));
        $pessiCalc   = TaxCalculator::calculatePESSI($gross);

        return response()->json([
            'gross_monthly'   => number_format($gross, 2),
            'gross_annual'    => number_format($annualGross, 2),
            'taxable_annual'  => number_format($taxable, 2),
            'annual_tax'      => number_format($result['annual_tax'], 2),
            'monthly_tax'     => number_format($result['monthly_tax'], 2),
            'effective_rate'  => $result['effective_rate'],
            'slab'            => $result['slab_label'],
            'eobi_employee'   => number_format($eobi['employee'], 2),
            'eobi_employer'   => number_format($eobi['employer'], 2),
            'pessi_employee'  => number_format($pessiCalc['employee'], 2),
            'net_estimate'    => number_format($gross - $result['monthly_tax'] - $eobi['employee'] - $pessiCalc['employee'], 2),
        ]);
    }

    // ── BANK EXPORT ──────────────────────────────────────────
    public function bankExport(PayrollPeriod $period)
    {
        $this->authorizePeriod($period);

        $payslips = Payslip::with(['employee'])
            ->where('payroll_period_id', $period->id)
            ->where('status', 'approved')
            ->get();

        $filename = "Payroll-Bank-{$period->year}-{$period->month}.csv";

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($payslips, $period) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Sr#','Employee ID','Employee Name','Bank Name',
                'IBAN','Account Number','Net Salary (PKR)',
                'Month','Remarks',
            ]);
            foreach ($payslips as $i => $p) {
                fputcsv($handle, [
                    $i + 1,
                    $p->employee->employee_id,
                    $p->employee->full_name,
                    $p->employee->bank_name ?? '',
                    $p->employee->bank_iban ?? '',
                    $p->employee->bank_account_no ?? '',
                    number_format($p->net_salary, 2, '.', ''),
                    $period->month_name,
                    'Salary Payment',
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ── PAYROLL REPORT ───────────────────────────────────────
    public function report(Request $request)
    {
        $companyId   = auth()->user()->company_id;
        $year        = $request->filled('year') ? (int)$request->year : now()->year;
        $departments = Department::where('company_id', $companyId)->get();

        $periods = PayrollPeriod::where('company_id', $companyId)
            ->where('year', $year)
            ->whereIn('status', ['approved','paid'])
            ->orderBy('month')
            ->get();

        $ytdStats = [
            'gross'   => $periods->sum('total_gross'),
            'net'     => $periods->sum('total_net'),
            'tax'     => $periods->sum('total_tax'),
            'eobi'    => $periods->sum('total_eobi'),
        ];

        return view('payroll.report', compact('periods','year','ytdStats','departments'));
    }

    // ── HELPERS ──────────────────────────────────────────────
    private function authorizePeriod(PayrollPeriod $period): void {
        if ($period->company_id !== auth()->user()->company_id) abort(403);
    }

    private function authorizePayslip(Payslip $payslip): void {
        if ($payslip->company_id !== auth()->user()->company_id) abort(403);
    }
}