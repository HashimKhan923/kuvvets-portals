<?php
namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Shift;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Services\EmailService;

class AttendanceController extends Controller
{
    // ── DAILY ATTENDANCE BOARD ───────────────────────────────
    public function index(Request $request)
    {
        $date = $request->filled('date')
            ? Carbon::parse($request->date)
            : Carbon::today();

        $companyId = auth()->user()->company_id;

        $query = Attendance::with(['employee.department', 'shift'])
            ->where('company_id', $companyId)
            ->whereDate('date', $date);

        if ($request->filled('department')) {
            $query->whereHas('employee', fn($q) =>
                $q->where('department_id', $request->department)
            );
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $records = $query->get();

        // Employees with NO record today
        $presentIds = $records->pluck('employee_id');
        $absentees  = Employee::with('department')
            ->where('company_id', $companyId)
            ->where('employment_status', 'active')
            ->whereNotIn('id', $presentIds)
            ->get();

        $summary = [
            'present'  => $records->whereIn('status', ['present','late','work_from_home'])->count(),
            'absent'   => $absentees->count(),
            'late'     => $records->where('status','late')->count(),
            'on_leave' => $records->where('status','on_leave')->count(),
            'half_day' => $records->where('status','half_day')->count(),
            'total'    => Employee::where('company_id', $companyId)->where('employment_status','active')->count(),
        ];

        $departments = \App\Models\Department::where('company_id', $companyId)->where('is_active', true)->get();

        return view('attendance.index', compact('records','absentees','summary','date','departments'));
    }

    // ── MY ATTENDANCE (Employee self-service) ────────────────
    public function my(Request $request)
    {
        $employee = auth()->user()->employee;
        if (!$employee) return redirect()->route('dashboard')->with('error','No employee profile linked.');

        $month  = $request->filled('month') ? $request->month : now()->format('Y-m');
        $start  = Carbon::parse($month . '-01')->startOfMonth();
        $end    = $start->copy()->endOfMonth();

        $records = Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$start, $end])
            ->orderBy('date')->get()->keyBy(fn($r) => $r->date->format('Y-m-d'));

        $todayAtt = Attendance::where('employee_id', $employee->id)
            ->whereDate('date', today())->first();

