@php
    use App\Models\BreakSession;

    // Determine display state
    $hasAttendance = (bool) $att;
    $isHoliday = (bool) $holiday;
    $isLeave   = (bool) $leave;

    if ($hasAttendance) {
        $statusKey = $att->status;
    } elseif ($isLeave) {
        $statusKey = 'on_leave';
    } elseif ($isHoliday) {
        $statusKey = 'holiday';
    } else {
        $statusKey = null;
    }

    $statusMap = [
        'present'        => ['present', 'fa-circle-check', 'Present', 'You attended work this day'],
        'late'           => ['late',    'fa-clock',         'Late',     'You arrived after your shift start'],
        'absent'         => ['absent',  'fa-circle-xmark',  'Absent',   'No attendance was recorded'],
        'half_day'       => ['present', 'fa-clock-rotate-left', 'Half Day', 'You worked half a day'],
        'on_leave'       => ['leave',   'fa-umbrella-beach','On Leave', 'You were on approved leave'],
        'holiday'        => ['holiday', 'fa-flag',          'Holiday',  'Company holiday'],
        'work_from_home' => ['present', 'fa-house-laptop',  'Work From Home', 'You worked remotely'],
    ];
    $sm = $statusMap[$statusKey] ?? ['', 'fa-circle-question', 'No Data', 'No record for this day'];
@endphp

@if(!$hasAttendance && !$isHoliday && !$isLeave)
    <div class="d-empty">
        <i class="fa-solid fa-calendar-xmark d-empty-ico"></i>
        <div style="font-weight:600;color:var(--text-secondary);">No record for this day</div>
        <div style="font-size:11.5px;margin-top:4px;">There is no attendance, leave, or holiday entry.</div>
    </div>
