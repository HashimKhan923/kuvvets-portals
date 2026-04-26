@extends('layouts.app')
@section('title', 'Attendance Report')
@section('page-title', 'Monthly Attendance Report')
@section('breadcrumb', 'Attendance · Reports · ' . $start->format('F Y'))

@section('content')

{{-- Filter --}}
<div class="card card-sm" style="margin-bottom:18px;">
    <div class="toolbar">
        <form method="GET" action="{{ route('attendance.report') }}" class="toolbar" style="flex:1;">
            <input type="month" name="month" value="{{ $month }}" class="form-input" style="width:auto;">
            <select name="department" class="form-select" style="min-width:150px;">
                <option value="">All Departments</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ request('department') == $dept->id ? 'selected' : '' }}>
                        {{ $dept->name }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-filter"></i> Generate
            </button>
        </form>
        <div style="font-size:12px;color:var(--text-muted);">
            {{ $start->format('d M') }} – {{ $end->format('d M Y') }} · {{ $report->count() }} employees
        </div>
    </div>
</div>

{{-- Report Table --}}
<div class="card card-flush">
    <table class="data-table">
        <thead>
            <tr>
                <th>Employee</th>
                <th>Department</th>
                <th class="center">Present</th>
                <th class="center">Absent</th>
                <th class="center">Late</th>
                <th class="center">Half Day</th>
                <th class="center">Leave</th>
                <th class="center">Total Hrs</th>
                <th class="center">OT Hrs</th>
                <th class="center">Late Mins</th>
            </tr>
        </thead>
        <tbody>
            @foreach($report as $row)
            <tr>
                <td>
                    <div class="td-employee">
                        <img src="{{ $row['employee']->avatar_url }}" class="avatar avatar-sm">
                        <div>
                            <div style="font-size:12px;font-weight:600;color:var(--text-primary);">
                                {{ $row['employee']->full_name }}
                            </div>
                            <div style="font-size:10px;color:var(--accent);">
                                {{ $row['employee']->employee_id }}
                            </div>
                        </div>
                    </div>
                </td>
                <td class="muted">{{ $row['employee']->department?->name ?? '—' }}</td>
                <td class="center" style="font-size:13px;font-weight:500;color:var(--green);">
                    {{ $row['present'] }}
                </td>
                <td class="center" style="font-size:13px;font-weight:{{ $row['absent'] > 0 ? '600' : '400' }};color:{{ $row['absent'] > 0 ? 'var(--red)' : 'var(--text-muted)' }};">
                    {{ $row['absent'] }}
                </td>
                <td class="center" style="font-size:13px;color:{{ $row['late'] > 0 ? 'var(--yellow)' : 'var(--text-muted)' }};">
                    {{ $row['late'] }}
                </td>
                <td class="center" style="font-size:13px;color:{{ $row['half_day'] > 0 ? 'var(--blue)' : 'var(--text-muted)' }};">
                    {{ $row['half_day'] }}
                </td>
                <td class="center" style="font-size:13px;color:var(--purple);">
                    {{ $row['on_leave'] }}
                </td>
                <td class="center" style="font-size:13px;font-weight:500;color:var(--text-primary);">
                    {{ $row['total_hours'] }}
                </td>
                <td class="center" style="font-size:13px;color:{{ $row['overtime_hours'] > 0 ? 'var(--accent)' : 'var(--text-muted)' }};">
                    {{ $row['overtime_hours'] }}
                </td>
                <td class="center" style="font-size:12px;color:{{ $row['late_minutes'] > 0 ? 'var(--red)' : 'var(--text-muted)' }};">
                    {{ $row['late_minutes'] }}m
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background:var(--bg-muted);border-top:2px solid var(--border-strong);">
                <td colspan="2" style="padding:11px 16px;font-size:12px;font-weight:700;color:var(--accent);">
                    Totals
                </td>
                <td class="center" style="padding:11px 16px;font-size:12px;font-weight:700;color:var(--green);">
                    {{ $report->sum('present') }}
                </td>
                <td class="center" style="padding:11px 16px;font-size:12px;font-weight:700;color:var(--red);">
                    {{ $report->sum('absent') }}
                </td>
                <td class="center" style="padding:11px 16px;font-size:12px;font-weight:700;color:var(--yellow);">
                    {{ $report->sum('late') }}
                </td>
                <td class="center" style="padding:11px 16px;font-size:12px;font-weight:700;color:var(--blue);">
                    {{ $report->sum('half_day') }}
                </td>
                <td class="center" style="padding:11px 16px;font-size:12px;font-weight:700;color:var(--purple);">
                    {{ $report->sum('on_leave') }}
                </td>
                <td class="center" style="padding:11px 16px;font-size:12px;font-weight:700;color:var(--text-primary);">
                    {{ $report->sum('total_hours') }}
                </td>
                <td class="center" style="padding:11px 16px;font-size:12px;font-weight:700;color:var(--accent);">
                    {{ $report->sum('overtime_hours') }}
                </td>
                <td class="center" style="padding:11px 16px;font-size:12px;font-weight:700;color:var(--red);">
                    {{ $report->sum('late_minutes') }}m
                </td>
            </tr>
        </tfoot>
    </table>
</div>

@endsection