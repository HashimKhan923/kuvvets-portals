<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Shift;
use App\Models\Department;
use App\Models\ShiftAssignmentLog;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShiftAssignmentController extends Controller
{
    // ── INDEX ────────────────────────────────────────────────
    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $query = Employee::with(['department', 'shift'])
            ->where('company_id', $companyId)
            ->where('employment_status', 'active');

        if ($request->filled('shift_filter')) {
            $query->where('shift_id', $request->shift_filter);
        }

        if ($request->filled('dept_filter')) {
            $query->where('department_id', $request->dept_filter);
        }

        $employees = $query->orderBy('first_name')->paginate(20)->withQueryString();

        $allEmployees = Employee::with(['department', 'shift'])
            ->where('company_id', $companyId)
            ->where('employment_status', 'active')
            ->orderBy('first_name')
            ->get();

        $shifts      = Shift::where('company_id', $companyId)->where('is_active', true)->get();
        $departments = Department::where('company_id', $companyId)->where('is_active', true)->get();

        $unassignedEmployees = Employee::with('department')
            ->where('company_id', $companyId)
            ->where('employment_status', 'active')
            ->whereNull('shift_id')
            ->orderBy('first_name')
            ->get();

        $stats = [
            'total'        => Employee::where('company_id', $companyId)->where('employment_status', 'active')->count(),
            'assigned'     => Employee::where('company_id', $companyId)->where('employment_status', 'active')->whereNotNull('shift_id')->count(),
            'unassigned'   => Employee::where('company_id', $companyId)->where('employment_status', 'active')->whereNull('shift_id')->count(),
            'total_shifts' => $shifts->count(),
        ];

        return view('attendance.shift-assignment', compact(
            'employees',
            'allEmployees',
            'shifts',
            'departments',
            'unassignedEmployees',
            'stats'
        ));
    }

    // ── STORE (Single Assignment) ────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'employee_id'    => 'required|exists:employees,id',
            'shift_id'       => 'required',
            'effective_from' => 'required|date',
            'notes'          => 'nullable|string|max:255',
        ]);

        $employee = Employee::where('company_id', auth()->user()->company_id)
            ->findOrFail($request->employee_id);

        $oldShiftId = $employee->shift_id;
        $newShiftId = $request->shift_id === 'none' ? null : $request->shift_id;

        // Validate shift belongs to company
        if ($newShiftId) {
            $shift = Shift::where('company_id', auth()->user()->company_id)
                ->findOrFail($newShiftId);
        }

        $employee->update([
            'shift_id'             => $newShiftId,
            'shift_effective_from' => $request->effective_from,
        ]);

        // Log the change
        $this->logShiftChange(
            $employee,
            $oldShiftId,
            $newShiftId,
            $request->effective_from,
            $request->notes
        );

        $message = $newShiftId
            ? 'Shift assigned to ' . $employee->full_name . ' successfully.'
            : 'Shift removed from ' . $employee->full_name . '.';

        return redirect()->route('attendance.shift-assignment')
            ->with('success', $message);
    }

    // ── BULK ASSIGN ──────────────────────────────────────────
    public function bulk(Request $request)
    {
        $request->validate([
            'employee_ids'   => 'required|array|min:1',
            'employee_ids.*' => 'exists:employees,id',
            'shift_id'       => 'required|exists:shifts,id',
            'effective_from' => 'required|date',
            'notes'          => 'nullable|string|max:255',
        ]);

        $companyId = auth()->user()->company_id;

        // Validate shift belongs to company
        $shift = Shift::where('company_id', $companyId)->findOrFail($request->shift_id);

        $employees = Employee::where('company_id', $companyId)
            ->whereIn('id', $request->employee_ids)
            ->get();

        $count = 0;

        DB::transaction(function () use ($employees, $shift, $request, &$count) {
            foreach ($employees as $employee) {
                $oldShiftId = $employee->shift_id;

                $employee->update([
                    'shift_id'             => $shift->id,
                    'shift_effective_from' => $request->effective_from,
                ]);

                $this->logShiftChange(
                    $employee,
                    $oldShiftId,
                    $shift->id,
                    $request->effective_from,
                    $request->notes ?? 'Bulk assignment'
                );

                $count++;
            }
        });

        return redirect()->route('attendance.shift-assignment')
            ->with('success', $shift->name . ' assigned to ' . $count . ' employee(s) successfully.');
    }

    // ── REMOVE SHIFT ─────────────────────────────────────────
    public function remove(Employee $employee)
    {
        $this->authorizeCompany($employee);

        $oldShiftId = $employee->shift_id;

        $employee->update([
            'shift_id'             => null,
            'shift_effective_from' => null,
        ]);

        $this->logShiftChange($employee, $oldShiftId, null, today(), 'Shift removed');

        return back()->with('success', 'Shift removed from ' . $employee->full_name . '.');
    }

    // ── ASSIGNMENT HISTORY ───────────────────────────────────
    public function history(Employee $employee)
    {
        $this->authorizeCompany($employee);

        $history = ShiftAssignmentLog::with(['shift', 'assignedBy'])
            ->where('employee_id', $employee->id)
            ->orderByDesc('effective_from')
            ->get();

        return response()->json($history);
    }

    // ── HELPERS ──────────────────────────────────────────────
    private function logShiftChange(
        Employee $employee,
        ?int $oldShiftId,
        ?int $newShiftId,
        string $effectiveFrom,
        ?string $notes = null
    ): void {
        // Only log if ShiftAssignmentLog model/table exists
        if (class_exists(\App\Models\ShiftAssignmentLog::class)) {
            ShiftAssignmentLog::create([
                'employee_id'    => $employee->id,
                'old_shift_id'   => $oldShiftId,
                'shift_id'       => $newShiftId,
                'effective_from' => $effectiveFrom,
                'notes'          => $notes,
                'assigned_by'    => auth()->id(),
            ]);
        }

        AuditLog::log('shift_assigned', $employee, [
            'old_shift_id' => $oldShiftId,
        ], [
            'new_shift_id'   => $newShiftId,
            'effective_from' => $effectiveFrom,
            'notes'          => $notes,
        ]);
    }

    private function authorizeCompany(Employee $employee): void
    {
        if ($employee->company_id !== auth()->user()->company_id) {
            abort(403, 'Unauthorized');
        }
    }
}