<?php
namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Department;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\LeaveBalance;
use App\Models\PayrollPeriod;
use App\Models\Payslip;
use App\Models\PerformanceCycle;
use App\Models\Appraisal;
use App\Models\TrainingSession;
use App\Models\TrainingEnrollment;
use App\Models\Asset;
use App\Models\MaintenanceRecord;
use App\Models\Document;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    // ── REPORTS HUB ──────────────────────────────────────────
    public function index()
    {
        $companyId = auth()->user()->company_id;

        // Quick stats for each report category
        $overview = [
            'employees'   => Employee::where('company_id', $companyId)
                ->where('employment_status', 'active')->count(),
            'departments' => Department::where('company_id', $companyId)->count(),
            'payroll_ytd' => PayrollPeriod::where('company_id', $companyId)
                ->where('status', 'paid')
                ->whereYear('created_at', now()->year)
                ->sum('total_net'),
            'open_leaves' => LeaveRequest::whereHas('employee',
                fn($q) => $q->where('company_id', $companyId))
                ->where('status', 'pending')->count(),
            'assets'      => Asset::where('company_id', $companyId)->count(),
            'documents'   => Document::where('company_id', $companyId)->count(),
        ];

        return view('reports.index', compact('overview'));
    }

    // ── WORKFORCE REPORT ─────────────────────────────────────
    public function workforce(Request $request)
    {
        $companyId   = auth()->user()->company_id;
        $departments = Department::where('company_id', $companyId)->get();

        // Headcount by department
        $headcountByDept = Department::where('company_id', $companyId)
            ->withCount(['employees as active_count' => fn($q) =>
                $q->where('employment_status', 'active')])
            ->get();

        // Employment type breakdown
        $byType = Employee::where('company_id', $companyId)
            ->where('employment_status', 'active')
            ->selectRaw('employment_type, COUNT(*) as count')
            ->groupBy('employment_type')
            ->pluck('count', 'employment_type');

        // Gender breakdown
        $byGender = Employee::where('company_id', $companyId)
            ->where('employment_status', 'active')
            ->selectRaw('gender, COUNT(*) as count')
            ->groupBy('gender')
            ->pluck('count', 'gender');

        // Status breakdown
        $byStatus = Employee::where('company_id', $companyId)
            ->selectRaw('employment_status, COUNT(*) as count')
            ->groupBy('employment_status')
            ->pluck('count', 'employment_status');

        // Monthly joining trend (last 12 months)
        $joiningTrend = [];
        for ($i = 11; $i >= 0; $i--) {
            $d = now()->subMonths($i);
            $joiningTrend[] = [
                'month' => $d->format('M Y'),
                'count' => Employee::where('company_id', $companyId)
                    ->whereMonth('joining_date', $d->month)
                    ->whereYear('joining_date', $d->year)
                    ->count(),
            ];
        }

        // Age distribution
        $ageGroups = [
            'Under 25'  => Employee::where('company_id', $companyId)
                ->where('employment_status', 'active')
                ->whereNotNull('date_of_birth')
                ->whereRaw('TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) < 25')->count(),
            '25–34'     => Employee::where('company_id', $companyId)
                ->where('employment_status', 'active')
                ->whereNotNull('date_of_birth')
                ->whereRaw('TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) BETWEEN 25 AND 34')->count(),
            '35–44'     => Employee::where('company_id', $companyId)
                ->where('employment_status', 'active')
                ->whereNotNull('date_of_birth')
                ->whereRaw('TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) BETWEEN 35 AND 44')->count(),
            '45–54'     => Employee::where('company_id', $companyId)
                ->where('employment_status', 'active')
                ->whereNotNull('date_of_birth')
                ->whereRaw('TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) BETWEEN 45 AND 54')->count(),
            '55+'       => Employee::where('company_id', $companyId)
                ->where('employment_status', 'active')
                ->whereNotNull('date_of_birth')
                ->whereRaw('TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) >= 55')->count(),
        ];

        // Tenure distribution
        $tenureGroups = [
            '< 1 Year'  => Employee::where('company_id', $companyId)
                ->where('employment_status', 'active')
                ->whereRaw('TIMESTAMPDIFF(YEAR, joining_date, CURDATE()) < 1')->count(),
            '1–3 Years' => Employee::where('company_id', $companyId)
                ->where('employment_status', 'active')
                ->whereRaw('TIMESTAMPDIFF(YEAR, joining_date, CURDATE()) BETWEEN 1 AND 3')->count(),
            '3–5 Years' => Employee::where('company_id', $companyId)
                ->where('employment_status', 'active')
                ->whereRaw('TIMESTAMPDIFF(YEAR, joining_date, CURDATE()) BETWEEN 3 AND 5')->count(),
            '5–10 Years'=> Employee::where('company_id', $companyId)
                ->where('employment_status', 'active')
                ->whereRaw('TIMESTAMPDIFF(YEAR, joining_date, CURDATE()) BETWEEN 5 AND 10')->count(),
            '10+ Years' => Employee::where('company_id', $companyId)
                ->where('employment_status', 'active')
                ->whereRaw('TIMESTAMPDIFF(YEAR, joining_date, CURDATE()) >= 10')->count(),
        ];

        // Employee list
        $query = Employee::with(['department', 'designation'])
            ->where('company_id', $companyId);

        if ($request->filled('department'))
            $query->where('department_id', $request->department);
        if ($request->filled('status'))
            $query->where('employment_status', $request->status);

        $employees = $query->orderBy('first_name')->get();

        return view('reports.workforce', compact(
            'headcountByDept', 'byType', 'byGender', 'byStatus',
            'joiningTrend', 'ageGroups', 'tenureGroups',
            'employees', 'departments'
        ));
    }

    // ── ATTENDANCE REPORT ────────────────────────────────────
    public function attendance(Request $request)
    {
        $companyId   = auth()->user()->company_id;
        $departments = Department::where('company_id', $companyId)->get();
        $month       = $request->filled('month') ? (int)$request->month : now()->month;
        $year        = $request->filled('year')  ? (int)$request->year  : now()->year;

        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        // Working days
        $workingDays = 0;
        $cur = $start->copy();
        while ($cur->lte($end)) {
            if (!$cur->isWeekend()) $workingDays++;
            $cur->addDay();
        }

        // Summary stats
        $totalAttendance = Attendance::whereHas('employee',
            fn($q) => $q->where('company_id', $companyId))
            ->whereBetween('date', [$start, $end]);

        $stats = [
            'working_days'  => $workingDays,
            'total_present' => (clone $totalAttendance)->whereIn('status',['present','late','work_from_home'])->count(),
            'total_absent'  => (clone $totalAttendance)->where('status','absent')->count(),
            'total_late'    => (clone $totalAttendance)->where('status','late')->count(),
            'total_ot_hrs'  => round((clone $totalAttendance)->sum('overtime_minutes') / 60, 1),
        ];

        // Daily attendance trend
        $dailyTrend = [];
        $cur = $start->copy();
        while ($cur->lte($end)) {
            if (!$cur->isWeekend()) {
                $dailyTrend[] = [
                    'date'    => $cur->format('d M'),
                    'present' => Attendance::whereHas('employee',
                        fn($q) => $q->where('company_id', $companyId))
                        ->where('date', $cur->toDateString())
                        ->whereIn('status', ['present','late','work_from_home'])
                        ->count(),
                    'absent'  => Attendance::whereHas('employee',
                        fn($q) => $q->where('company_id', $companyId))
                        ->where('date', $cur->toDateString())
                        ->where('status', 'absent')
                        ->count(),
                ];
            }
            $cur->addDay();
        }

        // Per-employee summary
        $query = Employee::with(['department'])
            ->where('company_id', $companyId)
            ->where('employment_status', 'active');

        if ($request->filled('department'))
            $query->where('department_id', $request->department);

        $employees = $query->orderBy('first_name')->get();

        $employeeReport = $employees->map(function ($emp) use ($start, $end, $workingDays) {
            $records  = Attendance::where('employee_id', $emp->id)
                ->whereBetween('date', [$start, $end])->get();
            $present  = $records->whereIn('status', ['present','late','work_from_home'])->count();
            $absent   = max(0, $workingDays - $present);
            $late     = $records->where('status', 'late')->count();
            $otHrs    = round($records->sum('overtime_minutes') / 60, 1);

            return [
                'employee'    => $emp,
                'present'     => $present,
                'absent'      => $absent,
                'late'        => $late,
                'ot_hours'    => $otHrs,
                'attendance_pct' => $workingDays > 0
                    ? round(($present / $workingDays) * 100, 1)
                    : 0,
            ];
        });

        return view('reports.attendance', compact(
            'stats', 'dailyTrend', 'employeeReport',
            'departments', 'month', 'year'
        ));
    }

    // ── LEAVE REPORT ─────────────────────────────────────────
    public function leave(Request $request)
    {
        $companyId   = auth()->user()->company_id;
        $departments = Department::where('company_id', $companyId)->get();
        $year        = $request->filled('year') ? (int)$request->year : now()->year;

        // Leave stats
        $stats = [
            'total_requests' => LeaveRequest::whereHas('employee',
                fn($q) => $q->where('company_id', $companyId))
                ->whereYear('from_date', $year)->count(),
            'approved'       => LeaveRequest::whereHas('employee',
                fn($q) => $q->where('company_id', $companyId))
                ->where('status', 'approved')->whereYear('from_date', $year)->count(),
            'pending'        => LeaveRequest::whereHas('employee',
                fn($q) => $q->where('company_id', $companyId))
                ->where('status', 'pending')->count(),
            'rejected'       => LeaveRequest::whereHas('employee',
                fn($q) => $q->where('company_id', $companyId))
                ->where('status', 'rejected')->whereYear('from_date', $year)->count(),
            'total_days'     => LeaveRequest::whereHas('employee',
                fn($q) => $q->where('company_id', $companyId))
                ->where('status', 'approved')->whereYear('from_date', $year)->sum('total_days'),
        ];

        // Monthly leave trend
        $monthlyTrend = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthlyTrend[] = [
                'month' => Carbon::create($year, $m)->format('M'),
                'count' => LeaveRequest::whereHas('employee',
                    fn($q) => $q->where('company_id', $companyId))
                    ->where('status', 'approved')
                    ->whereMonth('from_date', $m)
                    ->whereYear('from_date', $year)->count(),
            ];
        }

        // By leave type
        $byType = LeaveRequest::with('leaveType')
            ->whereHas('employee', fn($q) => $q->where('company_id', $companyId))
            ->where('status', 'approved')
            ->whereYear('from_date', $year)
            ->selectRaw('leave_type_id, SUM(total_days) as total_days, COUNT(*) as count')
            ->groupBy('leave_type_id')
            ->with('leaveType')
            ->get();

        // Per-employee summary
        $query = Employee::with(['department'])
            ->where('company_id', $companyId)
            ->where('employment_status', 'active');

        if ($request->filled('department'))
            $query->where('department_id', $request->department);

        $employees = $query->orderBy('first_name')->get();

        $employeeLeave = $employees->map(function ($emp) use ($year) {
            $requests = LeaveRequest::where('employee_id', $emp->id)
                ->whereYear('from_date', $year)->get();
            return [
                'employee'  => $emp,
                'total'     => $requests->count(),
                'approved'  => $requests->where('status', 'approved')->count(),
                'pending'   => $requests->where('status', 'pending')->count(),
                'days_taken'=> $requests->where('status', 'approved')->sum('total_days'),
            ];
        });

        return view('reports.leave', compact(
            'stats', 'monthlyTrend', 'byType',
            'employeeLeave', 'departments', 'year'
        ));
    }

    // ── PAYROLL REPORT (redirect to payroll module) ──────────
    public function payroll(Request $request)
    {
        return redirect()->route('payroll.report');
    }

    // ── PERFORMANCE REPORT (redirect to performance module) ──
    public function performance(Request $request)
    {
        return redirect()->route('performance.report');
    }

    // ── MASTER EMPLOYEE EXPORT (CSV) ─────────────────────────
    public function exportEmployeesCsv(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $query = Employee::with(['department', 'designation'])
            ->where('company_id', $companyId);

        if ($request->filled('status'))
            $query->where('employment_status', $request->status);

        $employees = $query->orderBy('first_name')->get();

        $filename = 'employees-' . now()->format('Y-m-d') . '.csv';
        $headers  = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($employees) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Employee ID', 'First Name', 'Last Name', 'Email',
                'Phone', 'CNIC', 'Department', 'Designation',
                'Employment Type', 'Status', 'Date of Joining',
                'Date of Birth', 'Gender', 'City',
                'Bank Name', 'Bank Account', 'IBAN',
                'EOBI Number', 'Basic Salary',
            ]);

            foreach ($employees as $emp) {
                fputcsv($handle, [
                    $emp->employee_id,
                    $emp->first_name,
                    $emp->last_name,
                    $emp->email,
                    $emp->phone,
                    $emp->cnic,
                    $emp->department?->name ?? '',
                    $emp->designation?->title ?? '',
                    $emp->employment_type,
                    $emp->employment_status,
                    $emp->date_of_joining?->format('d M Y') ?? '',
                    $emp->date_of_birth?->format('d M Y') ?? '',
                    $emp->gender,
                    $emp->city,
                    $emp->bank_name ?? '',
                    $emp->bank_account_no ?? '',
                    $emp->bank_iban ?? '',
                    $emp->eobi_number ?? '',
                    $emp->salaryStructure?->basic_salary ?? '',
                ]);
            }
            fclose($handle);
        };

        AuditLog::log('employee_data_exported', null, [], [
            'count' => $employees->count(),
            'ip'    => request()->ip(),
        ]);

        return response()->stream($callback, 200, $headers);
    }
}