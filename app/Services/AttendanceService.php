<?php
namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Location;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    /**
     * Result codes:
     *   ok, already_checked_in, already_checked_out, no_location,
     *   out_of_range, invalid_qr, qr_mismatch, no_check_in, error
     */
    public function checkIn(Employee $employee, array $data): array
    {
        $today = now()->toDateString();

        $existing = Attendance::where('employee_id', $employee->id)
            ->whereDate('date', $today)->first();

        if ($existing && $existing->check_in) {
            return [
                'status'  => 'already_checked_in',
                'message' => 'You have already checked in today at '
                    . Carbon::parse($existing->check_in)->format('h:i A'),
                'attendance' => $existing,
            ];
        }

        // Resolve location
        $resolved = $this->resolveLocation($employee, $data);
        if ($resolved['status'] !== 'ok') return $resolved;

        /** @var Location $location */
        $location = $resolved['location'];
        /** @var float $distance */
        $distance = $resolved['distance'];
        /** @var string $method */
        $method = $resolved['method'];

        // Shift
        $shiftId = $employee->employeeShifts()
            ->where('is_current', true)
            ->value('shift_id');

        $attendance = DB::transaction(function () use (
            $employee, $location, $data, $distance, $method, $shiftId, $existing, $today
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
                'source'              => 'web',
                'status'              => 'present',
                'device_info'         => substr(request()->userAgent() ?? '', 0, 255),
            ];

            if ($existing) {
                $existing->update($payload);
                return $existing->fresh();
            }
            return Attendance::create($payload);
        });

        return [
            'status'     => 'ok',
            'message'    => 'Checked in successfully at ' . $location->name,
            'attendance' => $attendance,
            'distance'   => round($distance),
            'location'   => $location,
        ];
    }

    public function checkOut(Employee $employee, array $data): array
    {
        $today = now()->toDateString();
        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('date', $today)->first();

        if (!$attendance || !$attendance->check_in) {
            return [
                'status'  => 'no_check_in',
                'message' => 'You have not checked in today.',
            ];
        }

        if ($attendance->check_out) {
            return [
                'status'  => 'already_checked_out',
                'message' => 'You have already checked out today at '
                    . Carbon::parse($attendance->check_out)->format('h:i A'),
                'attendance' => $attendance,
            ];
        }

        // For check-out, prefer the same location as check-in
        $forcedLocation = $attendance->location_id
            ? Location::find($attendance->location_id) : null;

        $resolved = $this->resolveLocation($employee, $data, $forcedLocation);
        if ($resolved['status'] !== 'ok') return $resolved;

        /** @var Location $location */
        $location = $resolved['location'];
        $distance = $resolved['distance'];
        $method   = $resolved['method'];

        DB::transaction(function () use ($attendance, $data, $distance, $method) {
            // If a break is still active, close it automatically
            $active = \App\Models\BreakSession::where('attendance_id', $attendance->id)
                ->whereNull('ended_at')->first();

            if ($active) {
                $now = now();
                $mins = (int) round($active->started_at->diffInSeconds($now) / 60);
                $active->update(['ended_at' => $now, 'duration_minutes' => $mins]);
            }

            // Recompute total break_minutes
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
            ]);
            Attendance::compute($attendance->fresh());
        });

        $fresh = $attendance->fresh();

        return [
            'status'     => 'ok',
            'message'    => 'Checked out successfully. Worked ' . $fresh->working_hours,
            'attendance' => $fresh,
            'distance'   => round($distance),
            'location'   => $location,
        ];
    }

    /**
     * Resolve which location the employee is at.
     * Accepts either qr_token + lat/lng (QR mode) or just lat/lng (GPS mode).
     */
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

        // QR mode — token takes priority
        if ($qr) {
            $scanned = Location::where('qr_token', $qr)
                ->where('is_active', true)->first();

            if (!$scanned) {
                return ['status' => 'invalid_qr', 'message' => 'Invalid or expired QR code.'];
            }

            // Is that scanned location in the employee's assigned list?
            if (!$assigned->contains('id', $scanned->id)) {
                return [
                    'status'  => 'qr_mismatch',
                    'message' => 'This QR code is not assigned to you. You can only check in at your assigned location.',
                ];
            }

            // If we also have GPS, verify proximity (extra safety)
            if ($lat !== null && $lng !== null) {
                $distance = $scanned->distanceTo($lat, $lng);
                if ($distance > $scanned->radius_meters) {
                    return [
                        'status'  => 'out_of_range',
                        'message' => 'You scanned the QR but your GPS shows you are '
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

            // QR only
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

        // If check-out is forced to match check-in location, verify only that one
        if ($forced) {
            $distance = $forced->distanceTo($lat, $lng);
            if ($distance > $forced->radius_meters) {
                return [
                    'status'  => 'out_of_range',
                    'message' => 'You are ' . round($distance) . 'm away from '
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
        $match = null; $bestDistance = null;
        foreach ($assigned as $loc) {
            $d = $loc->distanceTo($lat, $lng);
            if ($d <= $loc->radius_meters && ($bestDistance === null || $d < $bestDistance)) {
                $match = $loc;
                $bestDistance = $d;
            }
        }

        if (!$match) {
            // Find nearest to give a helpful message
            $nearest = null; $nearestDist = PHP_FLOAT_MAX;
            foreach ($assigned as $loc) {
                $d = $loc->distanceTo($lat, $lng);
                if ($d < $nearestDist) { $nearest = $loc; $nearestDist = $d; }
            }
            return [
                'status'  => 'out_of_range',
                'message' => $nearest
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