@extends('layouts.app')
@section('title', 'Training Report')
@section('page-title', 'Training Report')
@section('breadcrumb', 'Training · Report')

@section('content')

{{-- Summary Stats --}}
<div class="stats-grid-3">
    @foreach([
        ['Total Training Hours', number_format($totalHours) . 'h', 'fa-clock',       'blue'],
        ['Total Training Cost',  'PKR ' . number_format($totalCost), 'fa-money-bill', 'accent'],
        ['Employees in Report',  $report->count(),              'fa-users',           'green'],
    ] as [$l, $v, $icon, $color])
    <div class="stat-card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
            <div class="stat-label">{{ $l }}</div>
            <div class="stat-icon stat-icon-{{ $color }}"><i class="fa-solid {{ $icon }}"></i></div>
        </div>
        <div class="stat-num" style="font-size:22px;">{{ $v }}</div>
    </div>
    @endforeach
</div>

{{-- Filter --}}
<div class="card card-sm" style="margin-bottom:18px;">
    <div class="toolbar">
        <form method="GET" action="{{ route('training.report') }}" class="toolbar" style="flex:1;">
            <select name="department" class="form-select" style="min-width:180px;">
                <option value="">All Departments</option>
                @foreach($departments as $d)
                <option value="{{ $d->id }}" {{ request('department') == $d->id ? 'selected' : '' }}>
                    {{ $d->name }}
                </option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-filter"></i> Filter
            </button>
        </form>
    </div>
</div>

{{-- Report Table --}}
<div class="card card-flush">
    <div style="padding:14px 20px;border-bottom:1px solid var(--border);">
        <div class="card-title" style="margin-bottom:0;">
            <i class="fa-solid fa-chart-bar"></i> Employee Training Summary
        </div>
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Employee</th>
                <th>Department</th>
                <th class="center">Trainings</th>
                <th class="center">Attended</th>
                <th class="center">Absent</th>
                <th>Completion</th>
                <th class="center">Hours</th>
                <th class="center">Certs</th>
                <th class="center">Expired</th>
            </tr>
        </thead>
        <tbody>
            @forelse($report as $row)
            <tr>
                <td>
                    <div class="td-employee">
                        <img src="{{ $row['employee']->avatar_url }}" class="avatar avatar-sm">
                        <div>
                            <div class="td-employee name">{{ $row['employee']->full_name }}</div>
                            <div class="td-employee id">{{ $row['employee']->employee_id }}</div>
                        </div>
                    </div>
                </td>
                <td class="muted">{{ $row['employee']->department?->name ?? '—' }}</td>
                <td class="center" style="font-size:13px;font-weight:600;color:var(--text-primary);">
                    {{ $row['total_trainings'] }}
                </td>
                <td class="center" style="font-size:13px;font-weight:700;color:var(--green);">
                    {{ $row['attended'] }}
                </td>
                <td class="center" style="font-size:13px;font-weight:{{ $row['absent'] > 0 ? '700' : '400' }};
                    color:{{ $row['absent'] > 0 ? 'var(--red)' : 'var(--text-muted)' }};">
                    {{ $row['absent'] }}
                </td>
                <td>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <div class="progress-track" style="max-width:70px;flex:1;">
                            <div class="progress-fill"
                                 style="width:{{ $row['completion_rate'] }}%;
                                        background:{{ $row['completion_rate'] >= 80 ? 'var(--green)' : ($row['completion_rate'] >= 50 ? 'var(--accent)' : 'var(--red)') }};"></div>
                        </div>
                        <span style="font-size:12px;font-weight:700;
                                     color:{{ $row['completion_rate'] >= 80 ? 'var(--green)' : ($row['completion_rate'] >= 50 ? 'var(--accent)' : 'var(--red)') }};">
                            {{ $row['completion_rate'] }}%
                        </span>
                    </div>
                </td>
                <td class="center" style="font-size:13px;font-weight:700;color:var(--accent);">
                    {{ $row['total_hours'] }}h
                </td>
                <td class="center" style="font-size:13px;font-weight:700;color:var(--text-primary);">
                    {{ $row['certifications'] }}
                </td>
                <td class="center">
                    @if($row['expired_certs'] > 0)
                    <span style="font-size:13px;font-weight:700;color:var(--red);">
                        {{ $row['expired_certs'] }}
                    </span>
                    @else
                    <span class="text-muted">—</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9">
                    <div class="empty-state">
                        <i class="fa-solid fa-user-graduate"></i>
                        No training data found.
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>

        @if($report->count())
        <tfoot>
            <tr style="background:var(--bg-muted);border-top:2px solid var(--accent-border);">
                <td colspan="2" style="padding:10px 16px;font-size:11px;font-weight:700;color:var(--accent);">
                    TOTALS
                </td>
                <td class="center" style="padding:10px 16px;font-size:13px;font-weight:700;color:var(--text-primary);">
                    {{ $report->sum('total_trainings') }}
                </td>
                <td class="center" style="padding:10px 16px;font-size:13px;font-weight:700;color:var(--green);">
                    {{ $report->sum('attended') }}
                </td>
                <td class="center" style="padding:10px 16px;font-size:13px;font-weight:700;color:var(--red);">
                    {{ $report->sum('absent') }}
                </td>
                <td style="padding:10px 16px;font-size:13px;font-weight:700;color:var(--accent);">
                    {{ $report->count() > 0 ? round($report->avg('completion_rate')) : 0 }}% avg
                </td>
                <td class="center" style="padding:10px 16px;font-size:13px;font-weight:700;color:var(--accent);">
                    {{ $report->sum('total_hours') }}h
                </td>
                <td class="center" style="padding:10px 16px;font-size:13px;font-weight:700;color:var(--text-primary);">
                    {{ $report->sum('certifications') }}
                </td>
                <td class="center" style="padding:10px 16px;font-size:13px;font-weight:700;color:var(--red);">
                    {{ $report->sum('expired_certs') }}
                </td>
            </tr>
        </tfoot>
        @endif
    </table>
</div>

@endsection