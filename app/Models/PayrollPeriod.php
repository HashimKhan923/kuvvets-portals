<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollPeriod extends Model {
    protected $fillable = [
        'company_id','title','month','year','payment_date',
        'status','total_gross','total_deductions','total_net',
        'total_tax','total_eobi','employee_count',
        'created_by','approved_by','approved_at','notes',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'approved_at'  => 'datetime',
        'total_gross'  => 'decimal:2',
        'total_net'    => 'decimal:2',
        'total_tax'    => 'decimal:2',
    ];

    public function company()   { return $this->belongsTo(Company::class); }
    public function creator()   { return $this->belongsTo(User::class, 'created_by'); }
    public function approver()  { return $this->belongsTo(User::class, 'approved_by'); }
    public function payslips()  { return $this->hasMany(Payslip::class); }
    public function adjustments(){ return $this->hasMany(PayrollAdjustment::class); }

 public function getStatusBadgeAttribute(): array {
    return match($this->status) {
        'draft'      => ['bg'=>'#fffbeb', 'color'=>'#7a6a50', 'border'=>'#fde68a'],
        'processing' => ['bg'=>'#fffbeb', 'color'=>'#378ADD', 'border'=>'#fde68a'],
        'approved'   => ['bg'=>'#fffbeb', 'color'=>'#CBA557', 'border'=>'#fde68a'],
        'paid'       => ['bg'=>'#fffbeb', 'color'=>'#4CAF50', 'border'=>'#fde68a'],
        'cancelled'  => ['bg'=>'#fffbeb', 'color'=>'#E24B4A', 'border'=>'#fde68a'],
        default      => ['bg'=>'#fffbeb', 'color'=>'#7a6a50', 'border'=>'#fde68a'],
    };
}

    public function getMonthNameAttribute(): string {
        return \Carbon\Carbon::create($this->year, $this->month, 1)->format('F Y');
    }
}