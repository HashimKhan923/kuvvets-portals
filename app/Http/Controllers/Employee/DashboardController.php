<?php
namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\BreakSession;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $employee = auth()->user()->employee->load('department','designation');

        $shift = $employee->employeeShifts()
            ->where('is_current', true)
            ->with('shift')->first()?->shift;

        $today = Attendance::with(['location','breakSessions' => fn($q) => $q->orderBy('started_at')])
            ->where('employee_id', $employee->id)
            ->whereDate('date', today())
            ->first();

        $activeBreak = $today
            ? BreakSession::where('attendance_id', $today->id)->whereNull('ended_at')->first()
            : null;

        $activeLocations = $employee->activeLocations()->get();

        $monthStats = Attendance::where('employee_id', $employee->id)
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->selectRaw("
                SUM(CASE WHEN status='present' THEN 1 ELSE 0 END) as present_days,
                SUM(CASE WHEN status='late' THEN 1 ELSE 0 END) as late_days,
                SUM(CASE WHEN status='absent' THEN 1 ELSE 0 END) as absent_days,
                SUM(CASE WHEN status='half_day' THEN 1 ELSE 0 END) as half_days,
                COALESCE(SUM(working_minutes),0) as total_minutes,
                COALESCE(SUM(overtime_minutes),0) as overtime_minutes,
                COALESCE(SUM(late_minutes),0) as late_minutes_total,
                COALESCE(SUM(break_minutes),0) as break_minutes_total
            ")
            ->first();

        $last7 = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = now()->subDays($i);
            $att = Attendance::where('employee_id', $employee->id)
                ->whereDate('date', $d->toDateString())
                ->first();
            $last7[] = [
                'date'     => $d->format('D'),
                'day'      => $d->format('j'),
                'status'   => $att?->status,
                'is_today' => $d->isToday(),
                'minutes'  => $att?->working_minutes ?? 0,
            ];
        }

        return view('employee.dashboard', compact(
            'employee','shift','today','activeBreak','activeLocations','monthStats','last7'
        ));
    }
}