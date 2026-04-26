<?php
namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\LeaveBalance;
use App\Models\Holiday;
use App\Models\Employee;
use App\Models\Department;
use App\Models\AuditLog;
use App\Services\LeaveService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Services\EmailService;

class LeaveController extends Controller
{
    // ── ADMIN DASHBOARD ──────────────────────────────────────
    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $query = LeaveRequest::with([
                'employee.department',
                'leaveType',
                'reviewer'
            ])
            ->where('company_id', $companyId);

        if ($request->filled('status'))
            $query->where('status', $request->status);

        if ($request->filled('department'))
            $query->whereHas('employee', fn($q) =>
                $q->where('department_id', $request->department)
            );

        if ($request->filled('leave_type'))
            $query->where('leave_type_id', $request->leave_type);

        if ($request->filled('month')) {
            $month = Carbon::parse($request->month);
            $query->where(function ($q) use ($month) {
                $q->whereMonth('from_date', $month->month)
                  ->whereYear('from_date',  $month->year);
            });
        }

        $requests    = $query->latest()->paginate(15)->withQueryString();
        $leaveTypes  = LeaveType::where('company_id', $companyId)->get();
        $departments = Department::where('company_id', $companyId)->get();

        $stats = [
            'pending'       => LeaveRequest::where('company_id', $companyId)->where('status', 'pending')->count(),
            'approved_month'=> LeaveRequest::where('company_id', $companyId)->where('status', 'approved')
                                ->whereMonth('from_date', now()->month)->count(),
            'on_leave_today'=> LeaveRequest::where('company_id', $companyId)->where('status', 'approved')
                                ->where('from_date', '<=', today())->where('to_date', '>=', today())->count(),
            'rejected_month'=> LeaveRequest::where('company_id', $companyId)->where('status', 'rejected')
                                ->whereMonth('from_date', now()->month)->count(),
        ];

