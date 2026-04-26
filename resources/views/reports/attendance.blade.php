@extends('layouts.app')
@section('title','Attendance Report')
@section('page-title','Attendance Report')
@section('breadcrumb','Reports · Attendance')

@section('content')

{{-- Filter --}}
<div class="card card-gold" style="padding:14px 18px;margin-bottom:20px;
     display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
    <form method="GET" action="{{ route('reports.attendance') }}"
          style="display:flex;gap:10px;align-items:center;flex:1;flex-wrap:wrap;">
        <select name="month"
                style="background:var(--white);border:1px solid var(--border);border-radius:7px;
                       padding:8px 12px;color:var(--text-primary);font-size:13px;outline:none;">
            @foreach(range(1,12) as $m)
            <option value="{{ $m }}" {{ $month==$m?'selected':'' }}>
                {{ \Carbon\Carbon::create(null,$m)->format('F') }}
            </option>
            @endforeach
        </select>
        <select name="year"
                style="background:var(--white);border:1px solid var(--border);border-radius:7px;
                       padding:8px 12px;color:var(--text-primary);font-size:13px;outline:none;">
            @foreach([now()->year, now()->year-1, now()->year-2] as $y)
            <option value="{{ $y }}" {{ $year==$y?'selected':'' }}>{{ $y }}</option>
            @endforeach
        </select>
        <select name="department"
                style="background:var(--white);border:1px solid var(--border);border-radius:7px;
                       padding:8px 12px;color:var(--text-secondary);font-size:13px;outline:none;
                       min-width:160px;">
            <option value="">All Departments</option>
            @foreach($departments as $d)
            <option value="{{ $d->id }}" {{ request('department')==$d->id?'selected':'' }}>
                {{ $d->name }}
            </option>
            @endforeach
        </select>
        <button type="submit" class="btn-gold" style="padding:8px 14px;font-size:13px;">
            <i class="fa-solid fa-filter"></i> Generate
        </button>
    </form>
</div>

{{-- Stats --}}
<div style="display:grid;grid-template-columns:repeat(5,1fr);gap:14px;margin-bottom:22px;">
    @foreach([
        ['Working Days',   $stats['working_days'],  '#2B6CB0','#EBF8FF'],
        ['Total Present',  $stats['total_present'], '#2D7A4F','#F0FBF4'],
        ['Total Absent',   $stats['total_absent'],  '#C53030','#FFF5F5'],
        ['Late Arrivals',  $stats['total_late'],    '#B7791F','#FFFBEB'],
        ['Overtime Hours', $stats['total_ot_hrs'],  '#C49A3C','#FBF5E6'],
    ] as [$l,$v,$c,$bg])
    <div class="stat-card">
        <div style="font-size:10px;font-weight:600;color:var(--text-muted);
                    letter-spacing:.7px;text-transform:uppercase;margin-bottom:8px;">{{ $l }}</div>
        <div style="font-size:28px;font-weight:700;color:{{ $c }};
                    font-family:'Space Grotesk',sans-serif;">{{ $v }}</div>
    </div>
    @endforeach
</div>

{{-- Daily Trend Chart --}}
@if(count($dailyTrend))
<div class="card card-gold" style="padding:22px;margin-bottom:22px;">
    <div style="font-family:'Space Grotesk',sans-serif;font-size:14px;font-weight:600;
                color:var(--text-primary);margin-bottom:16px;">
        <i class="fa-solid fa-chart-area" style="color:var(--gold);margin-right:7px;"></i>
        Daily Attendance Trend —
        {{ \Carbon\Carbon::create($year,$month)->format('F Y') }}
    </div>
    <canvas id="dailyChart" height="80"></canvas>
</div>
@endif

