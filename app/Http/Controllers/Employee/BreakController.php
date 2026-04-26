<?php
namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Services\BreakService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BreakController extends Controller
{
    public function __construct(protected BreakService $service) {}

    public function start(Request $request): JsonResponse
    {
        $data = $request->validate([
            'reason' => 'nullable|in:lunch,prayer,tea,personal',
        ]);

        $result = $this->service->start($request->user()->employee, $data['reason'] ?? null);
        return response()->json($result, $result['status'] === 'ok' ? 200 : 422);
    }

    public function end(Request $request): JsonResponse
    {
        $result = $this->service->end($request->user()->employee);
        return response()->json($result, $result['status'] === 'ok' ? 200 : 422);
    }
}