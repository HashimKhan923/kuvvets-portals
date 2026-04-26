<?php
namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Services\EmployeeLeaveService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use App\Services\EmailService;

class LeaveController extends Controller
{
    public function __construct(protected EmployeeLeaveService $service) {}

    /**
     * Main leaves page — balances + history in tabs.
     */
    public function index(Request $request)
    {
        $employee = $request->user()->employee;
        $year = (int) $request->input('year', now()->year);

        $balances = $this->service->balancesFor($employee, $year);

        // Filter history
        $statusFilter = $request->input('status');
        $history = LeaveRequest::with('leaveType','reviewer')
            ->where('employee_id', $employee->id)
            ->when($statusFilter, fn($q) => $q->where('status', $statusFilter))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        // Stats
        $stats = [
            'pending'  => LeaveRequest::where('employee_id', $employee->id)->where('status','pending')->count(),
            'approved' => LeaveRequest::where('employee_id', $employee->id)->where('status','approved')->whereYear('from_date', $year)->count(),
            'rejected' => LeaveRequest::where('employee_id', $employee->id)->where('status','rejected')->whereYear('from_date', $year)->count(),
            'total_days_used' => LeaveRequest::where('employee_id', $employee->id)
                ->where('status','approved')
                ->whereYear('from_date', $year)
                ->sum('total_days'),
        ];

        return view('employee.leaves.index', compact('employee','year','balances','history','stats','statusFilter'));
    }

    /**
     * Apply form.
     */
    public function create(Request $request)
    {
        $employee = $request->user()->employee;
        $balances = $this->service->balancesFor($employee);
        $leaveTypes = $balances->pluck('leaveType')->filter();

        return view('employee.leaves.create', compact('employee','leaveTypes','balances'));
    }

    /**
     * Submit application.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'leave_type_id'        => 'required|exists:leave_types,id',
            'from_date'            => 'required|date|after_or_equal:'.now()->subYear()->toDateString(),
            'to_date'              => 'required|date|after_or_equal:from_date',
            'day_type'             => 'required|in:full_day,half_day_morning,half_day_afternoon',
            'reason'               => 'required|string|min:10|max:1000',
            'is_emergency'         => 'nullable|boolean',
            'contact_during_leave' => 'nullable|string|max:100',
            'document'             => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $result = $this->service->create(
            $request->user()->employee,
            $data,
            $request->file('document')
        );

        if ($result['status'] !== 'ok') {
            return back()->withInput()->with('error', $result['message']);
        }

        EmailService::leaveSubmitted($result['request']->load(['employee','leaveType']));

        return redirect()->route('employee.leaves.index')
            ->with('success', $result['message'].' Request #: '.$result['request']->request_number);
    }

    /**
     * Detail view / drawer.
     */
    public function show(Request $request, LeaveRequest $leaveRequest): JsonResponse
    {
        if ($leaveRequest->employee_id !== $request->user()->employee->id) {
            abort(403);
        }

        $leaveRequest->load('leaveType','reviewer','employee');

        return response()->json([
            'html' => view('employee.leaves._detail_drawer', ['lr' => $leaveRequest])->render(),
        ]);
    }

    /**
     * Cancel a request.
     */
    public function cancel(Request $request, LeaveRequest $leaveRequest)
    {
        $result = $this->service->cancel($request->user()->employee, $leaveRequest);

        if ($request->expectsJson()) {
            return response()->json($result, $result['status']==='ok' ? 200 : 422);
        }

        if ($result['status'] === 'ok') {
            EmailService::leaveCancelled($leaveRequest->fresh()->load(['employee','leaveType']));
        }

        return back()->with($result['status']==='ok' ? 'success' : 'error', $result['message']);
    }

    /**
     * AJAX: calculate days for given date range.
     */
    public function calculate(Request $request): JsonResponse
    {
        $data = $request->validate([
            'from_date' => 'required|date',
            'to_date'   => 'required|date',
            'day_type'  => 'nullable|in:full_day,half_day_morning,half_day_afternoon',
        ]);

        $calc = $this->service->calculateDays(
            $request->user()->employee,
            $data['from_date'],
            $data['to_date'],
            $data['day_type'] ?? 'full_day'
        );

        return response()->json($calc);
    }

    /**
     * Download attached document.
     */
    public function downloadDocument(Request $request, LeaveRequest $leaveRequest)
    {
        if ($leaveRequest->employee_id !== $request->user()->employee->id) abort(403);
        if (!$leaveRequest->document_path) abort(404);

        $path = $leaveRequest->document_path;
        if (!Storage::disk('public')->exists($path)) abort(404);

        return response()->download(
            Storage::disk('public')->path($path),
            'leave-'.$leaveRequest->request_number.'-document.'.pathinfo($path, PATHINFO_EXTENSION)
        );
    }
}