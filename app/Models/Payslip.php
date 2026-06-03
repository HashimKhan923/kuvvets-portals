<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\TaxCalculator;

class Payslip extends Model {
    protected $fillable = [
        'payroll_period_id','employee_id','company_id','payslip_number',
        'basic_salary','house_rent','medical','conveyance','fuel','utility',
        'meal','special_allowance','other_allowance','overtime_amount',
        'bonus','arrears','gross_salary',
        'working_days','present_days','absent_days','leave_days',
        'overtime_hours','absent_deduction',
        'income_tax','eobi_employee','eobi_employer','pessi_employee',
        'loan_deduction','other_deduction','total_deductions',
        'net_salary','annual_taxable_income','annual_tax','monthly_tax',
        'tax_slab','status','pdf_path','remarks',
    ];

    protected $casts = [
        'gross_salary'  => 'decimal:2',
        'net_salary'    => 'decimal:2',
        'income_tax'    => 'decimal:2',
        'basic_salary'  => 'decimal:2',
    ];

    public function period()   { return $this->belongsTo(PayrollPeriod::class, 'payroll_period_id'); }
    public function employee() { return $this->belongsTo(Employee::class); }
    public function company()  { return $this->belongsTo(Company::class); }

    public function getNetInWordsAttribute(): string {
        return TaxCalculator::amountInWords((float) $this->net_salary);
    }

   public function getStatusBadgeAttribute(): array {
    return match($this->status) {
        'draft'    => ['bg'=>'#fffbeb', 'color'=>'#7a6a50', 'border'=>'#fde68a'],
        'approved' => ['bg'=>'#fffbeb', 'color'=>'#CBA557', 'border'=>'#fde68a'],
        'paid'     => ['bg'=>'#fffbeb', 'color'=>'#4CAF50', 'border'=>'#fde68a'],
        default    => ['bg'=>'#fffbeb', 'color'=>'#7a6a50', 'border'=>'#fde68a'],
    };
}
}