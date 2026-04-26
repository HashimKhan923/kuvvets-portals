@extends('layouts.app')
@section('title', 'Performance')
@section('page-title', 'Performance Management')
@section('breadcrumb', 'Performance · Dashboard')

@section('content')

{{-- Stats --}}
<div class="stats-grid-3">
    @foreach([
        ['Active Cycles',    $stats['active_cycles'],    'fa-rotate',               'blue'],
        ['Total Appraisals', $stats['total_appraisals'], 'fa-clipboard-list',        'green'],
        ['Pending Reviews',  $stats['pending_reviews'],  'fa-clock',                 'yellow'],
        ['Completed',        $stats['completed'],         'fa-circle-check',          'green'],
        ['Goals On Track',   $stats['goals_on_track'],   'fa-chart-line',            'accent'],
        ['Goals At Risk',    $stats['goals_at_risk'],    'fa-triangle-exclamation',  'red'],
    ] as [$label, $val, $icon, $color])
    <div class="stat-card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
            <div class="stat-label">{{ $label }}</div>
            <div class="stat-icon stat-icon-{{ $color }}"><i class="fa-solid {{ $icon }}"></i></div>
        </div>
        <div class="stat-num">{{ $val }}</div>
    </div>
    @endforeach
</div>

{{-- Quick Links --}}
<div class="quick-links">
    <a href="{{ route('performance.cycles') }}" class="quick-link ql-blue">
        <i class="fa-solid fa-rotate"></i> Manage Cycles
    </a>
    <a href="{{ route('performance.kpis') }}" class="quick-link ql-yellow">
        <i class="fa-solid fa-bullseye"></i> KPI Library
    </a>
    <a href="{{ route('performance.report') }}" class="quick-link ql-green">
        <i class="fa-solid fa-file-chart-line"></i> Performance Report
    </a>
</div>

<div class="grid-2" style="margin-bottom:20px;">

    {{-- Rating Distribution Chart --}}
    <div class="card">
        <div class="card-title">
            <i class="fa-solid fa-chart-pie"></i> Rating Distribution
        </div>
        @if($ratingDist->sum() > 0)
        <canvas id="ratingChart" height="180"></canvas>
        @else
        <div class="empty-state">
            <i class="fa-solid fa-chart-pie"></i>
            No completed appraisals yet
        </div>
        @endif
    </div>

    {{-- Department Scores --}}
    <div class="card">
        <div class="card-title">
            <i class="fa-solid fa-sitemap"></i> Average Score by Department
        </div>
        @forelse($deptScores as $dept => $score)
        <div style="margin-bottom:14px;">
            <div style="display:flex;justify-content:space-between;margin-bottom:5px;">
                <span style="font-size:12px;color:var(--text-secondary);">{{ $dept }}</span>
                <span style="font-size:12px;font-weight:700;color:var(--accent);">
                    {{ $score }}/5
                </span>
            </div>
            <div class="progress-track">
                <div class="progress-fill" style="width:{{ ($score / 5) * 100 }}%;"></div>
            </div>
        </div>
        @empty
        <div class="empty-state" style="padding:32px;">No data yet</div>
        @endforelse
    </div>

</div>

{{-- Active Cycles --}}
<div class="card card-flush" style="margin-bottom:20px;">
    <div style="display:flex;align-items:center;justify-content:space-between;
                padding:14px 20px;border-bottom:1px solid var(--border);">
        <div class="card-title" style="margin-bottom:0;">
            <i class="fa-solid fa-rotate"></i> Performance Cycles
        </div>
        <a href="{{ route('performance.cycles') }}" class="btn btn-secondary btn-sm">
            View All →
        </a>
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Cycle</th>
                <th>Type</th>
                <th>Period</th>
                <th>Appraisals</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($cycles as $cycle)
            @php $badge = $cycle->status_badge; @endphp
            <tr>
                <td>
                    <div style="font-size:13px;font-weight:600;color:var(--text-primary);">
                        {{ $cycle->name }}
                    </div>
                    <div style="font-size:10px;color:var(--text-muted);">{{ $cycle->duration }}</div>
                </td>
                <td>
                    <span class="badge badge-accent">
                        {{ ucfirst(str_replace('_', ' ', $cycle->type)) }}
                    </span>
                </td>
                <td class="muted">
                    {{ $cycle->start_date->format('M Y') }} –
                    {{ $cycle->end_date->format('M Y') }}
                </td>
                <td style="font-size:13px;font-weight:700;color:var(--accent);">
                    {{ $cycle->appraisals_count }}
                </td>
                <td>
                    <span class="badge" style="background:{{ $badge['bg'] }};color:{{ $badge['color'] }};border:1px solid {{ $badge['border'] }};">
                        {{ ucfirst($cycle->status) }}
                    </span>
                </td>
                <td>
                    <a href="{{ route('performance.cycle', $cycle) }}" class="btn btn-secondary btn-xs">
                        Open →
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6">
                    <div class="empty-state">
                        <i class="fa-solid fa-rotate"></i>
                        No cycles yet.
                        <a href="{{ route('performance.cycles') }}">Create one →</a>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Recent Appraisals --}}
