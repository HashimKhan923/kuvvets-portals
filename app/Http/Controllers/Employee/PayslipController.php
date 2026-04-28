<?php
namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Payslip;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PayslipController extends Controller
{
    /**
     * List employee's payslips.
     */
    public function index(Request $request)
    {
        $employee = $request->user()->employee;
        $year = (int) $request->input('year', now()->year);

        $payslips = Payslip::with('period')
            ->where('employee_id', $employee->id)
            ->whereHas('period', fn($q) => $q->where('year', $year))
            ->whereIn('status', ['approved','paid'])   // don't show drafts
            ->orderByDesc(
                \App\Models\PayrollPeriod::select('year')
                    ->whereColumn('payroll_periods.id','payslips.payroll_period_id')
            )
            ->get()
            ->sortByDesc(fn($p) => $p->period->year * 100 + $p->period->month)
            ->values();

        // YTD totals across the selected year
        $ytd = Payslip::where('employee_id', $employee->id)
            ->whereHas('period', fn($q) => $q->where('year', $year))
            ->whereIn('status', ['approved','paid'])
            ->selectRaw('
                COALESCE(SUM(gross_salary),0)    as total_gross,
                COALESCE(SUM(net_salary),0)      as total_net,
                COALESCE(SUM(income_tax),0)      as total_tax,
                COALESCE(SUM(total_deductions),0) as total_deductions,
                COUNT(*) as count
            ')
            ->first();

        // Available years (from existing payslips)
        $availableYears = Payslip::where('employee_id', $employee->id)
            ->join('payroll_periods','payroll_periods.id','=','payslips.payroll_period_id')
            ->whereIn('payslips.status', ['approved','paid'])
            ->distinct()
            ->orderByDesc('payroll_periods.year')
            ->pluck('payroll_periods.year')
            ->map(fn($y) => (int) $y)
            ->all();

        if (empty($availableYears)) $availableYears = [now()->year];

        // Latest payslip (for quick info card)
        $latest = Payslip::with('period')
            ->where('employee_id', $employee->id)
            ->whereIn('status', ['approved','paid'])
            ->orderByDesc(
                \App\Models\PayrollPeriod::select('year')
                    ->whereColumn('payroll_periods.id','payslips.payroll_period_id')
            )
            ->get()
            ->sortByDesc(fn($p) => $p->period->year * 100 + $p->period->month)
            ->first();

        return view('employee.payslips.index', compact('employee','year','payslips','ytd','availableYears','latest'));
    }

    /**
     * Show single payslip detail.
     */
    public function show(Request $request, Payslip $payslip)
    {
        $this->authorizePayslip($request, $payslip);
        $payslip->load(['period','employee.department','employee.designation','company']);

        // Earnings breakdown
        $earnings = array_filter([
            'Basic Salary'      => (float) $payslip->basic_salary,
            'House Rent'        => (float) $payslip->house_rent,
            'Medical'           => (float) $payslip->medical,
            'Conveyance'        => (float) $payslip->conveyance,
            'Fuel'              => (float) $payslip->fuel,
            'Utility'           => (float) $payslip->utility,
            'Meal'              => (float) $payslip->meal,
            'Special Allowance' => (float) $payslip->special_allowance,
            'Other Allowance'   => (float) $payslip->other_allowance,
            'Overtime'          => (float) $payslip->overtime_amount,
            'Bonus'             => (float) $payslip->bonus,
            'Arrears'           => (float) $payslip->arrears,
        ], fn($v) => $v > 0);

        // Deductions breakdown
        $deductions = array_filter([
            'Income Tax'        => (float) $payslip->income_tax,
            'EOBI (Employee)'   => (float) $payslip->eobi_employee,
            'PESSI (Employee)'  => (float) $payslip->pessi_employee,
            'Loan'              => (float) $payslip->loan_deduction,
            'Absence'           => (float) $payslip->absent_deduction,
            'Other'             => (float) $payslip->other_deduction,
        ], fn($v) => $v > 0);

        return view('employee.payslips.show', compact('payslip','earnings','deductions'));
    }

    /**
     * Download PDF — reuses admin PDF template.
     */
    public function pdf(Request $request, Payslip $payslip)
    {
        $this->authorizePayslip($request, $payslip);
        $payslip->load(['employee.department','employee.designation','company','period']);

        $pdf = Pdf::loadView('payroll.payslip-pdf', compact('payslip'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont'           => 'DejaVu Sans',
                'isHtml5ParserEnabled'  => true,
                'isRemoteEnabled'       => true,
            ]);

        return $pdf->download("Payslip-{$payslip->payslip_number}.pdf");
    }

    /**
     * Ensure this payslip belongs to the logged-in employee
     * AND is not in draft status.
     */
    protected function authorizePayslip(Request $request, Payslip $payslip): void
    {
        abort_unless(
            $payslip->employee_id === $request->user()->employee->id,
            403,
            'This payslip does not belong to you.'
        );
        abort_if(
            $payslip->status === 'draft',
            403,
            'This payslip is not yet available.'
        );
    }

    // API method to return payslip list and YTD totals for mobile app

    public function apiIndex(Request $request)
    {
        $employee = $request->user()->employee;
        $year = (int) $request->input('year', now()->year);
        $payslips = \App\Models\Payslip::with('period')->where('employee_id', $employee->id)
            ->whereHas('period', fn($q) => $q->where('year', $year))->whereIn('status', ['approved','paid'])->get()
            ->sortByDesc(fn($p) => $p->period->year * 100 + $p->period->month)->values()
            ->map(fn($p) => ['id' => $p->id, 'status' => $p->status, 'gross_salary' => $p->gross_salary,
                'net_salary' => $p->net_salary, 'total_deductions' => $p->total_deductions,
                'basic_salary' => $p->basic_salary, 'house_rent' => $p->house_rent, 'medical' => $p->medical,
                'conveyance' => $p->conveyance, 'fuel' => $p->fuel, 'special_allowance' => $p->special_allowance,
                'overtime_amount' => $p->overtime_amount, 'bonus' => $p->bonus, 'income_tax' => $p->income_tax,
                'eobi_employee' => $p->eobi_employee, 'pessi_employee' => $p->pessi_employee,
                'loan_deduction' => $p->loan_deduction, 'absent_deduction' => $p->absent_deduction,
                'other_deduction' => $p->other_deduction,
                'period' => $p->period ? ['id' => $p->period->id, 'month' => $p->period->month, 'year' => $p->period->year,
                    'label' => $p->period->label ?? \Carbon\Carbon::create($p->period->year, $p->period->month)->format('F Y')] : null]);
        $ytd = \App\Models\Payslip::where('employee_id', $employee->id)->whereHas('period', fn($q) => $q->where('year', $year))
            ->whereIn('status', ['approved','paid'])
            ->selectRaw('COALESCE(SUM(gross_salary),0) as total_gross, COALESCE(SUM(net_salary),0) as total_net, COALESCE(SUM(total_deductions),0) as total_deductions, COUNT(*) as count')->first();
        $availableYears = \App\Models\Payslip::where('employee_id', $employee->id)
            ->join('payroll_periods','payroll_periods.id','=','payslips.payroll_period_id')
            ->whereIn('payslips.status', ['approved','paid'])->distinct()->orderByDesc('payroll_periods.year')
            ->pluck('payroll_periods.year')->map(fn($y) => (int) $y)->all();
        return response()->json(['payslips' => $payslips, 'ytd' => $ytd, 'available_years' => $availableYears ?: [now()->year]]);
    }
}