{{-- Employee Table --}}
<div class="card card-gold" style="overflow:hidden;">
    <div style="padding:14px 20px;border-bottom:1px solid var(--border-light);">
        <div style="font-family:'Space Grotesk',sans-serif;font-size:14px;font-weight:600;
                    color:var(--text-primary);">
            <i class="fa-solid fa-clipboard-list" style="color:var(--gold);margin-right:7px;"></i>
            Per-Employee Breakdown
        </div>
    </div>
    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="background:#F7F9FC;border-bottom:1px solid var(--border-light);">
                @foreach(['Employee','Department','Present','Absent','Late','OT Hours','Attendance %'] as $h)
                <th style="padding:10px 14px;text-align:left;font-size:10px;color:var(--text-muted);
                           letter-spacing:.7px;font-weight:600;text-transform:uppercase;">{{ $h }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($employeeReport as $row)
            <tr class="table-row">
                <td style="padding:10px 14px;">
                    <div style="display:flex;align-items:center;gap:9px;">
                        <img src="{{ $row['employee']->avatar_url }}"
                             style="width:28px;height:28px;border-radius:50%;
                                    object-fit:cover;border:1px solid var(--gold-mid);">
                        <div>
                            <a href="{{ route('employees.show', $row['employee']) }}"
                               style="font-size:12px;color:var(--text-primary);font-weight:500;
                                      text-decoration:none;">
                                {{ $row['employee']->full_name }}
                            </a>
                            <div style="font-size:10px;color:var(--gold-dark);">
                                {{ $row['employee']->employee_id }}
                            </div>
                        </div>
                    </div>
                </td>
                <td style="padding:10px 14px;font-size:12px;color:var(--text-secondary);">
                    {{ $row['employee']->department?->name ?? '—' }}
                </td>
                <td style="padding:10px 14px;font-size:13px;font-weight:600;
                           color:var(--success);font-family:'Space Grotesk',sans-serif;">
                    {{ $row['present'] }}
                </td>
                <td style="padding:10px 14px;font-size:13px;
                           color:{{ $row['absent'] > 3 ? 'var(--danger)' : 'var(--text-secondary)' }};
                           font-family:'Space Grotesk',sans-serif;
                           font-weight:{{ $row['absent'] > 3 ? '600' : '400' }};">
                    {{ $row['absent'] }}
                </td>
                <td style="padding:10px 14px;font-size:13px;
                           color:{{ $row['late'] > 3 ? 'var(--warning)' : 'var(--text-secondary)' }};
                           font-family:'Space Grotesk',sans-serif;">
                    {{ $row['late'] }}
                </td>
                <td style="padding:10px 14px;font-size:13px;
                           color:{{ $row['ot_hours'] > 0 ? 'var(--gold-dark)' : 'var(--text-light)' }};
                           font-family:'Space Grotesk',sans-serif;font-weight:600;">
                    {{ $row['ot_hours'] > 0 ? $row['ot_hours'].'h' : '—' }}
                </td>
                <td style="padding:10px 14px;">
                    <div style="display:flex;align-items:center;gap:8px;">
                        <div style="flex:1;max-width:80px;height:6px;background:var(--border-light);
                                    border-radius:3px;overflow:hidden;">
                            <div style="height:100%;width:{{ $row['attendance_pct'] }}%;
                                        background:{{ $row['attendance_pct'] >= 90 ? 'var(--success)' : ($row['attendance_pct'] >= 75 ? 'var(--gold)' : 'var(--danger)') }};
                                        border-radius:3px;"></div>
                        </div>
                        <span style="font-size:12px;font-weight:600;
                                     color:{{ $row['attendance_pct'] >= 90 ? 'var(--success)' : ($row['attendance_pct'] >= 75 ? 'var(--gold-dark)' : 'var(--danger)') }};
                                     font-family:'Space Grotesk',sans-serif;">
                            {{ $row['attendance_pct'] }}%
                        </span>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7"
                    style="padding:40px;text-align:center;color:var(--text-light);font-size:13px;">
                    No attendance data found.
                </td>
            </tr>
            @endforelse
        </tbody>
        @if($employeeReport->count())
        <tfoot>
            <tr style="background:#F7F9FC;border-top:2px solid var(--gold-mid);">
                <td colspan="2"
                    style="padding:10px 14px;font-size:11px;color:var(--gold-dark);font-weight:700;">
                    TOTALS / AVERAGES
                </td>
                <td style="padding:10px 14px;font-size:13px;font-weight:700;
                           color:var(--success);font-family:'Space Grotesk',sans-serif;">
                    {{ $employeeReport->sum('present') }}
                </td>
                <td style="padding:10px 14px;font-size:13px;font-weight:700;
                           color:var(--danger);font-family:'Space Grotesk',sans-serif;">
                    {{ $employeeReport->sum('absent') }}
                </td>
                <td style="padding:10px 14px;font-size:13px;font-weight:700;
                           color:var(--warning);font-family:'Space Grotesk',sans-serif;">
                    {{ $employeeReport->sum('late') }}
                </td>
                <td style="padding:10px 14px;font-size:13px;font-weight:700;
                           color:var(--gold-dark);font-family:'Space Grotesk',sans-serif;">
                    {{ $employeeReport->sum('ot_hours') }}h
                </td>
                <td style="padding:10px 14px;font-size:13px;font-weight:700;
                           color:var(--text-primary);font-family:'Space Grotesk',sans-serif;">
                    {{ $employeeReport->count() > 0 ? round($employeeReport->avg('attendance_pct'), 1) : 0 }}% avg
                </td>
            </tr>
        </tfoot>
        @endif
    </table>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
@if(count($dailyTrend))
const daily = @json($dailyTrend);
new Chart(document.getElementById('dailyChart'), {
    type: 'bar',
    data: {
        labels: daily.map(d => d.date),
        datasets: [
            {
                label: 'Present',
                data: daily.map(d => d.present),
                backgroundColor: 'rgba(45,122,79,.2)',
                borderColor: '#2D7A4F',
                borderWidth: 1.5,
                borderRadius: 3,
            },
            {
                label: 'Absent',
                data: daily.map(d => d.absent),
                backgroundColor: 'rgba(197,48,48,.15)',
                borderColor: '#C53030',
                borderWidth: 1.5,
                borderRadius: 3,
            }
        ]
    },
    options: {
        responsive: true,
        interaction: { mode:'index', intersect:false },
        plugins: {
            legend: { labels: { color:'#718096', font:{ family:'Sora', size:11 }, boxWidth:10 } },
            tooltip: { backgroundColor:'#1C2331', titleColor:'#F0D080', bodyColor:'#94A3B8' }
        },
        scales: {
            x: { grid:{ color:'#F0EBD8' }, ticks:{ color:'#718096', font:{ size:10 } } },
            y: { grid:{ color:'#F0EBD8' }, ticks:{ color:'#718096', font:{ size:11 }, stepSize:1 } }
        }
    }
});
@endif
</script>
@endpush
@endsection