        return view('attendance.my', compact('employee','records','todayAtt','month','start','end'));
    }

    // ── CHECK IN ─────────────────────────────────────────────
    public function checkIn(Request $request)
    {
        $employee = auth()->user()->employee;
        if (!$employee) return back()->with('error', 'No employee profile found.');

        $today = today();
        $existing = Attendance::where('employee_id', $employee->id)
            ->whereDate('date', $today)->first();

        if ($existing && $existing->check_in) {
            return back()->with('error', 'Already checked in today.');
        }

        $shift = $employee->employeeShifts()
            ->where('is_current', true)
            ->with('shift')->first()?->shift;

        Attendance::updateOrCreate(
            ['employee_id' => $employee->id, 'date' => $today],
            [
                'company_id'   => $employee->company_id,
                'shift_id'     => $shift?->id,
                'check_in'     => now(),
                'status'       => 'present',
                'source'       => 'web',
                'check_in_ip'  => $request->ip(),
            ]
        );

        AuditLog::log('check_in');
        return back()->with('success', 'Checked in at ' . now()->format('h:i A') . ' PKT');
    }

    // ── CHECK OUT ────────────────────────────────────────────
    public function checkOut(Request $request)
    {
        $employee = auth()->user()->employee;
        if (!$employee) return back()->with('error', 'No employee profile found.');

        $att = Attendance::where('employee_id', $employee->id)
            ->whereDate('date', today())->first();

        if (!$att || !$att->check_in) return back()->with('error', 'No check-in found for today.');
        if ($att->check_out) return back()->with('error', 'Already checked out today.');

        $att->update([
            'check_out'    => now(),
            'check_out_ip' => $request->ip(),
        ]);

        Attendance::compute($att);
        AuditLog::log('check_out');

        return back()->with('success', 'Checked out at ' . now()->format('h:i A') . '. ' . $att->fresh()->working_hours . ' logged.');
    }

    // ── MANUAL ENTRY / OVERRIDE ──────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date'        => 'required|date',
            'check_in'    => 'nullable|date_format:H:i',
            'check_out'   => 'nullable|date_format:H:i|after:check_in',
            'status'      => 'required|string',
            'notes'       => 'nullable|string',
        ]);

        $date     = Carbon::parse($request->date);
        $checkIn  = $request->check_in  ? Carbon::parse($request->date . ' ' . $request->check_in)  : null;
        $checkOut = $request->check_out ? Carbon::parse($request->date . ' ' . $request->check_out) : null;

        $employee = Employee::findOrFail($request->employee_id);
        $shift    = $employee->employeeShifts()->where('is_current', true)->with('shift')->first()?->shift;

        $att = Attendance::updateOrCreate(
            ['employee_id' => $request->employee_id, 'date' => $date],
            [
                'company_id' => auth()->user()->company_id,
                'shift_id'   => $shift?->id,
                'check_in'   => $checkIn,
                'check_out'  => $checkOut,
                'status'     => $request->status,
                'source'     => 'manual',
                'notes'      => $request->notes,
                'override'   => true,
            ]
        );

        if ($checkIn && $checkOut) Attendance::compute($att);

        AuditLog::log('attendance_manual_entry', $att);
        if ($att->override) {
            EmailService::attendanceOverridden($att);
        }
        return back()->with('success', 'Attendance record saved.');
    }

    // ── MONTHLY REPORT ───────────────────────────────────────
    public function report(Request $request)
    {
        $month     = $request->filled('month') ? $request->month : now()->format('Y-m');
        $companyId = auth()->user()->company_id;
        $start     = Carbon::parse($month . '-01')->startOfMonth();
        $end       = $start->copy()->endOfMonth();

        $query = Employee::with(['department','designation'])
            ->where('company_id', $companyId)
            ->where('employment_status', 'active');

        if ($request->filled('department'))
            $query->where('department_id', $request->department);

        $employees = $query->get();

        // Build summary per employee
        $report = $employees->map(function ($emp) use ($start, $end) {
            $atts = Attendance::where('employee_id', $emp->id)
                ->whereBetween('date', [$start, $end])->get();
            return [
                'employee'       => $emp,
                'present'        => $atts->whereIn('status',['present','late','work_from_home'])->count(),
                'absent'         => $atts->where('status','absent')->count(),
                'late'           => $atts->where('status','late')->count(),
                'half_day'       => $atts->where('status','half_day')->count(),
                'on_leave'       => $atts->where('status','on_leave')->count(),
                'total_hours'    => round($atts->sum('working_minutes') / 60, 1),
                'overtime_hours' => round($atts->sum('overtime_minutes') / 60, 1),
                'late_minutes'   => $atts->sum('late_minutes'),
            ];
        });

        $departments = \App\Models\Department::where('company_id', $companyId)->get();
        return view('attendance.report', compact('report','month','start','end','departments'));
    }

    // ── SHIFT MANAGEMENT ─────────────────────────────────────
    public function shifts()
    {
        $shifts = Shift::where('company_id', auth()->user()->company_id)->get();
        return view('attendance.shifts', compact('shifts'));
    }

    public function storeShift(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:100',
            'start_time'    => 'required|date_format:H:i',
            'end_time'      => 'required|date_format:H:i',
            'grace_minutes' => 'nullable|integer|min:0|max:60',
            'working_hours' => 'required|integer|min:1|max:24',
            'working_days'  => 'required|array',
        ]);

        Shift::create([
            'company_id'    => auth()->user()->company_id,
            'name'          => $request->name,
            'code'          => strtoupper(substr($request->name, 0, 3)) . rand(10,99),
            'start_time'    => $request->start_time,
            'end_time'      => $request->end_time,
            'grace_minutes' => $request->grace_minutes ?? 10,
            'break_minutes' => $request->break_minutes ?? 60,
            'working_hours' => $request->working_hours,
            'is_night_shift'=> $request->boolean('is_night_shift'),
            'working_days'  => $request->working_days,
        ]);

        return back()->with('success', 'Shift created successfully.');
    }

    // ── ASSIGN SHIFT TO EMPLOYEE ─────────────────────────────
    public function assignShift(Request $request)
    {
        $request->validate([
            'employee_id'    => 'required|exists:employees,id',
            'shift_id'       => 'required|exists:shifts,id',
            'effective_from' => 'required|date',
        ]);

        // Close previous shift assignment
        \App\Models\EmployeeShift::where('employee_id', $request->employee_id)
            ->where('is_current', true)
            ->update(['is_current' => false, 'effective_to' => now()]);

        \App\Models\EmployeeShift::create([
            'employee_id'    => $request->employee_id,
            'shift_id'       => $request->shift_id,
            'effective_from' => $request->effective_from,
            'is_current'     => true,
        ]);

        return back()->with('success', 'Shift assigned successfully.');
    }
}