<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model {
    protected $fillable = [
        'company_id','employee_id','shift_id','location_id','date',
        'check_in','check_out','working_minutes','overtime_minutes',
        'late_minutes','early_leave_minutes','break_minutes',
        'status','source','check_in_ip','check_out_ip',
        'check_in_lat','check_in_lng','check_in_distance_m','check_in_method',
        'check_out_lat','check_out_lng','check_out_distance_m','check_out_method',
        'device_info',
        'notes','is_approved','approved_by','approved_at','override',
    ];

    protected $casts = [
        'date'        => 'date',
        'check_in'    => 'datetime',
        'check_out'   => 'datetime',
        'approved_at' => 'datetime',
        'is_approved' => 'boolean',
        'override'    => 'boolean',
        'check_in_lat' => 'decimal:7',
        'check_in_lng' => 'decimal:7',
        'check_out_lat' => 'decimal:7',
        'check_out_lng' => 'decimal:7',
        'check_in_distance_m' => 'integer',
        'check_out_distance_m' => 'integer',
    ];

    public function employee()   { return $this->belongsTo(Employee::class); }
    public function shift()      { return $this->belongsTo(Shift::class); }
    public function approver()   { return $this->belongsTo(User::class, 'approved_by'); }

    public function getWorkingHoursAttribute(): string {
        $h = intdiv($this->working_minutes, 60);
        $m = $this->working_minutes % 60;
        return "{$h}h {$m}m";
    }

    public function getOvertimeHoursAttribute(): string {
        if (!$this->overtime_minutes) return '—';
        $h = intdiv($this->overtime_minutes, 60);
        $m = $this->overtime_minutes % 60;
        return "{$h}h {$m}m";
    }

    public function getStatusBadgeAttribute(): array {
        return match($this->status) {
            'present'       => ['bg'=>'#0a1a0a', 'color'=>'#4CAF50', 'border'=>'#1a3a0a'],
            'absent'        => ['bg'=>'#1a0505', 'color'=>'#E24B4A', 'border'=>'#3a1010'],
            'late'          => ['bg'=>'#1a1200', 'color'=>'#EF9F27', 'border'=>'#2a2008'],
            'half_day'      => ['bg'=>'#001015', 'color'=>'#378ADD', 'border'=>'#0a2a35'],
            'on_leave'      => ['bg'=>'#100a1a', 'color'=>'#7F77DD', 'border'=>'#2a1a3a'],
            'holiday'       => ['bg'=>'#0a1a0a', 'color'=>'#BA7517', 'border'=>'#2a2008'],
            'weekend'       => ['bg'=>'#111820', 'color'=>'#5a5040', 'border'=>'#1e2a35'],
            'work_from_home'=> ['bg'=>'#001015', 'color'=>'#1D9E75', 'border'=>'#0a2a20'],
            default         => ['bg'=>'#111820', 'color'=>'#5a5040', 'border'=>'#1e2a35'],
        };
    }

    // Compute all time metrics after check-out
    public static function compute(Attendance $att): void {
        if (!$att->check_in || !$att->check_out) return;

        $checkIn   = Carbon::parse($att->check_in);
        $checkOut  = Carbon::parse($att->check_out);
        $totalMins = $checkIn->diffInMinutes($checkOut);
        $workMins  = max(0, $totalMins - ($att->break_minutes ?? 0));

        $shift       = $att->shift;
        $lateMins    = 0;
        $earlyMins   = 0;
        $overtimeMins= 0;

        if ($shift) {
            $shiftStart = Carbon::parse($att->date->format('Y-m-d') . ' ' . $shift->start_time);
            $shiftEnd   = Carbon::parse($att->date->format('Y-m-d') . ' ' . $shift->end_time);
            if ($shift->is_night_shift) $shiftEnd->addDay();

            $gracedStart = $shiftStart->copy()->addMinutes($shift->grace_minutes);
            if ($checkIn->gt($gracedStart)) {
                $lateMins = $checkIn->diffInMinutes($shiftStart);
            }
            if ($checkOut->lt($shiftEnd)) {
                $earlyMins = $checkOut->diffInMinutes($shiftEnd);
            }
            $shiftWorkMins = $shift->working_hours * 60;
            if ($workMins > $shiftWorkMins) {
                $overtimeMins = $workMins - $shiftWorkMins;
            }
        }

        $status = 'present';
        if ($lateMins > 0 && $lateMins <= 120)  $status = 'late';
        if ($workMins < ($shift?->working_hours ?? 8) * 60 / 2) $status = 'half_day';

        $att->update([
            'working_minutes'     => $workMins,
            'late_minutes'        => $lateMins,
            'early_leave_minutes' => $earlyMins,
            'overtime_minutes'    => $overtimeMins,
            'status'              => $status,
        ]);
    }

    public function location() { return $this->belongsTo(Location::class); }

    public function breakSessions() { return $this->hasMany(BreakSession::class); }

    public function activeBreak()
    {
        return $this->hasOne(BreakSession::class)
            ->whereNull('ended_at')
            ->latest('started_at');
    }

    /** Computed late-ness bucket for coloring */
    public function getLateLabelAttribute(): ?string
    {
        if (!$this->late_minutes) return null;
        $h = intdiv($this->late_minutes, 60);
        $m = $this->late_minutes % 60;
        return $h ? "{$h}h {$m}m late" : "{$m}m late";
    }

    public function getBreakHoursAttribute(): string
    {
        if (!$this->break_minutes) return '—';
        $h = intdiv($this->break_minutes, 60);
        $m = $this->break_minutes % 60;
        return $h ? "{$h}h {$m}m" : "{$m}m";
    }
}