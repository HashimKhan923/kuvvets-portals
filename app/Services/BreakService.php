<?php
namespace App\Services;

use App\Models\Attendance;
use App\Models\BreakSession;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;

class BreakService
{
    /** Start a new break for today's attendance. */
    public function start(Employee $employee, ?string $reason = null): array
    {
        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('date', now()->toDateString())
            ->first();

        if (!$attendance || !$attendance->check_in) {
            return ['status' => 'not_checked_in', 'message' => 'You must check in first.'];
        }
        if ($attendance->check_out) {
            return ['status' => 'already_out', 'message' => 'You have already checked out.'];
        }

        // Check if there's an active break
        $active = BreakSession::where('attendance_id', $attendance->id)
            ->whereNull('ended_at')->first();

        if ($active) {
            return [
                'status'  => 'break_active',
                'message' => 'You already have an active break.',
                'break'   => $active,
            ];
        }

        $break = BreakSession::create([
            'attendance_id' => $attendance->id,
            'employee_id'   => $employee->id,
            'started_at'    => now(),
            'reason'        => $reason,
        ]);

        return [
            'status'  => 'ok',
            'message' => 'Break started',
            'break'   => $break,
        ];
    }

    /** End the currently-active break. */
    public function end(Employee $employee): array
    {
        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('date', now()->toDateString())
            ->first();

        if (!$attendance) {
            return ['status' => 'no_attendance', 'message' => 'No attendance record today.'];
        }

        $active = BreakSession::where('attendance_id', $attendance->id)
            ->whereNull('ended_at')->first();

        if (!$active) {
            return ['status' => 'no_break', 'message' => 'No active break to end.'];
        }

        $now = now();
        $minutes = (int) round($active->started_at->diffInSeconds($now) / 60);

        DB::transaction(function () use ($active, $now, $minutes, $attendance) {
            $active->update([
                'ended_at'         => $now,
                'duration_minutes' => $minutes,
            ]);

            // Recompute total break_minutes on attendance
            $total = BreakSession::where('attendance_id', $attendance->id)
                ->whereNotNull('ended_at')
                ->sum('duration_minutes');

            $attendance->update(['break_minutes' => $total]);
        });

        return [
            'status'   => 'ok',
            'message'  => 'Break ended — ' . $minutes . ' min',
            'minutes'  => $minutes,
            'break'    => $active->fresh(),
        ];
    }
}