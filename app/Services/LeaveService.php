<?php
namespace App\Services;

use App\Models\Employee;
use App\Models\LeaveType;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;

class LeaveService
{
    // Allocate annual leave balances for all active employees
    public static function allocateAnnualLeaves(int $companyId, int $year): int
    {
        $employees  = Employee::where('company_id', $companyId)
                        ->where('employment_status', 'active')->get();
        $leaveTypes = LeaveType::where('company_id', $companyId)
                        ->where('is_active', true)->get();
        $count = 0;

        foreach ($employees as $emp) {
            foreach ($leaveTypes as $type) {
                // Gender-based eligibility
                if ($emp->gender === 'male'   && !$type->applicable_to_male)   continue;
                if ($emp->gender === 'female' && !$type->applicable_to_female) continue;

                // Get previous year balance for carry forward
                $prevBalance    = LeaveBalance::where('employee_id', $emp->id)
                                    ->where('leave_type_id', $type->id)
                                    ->where('year', $year - 1)->first();
                $carryForward   = 0;
                if ($type->can_carry_forward && $prevBalance) {
                    $remaining    = $prevBalance->available;
                    $carryForward = min($remaining, $type->max_carry_forward_days);
                }

                LeaveBalance::firstOrCreate(
                    [
                        'employee_id'   => $emp->id,
                        'leave_type_id' => $type->id,
                        'year'          => $year,
                    ],
                    [
                        'allocated_days'  => $type->days_per_year,
                        'carried_forward' => $carryForward,
                        'used_days'       => 0,
                        'pending_days'    => 0,
                    ]
                );
                $count++;
            }
        }

        return $count;
    }

    // Get all leave balances for an employee for the current year
    public static function getBalances(int $employeeId, int $year): \Illuminate\Support\Collection
    {
        return LeaveBalance::where('employee_id', $employeeId)
            ->where('year', $year)
            ->with('leaveType')
            ->get();
    }

    // Check if employee has enough balance
    public static function hasBalance(
        int $employeeId,
        int $leaveTypeId,
        float $days,
        int $year
    ): bool {
        $balance = LeaveBalance::where('employee_id', $employeeId)
            ->where('leave_type_id', $leaveTypeId)
            ->where('year', $year)->first();

        if (!$balance) return false;
        return $balance->available >= $days;
    }

    // Deduct from balance when approved
    public static function deductBalance(LeaveRequest $request): void
    {
        $year = $request->from_date->year;

        LeaveBalance::where('employee_id', $request->employee_id)
            ->where('leave_type_id', $request->leave_type_id)
            ->where('year', $year)
            ->increment('used_days', $request->total_days);

        LeaveBalance::where('employee_id', $request->employee_id)
            ->where('leave_type_id', $request->leave_type_id)
            ->where('year', $year)
            ->decrement('pending_days', $request->total_days);
    }

    // Restore balance when rejected or cancelled
    public static function restoreBalance(LeaveRequest $request): void
    {
        $year = $request->from_date->year;

        if ($request->status === 'approved') {
            LeaveBalance::where('employee_id', $request->employee_id)
                ->where('leave_type_id', $request->leave_type_id)
                ->where('year', $year)
                ->decrement('used_days', $request->total_days);
        } else {
            LeaveBalance::where('employee_id', $request->employee_id)
                ->where('leave_type_id', $request->leave_type_id)
                ->where('year', $year)
                ->decrement('pending_days', $request->total_days);
        }
    }
}