        return view('leaves.index', compact(
            'requests', 'leaveTypes', 'departments', 'stats'
        ));
    }

    // ── APPROVE ───────────────────────────────────────────────
    public function approve(Request $request, LeaveRequest $leaveRequest)
    {
        $this->authorizeLeave($leaveRequest);

        if ($leaveRequest->status !== 'pending') {
            return back()->with('error', 'Only pending requests can be approved.');
        }

        $leaveRequest->update([
            'status'      => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'hr_notes'    => $request->hr_notes,
        ]);

        LeaveService::deductBalance($leaveRequest);
        AuditLog::log('leave_approved', $leaveRequest);
        EmailService::leaveApproved($leaveRequest);

        return back()->with('success',
            "{$leaveRequest->employee->full_name}'s leave approved."
        );
    }

    // ── REJECT ────────────────────────────────────────────────
    public function reject(Request $request, LeaveRequest $leaveRequest)
    {
        $this->authorizeLeave($leaveRequest);

        $request->validate([
            'rejection_reason' => 'required|string|min:5',
        ], ['rejection_reason.required' => 'Please provide a reason for rejection.']);

        $leaveRequest->update([
            'status'           => 'rejected',
            'reviewed_by'      => auth()->id(),
            'reviewed_at'      => now(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        LeaveService::restoreBalance($leaveRequest);
        AuditLog::log('leave_rejected', $leaveRequest);
        EmailService::leaveRejected($leaveRequest);

        return back()->with('success', 'Leave request rejected.');
    }

    // ── CREATE (HR adds leave for employee) ───────────────────
    public function create()
    {
        $companyId   = auth()->user()->company_id;
        $employees   = Employee::where('company_id', $companyId)
                        ->where('employment_status', 'active')
                        ->orderBy('first_name')->get();
        $leaveTypes  = LeaveType::where('company_id', $companyId)
                        ->where('is_active', true)->get();
        return view('leaves.create', compact('employees', 'leaveTypes'));
    }

    // ── STORE ─────────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'employee_id'   => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'from_date'     => 'required|date',
            'to_date'       => 'required|date|after_or_equal:from_date',
            'day_type'      => 'required|string',
            'reason'        => 'required|string|min:5',
            'document'      => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $companyId = auth()->user()->company_id;
        $fromDate  = Carbon::parse($request->from_date);
        $toDate    = Carbon::parse($request->to_date);

        // Calculate working days
        $totalDays = in_array($request->day_type, ['half_day_morning','half_day_afternoon'])
            ? 0.5
            : LeaveRequest::calculateWorkingDays($fromDate, $toDate, $companyId);

        if ($totalDays <= 0) {
            return back()->withErrors(['from_date' => 'The selected dates have no working days.'])
                         ->withInput();
        }

        // Check for overlapping leaves
        $overlap = LeaveRequest::where('employee_id', $request->employee_id)
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($q) use ($fromDate, $toDate) {
                $q->whereBetween('from_date', [$fromDate, $toDate])
                  ->orWhereBetween('to_date',   [$fromDate, $toDate])
                  ->orWhere(function ($q2) use ($fromDate, $toDate) {
                      $q2->where('from_date', '<=', $fromDate)
                         ->where('to_date',   '>=', $toDate);
                  });
            })->exists();

        if ($overlap) {
            return back()
                ->withErrors(['from_date' => 'Employee already has an overlapping leave request.'])
                ->withInput();
        }

        $docPath = null;
        if ($request->hasFile('document')) {
            $docPath = $request->file('document')->store('leave-docs', 'public');
        }

        $leaveRequest = LeaveRequest::create([
            'company_id'            => $companyId,
            'employee_id'           => $request->employee_id,
            'leave_type_id'         => $request->leave_type_id,
            'request_number'        => 'LV-' . strtoupper(Str::random(6)),
            'from_date'             => $fromDate,
            'to_date'               => $toDate,
            'total_days'            => $totalDays,
            'day_type'              => $request->day_type,
            'reason'                => $request->reason,
            'document_path'         => $docPath,
            'is_emergency'          => $request->boolean('is_emergency'),
            'contact_during_leave'  => $request->contact_during_leave,
            'status'                => 'pending',
        ]);

        // Add to pending balance
        LeaveBalance::where('employee_id', $request->employee_id)
            ->where('leave_type_id', $request->leave_type_id)
            ->where('year', $fromDate->year)
            ->increment('pending_days', $totalDays);

        AuditLog::log('leave_request_created', $leaveRequest);
        EmailService::leaveSubmitted($leaveRequest);

        return redirect()->route('leaves.index')
            ->with('success', "Leave request {$leaveRequest->request_number} created.");
    }

    // ── SHOW ──────────────────────────────────────────────────
    public function show(LeaveRequest $leaveRequest)
    {
        $this->authorizeLeave($leaveRequest);
        $leaveRequest->load(['employee.department', 'leaveType', 'reviewer']);
        return view('leaves.show', compact('leaveRequest'));
    }

    // ── CANCEL ────────────────────────────────────────────────
    public function cancel(LeaveRequest $leaveRequest)
    {
        $this->authorizeLeave($leaveRequest);

        if (!in_array($leaveRequest->status, ['pending', 'approved'])) {
            return back()->with('error', 'This leave cannot be cancelled.');
        }

        LeaveService::restoreBalance($leaveRequest);

        $leaveRequest->update([
            'status'      => 'cancelled',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        AuditLog::log('leave_cancelled', $leaveRequest);
        EmailService::leaveCancelled($leaveRequest);
        return back()->with('success', 'Leave request cancelled.');
    }

    // ── BALANCE MANAGEMENT ───────────────────────────────────
    public function balances(Request $request)
    {
        $companyId   = auth()->user()->company_id;
        $year        = $request->filled('year') ? (int)$request->year : now()->year;
        $leaveTypes  = LeaveType::where('company_id', $companyId)->get();
        $departments = Department::where('company_id', $companyId)->get();

        $query = Employee::with([
                "leaveBalances" => fn($q) => $q->where('year', $year)->with('leaveType')
            ])
            ->where('company_id', $companyId)
            ->where('employment_status', 'active');

        if ($request->filled('department'))
            $query->where('department_id', $request->department);

        $employees = $query->orderBy('first_name')->get();

        return view('leaves.balances', compact(
            'employees', 'leaveTypes', 'year', 'departments'
        ));
    }

    // ── ALLOCATE BALANCES ────────────────────────────────────
    public function allocate(Request $request)
    {
        $request->validate(['year' => 'required|integer|min:2020|max:2030']);
        $count = LeaveService::allocateAnnualLeaves(
            auth()->user()->company_id,
            $request->year
        );
        return back()->with('success',
            "Leave balances allocated for {$request->year} ({$count} records created)."
        );
    }

    // ── GRANT EXTRA DAYS ────────────────────────────────────
    public function grantExtra(Request $request)
    {
        $request->validate([
            'employee_id'   => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'days'          => 'required|numeric|min:0.5|max:30',
            'year'          => 'required|integer',
        ]);

        LeaveBalance::updateOrCreate(
            [
                'employee_id'   => $request->employee_id,
                'leave_type_id' => $request->leave_type_id,
                'year'          => $request->year,
            ],
            []
        );

        LeaveBalance::where('employee_id', $request->employee_id)
            ->where('leave_type_id', $request->leave_type_id)
            ->where('year', $request->year)
            ->increment('extra_granted', $request->days);

        return back()->with('success',
            "{$request->days} extra days granted successfully."
        );
    }

    // ── LEAVE TYPES ──────────────────────────────────────────
    public function types()
    {
        $companyId  = auth()->user()->company_id;
        $leaveTypes = LeaveType::where('company_id', $companyId)->get();
        return view('leaves.types', compact('leaveTypes'));
    }

    public function storeType(Request $request)
    {
        $request->validate([
            'name'                 => 'required|string|max:100',
            'code'                 => 'required|string|max:10',
            'days_per_year'        => 'required|integer|min:0',
            'color'                => 'nullable|string|max:7',
            'max_consecutive_days' => 'nullable|integer|min:1',
            'min_days_notice'      => 'nullable|integer|min:0',
        ]);

        LeaveType::create([
            'company_id'           => auth()->user()->company_id,
            'name'                 => $request->name,
            'code'                 => strtoupper($request->code),
            'description'          => $request->description,
            'days_per_year'        => $request->days_per_year,
            'is_paid'              => $request->boolean('is_paid', true),
            'requires_document'    => $request->boolean('requires_document'),
            'can_carry_forward'    => $request->boolean('can_carry_forward'),
            'max_carry_forward_days'=> $request->max_carry_forward_days ?? 0,
            'min_days_notice'      => $request->min_days_notice ?? 0,
            'max_consecutive_days' => $request->max_consecutive_days,
            'applicable_to_male'   => $request->boolean('applicable_to_male', true),
            'applicable_to_female' => $request->boolean('applicable_to_female', true),
            'color'                => $request->color ?? '#BA7517',
        ]);

        return back()->with('success', "Leave type \"{$request->name}\" created.");
    }

    // ── HOLIDAYS ─────────────────────────────────────────────
    public function holidays(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $year      = $request->filled('year') ? (int)$request->year : now()->year;
        $holidays  = Holiday::where('company_id', $companyId)
                        ->where('year', $year)
                        ->orderBy('date')->get();

        return view('leaves.holidays', compact('holidays', 'year'));
    }

    public function storeHoliday(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:150',
            'date'    => 'required|date',
            'date_to' => 'nullable|date|after_or_equal:date',
            'type'    => 'required|in:national,religious,company,optional',
            'year'    => 'required|integer',
        ]);

        Holiday::create([
            'company_id'   => auth()->user()->company_id,
            'name'         => $request->name,
            'date'         => $request->date,
            'date_to'      => $request->date_to,
            'type'         => $request->type,
            'is_recurring' => $request->boolean('is_recurring', true),
            'description'  => $request->description,
            'year'         => $request->year,
        ]);

        return back()->with('success', "Holiday \"{$request->name}\" added.");
    }

    public function destroyHoliday(Holiday $holiday)
    {
        $holiday->delete();
        return back()->with('success', 'Holiday removed.');
    }

    // ── CALENDAR ─────────────────────────────────────────────
    public function calendar(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $month     = $request->filled('month') ? $request->month : now()->format('Y-m');
        $start     = Carbon::parse($month . '-01')->startOfMonth();
        $end       = $start->copy()->endOfMonth();

        $leaves = LeaveRequest::with(['employee', 'leaveType'])
            ->where('company_id', $companyId)
            ->where('status', 'approved')
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('from_date', [$start, $end])
                  ->orWhereBetween('to_date',   [$start, $end])
                  ->orWhere(function ($q2) use ($start, $end) {
                      $q2->where('from_date', '<=', $start)
                         ->where('to_date',   '>=', $end);
                  });
            })->get();

        $holidays = Holiday::where('company_id', $companyId)
            ->where('year', $start->year)
            ->get();

        // Build calendar events
        $events = [];

        foreach ($leaves as $leave) {
            $events[] = [
                'type'  => 'leave',
                'date'  => $leave->from_date->format('Y-m-d'),
                'date_to'=> $leave->to_date->format('Y-m-d'),
                'label' => $leave->employee->full_name,
                'sub'   => $leave->leaveType->name,
                'color' => $leave->leaveType->color,
                'days'  => $leave->total_days,
            ];
        }

        foreach ($holidays as $holiday) {
            $events[] = [
                'type'  => 'holiday',
                'date'  => $holiday->date->format('Y-m-d'),
                'date_to'=> ($holiday->date_to ?? $holiday->date)->format('Y-m-d'),
                'label' => $holiday->name,
                'sub'   => ucfirst($holiday->type),
                'color' => match($holiday->type) {
                    'national'  => '#378ADD',
                    'religious' => '#EF9F27',
                    'company'   => '#4CAF50',
                    default     => '#7F77DD',
                },
            ];
        }

        return view('leaves.calendar', compact(
            'events', 'month', 'start', 'end', 'leaves', 'holidays'
        ));
    }

    // ── REPORT ───────────────────────────────────────────────
    public function report(Request $request)
    {
        $companyId   = auth()->user()->company_id;
        $year        = $request->filled('year') ? (int)$request->year : now()->year;
        $leaveTypes  = LeaveType::where('company_id', $companyId)->get();
        $departments = Department::where('company_id', $companyId)->get();

        $query = Employee::with(['department'])
            ->where('company_id', $companyId)
            ->where('employment_status', 'active');

        if ($request->filled('department'))
            $query->where('department_id', $request->department);

        $employees = $query->orderBy('first_name')->get();

        $report = $employees->map(function ($emp) use ($year, $leaveTypes) {
            $requests = LeaveRequest::where('employee_id', $emp->id)
                ->whereYear('from_date', $year)
                ->where('status', 'approved')
                ->get();

            $byType = $leaveTypes->mapWithKeys(fn($lt) => [
                $lt->id => $requests->where('leave_type_id', $lt->id)->sum('total_days')
            ]);

            return [
                'employee'  => $emp,
                'by_type'   => $byType,
                'total'     => $requests->sum('total_days'),
                'pending'   => LeaveRequest::where('employee_id', $emp->id)
                                ->whereYear('from_date', $year)
                                ->where('status', 'pending')->count(),
            ];
        });

        return view('leaves.report', compact(
            'report', 'leaveTypes', 'year', 'departments'
        ));
    }

    private function authorizeLeave(LeaveRequest $leave): void {
        if ($leave->company_id !== auth()->user()->company_id) abort(403);
    }
}