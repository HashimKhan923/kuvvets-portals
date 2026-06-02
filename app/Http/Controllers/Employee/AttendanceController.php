<?php
namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AttendanceController extends Controller
{
    public function __construct(protected AttendanceService $service) {}

    public function checkIn(Request $request): JsonResponse
    {
        $data = $request->validate([
            'lat'      => 'nullable|numeric|between:-90,90',
            'lng'      => 'nullable|numeric|between:-180,180',
            'accuracy' => 'nullable|numeric',
            'qr_token' => 'nullable|string|max:64',
        ]);

        $emp_id = $request->user()->id;
        $employee = \App\Models\Employee::where('user_id', $emp_id)->first();
        $result   = $this->service->checkIn($employee, $data);

        $httpCode = $result['status'] === 'ok' ? 200 : 422;
        return response()->json($result, $httpCode);
    }

    public function checkOut(Request $request): JsonResponse
    {
        $data = $request->validate([
            'lat'      => 'nullable|numeric|between:-90,90',
            'lng'      => 'nullable|numeric|between:-180,180',
            'accuracy' => 'nullable|numeric',
            'qr_token' => 'nullable|string|max:64',
        ]);

        $emp_id = $request->user()->id;
        $employee = \App\Models\Employee::where('user_id', $emp_id)->first();
        $result   = $this->service->checkOut($employee, $data);

        $httpCode = $result['status'] === 'ok' ? 200 : 422;
        return response()->json($result, $httpCode);
    }

    public function status(Request $request): JsonResponse
    {
        $emp_id = $request->user()->id;
        $employee = \App\Models\Employee::where('user_id', $emp_id)->first()->load('activeLocations');
        $today = \App\Models\Attendance::with(['location'])
            ->where('employee_id', $employee->id)
            ->whereDate('date', now()->toDateString())
            ->first();

        $activeBreak = $today
            ? \App\Models\BreakSession::where('attendance_id', $today->id)->whereNull('ended_at')->first()
            : null;

        return response()->json([
            'checked_in'          => (bool) $today?->check_in,
            'checked_out'         => (bool) $today?->check_out,
            'check_in_at'         => $today?->check_in?->toIso8601String(),
            'check_out_at'        => $today?->check_out?->toIso8601String(),
            'location'            => $today?->location?->name,
            'status'              => $today?->status,
            'is_late'             => (bool) $today?->is_late,
            'late_minutes'        => $today?->late_minutes ?? 0,
            'working_minutes'     => $today?->live_working_minutes ?? 0,
            'break_minutes'       => $today?->break_minutes ?? 0,
            'early_leave_minutes' => $today?->early_leave_minutes ?? 0,
            'overtime_minutes'    => $today?->overtime_minutes ?? 0,
            'on_break'            => (bool) $activeBreak,
            'break_started_at'    => $activeBreak?->started_at?->toIso8601String(),
            'break_reason'        => $activeBreak?->reason,
        ]);
    }
}