<div class="card card-flush">
    <div style="padding:14px 20px;border-bottom:1px solid var(--border);">
        <div class="card-title" style="margin-bottom:0;">
            <i class="fa-solid fa-clipboard-list"></i> Recent Appraisals
        </div>
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Employee</th>
                <th>Department</th>
                <th>Cycle</th>
                <th>Score</th>
                <th>Rating</th>
                <th>Status</th>
                <th class="center">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentAppraisals as $apr)
            @php
                $sBadge = $apr->status_badge;
                $rBadge = $apr->rating_badge;
            @endphp
            <tr>
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
                <td class="muted">{{ $apr->cycle->name }}</td>
                <td>
                    @if($apr->overall_score)
                    <div style="font-size:16px;font-weight:700;color:var(--accent);">
                        {{ number_format($apr->overall_score, 1) }}
                        <span style="font-size:11px;color:var(--text-muted);font-weight:400;">/5</span>
                    </div>
                    <div class="progress-track" style="width:70px;margin-top:3px;">
                        <div class="progress-fill"
                             style="width:{{ ($apr->overall_score / 5) * 100 }}%;"></div>
                    </div>
                    @else
                    <span class="text-muted">Pending</span>
                    @endif
                </td>
                <td>
                    @if($apr->overall_rating)
                    <span class="badge" style="background:{{ $rBadge['bg'] }};color:{{ $rBadge['color'] }};border:1px solid {{ $rBadge['border'] }};font-size:10px;">
                        {{ $rBadge['label'] }}
                    </span>
                    @else
                    <span class="text-muted">—</span>
                    @endif
                </td>
                <td>
                    <span class="badge" style="background:{{ $sBadge['bg'] }};color:{{ $sBadge['color'] }};border:1px solid {{ $sBadge['border'] }};">
                        {{ ucfirst(str_replace('_', ' ', $apr->status)) }}
                    </span>
                </td>
                <td class="center">
                    <a href="{{ route('performance.appraisal', $apr) }}" class="action-btn" title="View">
                        <i class="fa-solid fa-eye"></i>
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7">
                    <div class="empty-state">No appraisals yet.</div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
@if($ratingDist->sum() > 0)
new Chart(document.getElementById('ratingChart'), {
    type: 'doughnut',
    data: {
        labels: ['Outstanding','Exceeds Expectations','Meets Expectations','Needs Improvement','Unsatisfactory'],
        datasets: [{
            data: [
                {{ $ratingDist['outstanding']          ?? 0 }},
                {{ $ratingDist['exceeds_expectations'] ?? 0 }},
                {{ $ratingDist['meets_expectations']   ?? 0 }},
                {{ $ratingDist['needs_improvement']    ?? 0 }},
                {{ $ratingDist['unsatisfactory']       ?? 0 }}
            ],
            backgroundColor: ['#22C55E','#3B82F6','#C2531B','#F59E0B','#EF4444'],
            borderColor: '#FFFFFF',
            borderWidth: 2,
            hoverOffset: 6
        }]
    },
    options: {
        cutout: '65%',
        plugins: {
            legend: {
                position: 'bottom',
                labels: { color: '#A89080', font: { family: 'Plus Jakarta Sans', size: 11 }, boxWidth: 10, padding: 10 }
            },
            tooltip: {
                backgroundColor: '#FFFFFF',
                borderColor: '#F0EAE2',
                borderWidth: 1,
                titleColor: '#2D1F14',
                bodyColor: '#6B5347'
            }
        }
    }
});
@endif
</script>
@endpush

@endsection