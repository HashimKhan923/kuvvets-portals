<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class LeaveRequest extends Model {
    use SoftDeletes;

    protected $fillable = [
        'company_id', 'employee_id', 'leave_type_id', 'request_number',
        'from_date', 'to_date', 'total_days', 'day_type', 'reason',
        'document_path', 'status', 'reviewed_by', 'reviewed_at',
        'rejection_reason', 'hr_notes', 'is_emergency', 'contact_during_leave',
    ];

    protected $casts = [
        'from_date'   => 'date',
        'to_date'     => 'date',
        'reviewed_at' => 'datetime',
        'total_days'  => 'decimal:1',
        'is_emergency'=> 'boolean',
    ];

    public function employee()  { return $this->belongsTo(Employee::class); }
    public function leaveType() { return $this->belongsTo(LeaveType::class); }
    public function reviewer()  { return $this->belongsTo(User::class, 'reviewed_by'); }

    public function getStatusBadgeAttribute(): array {
        return match($this->status) {
            'pending'   => ['bg'=>'#1a1200', 'color'=>'#EF9F27', 'border'=>'#2a2008'],
            'approved'  => ['bg'=>'#0a1a0a', 'color'=>'#4CAF50', 'border'=>'#1a3a0a'],
            'rejected'  => ['bg'=>'#1a0505', 'color'=>'#E24B4A', 'border'=>'#3a1010'],
            'cancelled' => ['bg'=>'#111820', 'color'=>'#5a5040', 'border'=>'#1e2a35'],
            'withdrawn' => ['bg'=>'#111820', 'color'=>'#5a5040', 'border'=>'#1e2a35'],
            default     => ['bg'=>'#111820', 'color'=>'#7a6a50', 'border'=>'#1e2a35'],
        };
    }

    public function getDurationTextAttribute(): string {
        if ($this->total_days == 0.5) {
            return 'Half Day (' . match($this->day_type) {
                'half_day_morning'   => 'Morning',
                'half_day_afternoon' => 'Afternoon',
                default              => '',
            } . ')';
        }
        return $this->total_days . ' ' . ($this->total_days == 1 ? 'Day' : 'Days');
    }

    // Calculate working days between two dates excluding weekends and holidays
    public static function calculateWorkingDays(
        Carbon $from,
        Carbon $to,
        int $companyId
    ): float {
        $holidays = Holiday::where('company_id', $companyId)
            ->where('year', $from->year)
            ->get()
            ->flatMap(function ($h) {
                $dates = [];
                $start = $h->date;
                $end   = $h->date_to ?? $h->date;
                while ($start->lte($end)) {
                    $dates[] = $start->format('Y-m-d');
                    $start->addDay();
                }
                return $dates;
            })->toArray();

        $days    = 0;
        $current = $from->copy();

        while ($current->lte($to)) {
            if (
                !$current->isWeekend()
                && !in_array($current->format('Y-m-d'), $holidays)
            ) {
                $days++;
            }
            $current->addDay();
        }

        return $days;
    }
}