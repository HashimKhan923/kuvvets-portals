@extends('layouts.app')
@section('title', 'Performance Report')
@section('page-title', 'Performance Report')
@section('breadcrumb', 'Performance · Report')

@section('content')

{{-- Filter --}}
<div class="card card-sm" style="margin-bottom:20px;">
    <div class="toolbar">
        <form method="GET" action="{{ route('performance.report') }}" class="toolbar" style="flex:1;">
            <select name="cycle" class="form-select" style="min-width:200px;">
                <option value="">Select Cycle</option>
                @foreach($cycles as $c)
                <option value="{{ $c->id }}" {{ $cycle?->id == $c->id ? 'selected' : '' }}>
                    {{ $c->name }}
                </option>
                @endforeach
            </select>
            <select name="department" class="form-select" style="min-width:160px;">
                <option value="">All Departments</option>
                @foreach($departments as $d)
                <option value="{{ $d->id }}" {{ request('department') == $d->id ? 'selected' : '' }}>
                    {{ $d->name }}
                </option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-filter"></i> Generate Report
            </button>
        </form>
        @if($cycle)
        <div style="font-size:12px;color:var(--text-muted);">
            {{ $appraisals->count() }} completed appraisals ·
            Avg: {{ $appraisals->avg('overall_score') ? number_format($appraisals->avg('overall_score'), 2) : '—' }}/5
        </div>
        @endif
    </div>
</div>

@if($cycle && $appraisals->count())

{{-- Summary Cards --}}
@php
    $avgScore     = round($appraisals->avg('overall_score'), 2);
    $topPerf      = $appraisals->where('overall_rating', 'outstanding')->count()
                  + $appraisals->where('overall_rating', 'exceeds_expectations')->count();
    $promoted     = $appraisals->where('promotion_recommended', true)->count();
    $avgIncrement = round($appraisals->whereNotNull('increment_recommended')->avg('increment_recommended'), 1);
@endphp
<div style="display:grid;grid-template-columns:repeat(5,1fr);gap:12px;margin-bottom:20px;">
    @foreach([
        ['Avg Score',     $avgScore . '/5',       'fa-star',            'accent'],
        ['Top Performers', $topPerf,              'fa-trophy',          'green'],
        ['Completed',     $appraisals->count(),   'fa-circle-check',    'green'],
        ['Promotions',    $promoted,              'fa-arrow-trend-up',  'blue'],
        ['Avg Increment', $avgIncrement . '%',    'fa-percent',         'yellow'],
    ] as [$l, $v, $icon, $color])
    <div class="stat-card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
            <div class="stat-label">{{ $l }}</div>
            <div class="stat-icon stat-icon-{{ $color }}"><i class="fa-solid {{ $icon }}"></i></div>
        </div>
        <div style="font-size:24px;font-weight:700;color:var(--text-primary);">{{ $v }}</div>
    </div>
    @endforeach
</div>

{{-- Rankings Table --}}
<div class="card card-flush">
    <div style="display:flex;align-items:center;justify-content:space-between;
                padding:14px 20px;border-bottom:1px solid var(--border);">
        <div class="card-title" style="margin-bottom:0;">
            <i class="fa-solid fa-trophy"></i>
            {{ $cycle->name }} — Performance Rankings
        </div>
        <span style="font-size:11px;color:var(--text-muted);">Sorted by score (highest first)</span>
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th class="center">Rank</th>
                <th>Employee</th>
                <th>Department</th>
                <th>Score</th>
                <th>Rating</th>
                <th>Goals</th>
                <th>Increment</th>
                <th>Promotion</th>
                <th class="center">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($appraisals as $i => $apr)
            @php
                $rBadge    = $apr->rating_badge;
                $rank      = $i + 1;
                $rankColor = $rank === 1 ? 'var(--yellow)' : ($rank === 2 ? 'var(--text-muted)' : ($rank === 3 ? 'var(--accent)' : 'var(--text-muted)'));
            @endphp
            <tr>
                <td class="center">
                    <div style="font-size:{{ $rank <= 3 ? '20' : '14' }}px;
                                font-weight:700;color:{{ $rankColor }};">
                        {{ $rank <= 3 ? ['🥇','🥈','🥉'][$rank - 1] : '#' . $rank }}
                    </div>
                </td>
                <td>
                    <div class="td-employee">
                        <img src="{{ $apr->employee->avatar_url }}" class="avatar avatar-sm">
                        <div>
                            <div class="td-employee name">{{ $apr->employee->full_name }}</div>
                            <div class="td-employee id">{{ $apr->employee->employee_id }}</div>
                        </div>
                    </div>
                </td>
                <td class="muted">{{ $apr->employee->department?->name ?? '—' }}</td>
                <td>
                    <div style="font-size:18px;font-weight:700;color:var(--accent);">
                        {{ number_format($apr->overall_score, 1) }}
                        <span style="font-size:11px;color:var(--text-muted);font-weight:400;">/5</span>
                    </div>
                    <div class="progress-track" style="width:80px;margin-top:3px;">
                        <div class="progress-fill"
                             style="width:{{ ($apr->overall_score / 5) * 100 }}%;"></div>
                    </div>
                </td>
                <td>
                    <span class="badge" style="background:{{ $rBadge['bg'] }};color:{{ $rBadge['color'] }};border:1px solid {{ $rBadge['border'] }};font-size:10px;">
                        {{ $rBadge['label'] }}
                    </span>
                </td>
                <td class="muted">
                    @php
                        $empGoals = \App\Models\EmployeeGoal::where('employee_id', $apr->employee_id)
                            ->where('performance_cycle_id', $apr->performance_cycle_id)->get();
                        $completedGoals = $empGoals->where('status', 'completed')->count();
                    @endphp
                    {{ $completedGoals }}/{{ $empGoals->count() }}
                </td>
                <td>
                    @if($apr->increment_recommended)
                    <span style="font-size:13px;font-weight:700;color:var(--green);">
                        +{{ $apr->increment_recommended }}%
                    </span>
                    @else
                    <span class="text-muted">—</span>
                    @endif
                </td>
                <td>
                    @if($apr->promotion_recommended)
                    <span class="badge badge-accent" style="font-size:10px;">
                        <i class="fa-solid fa-star" style="font-size:9px;"></i> Yes
                    </span>
                    @else
                    <span class="text-muted">—</span>
                    @endif
                </td>
                <td class="center">
                    <a href="{{ route('performance.appraisal', $apr) }}" class="action-btn" title="View">
                        <i class="fa-solid fa-eye"></i>
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@elseif($cycle)
<div class="card">
    <div class="empty-state">
        <i class="fa-solid fa-clipboard-list"></i>
        No completed appraisals found for {{ $cycle->name }}.
    </div>
</div>
@else
<div class="card">
    <div class="empty-state">
        <i class="fa-solid fa-chart-line"></i>
        Select a performance cycle above to generate the report.
    </div>
</div>
@endif

@endsection