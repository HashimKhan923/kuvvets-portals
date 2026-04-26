<?php
namespace App\Services;

use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Models\Payslip;
use App\Models\SalaryStructure;
use App\Models\PayrollAdjustment;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Support\Str;

class PayrollService
{
    /**
     * Generate payslips for all active employees in a period
     */
    public static function generatePayroll(PayrollPeriod $period): array
    {
        $companyId = $period->company_id;
        $month     = $period->month;
        $year      = $period->year;

        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        // Working days in month (Mon–Fri only)
        $workingDays = 0;
        $cur = $start->copy();
        while ($cur->lte($end)) {
            if (!$cur->isWeekend()) $workingDays++;
            $cur->addDay();
        }

        $employees = Employee::where('company_id', $companyId)
            ->where('employment_status', 'active')
            ->with(['salaryStructure'])
            ->get();

        $generated = 0;
        $skipped   = 0;

        foreach ($employees as $emp) {
            // Skip if payslip already exists
            if (Payslip::where('payroll_period_id', $period->id)
                ->where('employee_id', $emp->id)->exists()) {
                $skipped++;
                continue;
            }

            $structure = SalaryStructure::where('employee_id', $emp->id)
                ->where('is_current', true)->first();

            if (!$structure) {
                $skipped++;
                continue;
            }

            self::generatePayslip($period, $emp, $structure, $workingDays, $start, $end);
            $generated++;
        }

        // Recalculate period totals
        self::recalculatePeriodTotals($period);

        return ['generated' => $generated, 'skipped' => $skipped];
    }

