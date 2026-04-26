<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveBalance extends Model {
    protected $fillable = [
        'employee_id', 'leave_type_id', 'year',
        'allocated_days', 'used_days', 'pending_days',
        'carried_forward', 'extra_granted',
    ];

    protected $casts = [
        'allocated_days'  => 'decimal:1',
        'used_days'       => 'decimal:1',
        'pending_days'    => 'decimal:1',
        'carried_forward' => 'decimal:1',
        'extra_granted'   => 'decimal:1',
    ];

    public function employee()  { return $this->belongsTo(Employee::class); }
    public function leaveType() { return $this->belongsTo(LeaveType::class); }

    // Total available = allocated + carried + extra - used - pending
    public function getAvailableAttribute(): float {
        return max(0,
            $this->allocated_days
            + $this->carried_forward
            + $this->extra_granted
            - $this->used_days
            - $this->pending_days
        );
    }

    public function getTotalAllocatedAttribute(): float {
        return $this->allocated_days
             + $this->carried_forward
             + $this->extra_granted;
    }

    public function getUsagePercentAttribute(): int {
        $total = $this->total_allocated;
        if ($total <= 0) return 0;
        return min(100, (int) round(($this->used_days / $total) * 100));
    }
}