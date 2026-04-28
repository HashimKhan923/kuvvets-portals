<?php
namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceHistoryController extends Controller
{
    public function index(Request $request)
    {
        $employee = $request->user()->employee;

        // Filters
        $month  = (int) $request->input('month', now()->month);
        $year   = (int) $request->input('year',  now()->year);
        $view   = $request->input('view', 'calendar'); // calendar | list
        $status = $request->input('status'); // optional filter
        $locId  = $request->input('location_id');

        $month = max(1, min(12, $month));
        $year  = max(now()->year - 5, min(now()->year + 1, $year));

        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end   = (clone $start)->endOfMonth();

        // Records for the month, keyed by date string
        $query = Attendance::with(['location','shift','breakSessions'])
            ->where('employee_id', $employee->id)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()]);

        if ($status) $query->where('status', $status);
        if ($locId)  $query->where('location_id', $locId);

        $records = $query->orderBy('date', 'desc')->get();
        $byDate  = $records->keyBy(fn($r) => $r->date->toDateString());

        // Holidays in this month (company-wide)
        $holidays = Holiday::where('company_id', $employee->company_id)
            ->where(function($q) use ($start, $end) {
                $q->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                  ->orWhereBetween('date_to', [$start->toDateString(), $end->toDateString()]);
            })
            ->get()
            ->keyBy(fn($h) => $h->date->toDateString());

        // Approved leaves overlapping this month
        $leaves = LeaveRequest::with('leaveType')
            ->where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->where(function($q) use ($start, $end) {
                $q->whereBetween('from_date', [$start->toDateString(), $end->toDateString()])
                  ->orWhereBetween('to_date',  [$start->toDateString(), $end->toDateString()])
                  ->orWhere(function($q2) use ($start, $end) {
                      $q2->where('from_date', '<=', $start->toDateString())
                         ->where('to_date',   '>=', $end->toDateString());
                  });
            })
            ->get();

        // Build flat array of leave-day → leave object
        $leaveDays = [];
        foreach ($leaves as $lv) {
            $cursor = $lv->from_date->copy();
            while ($cursor->lte($lv->to_date)) {
                $leaveDays[$cursor->toDateString()] = $lv;
                $cursor->addDay();
            }
        }

        // Calendar grid (always renders 6 weeks for stable layout)
        $gridStart = $start->copy()->startOfWeek(Carbon::MONDAY);
        $cells = [];
        for ($i = 0; $i < 42; $i++) {
            $d = $gridStart->copy()->addDays($i);
            $key = $d->toDateString();
            $isWeekend = in_array($d->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY]);
            $cells[] = [
                'date'        => $d,
                'key'         => $key,
                'in_month'    => $d->month === $month,
                'is_today'    => $d->isToday(),
                'is_future'   => $d->isAfter(now()->endOfDay()),
                'is_weekend'  => $isWeekend,
                'attendance'  => $byDate->get($key),
                'holiday'     => $holidays->get($key),
                'leave'       => $leaveDays[$key] ?? null,
            ];
        }

        // Month stats
        $monthStats = $records->reduce(function ($acc, $r) {
            $acc['working_minutes']  += $r->working_minutes;
            $acc['overtime_minutes'] += $r->overtime_minutes;
            $acc['late_minutes']     += $r->late_minutes;
            $acc['break_minutes']    += $r->break_minutes;
            $acc['early_leave_minutes'] += $r->early_leave_minutes;
            $acc['by_status'][$r->status] = ($acc['by_status'][$r->status] ?? 0) + 1;
            return $acc;
        }, [
            'working_minutes'=>0,'overtime_minutes'=>0,'late_minutes'=>0,
            'break_minutes'=>0,'early_leave_minutes'=>0,'by_status'=>[],
        ]);
        $monthStats['holiday_count'] = $holidays->count();
        $monthStats['leave_count']   = count($leaveDays);

        // Locations available for filter
        $availableLocations = $employee->locations()
            ->select('locations.id','locations.name')
            ->get();

        return view('employee.attendance.index', compact(
            'employee','month','year','view','status','locId',
            'cells','records','monthStats','availableLocations','start'
        ));
    }

    /**
     * AJAX: details for a single day (drawer content).
     */
    public function dayDetail(Request $request, string $date)
    {
        $employee = $request->user()->employee;
        $date = Carbon::parse($date)->toDateString();

        $att = Attendance::with(['location','shift','breakSessions','approver'])
            ->where('employee_id', $employee->id)
            ->whereDate('date', $date)
            ->first();

        $holiday = Holiday::where('company_id', $employee->company_id)
            ->where(function($q) use ($date) {
                $q->whereDate('date', $date)
                  ->orWhere(function($q2) use ($date) {
                      $q2->whereDate('date', '<=', $date)
                         ->whereDate('date_to', '>=', $date);
                  });
            })->first();

        $leave = LeaveRequest::with('leaveType')
            ->where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->whereDate('from_date', '<=', $date)
            ->whereDate('to_date',   '>=', $date)
            ->first();

        return response()->json([
            'date'    => Carbon::parse($date)->format('l, F j, Y'),
            'iso'     => $date,
            'has_data'=> (bool) ($att || $holiday || $leave),
            'html'    => view('employee.attendance._day_drawer', compact(
                'att','holiday','leave','date'
            ))->render(),
        ]);
    }

    /**
     * Export current month/filters to CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        $employee = $request->user()->employee;
        $month = (int) $request->input('month', now()->month);
        $year  = (int) $request->input('year',  now()->year);
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end   = (clone $start)->endOfMonth();

        $records = Attendance::with(['location','shift'])
            ->where('employee_id', $employee->id)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('date')
            ->get();

        $filename = "attendance_{$employee->employee_id}_{$year}_" . str_pad($month,2,'0',STR_PAD_LEFT) . ".csv";

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return response()->streamDownload(function () use ($records, $employee, $start) {
            $out = fopen('php://output', 'w');

            // Header rows
            fputcsv($out, ['KUVVET — Attendance Report']);
            fputcsv($out, ['Employee', $employee->full_name . ' (' . $employee->employee_id . ')']);
            fputcsv($out, ['Period',   $start->format('F Y')]);
            fputcsv($out, []);
            fputcsv($out, [
                'Date','Day','Status','Shift','Location',
                'Check-In','Check-Out','Working','Break','Overtime','Late','Early Out','Notes'
            ]);

            foreach ($records as $r) {
                fputcsv($out, [
                    $r->date->format('Y-m-d'),
                    $r->date->format('l'),
                    ucfirst(str_replace('_',' ',$r->status)),
                    $r->shift?->name ?? '—',
                    $r->location?->name ?? '—',
                    $r->check_in  ? $r->check_in->format('h:i A')  : '—',
                    $r->check_out ? $r->check_out->format('h:i A') : '—',
                    $r->working_hours,
                    $r->break_hours,
                    $r->overtime_hours,
                    $r->late_minutes ? $r->late_minutes . 'm' : '—',
                    $r->early_leave_minutes ? $r->early_leave_minutes . 'm' : '—',
                    $r->notes ?? '',
                ]);
            }
            fclose($out);
        }, $filename, $headers);
    }

// API method to return attendance records for a month (for mobile app)
    public function apiIndex(Request $request)
    {
        $employee = $request->user()->employee;
        $month = (int) $request->input('month', now()->month);
        $year  = (int) $request->input('year', now()->year);
        $start = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
        $end   = (clone $start)->endOfMonth();
        $records = \App\Models\Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->with('location','shift')->orderBy('date', 'desc')->get()
            ->map(fn($r) => ['date' => $r->date->toDateString(), 'status' => $r->status,
                'check_in' => $r->check_in?->format('h:i A'), 'check_out' => $r->check_out?->format('h:i A'),
                'working_minutes' => $r->working_minutes, 'overtime_minutes' => $r->overtime_minutes,
                'late_minutes' => $r->late_minutes, 'break_minutes' => $r->break_minutes,
                'location_name' => $r->location?->name, 'shift_name' => $r->shift?->name]);
        $stats = \App\Models\Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw("SUM(CASE WHEN status='present' THEN 1 ELSE 0 END) as present_days, SUM(CASE WHEN status='late' THEN 1 ELSE 0 END) as late_days, SUM(CASE WHEN status='absent' THEN 1 ELSE 0 END) as absent_days, SUM(CASE WHEN status='half_day' THEN 1 ELSE 0 END) as half_days, COALESCE(SUM(working_minutes),0) as total_minutes, COALESCE(SUM(overtime_minutes),0) as overtime_minutes, COALESCE(SUM(late_minutes),0) as late_minutes")->first();
        return response()->json(['records' => $records, 'stats' => $stats]);
    }
}