@extends('employee.layouts.app')
@section('title', 'Attendance')
@section('page-title', 'Attendance')
@section('page-sub', $start->format('F Y'))

@push('styles')
<style>
    /* ═══════════════════════════════════════════════════
       ATTENDANCE PAGE
    ═══════════════════════════════════════════════════ */

    /* Top stats banner */
    .stat-row {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 10px;
        margin-bottom: 18px;
    }
    @media (max-width: 980px) { .stat-row { grid-template-columns: repeat(3, 1fr); } }
    @media (max-width: 480px) { .stat-row { grid-template-columns: repeat(2, 1fr); } }

    .stat-tile {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 14px;
        position: relative;
        overflow: hidden;
    }
    .stat-tile-lbl {
        font-size: 10.5px; color: var(--text-muted);
        text-transform: uppercase; letter-spacing: .6px; font-weight: 600;
        display: flex; align-items: center; gap: 6px;
    }
    .stat-tile-val {
        font-family: 'Space Grotesk', sans-serif;
        font-size: 22px; font-weight: 700;
        color: var(--text-primary);
        margin-top: 6px;
        font-variant-numeric: tabular-nums;
    }
    .stat-tile-val .unit { font-size: 12px; font-weight: 500; color: var(--text-muted); }
    .stat-tile.green  { background: var(--green-bg);  border-color: var(--green-border); }
    .stat-tile.green  .stat-tile-lbl, .stat-tile.green  .stat-tile-val  { color: var(--green); }
    .stat-tile.yellow { background: var(--yellow-bg); border-color: var(--yellow-border); }
    .stat-tile.yellow .stat-tile-lbl, .stat-tile.yellow .stat-tile-val { color: var(--yellow); }
    .stat-tile.red    { background: var(--red-bg);    border-color: var(--red-border); }
    .stat-tile.red    .stat-tile-lbl, .stat-tile.red    .stat-tile-val { color: var(--red); }
    .stat-tile.purple { background: var(--purple-bg); border-color: var(--purple-border); }
    .stat-tile.purple .stat-tile-lbl, .stat-tile.purple .stat-tile-val { color: var(--purple); }
    .stat-tile.blue   { background: var(--blue-bg);   border-color: var(--blue-border); }
    .stat-tile.blue   .stat-tile-lbl, .stat-tile.blue   .stat-tile-val { color: var(--blue); }

    /* Toolbar */
    .toolbar {
        display: flex; align-items: center; gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 16px;
    }
    .month-nav {
        display: flex; align-items: center; gap: 4px;
        background: var(--bg-card); border: 1px solid var(--border);
        border-radius: 12px; padding: 4px;
    }
    .month-nav button, .month-nav a {
        width: 34px; height: 34px; border-radius: 8px;
        background: none; border: none; cursor: pointer;
        color: var(--text-secondary); font-size: 13px;
        display: inline-flex; align-items: center; justify-content: center;
        text-decoration: none; transition: all .15s;
    }
    .month-nav button:hover, .month-nav a:hover { background: var(--bg-hover); color: var(--accent); }
    .month-nav .month-label {
        padding: 0 12px; font-size: 13px; font-weight: 700;
        font-family: 'Space Grotesk', sans-serif;
        min-width: 130px; text-align: center;
    }

    .toolbar-spacer { flex: 1; }

    .toolbar select {
        height: 38px; padding: 0 32px 0 12px;
        border: 1px solid var(--border); border-radius: 10px;
        background: var(--bg-card); color: var(--text-primary);
        font: inherit; font-size: 13px; cursor: pointer;
        appearance: none;
        background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236B5347' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 10px center;
    }
    .toolbar select:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 3px rgba(194,83,27,.1); }

    .view-tabs {
        display: inline-flex; background: var(--bg-card);
        border: 1px solid var(--border); border-radius: 10px; padding: 3px;
    }
    .view-tabs a {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 7px 14px; border-radius: 8px;
        font-size: 12.5px; font-weight: 600;
        color: var(--text-secondary); text-decoration: none;
        transition: all .15s;
    }
    .view-tabs a.active { background: var(--accent-bg); color: var(--accent); }
    .view-tabs a:not(.active):hover { background: var(--bg-hover); color: var(--accent); }

    /* ═══════════════════════════════════════════════════
       CALENDAR
    ═══════════════════════════════════════════════════ */
    .cal-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 16px;
        overflow: hidden;
    }
    .cal-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        background: var(--border);
        gap: 1px;
    }
    .cal-head {
        background: var(--bg-muted);
        padding: 10px 8px;
        text-align: center;
        font-size: 11px; font-weight: 700;
        color: var(--text-secondary);
        letter-spacing: .8px; text-transform: uppercase;
    }
    .cal-day {
        background: var(--bg-card);
        min-height: 90px;
        padding: 8px 10px;
        position: relative;
        cursor: pointer;
        transition: background .15s;
        display: flex; flex-direction: column;
    }
    .cal-day:hover { background: var(--bg-hover); }
    .cal-day.out-of-month { background: var(--bg-page); cursor: default; }
    .cal-day.out-of-month:hover { background: var(--bg-page); }
    .cal-day.future { cursor: default; opacity: .55; }
    .cal-day.future:hover { background: var(--bg-card); }
    .cal-day.today { box-shadow: inset 0 0 0 2px var(--accent); }
    .cal-day.weekend:not(.has-attendance):not(.is-holiday):not(.is-leave) {
        background: var(--bg-muted);
    }

    .cal-day-num {
        font-size: 13.5px; font-weight: 700;
        color: var(--text-primary);
        font-family: 'Space Grotesk', sans-serif;
    }
    .cal-day.today .cal-day-num { color: var(--accent); }
    .cal-day.out-of-month .cal-day-num { color: var(--text-muted); opacity: .55; }

    .cal-day-tag {
        display: inline-block;
        font-size: 9px; font-weight: 700;
        padding: 2px 6px; border-radius: 4px;
        margin-left: auto;
        letter-spacing: .3px;
    }
    .cal-day-top { display: flex; align-items: center; gap: 4px; }

    .cal-day-status {
        margin-top: auto; display: flex; align-items: center; gap: 4px;
        font-size: 10.5px; font-weight: 600;
    }
    .cal-day-bar {
        position: absolute; bottom: 0; left: 0; right: 0;
        height: 3px;
    }

    /* Status colors as bottom bar */
    .status-present  .cal-day-bar { background: var(--green); }
    .status-late     .cal-day-bar { background: var(--yellow); }
    .status-absent   .cal-day-bar { background: var(--red); }
    .status-half_day .cal-day-bar { background: var(--blue); }
    .status-on_leave .cal-day-bar { background: var(--purple); }
    .status-holiday  .cal-day-bar { background: #14B8A6; }
    .status-work_from_home .cal-day-bar { background: #06B6D4; }

    .cal-day-time {
        font-size: 10.5px; color: var(--text-muted);
        font-variant-numeric: tabular-nums;
        margin-top: 2px;
    }

    .cal-day .pill {
        display: inline-flex; align-items: center;
        font-size: 9px; font-weight: 700;
        padding: 2px 6px; border-radius: 4px;
        letter-spacing: .3px; text-transform: uppercase;
    }
    .pill-present { background: var(--green-bg); color: var(--green); }
    .pill-late    { background: var(--yellow-bg); color: var(--yellow); }
    .pill-absent  { background: var(--red-bg); color: var(--red); }
    .pill-half    { background: var(--blue-bg); color: var(--blue); }
    .pill-leave   { background: var(--purple-bg); color: var(--purple); }
    .pill-holiday { background: #CCFBF1; color: #0F766E; }
    .pill-wfh     { background: #CFFAFE; color: #0E7490; }
    .pill-ot { background: var(--purple-bg); color: var(--purple); margin-left: 4px; }

    /* Legend */
    .legend {
        display: flex; flex-wrap: wrap; gap: 12px;
        padding: 12px 16px;
        background: var(--bg-muted);
        border-top: 1px solid var(--border);
        font-size: 11px;
    }
    .legend-item { display: inline-flex; align-items: center; gap: 5px; color: var(--text-secondary); }
    .legend-dot { width: 8px; height: 8px; border-radius: 2px; }

    @media (max-width: 700px) {
        .cal-day { min-height: 60px; padding: 6px 5px; }
        .cal-day-num { font-size: 12px; }
        .cal-day .pill { font-size: 8px; padding: 1px 4px; }
        .cal-day-time { display: none; }
        .cal-head { padding: 8px 4px; font-size: 10px; }
    }

    /* ═══════════════════════════════════════════════════
       LIST VIEW
    ═══════════════════════════════════════════════════ */
    .table-card {
        background: var(--bg-card); border: 1px solid var(--border);
        border-radius: 16px; overflow: hidden;
    }
    .att-table { width: 100%; border-collapse: collapse; }
    .att-table th {
        text-align: left; padding: 12px 14px;
        background: var(--bg-muted);
        font-size: 11px; font-weight: 700;
        color: var(--text-secondary);
        letter-spacing: .6px; text-transform: uppercase;
        border-bottom: 1px solid var(--border);
        white-space: nowrap;
    }
    .att-table td {
        padding: 14px; font-size: 13px;
        border-bottom: 1px solid var(--border);
        color: var(--text-primary);
        vertical-align: middle;
    }
    .att-table tr:last-child td { border-bottom: none; }
    .att-table tr { cursor: pointer; transition: background .15s; }
    .att-table tbody tr:hover { background: var(--bg-hover); }
    .att-table .date-cell { font-weight: 600; }
    .att-table .day-cell  { font-size: 11px; color: var(--text-muted); }
    .att-table .num-cell  { font-variant-numeric: tabular-nums; font-weight: 600; }
    .att-table .empty {
        text-align: center; padding: 60px 20px; color: var(--text-muted);
        cursor: default;
    }
    .att-table .empty:hover { background: var(--bg-card); }

    @media (max-width: 700px) {
        .att-table .col-hide-mobile { display: none; }
        .att-table th, .att-table td { padding: 10px 8px; font-size: 12px; }
    }

    /* ═══════════════════════════════════════════════════
       DAY DETAIL DRAWER
    ═══════════════════════════════════════════════════ */
    .drawer-overlay {
        position: fixed; inset: 0; z-index: 90;
        background: rgba(15,10,5,.5); backdrop-filter: blur(4px);
        animation: fadeIn .2s ease;
    }
    @keyframes fadeIn { from{opacity:0} to{opacity:1} }
    .drawer {
        position: fixed; top: 0; right: 0; bottom: 0;
        width: 100%; max-width: 460px;
        background: var(--bg-card);
        z-index: 91;
        display: flex; flex-direction: column;
        box-shadow: -10px 0 40px rgba(0,0,0,.15);
        animation: slideInRight .25s cubic-bezier(.22,.61,.36,1);
    }
    @keyframes slideInRight { from{transform:translateX(100%)} to{transform:translateX(0)} }
    .drawer-hd {
        padding: 18px 22px; border-bottom: 1px solid var(--border);
        display: flex; align-items: center; justify-content: space-between;
    }
    .drawer-title {
        font-family: 'Space Grotesk', sans-serif;
        font-size: 16px; font-weight: 700;
    }
    .drawer-sub { font-size: 11.5px; color: var(--text-muted); margin-top: 2px; }
    .drawer-body {
        flex: 1; overflow-y: auto; padding: 20px 22px;
    }
    .drawer-spinner {
        display: inline-block; width: 28px; height: 28px;
        border: 3px solid var(--border-strong);
        border-top-color: var(--accent);
        border-radius: 50%; animation: spin .7s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* drawer items */
    .d-status-bar {
        padding: 16px;
        background: var(--bg-muted);
        border: 1px solid var(--border);
        border-radius: 14px;
        text-align: center;
        margin-bottom: 18px;
    }
    .d-status-bar.present { background: var(--green-bg); border-color: var(--green-border); }
    .d-status-bar.late    { background: var(--yellow-bg); border-color: var(--yellow-border); }
    .d-status-bar.absent  { background: var(--red-bg); border-color: var(--red-border); }
    .d-status-bar.leave   { background: var(--purple-bg); border-color: var(--purple-border); }
    .d-status-bar.holiday { background: #CCFBF1; border-color: #99F6E4; }
    .d-status-icon { font-size: 28px; margin-bottom: 6px; }
    .d-status-txt  { font-size: 13px; font-weight: 700; letter-spacing: .3px; }
    .d-status-sub  { font-size: 11px; color: var(--text-muted); margin-top: 2px; }
    .d-status-bar.present .d-status-icon, .d-status-bar.present .d-status-txt { color: var(--green); }
    .d-status-bar.late .d-status-icon, .d-status-bar.late .d-status-txt { color: var(--yellow); }
    .d-status-bar.absent .d-status-icon, .d-status-bar.absent .d-status-txt { color: var(--red); }
    .d-status-bar.leave .d-status-icon, .d-status-bar.leave .d-status-txt { color: var(--purple); }
    .d-status-bar.holiday .d-status-icon, .d-status-bar.holiday .d-status-txt { color: #0F766E; }

    .d-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 16px; }
    .d-tile {
        background: var(--bg-muted); border: 1px solid var(--border);
        border-radius: 12px; padding: 12px;
    }
    .d-tile-lbl {
        font-size: 10px; color: var(--text-muted);
        text-transform: uppercase; letter-spacing: .6px; font-weight: 600;
    }
    .d-tile-val {
        font-size: 14px; font-weight: 700;
        margin-top: 4px;
        font-variant-numeric: tabular-nums;
        font-family: 'Space Grotesk', sans-serif;
    }
    .d-tile-val .unit { font-size: 10px; color: var(--text-muted); font-weight: 500; margin-left: 1px; }

    .d-section-title {
        font-size: 11px; font-weight: 700;
        color: var(--text-secondary);
        letter-spacing: .8px; text-transform: uppercase;
        margin: 18px 0 10px;
    }
    .d-row {
        display: flex; align-items: center; justify-content: space-between;
        padding: 10px 12px; background: var(--bg-muted); border-radius: 10px;
        margin-bottom: 6px; font-size: 12.5px;
    }
    .d-row-lbl { color: var(--text-muted); }
    .d-row-val { font-weight: 600; color: var(--text-primary); }

    .d-break-item {
        display: flex; align-items: center; gap: 10px;
        padding: 10px 12px; background: var(--bg-muted);
        border-radius: 10px; margin-bottom: 6px;
    }
    .d-break-ico {
        width: 32px; height: 32px; border-radius: 8px;
        background: var(--yellow-bg); color: var(--yellow);
        display: flex; align-items: center; justify-content: center;
        font-size: 12px; flex-shrink: 0;
    }
    .d-break-txt { flex: 1; font-size: 12.5px; font-weight: 600; }
    .d-break-meta { font-size: 11px; color: var(--text-muted); }
    .d-break-dur { font-size: 12px; font-weight: 700; color: var(--text-primary); font-variant-numeric: tabular-nums; }

    .d-empty {
        text-align: center; padding: 40px 20px; color: var(--text-muted); font-size: 13px;
    }
    .d-empty-ico { font-size: 32px; opacity: .35; margin-bottom: 10px; display: block; }

    .d-map-link {
        display: flex; align-items: center; gap: 6px;
        font-size: 11.5px; color: var(--accent);
        text-decoration: none; font-weight: 600;
        margin-top: 6px;
    }
    .d-map-link:hover { text-decoration: underline; }
</style>
@endpush

@section('content')

<div x-data="historyApp()">

    {{-- ═══════════════════════════ Stats banner ═══════════════════════════ --}}
    <div class="stat-row">
        <div class="stat-tile green">
            <div class="stat-tile-lbl"><i class="fa-solid fa-circle-check"></i> Present</div>
            <div class="stat-tile-val">{{ $monthStats['by_status']['present'] ?? 0 }}<span class="unit">d</span></div>
        </div>
        <div class="stat-tile yellow">
            <div class="stat-tile-lbl"><i class="fa-solid fa-clock"></i> Late</div>
            <div class="stat-tile-val">{{ $monthStats['by_status']['late'] ?? 0 }}<span class="unit">d</span></div>
        </div>
        <div class="stat-tile red">
            <div class="stat-tile-lbl"><i class="fa-solid fa-circle-xmark"></i> Absent</div>
            <div class="stat-tile-val">{{ $monthStats['by_status']['absent'] ?? 0 }}<span class="unit">d</span></div>
        </div>
        <div class="stat-tile">
            <div class="stat-tile-lbl"><i class="fa-solid fa-business-time"></i> Worked</div>
            <div class="stat-tile-val">{{ intdiv($monthStats['working_minutes'], 60) }}<span class="unit">h {{ $monthStats['working_minutes'] % 60 }}m</span></div>
        </div>
        <div class="stat-tile purple">
            <div class="stat-tile-lbl"><i class="fa-solid fa-bolt"></i> Overtime</div>
            <div class="stat-tile-val">{{ intdiv($monthStats['overtime_minutes'], 60) }}<span class="unit">h {{ $monthStats['overtime_minutes'] % 60 }}m</span></div>
        </div>
        <div class="stat-tile blue">
            <div class="stat-tile-lbl"><i class="fa-solid fa-mug-hot"></i> Break</div>
            <div class="stat-tile-val">{{ intdiv($monthStats['break_minutes'], 60) }}<span class="unit">h {{ $monthStats['break_minutes'] % 60 }}m</span></div>
        </div>
    </div>

    {{-- ═══════════════════════════ Toolbar ═══════════════════════════ --}}
    <form method="GET" id="filterForm">
        <input type="hidden" name="view" value="{{ $view }}">

        <div class="toolbar">

            {{-- Month nav --}}
            @php
                $prev = (clone $start)->subMonth();
                $next = (clone $start)->addMonth();
            @endphp
            <div class="month-nav">
                <a href="{{ route('employee.attendance.index', ['month'=>$prev->month,'year'=>$prev->year,'view'=>$view]) }}" title="Previous month">
                    <i class="fa-solid fa-chevron-left"></i>
                </a>
                <div class="month-label">{{ $start->format('F Y') }}</div>
                <a href="{{ route('employee.attendance.index', ['month'=>$next->month,'year'=>$next->year,'view'=>$view]) }}" title="Next month">
                    <i class="fa-solid fa-chevron-right"></i>
                </a>
                @if(!now()->isSameMonth($start))
                    <a href="{{ route('employee.attendance.index', ['view'=>$view]) }}" title="Go to today" style="padding:0 10px;font-size:11px;font-weight:600;">Today</a>
                @endif
            </div>

            <div class="toolbar-spacer"></div>

            {{-- Status filter --}}
            <select name="status" onchange="document.getElementById('filterForm').submit()">
                <option value="">All status</option>
                @foreach(['present'=>'Present','late'=>'Late','absent'=>'Absent','half_day'=>'Half day','on_leave'=>'On leave','holiday'=>'Holiday','work_from_home'=>'WFH'] as $val=>$lbl)
                    <option value="{{ $val }}" {{ $status===$val ? 'selected' : '' }}>{{ $lbl }}</option>
                @endforeach
            </select>

            {{-- Location filter --}}
            @if($availableLocations->count() > 1)
                <select name="location_id" onchange="document.getElementById('filterForm').submit()">
                    <option value="">All locations</option>
                    @foreach($availableLocations as $l)
                        <option value="{{ $l->id }}" {{ $locId == $l->id ? 'selected' : '' }}>{{ $l->name }}</option>
                    @endforeach
                </select>
            @endif

            {{-- View toggle --}}
            <div class="view-tabs">
                <a href="{{ route('employee.attendance.index', request()->query() + ['view'=>'calendar']) }}" class="{{ $view==='calendar' ? 'active' : '' }}">
                    <i class="fa-solid fa-calendar"></i> Calendar
                </a>
                <a href="{{ route('employee.attendance.index', request()->query() + ['view'=>'list']) }}" class="{{ $view==='list' ? 'active' : '' }}">
                    <i class="fa-solid fa-list"></i> List
                </a>
            </div>

            {{-- Export --}}
            <a href="{{ route('employee.attendance.export', request()->query()) }}" class="btn btn-secondary" style="height:38px;padding:0 14px;">
                <i class="fa-solid fa-download"></i> Export
            </a>
        </div>

        {{-- preserve month/year on submit --}}
        <input type="hidden" name="month" value="{{ $month }}">
        <input type="hidden" name="year" value="{{ $year }}">
    </form>

    {{-- ═══════════════════════════ CALENDAR ═══════════════════════════ --}}
    @if($view === 'calendar')
        <div class="cal-card">
            <div class="cal-grid">
                @foreach(['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $dow)
                    <div class="cal-head">{{ $dow }}</div>
                @endforeach

                @foreach($cells as $cell)
                    @php
                        $att = $cell['attendance'];
                        $hol = $cell['holiday'];
                        $lv  = $cell['leave'];

                        $statusClass = '';
                        $pill = null;
                        $pillClass = '';

                        if ($att) {
                            $statusClass = 'status-' . $att->status . ' has-attendance';
                            $pill = match($att->status) {
                                'present'        => ['Present', 'pill-present'],
                                'late'           => ['Late', 'pill-late'],
                                'absent'         => ['Absent', 'pill-absent'],
                                'half_day'       => ['Half', 'pill-half'],
                                'on_leave'       => ['Leave', 'pill-leave'],
                                'work_from_home' => ['WFH', 'pill-wfh'],
                                'holiday'        => ['Holiday', 'pill-holiday'],
                                default          => null,
                            };
                        } elseif ($lv) {
                            $statusClass = 'status-on_leave is-leave';
                            $pill = ['Leave', 'pill-leave'];
                        } elseif ($hol) {
                            $statusClass = 'status-holiday is-holiday';
                            $pill = ['Holiday', 'pill-holiday'];
                        }
                    @endphp

                    <div class="cal-day {{ $cell['in_month'] ? '' : 'out-of-month' }}
                                {{ $cell['is_today'] ? 'today' : '' }}
                                {{ $cell['is_future'] ? 'future' : '' }}
                                {{ $cell['is_weekend'] ? 'weekend' : '' }}
                                {{ $statusClass }}"
                         @if($cell['in_month'] && !$cell['is_future']) @click="openDay('{{ $cell['key'] }}')" @endif
                    >
                        <div class="cal-day-top">
                            <span class="cal-day-num">{{ $cell['date']->day }}</span>
                            @if($pill && $pill[0])
                                <span class="pill {{ $pill[1] }}" style="margin-left:auto;">{{ $pill[0] }}</span>
                            @endif
                            @if($att && $att->overtime_minutes > 0)
                                <span class="pill pill-ot" title="Overtime">OT</span>
                            @endif
                        </div>

                        @if($att && $att->check_in)
                            <div class="cal-day-time">
                                {{ $att->check_in->format('h:i') }}
                                @if($att->check_out) – {{ $att->check_out->format('h:i') }} @endif
                            </div>
                        @elseif($hol && !$att)
                            <div class="cal-day-time" style="font-size:10px;font-weight:600;color:#0F766E;">
                                {{ $hol->name }}
                            </div>
                        @elseif($lv && !$att)
                            <div class="cal-day-time" style="font-size:10px;font-weight:600;color:var(--purple);">
                                {{ $lv->leaveType?->name ?? 'On Leave' }}
                            </div>
                        @endif

                        <div class="cal-day-bar"></div>
                    </div>
                @endforeach
            </div>

            {{-- Legend --}}
            <div class="legend">
                <span class="legend-item"><span class="legend-dot" style="background:var(--green);"></span> Present</span>
                <span class="legend-item"><span class="legend-dot" style="background:var(--yellow);"></span> Late</span>
                <span class="legend-item"><span class="legend-dot" style="background:var(--red);"></span> Absent</span>
                <span class="legend-item"><span class="legend-dot" style="background:var(--blue);"></span> Half day</span>
                <span class="legend-item"><span class="legend-dot" style="background:var(--purple);"></span> On leave</span>
                <span class="legend-item"><span class="legend-dot" style="background:#14B8A6;"></span> Holiday</span>
                <span class="legend-item"><span class="legend-dot" style="background:#06B6D4;"></span> Work from home</span>
                <span class="legend-item" style="margin-left:auto;"><i class="fa-solid fa-circle-info"></i> Click any day for details</span>
            </div>
        </div>

    {{-- ═══════════════════════════ LIST ═══════════════════════════ --}}
    @else
        <div class="table-card">
            <div style="overflow-x:auto;">
                <table class="att-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Check-in</th>
                            <th>Check-out</th>
                            <th class="col-hide-mobile">Working</th>
                            <th class="col-hide-mobile">Break</th>
                            <th class="col-hide-mobile">OT</th>
                            <th class="col-hide-mobile">Late</th>
                            <th class="col-hide-mobile">Location</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $r)
                            <tr @click="openDay('{{ $r->date->toDateString() }}')">
                                <td>
                                    <div class="date-cell">{{ $r->date->format('M j, Y') }}</div>
                                    <div class="day-cell">{{ $r->date->format('l') }}</div>
                                </td>
                                <td>
                                    @php
                                        $sb = match($r->status) {
                                            'present'        => 'badge-green',
                                            'late'           => 'badge-yellow',
                                            'absent'         => 'badge-red',
                                            'half_day'       => 'badge-blue',
                                            'on_leave'       => 'badge-accent',
                                            'work_from_home' => 'badge-blue',
                                            default          => 'badge-gray',
                                        };
                                    @endphp
                                    <span class="badge {{ $sb }}">{{ ucwords(str_replace('_',' ',$r->status)) }}</span>
                                </td>
                                <td class="num-cell">{{ $r->check_in ? $r->check_in->format('h:i A') : '—' }}</td>
                                <td class="num-cell">{{ $r->check_out ? $r->check_out->format('h:i A') : '—' }}</td>
                                <td class="num-cell col-hide-mobile">{{ $r->working_hours }}</td>
                                <td class="num-cell col-hide-mobile">{{ $r->break_hours }}</td>
                                <td class="num-cell col-hide-mobile">
                                    @if($r->overtime_minutes > 0)
                                        <span class="badge badge-accent" style="font-size:10px;">{{ $r->overtime_hours }}</span>
                                    @else — @endif
                                </td>
                                <td class="num-cell col-hide-mobile">
                                    @if($r->late_minutes > 0)
                                        <span class="badge badge-yellow" style="font-size:10px;">{{ $r->late_minutes }}m</span>
                                    @else — @endif
                                </td>
                                <td class="col-hide-mobile" style="font-size:12px;">{{ $r->location?->name ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="empty">
                                <i class="fa-solid fa-calendar-xmark" style="font-size:32px;opacity:.3;display:block;margin-bottom:10px;"></i>
                                No attendance records found for this filter.
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- ═══════════ Day detail drawer ═══════════ --}}
    <template x-teleport="body">
        <div x-show="drawerOpen" style="display:none;">
            <div class="drawer-overlay" @click="closeDay()"></div>
            <div class="drawer">
                <div class="drawer-hd">
                    <div>
                        <div class="drawer-title" x-text="drawerDate || 'Day Details'"></div>
                        <div class="drawer-sub">Attendance breakdown</div>
                    </div>
                    <button class="modal-close" style="width:34px;height:34px;border-radius:10px;background:var(--bg-muted);border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--text-secondary);" @click="closeDay()">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="drawer-body">
                    <template x-if="loading">
                        <div style="text-align:center;padding:60px 0;">
                            <div class="drawer-spinner"></div>
                            <div style="margin-top:14px;font-size:12.5px;color:var(--text-muted);">Loading…</div>
                        </div>
                    </template>
                    <div x-show="!loading" x-html="drawerHtml"></div>
                </div>
            </div>
        </div>
    </template>

</div>

@endsection

@push('scripts')
<script>
function historyApp() {
    return {
        drawerOpen: false,
        drawerHtml: '',
        drawerDate: '',
        loading: false,

        async openDay(dateStr) {
            this.drawerOpen = true;
            this.loading = true;
            this.drawerHtml = '';
            this.drawerDate = '';
            try {
                const url = "{{ url('employee/attendance/day') }}/" + dateStr;
                const res = await fetch(url, {
                    headers: { 'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest' }
                });
                const data = await res.json();
                this.drawerDate = data.date;
                this.drawerHtml = data.html;
            } catch (e) {
                this.drawerHtml = '<div class="d-empty"><i class="fa-solid fa-circle-exclamation d-empty-ico"></i>Failed to load day details.</div>';
            } finally {
                this.loading = false;
            }
        },
        closeDay() { this.drawerOpen = false; }
    };
}
</script>
@endpush