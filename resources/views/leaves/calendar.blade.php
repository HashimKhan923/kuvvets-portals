@extends('layouts.app')
@section('title', 'Leave Calendar')
@section('page-title', 'Leave Calendar')
@section('breadcrumb', 'Leaves · Calendar · ' . $start->format('F Y'))

@section('content')

{{-- Navigation --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;flex-wrap:wrap;gap:10px;">
    <div style="display:flex;align-items:center;gap:8px;">
        <a href="{{ route('leaves.calendar', ['month' => $start->copy()->subMonth()->format('Y-m')]) }}"
           class="action-btn" title="Previous month">
            <i class="fa-solid fa-chevron-left"></i>
        </a>
        <span style="font-size:16px;font-weight:700;color:var(--text-primary);min-width:140px;text-align:center;">
            {{ $start->format('F Y') }}
        </span>
        <a href="{{ route('leaves.calendar', ['month' => $start->copy()->addMonth()->format('Y-m')]) }}"
           class="action-btn" title="Next month">
            <i class="fa-solid fa-chevron-right"></i>
        </a>
        <a href="{{ route('leaves.calendar', ['month' => now()->format('Y-m')]) }}"
           class="quick-link ql-accent" style="padding:6px 12px;font-size:12px;margin-left:4px;">
            Today
        </a>
    </div>
    <div style="display:flex;gap:12px;">
        @foreach([
            ['Leave',   'var(--accent)'],
            ['Holiday', 'var(--blue)'],
            ['Weekend', 'var(--bg-muted)'],
        ] as [$label, $color])
        <div style="display:flex;align-items:center;gap:5px;font-size:11px;color:var(--text-muted);">
            <div style="width:10px;height:10px;border-radius:2px;background:{{ $color }};
                        border:1px solid var(--border);"></div>
            {{ $label }}
        </div>
        @endforeach
    </div>
</div>

{{-- Calendar --}}
<div class="card" style="margin-bottom:18px;">
    {{-- Day headers --}}
    <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:4px;margin-bottom:6px;">
        @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $d)
        <div style="text-align:center;font-size:10px;color:var(--text-muted);
                    letter-spacing:.5px;padding:4px;font-weight:600;">
            {{ $d }}
        </div>
        @endforeach
    </div>

    @php
        $firstDow  = $start->dayOfWeek;
        $totalDays = $start->daysInMonth;

        // Index events by date
        $eventsByDate = [];
        foreach ($events as $ev) {
            $cur = \Carbon\Carbon::parse($ev['date']);
            $end = \Carbon\Carbon::parse($ev['date_to']);
            while ($cur->lte($end)) {
                $key = $cur->format('Y-m-d');
                $eventsByDate[$key][] = $ev;
                $cur->addDay();
            }
        }
    @endphp

    <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:4px;">

        {{-- Empty cells --}}
        @for($i = 0; $i < $firstDow; $i++)
            <div></div>
        @endfor

        @for($d = 1; $d <= $totalDays; $d++)
        @php
            $dateStr   = $start->copy()->day($d)->format('Y-m-d');
            $dayObj    = \Carbon\Carbon::parse($dateStr);
            $isToday   = $dateStr === today()->format('Y-m-d');
            $isWeekend = $dayObj->isWeekend();
            $dayEvents = $eventsByDate[$dateStr] ?? [];
            $holidays  = collect($dayEvents)->where('type', 'holiday');
            $dayLeaves = collect($dayEvents)->where('type', 'leave');
        @endphp
        <div style="min-height:80px;
                    background:{{ $isWeekend ? 'var(--bg-muted)' : 'var(--bg-card)' }};
                    border:1px solid {{ $isToday ? 'var(--accent)' : 'var(--border)' }};
                    border-radius:7px;padding:6px;position:relative;">
            <div style="font-size:12px;font-weight:{{ $isToday ? '700' : '400' }};
                        color:{{ $isToday ? 'var(--accent)' : ($isWeekend ? 'var(--text-muted)' : 'var(--text-secondary)') }};
                        margin-bottom:4px;">
                {{ $d }}
            </div>

            {{-- Holiday pills --}}
            @foreach($holidays as $h)
            <div style="background:var(--blue-bg);border:1px solid var(--blue-border);
                        border-radius:3px;padding:2px 4px;margin-bottom:2px;overflow:hidden;">
                <div style="font-size:9px;color:var(--blue);font-weight:600;
                            white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    🎌 {{ $h['label'] }}
                </div>
            </div>
            @endforeach

            {{-- Leave pills --}}
            @foreach($dayLeaves->take(3) as $l)
            <div style="background:{{ $l['color'] }}20;border:1px solid {{ $l['color'] }}50;
                        border-radius:3px;padding:2px 4px;margin-bottom:2px;overflow:hidden;">
                <div style="font-size:9px;color:{{ $l['color'] }};
                            white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"
                     title="{{ $l['label'] }} — {{ $l['sub'] }}">
                    {{ Str::limit($l['label'], 12) }}
                </div>
            </div>
            @endforeach

            @if($dayLeaves->count() > 3)
            <div style="font-size:9px;color:var(--text-muted);">
                +{{ $dayLeaves->count() - 3 }} more
            </div>
            @endif
        </div>
        @endfor

    </div>
</div>

{{-- Who's On Leave List --}}
@if($leaves->count())
<div class="card">
    <div class="card-title">
        <i class="fa-solid fa-umbrella-beach"></i>
        Approved Leaves — {{ $start->format('F Y') }} ({{ $leaves->count() }} requests)
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:10px;">
        @foreach($leaves as $leave)
        <div style="background:var(--bg-muted);border:1px solid var(--border);border-radius:8px;
                    padding:12px;border-left:3px solid {{ $leave->leaveType->color }};">
            <div style="display:flex;align-items:center;gap:9px;margin-bottom:6px;">
                <img src="{{ $leave->employee->avatar_url }}" class="avatar avatar-sm">
                <div>
                    <div style="font-size:12px;font-weight:600;color:var(--text-primary);">
                        {{ $leave->employee->full_name }}
                    </div>
                    <div style="font-size:10px;color:var(--text-muted);">
                        {{ $leave->employee->department?->name ?? '—' }}
                    </div>
                </div>
            </div>
            <div style="font-size:11px;color:var(--text-secondary);">
                <i class="fa-solid fa-tag" style="font-size:9px;color:var(--accent);margin-right:4px;"></i>
                {{ $leave->leaveType->name }}
            </div>
            <div style="font-size:11px;color:var(--text-muted);margin-top:3px;">
                {{ $leave->from_date->format('d M') }} – {{ $leave->to_date->format('d M Y') }}
                · {{ $leave->total_days }} {{ $leave->total_days == 1 ? 'day' : 'days' }}
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

@endsection