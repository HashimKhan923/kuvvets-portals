<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryStructure extends Model {
    protected $fillable = [
        'company_id','employee_id','structure_name','basic_salary',
        'house_rent','medical','conveyance','fuel','utility','meal',
        'special_allowance','other_allowance',
        'eobi_employee','eobi_employer','pessi_employee','pessi_employer',
        'loan_deduction','other_deduction',
        'tax_exempt','tax_rebate','effective_from','effective_to',
        'is_current','notes',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_to'   => 'date',
        'tax_exempt'     => 'boolean',
        'is_current'     => 'boolean',
        'basic_salary'   => 'decimal:2',
        'house_rent'     => 'decimal:2',
        'medical'        => 'decimal:2',
        'conveyance'     => 'decimal:2',
        'fuel'           => 'decimal:2',
        'utility'        => 'decimal:2',
        'meal'           => 'decimal:2',
        'special_allowance' => 'decimal:2',
        'other_allowance'   => 'decimal:2',
        'loan_deduction'    => 'decimal:2',
        'other_deduction'   => 'decimal:2',
        'tax_rebate'        => 'decimal:2',
    ];

    public function employee() { return $this->belongsTo(Employee::class); }
    public function company()  { return $this->belongsTo(Company::class); }

    public function getGrossSalaryAttribute(): float {
        return $this->basic_salary
             + $this->house_rent
             + $this->medical
             + $this->conveyance
             + $this->fuel
             + $this->utility
             + $this->meal
             + $this->special_allowance
             + $this->other_allowance;
    }

    public function getTotalAllowancesAttribute(): float {
        return $this->house_rent + $this->medical + $this->conveyance
             + $this->fuel + $this->utility + $this->meal
             + $this->special_allowance + $this->other_allowance;
    }
}