<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollAdjustment extends Model {
    protected $fillable = [
        'payroll_period_id','employee_id','type',
        'description','amount','effect','created_by',
    ];

    protected $casts = ['amount' => 'decimal:2'];

    public function period()   { return $this->belongsTo(PayrollPeriod::class, 'payroll_period_id'); }
    public function employee() { return $this->belongsTo(Employee::class); }
    public function creator()  { return $this->belongsTo(User::class, 'created_by'); }
}