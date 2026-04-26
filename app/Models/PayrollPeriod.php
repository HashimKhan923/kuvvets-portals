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
            'draft'      => ['bg'=>'#111820','color'=>'#7a6a50','border'=>'#1e2a35'],
            'processing' => ['bg'=>'#001015','color'=>'#378ADD','border'=>'#0a2a35'],
            'approved'   => ['bg'=>'#1a1200','color'=>'#EF9F27','border'=>'#2a2008'],
            'paid'       => ['bg'=>'#0a1a0a','color'=>'#4CAF50','border'=>'#1a3a0a'],
            'cancelled'  => ['bg'=>'#1a0505','color'=>'#E24B4A','border'=>'#3a1010'],
            default      => ['bg'=>'#111820','color'=>'#7a6a50','border'=>'#1e2a35'],
        };
    }

    public function getMonthNameAttribute(): string {
        return \Carbon\Carbon::create($this->year, $this->month, 1)->format('F Y');
    }
}