    /**
     * Generate a single payslip
     */
    public static function generatePayslip(
        PayrollPeriod $period,
        Employee $emp,
        SalaryStructure $structure,
        int $workingDays,
        Carbon $start,
        Carbon $end
    ): Payslip {
        $month = $period->month;
        $year  = $period->year;

        // ── Attendance data ───────────────────────────────────
        $attendances = Attendance::where('employee_id', $emp->id)
            ->whereBetween('date', [$start, $end])
            ->get();

        $presentDays  = $attendances->whereIn('status', ['present','late','work_from_home'])->count();
        $absentDays   = max(0, $workingDays - $presentDays);
        $overtimeHours= (int) round($attendances->sum('overtime_minutes') / 60);

        // Leave days (approved, paid)
        $leaveDays = LeaveRequest::where('employee_id', $emp->id)
            ->where('status', 'approved')
            ->whereHas('leaveType', fn($q) => $q->where('is_paid', true))
            ->where(function($q) use ($start, $end) {
                $q->whereBetween('from_date', [$start, $end])
                  ->orWhereBetween('to_date', [$start, $end]);
            })->sum('total_days');

        // ── Earnings ────────────────────────────────────────
        $perDayRate     = $workingDays > 0 ? $structure->basic_salary / $workingDays : 0;
        $absentDeduction= round($perDayRate * $absentDays, 2);
        $effectiveBasic = max(0, $structure->basic_salary - $absentDeduction);

        $overtimeAmount = TaxCalculator::calculateOvertime($structure->basic_salary, $overtimeHours);

        // ── Adjustments (bonus/arrears/deductions) ──────────
        $adjustments = PayrollAdjustment::where('payroll_period_id', $period->id)
            ->where('employee_id', $emp->id)->get();

        $bonus   = $adjustments->where('type','bonus')->sum('amount');
        $arrears = $adjustments->where('type','arrears')->sum('amount');
        $extraDeduction = $adjustments->where('effect','deduct')
            ->whereNotIn('type',['bonus','arrears'])->sum('amount');

        // ── Gross ───────────────────────────────────────────
        $gross = $effectiveBasic
               + $structure->house_rent
               + $structure->medical
               + $structure->conveyance
               + $structure->fuel
               + $structure->utility
               + $structure->meal
               + $structure->special_allowance
               + $structure->other_allowance
               + $overtimeAmount
               + $bonus
               + $arrears;

        // ── EOBI ────────────────────────────────────────────
        $eobi  = TaxCalculator::calculateEOBI($effectiveBasic);
        $pessi = TaxCalculator::calculatePESSI($gross);

        // ── Income Tax ──────────────────────────────────────
        $annualTaxableIncome = 0;
        $annualTax           = 0;
        $monthlyTax          = 0;
        $taxSlab             = 'Exempt';

        if (!$structure->tax_exempt) {
            // Annual taxable = (gross * 12) - EOBI annual - rebate
            $annualTaxableIncome = ($gross * 12)
                - ($eobi['employee'] * 12)
                - $structure->tax_rebate;

            $taxResult   = TaxCalculator::calculateAnnualTax(max(0, $annualTaxableIncome));
            $annualTax   = $taxResult['annual_tax'];
            $monthlyTax  = $taxResult['monthly_tax'];
            $taxSlab     = $taxResult['slab_label'];
        }

        // ── Total Deductions ────────────────────────────────
        $totalDeductions = $monthlyTax
            + $eobi['employee']
            + $pessi['employee']
            + $structure->loan_deduction
            + $extraDeduction
            + $absentDeduction;

        $netSalary = max(0, $gross - $totalDeductions);

        // ── Create Payslip ──────────────────────────────────
        return Payslip::create([
            'payroll_period_id'    => $period->id,
            'employee_id'          => $emp->id,
            'company_id'           => $emp->company_id,
            'payslip_number'       => 'PS-' . $year . str_pad($month, 2, '0', STR_PAD_LEFT) . '-' . str_pad($emp->id, 4, '0', STR_PAD_LEFT),
            // Earnings
            'basic_salary'         => $effectiveBasic,
            'house_rent'           => $structure->house_rent,
            'medical'              => $structure->medical,
            'conveyance'           => $structure->conveyance,
            'fuel'                 => $structure->fuel,
            'utility'              => $structure->utility,
            'meal'                 => $structure->meal,
            'special_allowance'    => $structure->special_allowance,
            'other_allowance'      => $structure->other_allowance,
            'overtime_amount'      => $overtimeAmount,
            'bonus'                => $bonus,
            'arrears'              => $arrears,
            'gross_salary'         => $gross,
            // Attendance
            'working_days'         => $workingDays,
            'present_days'         => $presentDays,
            'absent_days'          => $absentDays,
            'leave_days'           => $leaveDays,
            'overtime_hours'       => $overtimeHours,
            'absent_deduction'     => $absentDeduction,
            // Deductions
            'income_tax'           => $monthlyTax,
            'eobi_employee'        => $eobi['employee'],
            'eobi_employer'        => $eobi['employer'],
            'pessi_employee'       => $pessi['employee'],
            'loan_deduction'       => $structure->loan_deduction,
            'other_deduction'      => $extraDeduction,
            'total_deductions'     => $totalDeductions,
            // Net
            'net_salary'           => $netSalary,
            // Tax
            'annual_taxable_income'=> $annualTaxableIncome,
            'annual_tax'           => $annualTax,
            'monthly_tax'          => $monthlyTax,
            'tax_slab'             => $taxSlab,
            'status'               => 'draft',
        ]);
    }

    /**
     * Recalculate period summary totals
     */
    public static function recalculatePeriodTotals(PayrollPeriod $period): void
    {
        $payslips = Payslip::where('payroll_period_id', $period->id)->get();

        $period->update([
            'total_gross'      => $payslips->sum('gross_salary'),
            'total_deductions' => $payslips->sum('total_deductions'),
            'total_net'        => $payslips->sum('net_salary'),
            'total_tax'        => $payslips->sum('income_tax'),
            'total_eobi'       => $payslips->sum('eobi_employee') + $payslips->sum('eobi_employer'),
            'employee_count'   => $payslips->count(),
        ]);
    }
}