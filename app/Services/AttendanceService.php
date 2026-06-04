<?php
namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Location;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    /**
     * Minutes before shift start that check-in window opens.
     */
    const CHECKIN_EARLY_WINDOW = 30;

    /**
     * Result codes:
     *   ok, already_checked_in, already_checked_out, no_location,
     *   out_of_range, invalid_qr, qr_mismatch, no_check_in,
     *   no_shift, not_working_day, too_early, error
     */
    public function checkIn(Employee $employee, array $data): array
    {
        $today = now()->toDateString();

        // ── Already checked in ───────────────────────────────
        $existing = Attendance::where('employee_id', $employee->id)
            ->whereDate('date', $today)->first();

        if ($existing && $existing->check_in) {
            return [
                'status'     => 'already_checked_in',
                'message'    => 'You have already checked in today at '
                    . Carbon::parse($existing->check_in)->format('h:i A'),
                'attendance' => $existing,
            ];
        }

        // ── Resolve shift ────────────────────────────────────
        $shiftId = $employee->employeeShifts()
            ->where('is_current', true)
            ->value('shift_id');

        $shift = $shiftId ? Shift::find($shiftId) : null;

        // ── Enforce shift rules ──────────────────────────────
        $shiftCheck = $this->enforceShiftCheckIn($employee, $shift);
        if ($shiftCheck !== null) {
            return $shiftCheck;
        }

        // ── Resolve location ─────────────────────────────────
        $resolved = $this->resolveLocation($employee, $data);
        if ($resolved['status'] !== 'ok') return $resolved;

        /** @var Location $location */
        $location = $resolved['location'];
        $distance = $resolved['distance'];
        $method   = $resolved['method'];

        // ── Compute late status ──────────────────────────────
        $lateInfo = $this->computeLateInfo($shift);

        // ── Create attendance record ─────────────────────────
        $attendance = DB::transaction(function () use (
            $employee, $location, $data, $distance, $method,
            $shiftId, $existing, $today, $lateInfo
        ) {
            $payload = [
                'company_id'          => $employee->company_id,
                'employee_id'         => $employee->id,
                'shift_id'            => $shiftId,
                'location_id'         => $location->id,
                'date'                => $today,
                'check_in'            => now(),
                'check_in_lat'        => $data['lat'] ?? null,
                'check_in_lng'        => $data['lng'] ?? null,
                'check_in_distance_m' => (int) round($distance),
                'check_in_method'     => $method,
                'check_in_ip'         => request()->ip(),
                'source'              => request()->is('mobile-api/*') ? 'mobile' : 'web',
                'status'              => 'short_day',
                'is_late'             => $lateInfo['is_late'],
                'late_minutes'        => $lateInfo['late_minutes'],
                'device_info'         => substr(request()->userAgent() ?? '', 0, 255),
            ];

            if ($existing) {
                $existing->update($payload);
                return $existing->fresh();
            }
            return Attendance::create($payload);
        });

        $message = $lateInfo['late_minutes'] > 0
            ? 'Checked in at ' . now()->format('h:i A')
              . '. You are ' . $lateInfo['late_minutes'] . ' minute(s) late.'
            : 'Checked in successfully at ' . $location->name . '.';

        return [
            'status'       => 'ok',
            'message'      => $message,
            'attendance'   => $attendance,
            'distance'     => round($distance),
            'location'     => $location,
            'is_late'      => $lateInfo['is_late'],
            'late_minutes' => $lateInfo['late_minutes'],
        ];
    }

    public function checkOut(Employee $employee, array $data): array
    {
        $today = now()->toDateString();

        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('date', $today)->first();

        // ── Must have checked in ─────────────────────────────
        if (!$attendance || !$attendance->check_in) {
            return [
                'status'  => 'no_check_in',
                'message' => 'You have not checked in today.',
            ];
        }

        // ── Already checked out ──────────────────────────────
        if ($attendance->check_out) {
            return [
                'status'     => 'already_checked_out',
                'message'    => 'You have already checked out today at '
                    . Carbon::parse($attendance->check_out)->format('h:i A'),
                'attendance' => $attendance,
            ];
        }

        // ── Resolve location ─────────────────────────────────
        $forcedLocation = $attendance->location_id
            ? Location::find($attendance->location_id) : null;

        $resolved = $this->resolveLocation($employee, $data, $forcedLocation);
        if ($resolved['status'] !== 'ok') return $resolved;

        $location = $resolved['location'];
        $distance = $resolved['distance'];
        $method   = $resolved['method'];

        // ── Compute early leave ──────────────────────────────
        $earlyLeaveMinutes = $this->computeEarlyLeave($attendance);

        // ── Update attendance ────────────────────────────────
        DB::transaction(function () use ($attendance, $data, $distance, $method, $earlyLeaveMinutes) {
            // Close any open break session
            $active = \App\Models\BreakSession::where('attendance_id', $attendance->id)
                ->whereNull('ended_at')->first();

            if ($active) {
                $now  = now();
                $mins = (int) round($active->started_at->diffInSeconds($now) / 60);
                $active->update(['ended_at' => $now, 'duration_minutes' => $mins]);
            }

            // Recompute total break minutes
            $totalBreak = \App\Models\BreakSession::where('attendance_id', $attendance->id)
                ->whereNotNull('ended_at')->sum('duration_minutes');

            $attendance->update([
                'check_out'            => now(),
                'check_out_lat'        => $data['lat'] ?? null,
                'check_out_lng'        => $data['lng'] ?? null,
                'check_out_distance_m' => (int) round($distance),
                'check_out_method'     => $method,
                'check_out_ip'         => request()->ip(),
                'break_minutes'        => $totalBreak,
                'early_leave_minutes'  => $earlyLeaveMinutes,
            ]);

            Attendance::compute($attendance->fresh());
        });

        $fresh = $attendance->fresh();

        $message = $earlyLeaveMinutes > 0
            ? 'Checked out at ' . now()->format('h:i A')
              . '. You left ' . $earlyLeaveMinutes . ' minute(s) early.'
            : 'Checked out successfully. You worked ' . $fresh->working_hours . '.';

        return [
            'status'              => 'ok',
            'message'             => $message,
            'attendance'          => $fresh,
            'distance'            => round($distance),
            'location'            => $location,
            'early_leave_minutes' => $earlyLeaveMinutes,
        ];
    }

    // ═══════════════════════════════════════════════════════
    // SHIFT ENFORCEMENT
    // ═══════════════════════════════════════════════════════

    /**
     * Returns an error array if check-in should be blocked, null if allowed.
     */
    protected function enforceShiftCheckIn(Employee $employee, ?Shift $shift): ?array
    {
        // No shift assigned — block
        if (!$shift) {
            return [
                'status'  => 'no_shift',
                'message' => 'You have no shift assigned. Please contact HR before checking in.',
            ];
        }

        $now         = now();
        $todayName   = $now->format('D'); // Mon, Tue, Wed, Thu, Fri, Sat, Sun
        $workingDays = $shift->working_days ?? [];

        // Not a working day — block
        if (!empty($workingDays) && !in_array($todayName, $workingDays)) {
            return [
                'status'  => 'not_working_day',
                'message' => 'Today (' . $now->format('l') . ') is not a working day '
                    . 'for your shift "' . $shift->name . '". '
                    . 'Your working days are: ' . implode(', ', $workingDays) . '.',
            ];
        }

        // Parse shift times (time-only, no date)
        $shiftStart  = Carbon::createFromFormat('H:i:s', $shift->start_time)
                       ?? Carbon::createFromFormat('H:i', $shift->start_time);
        $graceMin    = $shift->grace_minutes ?? 10;

        // Window opens 30 min before shift start
        $windowOpen  = $shiftStart->copy()->subMinutes(self::CHECKIN_EARLY_WINDOW);

        // Window closes after grace period (late check-in is still allowed but marked late)
        // We do NOT block late check-ins — we only block TOO EARLY
        $nowTime = Carbon::createFromFormat('H:i:s', $now->format('H:i:s'));

        // Too early — block
        if ($nowTime->lt($windowOpen)) {
            return [
                'status'  => 'too_early',
                'message' => 'Too early to check in. Your shift "' . $shift->name
                    . '" starts at ' . $shiftStart->format('h:i A') . '. '
                    . 'Check-in opens at ' . $windowOpen->format('h:i A') . '.',
                'opens_at' => $windowOpen->format('h:i A'),
            ];
        }

        // All good — allow
        return null;
    }

    /**
     * Compute whether employee is late based on shift start + grace period.
     */
    protected function computeLateInfo(?Shift $shift): array
    {
        if (!$shift) {
            return ['is_late' => false, 'late_minutes' => 0];
        }

        $shiftStart  = Carbon::createFromFormat('H:i:s', $shift->start_time)
                       ?? Carbon::createFromFormat('H:i', $shift->start_time);
        $graceEnd    = $shiftStart->copy()->addMinutes($shift->grace_minutes ?? 10);
        $nowTime     = Carbon::createFromFormat('H:i:s', now()->format('H:i:s'));

        if ($nowTime->gt($graceEnd)) {
            return [
                'is_late'      => true,
                'late_minutes' => (int) $graceEnd->diffInMinutes($nowTime),
            ];
        }

        return ['is_late' => false, 'late_minutes' => 0];
    }

    /**
     * Compute early leave minutes vs shift end time.
     */
    protected function computeEarlyLeave(Attendance $attendance): int
    {
        $shiftId = $attendance->shift_id;
        if (!$shiftId) return 0;

        $shift = Shift::find($shiftId);
        if (!$shift || !$shift->end_time) return 0;

        $shiftEnd = Carbon::createFromFormat('H:i:s', $shift->end_time)
                    ?? Carbon::createFromFormat('H:i', $shift->end_time);
        $nowTime  = Carbon::createFromFormat('H:i:s', now()->format('H:i:s'));

        // Night shift: end time is next day (e.g. start 22:00, end 06:00)
        $shiftStart = Carbon::createFromFormat('H:i:s', $shift->start_time);
        if ($shiftEnd->lt($shiftStart)) {
            // If current time is before midnight and we're in a night shift,
            // shift end is tomorrow — so any checkout tonight is early
            if ($nowTime->gt($shiftStart)) {
                $shiftEnd->addDay();
            }
        }

        if ($nowTime->lt($shiftEnd)) {
            return (int) $nowTime->diffInMinutes($shiftEnd);
        }

        return 0;
    }

    // ═══════════════════════════════════════════════════════
    // LOCATION RESOLVER (unchanged)
    // ═══════════════════════════════════════════════════════

    protected function resolveLocation(
        Employee $employee,
        array $data,
        ?Location $forced = null
    ): array {
        $lat = isset($data['lat']) ? (float) $data['lat'] : null;
        $lng = isset($data['lng']) ? (float) $data['lng'] : null;
        $qr  = $data['qr_token'] ?? null;

        $assigned = $employee->activeLocations()->get();
        if ($assigned->isEmpty()) {
            return [
                'status'  => 'no_location',
                'message' => 'No location is assigned to you. Contact HR.',
            ];
        }

        // QR mode
        if ($qr) {
            $scanned = Location::where('qr_token', $qr)
                ->where('is_active', true)->first();

            if (!$scanned) {
                return ['status' => 'invalid_qr', 'message' => 'Invalid or expired QR code.'];
            }

            if (!$assigned->contains('id', $scanned->id)) {
                return [
                    'status'  => 'qr_mismatch',
                    'message' => 'This QR code is not assigned to you. You can only check in at your assigned location.',
                ];
            }

            if ($lat !== null && $lng !== null) {
                $distance = $scanned->distanceTo($lat, $lng);
                if ($distance > $scanned->radius_meters) {
                    return [
                        'status'   => 'out_of_range',
                        'message'  => 'You scanned the QR but your GPS shows you are '
                            . round($distance) . 'm away (max '
                            . $scanned->radius_meters . 'm). Move closer and try again.',
                        'distance' => $distance,
                    ];
                }
                return [
                    'status'   => 'ok',
                    'location' => $scanned,
                    'distance' => $distance,
                    'method'   => 'qr+gps',
                ];
            }

            return [
                'status'   => 'ok',
                'location' => $scanned,
                'distance' => 0,
                'method'   => 'qr',
            ];
        }

        // GPS-only mode
        if ($lat === null || $lng === null) {
            return [
                'status'  => 'error',
                'message' => 'Location is required. Please enable GPS and try again.',
            ];
        }

        // Forced location (checkout matches check-in location)
        if ($forced) {
            $distance = $forced->distanceTo($lat, $lng);
            if ($distance > $forced->radius_meters) {
                return [
                    'status'   => 'out_of_range',
                    'message'  => 'You are ' . round($distance) . 'm away from '
                        . $forced->name . ' (max ' . $forced->radius_meters . 'm). '
                        . 'Please return to your check-in location.',
                    'distance' => $distance,
                ];
            }
            return [
                'status'   => 'ok',
                'location' => $forced,
                'distance' => $distance,
                'method'   => 'gps',
            ];
        }

        // Find closest assigned location within radius
        $match = null;
        $bestDistance = null;

        foreach ($assigned as $loc) {
            $d = $loc->distanceTo($lat, $lng);
            if ($d <= $loc->radius_meters && ($bestDistance === null || $d < $bestDistance)) {
                $match        = $loc;
                $bestDistance = $d;
            }
        }

        if (!$match) {
            $nearest     = null;
            $nearestDist = PHP_FLOAT_MAX;
            foreach ($assigned as $loc) {
                $d = $loc->distanceTo($lat, $lng);
                if ($d < $nearestDist) {
                    $nearest     = $loc;
                    $nearestDist = $d;
                }
            }
            return [
                'status'   => 'out_of_range',
                'message'  => $nearest
                    ? 'You are ' . round($nearestDist) . 'm away from '
                      . $nearest->name . ' (max ' . $nearest->radius_meters
                      . 'm). Move closer to your assigned location.'
                    : 'You are outside the allowed check-in area.',
                'distance' => $nearestDist,
            ];
        }

        return [
            'status'   => 'ok',
            'location' => $match,
            'distance' => $bestDistance,
            'method'   => 'gps',
        ];
    }
}