<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model {
    protected $fillable = [
        'company_id', 'name', 'code', 'description', 'days_per_year',
        'is_paid', 'requires_document', 'can_carry_forward',
        'max_carry_forward_days', 'min_days_notice', 'max_consecutive_days',
        'applicable_to_male', 'applicable_to_female', 'color', 'is_active',
    ];

    protected $casts = [
        'is_paid'              => 'boolean',
        'requires_document'    => 'boolean',
        'can_carry_forward'    => 'boolean',
        'applicable_to_male'   => 'boolean',
        'applicable_to_female' => 'boolean',
        'is_active'            => 'boolean',
    ];

    public function company()       { return $this->belongsTo(Company::class); }
    public function leaveRequests() { return $this->hasMany(LeaveRequest::class); }
    public function leaveBalances() { return $this->hasMany(LeaveBalance::class); }
}