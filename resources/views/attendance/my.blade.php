@extends('layouts.app')
@section('title', 'My Attendance')
@section('page-title', 'My Attendance')
@section('breadcrumb', 'Attendance · ' . now()->format('F Y'))

@section('content')

{{-- Today Card + Monthly Summary --}}
<div class="grid-2" style="margin-bottom:24px;">

    {{-- Today Check In/Out --}}
    <div class="card">
        <div class="section-label">
            Today — {{ now()->setTimezone('Asia/Karachi')->format('l, d M Y') }}
        </div>

        @if($todayAtt)
        <div style="display:flex;gap:20px;margin-bottom:20px;">
            <div>
                <div style="font-size:10px;color:var(--text-muted);margin-bottom:4px;">Check In</div>
                <div style="font-size:22px;font-weight:700;color:var(--green);">
                    {{ $todayAtt->check_in?->setTimezone('Asia/Karachi')->format('h:i A') ?? '—' }}
                </div>
            </div>
            <div>
                <div style="font-size:10px;color:var(--text-muted);margin-bottom:4px;">Check Out</div>
                <div style="font-size:22px;font-weight:700;color:{{ $todayAtt->check_out ? 'var(--text-secondary)' : 'var(--accent)' }};">
                    {{ $todayAtt->check_out?->setTimezone('Asia/Karachi')->format('h:i A') ?? 'Active' }}
                </div>
            </div>
            @if($todayAtt->check_out)
            <div>
                <div style="font-size:10px;color:var(--text-muted);margin-bottom:4px;">Total Hours</div>
                <div style="font-size:22px;font-weight:700;color:var(--accent);">
                    {{ $todayAtt->working_hours }}
                </div>
            </div>
            @endif
        </div>
        @else
        <div style="font-size:13px;color:var(--text-muted);margin-bottom:20px;">
            You haven't checked in today.
        </div>
        @endif

        <div style="display:flex;gap:10px;">
            @if(!$todayAtt || !$todayAtt->check_in)
            <form method="POST" action="{{ route('attendance.checkin') }}">
                @csrf
                <button type="submit" class="btn btn-success">
                    <i class="fa-solid fa-right-to-bracket"></i> Check In
                </button>
            </form>
            @elseif(!$todayAtt->check_out)
            <form method="POST" action="{{ route('attendance.checkout') }}">
                @csrf
                <button type="submit" class="btn btn-danger">
                    <i class="fa-solid fa-right-from-bracket"></i> Check Out
                </button>
            </form>
            @else
            <div class="flash flash-success" style="margin-bottom:0;">
                <i class="fa-solid fa-check-circle"></i> Day completed
            </div>
            @endif
        </div>
    </div>

    {{-- Monthly Summary --}}
    <div class="card">
        <div class="section-label">{{ $start->format('F Y') }} Summary</div>
        @php
            $monthRecords  = $records->values();
            $presentCount  = $monthRecords->whereIn('status', ['present','late','work_from_home'])->count();
            $absentCount   = $monthRecords->where('status', 'absent')->count();
            $lateCount     = $monthRecords->where('status', 'late')->count();
            $totalHours    = round($monthRecords->sum('working_minutes') / 60, 1);
            $overtimeHours = round($monthRecords->sum('overtime_minutes') / 60, 1);
        @endphp
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;">
            @foreach([
                ['Present Days', $presentCount,  'green'],
                ['Absent Days',  $absentCount,   'red'],
                ['Late Days',    $lateCount,     'yellow'],
                ['Total Hours',  $totalHours,    'accent'],
                ['OT Hours',     $overtimeHours, 'purple'],
            ] as [$label, $value, $color])
            <div class="detail-block" style="text-align:center;">
                <div style="font-size:20px;font-weight:700;color:var(--{{ $color }});margin-bottom:4px;">
                    {{ $value }}
                </div>
                <div class="detail-block-label" style="margin-bottom:0;">{{ $label }}</div>
            </div>
            @endforeach
        </div>
    </div>

</div>

{{-- Attendance Calendar --}}
<div class="card">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
        <div class="card-title" style="margin-bottom:0;">
            <i class="fa-solid fa-calendar-days"></i>
            {{ $start->format('F Y') }} Attendance Calendar
        </div>
        <form method="GET" action="{{ route('attendance.my') }}" style="display:flex;gap:8px;">
            <input type="month" name="month" value="{{ $month }}"
                   class="form-input" style="width:auto;">
            <button type="submit" class="btn btn-primary btn-sm">Go</button>
        </form>
    </div>

    {{-- Day headers --}}
    <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:4px;margin-bottom:4px;">
        @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $day)
        <div style="text-align:center;font-size:10px;color:var(--text-muted);letter-spacing:.5px;padding:4px;">
            {{ $day }}
        </div>
        @endforeach
    </div>

    {{-- Calendar cells --}}
    @php
        $firstDayOfWeek = $start->dayOfWeek;
        $totalDays      = $start->daysInMonth;
    @endphp
    <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:4px;">

        {{-- Empty cells before month starts --}}
        @for($i = 0; $i < $firstDayOfWeek; $i++)
            <div></div>
        @endfor

        @for($d = 1; $d <= $totalDays; $d++)
        @php
            $dateStr  = $start->copy()->day($d)->format('Y-m-d');
            $att      = $records->get($dateStr);
            $isToday  = $dateStr === today()->format('Y-m-d');
            $isFuture = $dateStr > today()->format('Y-m-d');

            if ($att) {
                $badge   = $att->status_badge;
                $cellBg  = $badge['bg'];
                $cellClr = $badge['color'];
            } elseif ($isFuture) {
                $cellBg  = 'var(--bg-muted)';
                $cellClr = 'var(--border-strong)';
            } else {
                $cellBg  = 'var(--bg-muted)';
                $cellClr = 'var(--text-muted)';
            }
        @endphp
        <div style="background:{{ $cellBg }};
                    border:1px solid {{ $isToday ? 'var(--accent)' : 'var(--border)' }};
                    border-radius:7px;padding:8px 4px;text-align:center;
                    min-height:52px;display:flex;flex-direction:column;
                    align-items:center;justify-content:center;">
            <div style="font-size:13px;font-weight:{{ $isToday ? '700' : '400' }};
                        color:{{ $isToday ? 'var(--accent)' : $cellClr }};">
                {{ $d }}
            </div>
            @if($att)
            <div style="font-size:9px;color:{{ $cellClr }};margin-top:2px;letter-spacing:.3px;">
                {{ strtoupper(substr(str_replace('_', ' ', $att->status), 0, 4)) }}
            </div>
            @if($att->check_in)
            <div style="font-size:9px;color:var(--text-muted);">
                {{ $att->check_in->format('H:i') }}
            </div>
            @endif
            @endif
        </div>
        @endfor

    </div>

    {{-- Legend --}}
    <div style="display:flex;gap:14px;flex-wrap:wrap;margin-top:18px;padding-top:14px;border-top:1px solid var(--border);">
        @foreach([
            ['Present',  'green'],
            ['Absent',   'red'],
            ['Late',     'yellow'],
            ['Half Day', 'blue'],
            ['On Leave', 'purple'],
        ] as [$label, $color])
        <div style="display:flex;align-items:center;gap:5px;font-size:11px;color:var(--text-muted);">
            <div style="width:8px;height:8px;border-radius:50%;background:var(--{{ $color }});"></div>
            {{ $label }}
        </div>
        @endforeach
    </div>

</div>

@endsection