@else
    {{-- Status banner --}}
    <div class="d-status-bar {{ $sm[0] }}">
        <div class="d-status-icon"><i class="fa-solid {{ $sm[1] }}"></i></div>
        <div class="d-status-txt">{{ $sm[2] }}</div>
        <div class="d-status-sub">{{ $sm[3] }}</div>
    </div>

    {{-- Holiday card --}}
    @if($isHoliday && !$hasAttendance)
        <div class="d-row" style="background:#CCFBF1;color:#0F766E;">
            <span class="d-row-lbl" style="color:#0F766E;font-weight:600;">{{ $holiday->name }}</span>
            <span class="d-row-val" style="color:#0F766E;text-transform:capitalize;">{{ $holiday->type }}</span>
        </div>
        @if($holiday->description)
            <div style="margin-top:6px;padding:10px 12px;background:var(--bg-muted);border-radius:10px;font-size:12px;color:var(--text-secondary);line-height:1.6;">
                {{ $holiday->description }}
            </div>
        @endif
    @endif

    {{-- Leave card --}}
    @if($isLeave)
        <div class="d-section-title">Leave Details</div>
        <div class="d-row">
            <span class="d-row-lbl">Leave type</span>
            <span class="d-row-val">{{ $leave->leaveType?->name ?? '—' }}</span>
        </div>
        <div class="d-row">
            <span class="d-row-lbl">Period</span>
            <span class="d-row-val">{{ $leave->from_date->format('M j') }} → {{ $leave->to_date->format('M j') }}</span>
        </div>
        <div class="d-row">
            <span class="d-row-lbl">Duration</span>
            <span class="d-row-val">{{ $leave->duration_text }}</span>
        </div>
        @if($leave->reason)
            <div style="margin-top:8px;padding:10px 12px;background:var(--bg-muted);border-radius:10px;font-size:12px;color:var(--text-secondary);line-height:1.6;">
                <strong style="color:var(--text-primary);">Reason:</strong> {{ $leave->reason }}
            </div>
        @endif
    @endif

    {{-- Attendance details --}}
    @if($hasAttendance)
        {{-- Time tiles --}}
        <div class="d-grid">
            <div class="d-tile">
                <div class="d-tile-lbl"><i class="fa-solid fa-right-to-bracket"></i> Check-in</div>
                <div class="d-tile-val">{{ $att->check_in ? $att->check_in->format('h:i A') : '—' }}</div>
            </div>
            <div class="d-tile">
                <div class="d-tile-lbl"><i class="fa-solid fa-right-from-bracket"></i> Check-out</div>
                <div class="d-tile-val">{{ $att->check_out ? $att->check_out->format('h:i A') : '—' }}</div>
            </div>
            <div class="d-tile">
                <div class="d-tile-lbl"><i class="fa-solid fa-business-time"></i> Working</div>
                <div class="d-tile-val">
                    {{ intdiv($att->working_minutes,60) }}<span class="unit">h</span>
                    {{ $att->working_minutes % 60 }}<span class="unit">m</span>
                </div>
            </div>
            <div class="d-tile">
                <div class="d-tile-lbl"><i class="fa-solid fa-mug-hot"></i> Break</div>
                <div class="d-tile-val">
                    {{ intdiv($att->break_minutes,60) }}<span class="unit">h</span>
                    {{ $att->break_minutes % 60 }}<span class="unit">m</span>
                </div>
            </div>
            @if($att->overtime_minutes > 0)
            <div class="d-tile" style="background:var(--purple-bg);border-color:var(--purple-border);">
                <div class="d-tile-lbl" style="color:var(--purple);"><i class="fa-solid fa-bolt"></i> Overtime</div>
                <div class="d-tile-val" style="color:var(--purple);">
                    {{ intdiv($att->overtime_minutes,60) }}<span class="unit">h</span>
                    {{ $att->overtime_minutes % 60 }}<span class="unit">m</span>
                </div>
            </div>
            @endif
            @if($att->late_minutes > 0)
            <div class="d-tile" style="background:var(--yellow-bg);border-color:var(--yellow-border);">
                <div class="d-tile-lbl" style="color:var(--yellow);"><i class="fa-solid fa-clock"></i> Late by</div>
                <div class="d-tile-val" style="color:var(--yellow);">
                    {{ intdiv($att->late_minutes,60) }}<span class="unit">h</span>
                    {{ $att->late_minutes % 60 }}<span class="unit">m</span>
                </div>
            </div>
            @endif
            @if($att->early_leave_minutes > 0)
            <div class="d-tile" style="background:var(--red-bg);border-color:var(--red-border);">
                <div class="d-tile-lbl" style="color:var(--red);"><i class="fa-solid fa-person-walking-arrow-right"></i> Left early</div>
                <div class="d-tile-val" style="color:var(--red);">
                    {{ intdiv($att->early_leave_minutes,60) }}<span class="unit">h</span>
                    {{ $att->early_leave_minutes % 60 }}<span class="unit">m</span>
                </div>
            </div>
            @endif
        </div>

        {{-- Shift / location --}}
        <div class="d-section-title">Schedule &amp; Location</div>
        @if($att->shift)
            <div class="d-row">
                <span class="d-row-lbl"><i class="fa-solid fa-clock"></i> Shift</span>
                <span class="d-row-val">{{ $att->shift->name }} ({{ $att->shift->timing }})</span>
            </div>
        @endif
        @if($att->location)
            <div class="d-row">
                <span class="d-row-lbl"><i class="fa-solid fa-location-dot"></i> Location</span>
                <span class="d-row-val">{{ $att->location->name }}</span>
            </div>
        @endif

        {{-- Check-in details --}}
        @if($att->check_in)
            <div class="d-section-title">Check-in Verification</div>
            <div class="d-row">
                <span class="d-row-lbl">Method</span>
                <span class="d-row-val">
                    @switch($att->check_in_method)
                        @case('gps')    <i class="fa-solid fa-location-crosshairs"></i> GPS @break
                        @case('qr')     <i class="fa-solid fa-qrcode"></i> QR Code @break
                        @case('qr+gps') <i class="fa-solid fa-shield-halved"></i> QR + GPS @break
                        @default {{ ucfirst($att->check_in_method ?? 'manual') }}
                    @endswitch
                </span>
            </div>
            @if($att->check_in_distance_m !== null)
                <div class="d-row">
                    <span class="d-row-lbl">Distance from location</span>
                    <span class="d-row-val">{{ $att->check_in_distance_m }} m</span>
                </div>
            @endif
            @if($att->check_in_lat && $att->check_in_lng)
                <a class="d-map-link" target="_blank" rel="noopener"
                   href="https://www.google.com/maps?q={{ $att->check_in_lat }},{{ $att->check_in_lng }}">
                    <i class="fa-solid fa-map-location-dot"></i> View check-in on map
                </a>
            @endif
        @endif

        {{-- Check-out details --}}
        @if($att->check_out)
            <div class="d-section-title">Check-out Verification</div>
            <div class="d-row">
                <span class="d-row-lbl">Method</span>
                <span class="d-row-val">
                    @switch($att->check_out_method)
                        @case('gps')    <i class="fa-solid fa-location-crosshairs"></i> GPS @break
                        @case('qr')     <i class="fa-solid fa-qrcode"></i> QR Code @break
                        @case('qr+gps') <i class="fa-solid fa-shield-halved"></i> QR + GPS @break
                        @default {{ ucfirst($att->check_out_method ?? 'manual') }}
                    @endswitch
                </span>
            </div>
            @if($att->check_out_distance_m !== null)
                <div class="d-row">
                    <span class="d-row-lbl">Distance from location</span>
                    <span class="d-row-val">{{ $att->check_out_distance_m }} m</span>
                </div>
            @endif
            @if($att->check_out_lat && $att->check_out_lng)
                <a class="d-map-link" target="_blank" rel="noopener"
                   href="https://www.google.com/maps?q={{ $att->check_out_lat }},{{ $att->check_out_lng }}">
                    <i class="fa-solid fa-map-location-dot"></i> View check-out on map
                </a>
            @endif
        @endif

        {{-- Breaks --}}
        @if($att->breakSessions->count())
            <div class="d-section-title">Breaks ({{ $att->breakSessions->count() }})</div>
            @foreach($att->breakSessions as $bs)
                <div class="d-break-item">
                    <div class="d-break-ico"><i class="fa-solid {{ BreakSession::reasonIcon($bs->reason) }}"></i></div>
                    <div style="flex:1;min-width:0;">
                        <div class="d-break-txt">{{ BreakSession::reasonLabel($bs->reason) }}</div>
                        <div class="d-break-meta">
                            {{ $bs->started_at->format('h:i A') }}
                            @if($bs->ended_at) – {{ $bs->ended_at->format('h:i A') }} @else <span style="color:var(--yellow);font-weight:600;">Active</span> @endif
                        </div>
                    </div>
                    <div class="d-break-dur">{{ $bs->duration_minutes }}m</div>
                </div>
            @endforeach
        @endif

        {{-- Approval & override --}}
        @if($att->is_approved || $att->override)
            <div class="d-section-title">Status</div>
            @if($att->is_approved)
                <div class="d-row" style="background:var(--green-bg);">
                    <span class="d-row-lbl" style="color:var(--green);">
                        <i class="fa-solid fa-circle-check"></i> Approved
                        @if($att->approver) by {{ $att->approver->name }} @endif
                    </span>
                    @if($att->approved_at)
                        <span class="d-row-val" style="color:var(--green);font-size:11px;">{{ $att->approved_at->format('M j, h:i A') }}</span>
                    @endif
                </div>
            @endif
            @if($att->override)
                <div class="d-row" style="background:var(--yellow-bg);">
                    <span class="d-row-lbl" style="color:var(--yellow);">
                        <i class="fa-solid fa-pen-to-square"></i> Manually edited by HR
                    </span>
                </div>
            @endif
        @endif

        {{-- Notes --}}
        @if($att->notes)
            <div class="d-section-title">Notes</div>
            <div style="padding:12px 14px;background:var(--bg-muted);border-radius:10px;font-size:12.5px;color:var(--text-secondary);line-height:1.6;">
                {{ $att->notes }}
            </div>
        @endif
    @endif
@endif