@extends('layouts.app')
@section('title', 'Leave Report')
@section('page-title', 'Leave Report')
@section('breadcrumb', 'Leaves · Report · ' . $year)

@section('content')

{{-- Filter --}}
<div class="card card-sm" style="margin-bottom:18px;">
    <div class="toolbar">
        <form method="GET" action="{{ route('leaves.report') }}" class="toolbar" style="flex:1;">
            <select name="year" class="form-select" style="width:auto;">
                @for($y = now()->year + 1; $y >= 2022; $y--)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
            <select name="department" class="form-select" style="min-width:150px;">
                <option value="">All Departments</option>
                @foreach($departments as $d)
                    <option value="{{ $d->id }}" {{ request('department') == $d->id ? 'selected' : '' }}>
                        {{ $d->name }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-filter"></i> Filter
            </button>
        </form>
        <div style="font-size:12px;color:var(--text-muted);">
            {{ $report->count() }} employees · {{ $year }}
        </div>
    </div>
</div>

{{-- Summary Cards --}}
<div class="stats-grid-4" style="margin-bottom:20px;">
    @php
        $totalLeaves   = $report->sum('total');
        $totalPending  = $report->sum('pending');
        $avgLeaves     = $report->count() > 0 ? round($totalLeaves / $report->count(), 1) : 0;
    @endphp
    @foreach([
        ['Total Approved Days', $totalLeaves,  'fa-calendar-check',  'green'],
        ['Pending Requests',    $totalPending, 'fa-clock',           'yellow'],
        ['Employees Covered',   $report->count(), 'fa-users',        'accent'],
        ['Avg Days / Employee', $avgLeaves,    'fa-chart-bar',       'blue'],
    ] as [$label, $val, $icon, $color])
    <div class="stat-card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
            <div class="stat-label">{{ $label }}</div>
            <div class="stat-icon stat-icon-{{ $color }}"><i class="fa-solid {{ $icon }}"></i></div>
        </div>
        <div class="stat-num" style="font-size:22px;">{{ $val }}</div>
    </div>
    @endforeach
</div>

{{-- Report Table --}}
<div class="card card-flush" style="overflow-x:auto;">
    <div style="display:flex;align-items:center;justify-content:space-between;
                padding:14px 20px;border-bottom:1px solid var(--border);">
        <div class="card-title" style="margin-bottom:0;">
            <i class="fa-solid fa-file-lines"></i> Employee Leave Summary — {{ $year }}
        </div>
        <span style="font-size:11px;color:var(--text-muted);">Approved leaves only</span>
    </div>
    <table class="data-table" style="min-width:700px;">
        <thead>
            <tr>
                <th style="min-width:200px;">Employee</th>
                @foreach($leaveTypes as $lt)
                <th class="center" style="min-width:90px;">
                    <div style="display:flex;align-items:center;justify-content:center;gap:4px;">
                        <div style="width:7px;height:7px;border-radius:50%;
                                    background:{{ $lt->color ?? 'var(--accent)' }};flex-shrink:0;"></div>
                        {{ $lt->code }}
                    </div>
                    <div style="font-size:9px;color:var(--text-muted);font-weight:400;margin-top:1px;">
                        {{ $lt->name }}
                    </div>
                </th>
                @endforeach
                <th class="center">Total</th>
                <th class="center">Pending</th>
            </tr>
        </thead>
        <tbody>
            @forelse($report as $row)
            <tr>
                <td>
                    <div class="td-employee">
                        <img src="{{ $row['employee']->avatar_url }}" class="avatar avatar-sm">
                        <div>
                            <div style="font-size:12px;font-weight:600;color:var(--text-primary);">
                                {{ $row['employee']->full_name }}
                            </div>
                            <div style="font-size:10px;color:var(--text-muted);">
                                {{ $row['employee']->department?->name ?? '—' }}
                            </div>
                        </div>
                    </div>
                </td>
                @foreach($leaveTypes as $lt)
                @php $days = (float)($row['by_type'][$lt->id] ?? 0); @endphp
                <td class="center">
                    @if($days > 0)
                        <span style="font-size:13px;font-weight:600;
                                     color:{{ $lt->color ?? 'var(--accent)' }};">
                            {{ $days }}
                        </span>
                    @else
                        <span class="text-muted" style="font-size:11px;">—</span>
                    @endif
                </td>
                @endforeach
                <td class="center">
                    <span style="font-size:13px;font-weight:700;
                                 color:{{ $row['total'] > 0 ? 'var(--text-primary)' : 'var(--text-muted)' }};">
                        {{ $row['total'] }}
                    </span>
                </td>
                <td class="center">
                    @if($row['pending'] > 0)
                        <span style="font-size:12px;font-weight:600;color:var(--yellow);">
                            {{ $row['pending'] }}
                        </span>
                    @else
                        <span class="text-muted" style="font-size:11px;">—</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="{{ $leaveTypes->count() + 3 }}">
                    <div class="empty-state">
                        <i class="fa-solid fa-file-lines"></i>
                        No leave data found for {{ $year }}.
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
        @if($report->count())
        <tfoot>
            <tr style="background:var(--bg-muted);border-top:2px solid var(--border-strong);">
                <td style="padding:11px 16px;font-size:11px;font-weight:700;
                           color:var(--accent);letter-spacing:.5px;">TOTALS</td>
                @foreach($leaveTypes as $lt)
                <td class="center" style="padding:11px 16px;font-weight:700;font-size:12px;">
                    {{ $report->sum(fn($r) => $r['by_type'][$lt->id] ?? 0) }}
                </td>
                @endforeach
                <td class="center" style="padding:11px 16px;font-size:13px;font-weight:700;
                           color:var(--text-primary);">
                    {{ $report->sum('total') }}
                </td>
                <td class="center" style="padding:11px 16px;font-weight:700;color:var(--yellow);">
                    {{ $report->sum('pending') }}
                </td>
            </tr>
        </tfoot>
        @endif
    </table>
</div>

@endsection
