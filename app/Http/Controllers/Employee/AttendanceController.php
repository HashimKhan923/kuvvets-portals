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

        $employee = $request->user()->employee;
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

        $employee = $request->user()->employee;
        $result   = $this->service->checkOut($employee, $data);

        $httpCode = $result['status'] === 'ok' ? 200 : 422;
        return response()->json($result, $httpCode);
    }

    public function status(Request $request): JsonResponse
    {
        $employee = $request->user()->employee;
        $today = \App\Models\Attendance::where('employee_id', $employee->id)
            ->whereDate('date', now()->toDateString())
            ->first();

        return response()->json([
            'checked_in'   => (bool) $today?->check_in,
            'checked_out'  => (bool) $today?->check_out,
            'check_in_at'  => $today?->check_in?->toIso8601String(),
            'check_out_at' => $today?->check_out?->toIso8601String(),
            'location'     => $today?->location?->name,
            'status'       => $today?->status,
        ]);
    }
}