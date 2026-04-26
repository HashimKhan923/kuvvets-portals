<?php
namespace App\Services;

use App\Models\Employee;
use App\Models\Holiday;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EmployeeLeaveService
{
    /**
     * Get all leave balances for employee in a given year.
     * Auto-creates rows for leave types that apply to this employee's gender.
     */
    public function balancesFor(Employee $employee, int $year = null): \Illuminate\Support\Collection
    {
        $year = $year ?? now()->year;

        $types = LeaveType::where('company_id', $employee->company_id)
            ->where('is_active', true)
            ->when($employee->gender === 'male',   fn($q) => $q->where('applicable_to_male', true))
            ->when($employee->gender === 'female', fn($q) => $q->where('applicable_to_female', true))
            ->orderBy('name')
            ->get();

        $balances = LeaveBalance::where('employee_id', $employee->id)
            ->where('year', $year)
            ->get()->keyBy('leave_type_id');

        return $types->map(function ($type) use ($balances, $employee, $year) {
            $bal = $balances->get($type->id);

            if (!$bal) {
                // Auto-create row with allocation from leave type
                $bal = LeaveBalance::create([
                    'employee_id'     => $employee->id,
                    'leave_type_id'   => $type->id,
                    'year'            => $year,
                    'allocated_days'  => $type->days_per_year,
                    'used_days'       => 0,
                    'pending_days'    => 0,
                    'carried_forward' => 0,
                    'extra_granted'   => 0,
                ]);
            }
            $bal->setRelation('leaveType', $type);
            return $bal;
        });
    }

    /**
     * Calculate leave days between two dates excluding weekends & holidays.
     */
    public function calculateDays(
        Employee $employee,
        string $from,
        string $to,
        string $dayType = 'full_day'
    ): array {
        $start = Carbon::parse($from);
        $end   = Carbon::parse($to);

        if ($end->lt($start)) {
            return ['days' => 0, 'working' => 0, 'weekend' => 0, 'holiday' => 0, 'dates' => []];
        }

        if ($dayType !== 'full_day') {
            // Half-day — must be same-day
            if (!$start->isSameDay($end)) {
                return ['error' => 'Half-day leave must be on a single date.'];
            }
            return [
                'days'     => 0.5,
                'working'  => 0.5,
                'weekend'  => 0,
                'holiday'  => 0,
                'dates'    => [['date'=>$start->toDateString(),'kind'=>'working','label'=>'Half day']],
            ];
        }

        // Get holidays in range
        $holidaysRaw = Holiday::where('company_id', $employee->company_id)
            ->where(function($q) use ($start, $end) {
                $q->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                  ->orWhere(function($q2) use ($start, $end) {
                      $q2->where('date', '<=', $start->toDateString())
                         ->whereNotNull('date_to')
                         ->where('date_to', '>=', $end->toDateString());
                  });
            })->get();
        $holidayDates = [];
        foreach ($holidaysRaw as $h) {
            $cursor = $h->date->copy();
            $stop   = $h->date_to ?: $h->date;
            while ($cursor->lte($stop)) {
                $holidayDates[$cursor->toDateString()] = $h->name;
                $cursor->addDay();
            }
        }

        $working = 0;
        $weekend = 0;
        $holiday = 0;
        $dates = [];
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            $key = $cursor->toDateString();
            if (isset($holidayDates[$key])) {
                $holiday++;
                $dates[] = ['date'=>$key,'kind'=>'holiday','label'=>$holidayDates[$key]];
            } elseif (in_array($cursor->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY])) {
                $weekend++;
                $dates[] = ['date'=>$key,'kind'=>'weekend','label'=>'Weekend'];
            } else {
                $working++;
                $dates[] = ['date'=>$key,'kind'=>'working','label'=>'Working day'];
            }
            $cursor->addDay();
        }

        return [
            'days'    => $working,
            'working' => $working,
            'weekend' => $weekend,
            'holiday' => $holiday,
            'dates'   => $dates,
        ];
    }

    /**
     * Check if dates overlap with an existing pending/approved request.
     */
    public function hasOverlap(Employee $employee, string $from, string $to, ?int $ignoreId = null): ?LeaveRequest
    {
        return LeaveRequest::where('employee_id', $employee->id)
            ->whereIn('status', ['pending','approved'])
            ->when($ignoreId, fn($q) => $q->where('id','!=',$ignoreId))
            ->where(function($q) use ($from, $to) {
                $q->whereBetween('from_date', [$from, $to])
                  ->orWhereBetween('to_date',  [$from, $to])
                  ->orWhere(function($q2) use ($from, $to) {
                      $q2->where('from_date', '<=', $from)
                         ->where('to_date',   '>=', $to);
                  });
            })
            ->first();
    }

    /**
     * Validate + create a leave request.
     */
    public function create(Employee $employee, array $data, ?UploadedFile $document = null): array
    {
        $type = LeaveType::where('company_id', $employee->company_id)
            ->findOrFail($data['leave_type_id']);

        // Gender check
        if ($employee->gender === 'male' && !$type->applicable_to_male) {
            return ['status'=>'error','message'=>'This leave type is not applicable to male employees.'];
        }
        if ($employee->gender === 'female' && !$type->applicable_to_female) {
            return ['status'=>'error','message'=>'This leave type is not applicable to female employees.'];
        }

        // Calculate days
        $calc = $this->calculateDays($employee, $data['from_date'], $data['to_date'], $data['day_type'] ?? 'full_day');
        if (isset($calc['error'])) {
            return ['status'=>'error','message'=>$calc['error']];
        }
        if ($calc['days'] <= 0) {
            return ['status'=>'error','message'=>'No working days in the selected range. Please adjust dates.'];
        }

        // Max consecutive days check
        if ($type->max_consecutive_days && $calc['days'] > $type->max_consecutive_days) {
            return [
                'status'=>'error',
                'message' => "Maximum {$type->max_consecutive_days} consecutive days allowed for {$type->name}."
            ];
        }

        // Min notice check (skip for emergency)
        if (!($data['is_emergency'] ?? false) && $type->min_days_notice > 0) {
            $noticeDays = now()->startOfDay()->diffInDays(Carbon::parse($data['from_date'])->startOfDay(), false);
            if ($noticeDays < $type->min_days_notice) {
                return [
                    'status' => 'error',
                    'message' => "{$type->name} requires at least {$type->min_days_notice} days notice. Check 'Emergency leave' if urgent."
                ];
            }
        }

        // Document required?
        if ($type->requires_document && !$document) {
            return ['status'=>'error','message'=>"{$type->name} requires supporting document upload."];
        }

        // Overlap check
        $overlap = $this->hasOverlap($employee, $data['from_date'], $data['to_date']);
        if ($overlap) {
            return [
                'status'=>'error',
                'message'=>'You already have a '.$overlap->status.' leave request ('.$overlap->request_number.') for these dates.'
            ];
        }

        // Balance check (skip for unpaid leave)
        if ($type->is_paid) {
            $year = Carbon::parse($data['from_date'])->year;
            $balances = $this->balancesFor($employee, $year);
            $bal = $balances->first(fn($b) => $b->leave_type_id === $type->id);
            $available = $bal ? (float) $bal->available : 0;

            if ($calc['days'] > $available) {
                return [
                    'status' => 'error',
                    'message' => "Not enough balance. You have {$available} day(s) of {$type->name} available but requested {$calc['days']}."
                ];
            }
        }

        // Create
        $request = DB::transaction(function () use ($employee, $type, $data, $calc, $document) {
            $docPath = null;
            if ($document) {
                $docPath = $document->store('leave_documents/'.$employee->id, 'public');
            }

            $lr = LeaveRequest::create([
                'company_id'           => $employee->company_id,
                'employee_id'          => $employee->id,
                'leave_type_id'        => $type->id,
                'request_number'       => 'LR-'.now()->format('Ymd').'-'.strtoupper(Str::random(5)),
                'from_date'            => $data['from_date'],
                'to_date'              => $data['to_date'],
                'total_days'           => $calc['days'],
                'day_type'             => $data['day_type'] ?? 'full_day',
                'reason'               => $data['reason'],
                'document_path'        => $docPath,
                'status'               => 'pending',
                'is_emergency'         => (bool) ($data['is_emergency'] ?? false),
                'contact_during_leave' => $data['contact_during_leave'] ?? null,
            ]);

            // Bump pending balance
            if ($type->is_paid) {
                $year = Carbon::parse($data['from_date'])->year;
                $bal = LeaveBalance::where('employee_id', $employee->id)
                    ->where('leave_type_id', $type->id)
                    ->where('year', $year)->first();
                if ($bal) {
                    $bal->increment('pending_days', $calc['days']);
                }
            }

            return $lr;
        });

        return [
            'status'  => 'ok',
            'message' => 'Leave request submitted successfully.',
            'request' => $request,
        ];
    }

    /**
     * Cancel a pending or future-approved leave request.
     */
    public function cancel(Employee $employee, LeaveRequest $request): array
    {
        if ($request->employee_id !== $employee->id) {
            return ['status'=>'error','message'=>'Not allowed.'];
        }
        if (!in_array($request->status, ['pending','approved'])) {
            return ['status'=>'error','message'=>'Only pending or approved requests can be cancelled.'];
        }
        if ($request->status === 'approved' && $request->from_date->isPast()) {
            return ['status'=>'error','message'=>'Cannot cancel a leave that has already started.'];
        }

        DB::transaction(function () use ($request) {
            $oldStatus = $request->status;
            $request->update(['status' => 'cancelled']);

            // Release balance
            $year = $request->from_date->year;
            $bal = LeaveBalance::where('employee_id', $request->employee_id)
                ->where('leave_type_id', $request->leave_type_id)
                ->where('year', $year)->first();

            if ($bal) {
                if ($oldStatus === 'pending') {
                    $bal->decrement('pending_days', $request->total_days);
                } elseif ($oldStatus === 'approved') {
                    $bal->decrement('used_days', $request->total_days);
                }
            }
        });

        return ['status'=>'ok','message'=>'Leave request cancelled.'];
    }
}