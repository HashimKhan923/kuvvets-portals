@extends('layouts.app')
@section('title','Leave Report')
@section('page-title','Leave Report')
@section('breadcrumb','Reports · Leave · ' . $year)

@section('content')

{{-- Filter --}}
<div class="card card-gold" style="padding:14px 18px;margin-bottom:20px;
     display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
    <form method="GET" action="{{ route('reports.leave') }}"
          style="display:flex;gap:10px;align-items:center;flex:1;flex-wrap:wrap;">
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
                       min-width:180px;">
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
        ['Total Requests', $stats['total_requests'], '#2B6CB0','#EBF8FF'],
        ['Approved',       $stats['approved'],       '#2D7A4F','#F0FBF4'],
        ['Pending',        $stats['pending'],        '#B7791F','#FFFBEB'],
        ['Rejected',       $stats['rejected'],       '#C53030','#FFF5F5'],
        ['Total Days',     $stats['total_days'],     '#C49A3C','#FBF5E6'],
    ] as [$l,$v,$c,$bg])
    <div class="stat-card">
        <div style="font-size:10px;font-weight:600;color:var(--text-muted);
                    letter-spacing:.7px;text-transform:uppercase;margin-bottom:8px;">{{ $l }}</div>
        <div style="font-size:28px;font-weight:700;color:{{ $c }};
                    font-family:'Space Grotesk',sans-serif;">{{ $v }}</div>
    </div>
    @endforeach
</div>

{{-- Charts --}}
<div style="display:grid;grid-template-columns:2fr 1fr;gap:18px;margin-bottom:22px;">

    {{-- Monthly Trend --}}
    <div class="card card-gold" style="padding:22px;">
        <div style="font-family:'Space Grotesk',sans-serif;font-size:14px;font-weight:600;
                    color:var(--text-primary);margin-bottom:16px;">
            <i class="fa-solid fa-chart-area" style="color:var(--gold);margin-right:7px;"></i>
            Monthly Leave Trend — {{ $year }}
        </div>
        <canvas id="leaveMonthlyChart" height="100"></canvas>
    </div>

    {{-- By Leave Type --}}
    <div class="card card-gold" style="padding:22px;">
        <div style="font-family:'Space Grotesk',sans-serif;font-size:14px;font-weight:600;
                    color:var(--text-primary);margin-bottom:14px;">
            <i class="fa-solid fa-chart-pie" style="color:var(--gold);margin-right:7px;"></i>
            By Leave Type
        </div>
        @forelse($byType as $lt)
        <div style="display:flex;align-items:center;justify-content:space-between;
                    padding:8px 0;border-bottom:1px solid var(--border-light);">
            <span style="font-size:12px;color:var(--text-secondary);">
                {{ $lt->leaveType?->name ?? 'Unknown' }}
            </span>
            <div style="text-align:right;">
                <span style="font-size:13px;font-weight:600;color:var(--gold-dark);
                             font-family:'Space Grotesk',sans-serif;">
                    {{ $lt->total_days }}d
                </span>
                <span style="font-size:10px;color:var(--text-muted);margin-left:5px;">
                    ({{ $lt->count }} req)
                </span>
            </div>
        </div>
        @empty
        <div style="padding:24px;text-align:center;color:var(--text-light);font-size:13px;">
            No data
        </div>
        @endforelse
    </div>
</div>

{{-- Per Employee Table --}}
<div class="card card-gold" style="overflow:hidden;">
    <div style="padding:14px 20px;border-bottom:1px solid var(--border-light);">
        <div style="font-family:'Space Grotesk',sans-serif;font-size:14px;font-weight:600;
                    color:var(--text-primary);">
            <i class="fa-solid fa-users" style="color:var(--gold);margin-right:7px;"></i>
            Per-Employee Leave Summary — {{ $year }}
        </div>
    </div>
    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="background:#F7F9FC;border-bottom:1px solid var(--border-light);">
                @foreach(['Employee','Department','Total Requests','Approved',
                          'Pending','Days Taken'] as $h)
                <th style="padding:10px 14px;text-align:left;font-size:10px;color:var(--text-muted);
                           letter-spacing:.7px;font-weight:600;text-transform:uppercase;">{{ $h }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($employeeLeave as $row)
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
                           color:var(--text-primary);font-family:'Space Grotesk',sans-serif;">
                    {{ $row['total'] }}
                </td>
                <td style="padding:10px 14px;font-size:13px;font-weight:600;
                           color:var(--success);font-family:'Space Grotesk',sans-serif;">
                    {{ $row['approved'] }}
                </td>
                <td style="padding:10px 14px;font-size:13px;
                           color:{{ $row['pending'] > 0 ? 'var(--warning)' : 'var(--text-light)' }};
                           font-family:'Space Grotesk',sans-serif;
                           font-weight:{{ $row['pending'] > 0 ? '600' : '400' }};">
                    {{ $row['pending'] ?: '—' }}
                </td>
                <td style="padding:10px 14px;">
                    <span style="font-size:14px;font-weight:700;color:var(--gold-dark);
                                 font-family:'Space Grotesk',sans-serif;">
                        {{ $row['days_taken'] }}
                    </span>
                    <span style="font-size:11px;color:var(--text-muted);"> days</span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6"
                    style="padding:40px;text-align:center;color:var(--text-light);font-size:13px;">
                    No leave data found.
                </td>
            </tr>
            @endforelse
        </tbody>
        @if($employeeLeave->count())
        <tfoot>
            <tr style="background:#F7F9FC;border-top:2px solid var(--gold-mid);">
                <td colspan="2"
                    style="padding:10px 14px;font-size:11px;color:var(--gold-dark);font-weight:700;">
                    TOTALS
                </td>
                <td style="padding:10px 14px;font-size:13px;font-weight:700;
                           color:var(--text-primary);font-family:'Space Grotesk',sans-serif;">
                    {{ $employeeLeave->sum('total') }}
                </td>
                <td style="padding:10px 14px;font-size:13px;font-weight:700;
                           color:var(--success);font-family:'Space Grotesk',sans-serif;">
                    {{ $employeeLeave->sum('approved') }}
                </td>
                <td style="padding:10px 14px;font-size:13px;font-weight:700;
                           color:var(--warning);font-family:'Space Grotesk',sans-serif;">
                    {{ $employeeLeave->sum('pending') }}
                </td>
                <td style="padding:10px 14px;font-size:14px;font-weight:700;
                           color:var(--gold-dark);font-family:'Space Grotesk',sans-serif;">
                    {{ $employeeLeave->sum('days_taken') }} days
                </td>
            </tr>
        </tfoot>
        @endif
    </table>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
const monthlyData = @json($monthlyTrend);
new Chart(document.getElementById('leaveMonthlyChart'), {
    type: 'bar',
    data: {
        labels: monthlyData.map(d => d.month),
        datasets: [{
            label: 'Approved Leave Requests',
            data: monthlyData.map(d => d.count),
            backgroundColor: 'rgba(196,154,60,.2)',
            borderColor: '#C49A3C',
            borderWidth: 2,
            borderRadius: 4,
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display:false },
            tooltip: { backgroundColor:'#1C2331', titleColor:'#F0D080', bodyColor:'#94A3B8' }
        },
        scales: {
            x: { grid:{ color:'#F0EBD8' }, ticks:{ color:'#718096', font:{ size:11 } } },
            y: { grid:{ color:'#F0EBD8' }, ticks:{ color:'#718096', font:{ size:11 }, stepSize:1 } }
        }
    }
});
</script>
@endpush
@endsection