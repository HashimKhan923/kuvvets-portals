@extends('layouts.app')
@section('title', 'Payroll Report')
@section('page-title', 'Payroll Report')
@section('breadcrumb', 'Payroll · Annual Report · ' . $year)

@section('content')

{{-- Filter --}}
<div class="card card-sm" style="margin-bottom:20px;">
    <div class="toolbar">
        <form method="GET" action="{{ route('payroll.report') }}" class="toolbar" style="flex:1;">
            <select name="year" class="form-select" style="width:auto;">
                @for($y = now()->year + 1; $y >= 2022; $y--)
                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
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
                <i class="fa-solid fa-filter"></i> Generate
            </button>
        </form>
        <div style="font-size:12px;color:var(--text-muted);">
            {{ $periods->count() }} months processed in {{ $year }}
        </div>
    </div>
</div>

{{-- YTD Stats --}}
<div class="stats-grid-4">
    @foreach([
        ['YTD Gross Paid', 'PKR ' . number_format($ytdStats['gross']), 'fa-money-bill-wave', 'accent'],
        ['YTD Net Paid',   'PKR ' . number_format($ytdStats['net']),   'fa-circle-check',    'green'],
        ['YTD Income Tax', 'PKR ' . number_format($ytdStats['tax']),   'fa-file-invoice',    'yellow'],
        ['YTD EOBI Total', 'PKR ' . number_format($ytdStats['eobi']),  'fa-shield-halved',   'purple'],
    ] as [$label, $val, $icon, $color])
    <div class="stat-card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
            <div class="stat-label">{{ $label }}</div>
            <div class="stat-icon stat-icon-{{ $color }}"><i class="fa-solid {{ $icon }}"></i></div>
        </div>
        <div class="stat-num" style="font-size:18px;">{{ $val }}</div>
    </div>
    @endforeach
</div>

{{-- Charts --}}
<div class="grid-2-1" style="margin-bottom:20px;">
    <div class="card">
        <div class="card-title">
            <i class="fa-solid fa-chart-area"></i> Monthly Payroll Trend — {{ $year }}
        </div>
        <canvas id="monthlyChart" height="80"></canvas>
    </div>
    <div class="card">
        <div class="card-title">
            <i class="fa-solid fa-chart-pie"></i> Deduction Breakdown
        </div>
        <canvas id="deductionChart" height="180"></canvas>
        <div style="display:flex;flex-direction:column;gap:6px;margin-top:14px;">
            @foreach([
                ['Income Tax', 'yellow', $ytdStats['tax']],
                ['EOBI Total', 'blue',   $ytdStats['eobi']],
                ['Other',      'muted',  max(0, $ytdStats['gross'] - $ytdStats['net'] - $ytdStats['tax'] - $ytdStats['eobi'])],
            ] as [$l, $c, $v])
            <div style="display:flex;align-items:center;justify-content:space-between;font-size:11px;">
                <div style="display:flex;align-items:center;gap:6px;color:var(--text-muted);">
                    <div style="width:8px;height:8px;border-radius:50%;background:var(--{{ $c }});"></div>
                    {{ $l }}
                </div>
                <span style="font-weight:700;color:var(--{{ $c }});">PKR {{ number_format($v) }}</span>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Month-by-Month Table --}}
<div class="card card-flush" style="margin-bottom:20px;">
    <div style="display:flex;align-items:center;justify-content:space-between;
                padding:14px 20px;border-bottom:1px solid var(--border);">
        <div class="card-title" style="margin-bottom:0;">
            <i class="fa-solid fa-calendar"></i> Month-by-Month Summary
        </div>
        <span style="font-size:11px;color:var(--text-muted);">All amounts in PKR</span>
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Month</th>
                <th>Status</th>
                <th>Employees</th>
                <th>Gross</th>
                <th>Deductions</th>
                <th>Net</th>
                <th>Income Tax</th>
                <th>EOBI</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($periods as $p)
            @php $badge = $p->status_badge; @endphp
            <tr>
                <td>
                    <div style="font-size:13px;font-weight:600;color:var(--text-primary);">
                        {{ $p->month_name }}
                    </div>
                    @if($p->payment_date)
                    <div style="font-size:10px;color:var(--text-muted);">
                        Payment: {{ $p->payment_date->format('d M Y') }}
                    </div>
                    @endif
                </td>
                <td>
                    <span class="badge" style="background:{{ $badge['bg'] }};color:{{ $badge['color'] }};border:1px solid {{ $badge['border'] }};">
                        {{ ucfirst($p->status) }}
                    </span>
                </td>
                <td style="font-size:13px;font-weight:700;color:var(--accent);">
                    {{ $p->employee_count }}
                </td>
                <td style="font-size:12px;font-weight:500;color:var(--text-primary);">
                    {{ number_format($p->total_gross) }}
                </td>
                <td style="font-size:12px;color:var(--red);">
                    {{ number_format($p->total_deductions) }}
                </td>
                <td style="font-size:13px;font-weight:700;color:var(--green);">
                    {{ number_format($p->total_net) }}
                </td>
                <td style="font-size:12px;color:var(--yellow);">
                    {{ number_format($p->total_tax) }}
                </td>
                <td style="font-size:12px;color:var(--blue);">
                    {{ number_format($p->total_eobi) }}
                </td>
                <td>
                    <div style="display:flex;gap:5px;">
                        <a href="{{ route('payroll.period', $p) }}" class="btn btn-secondary btn-xs">
                            Open →
                        </a>
                        @if($p->status === 'approved')
                        <a href="{{ route('payroll.export', $p) }}" class="btn btn-blue btn-xs">
                            <i class="fa-solid fa-file-csv"></i>
                        </a>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9">
                    <div class="empty-state">
                        <i class="fa-solid fa-file-invoice-dollar"></i>
                        No payroll periods found for {{ $year }}.
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
        @if($periods->count())
        <tfoot>
            <tr style="background:var(--bg-muted);border-top:2px solid var(--border-strong);">
                <td colspan="3" style="padding:12px 16px;font-size:11px;font-weight:700;
                                       color:var(--accent);letter-spacing:.5px;">ANNUAL TOTALS</td>
                <td style="padding:12px 16px;font-weight:700;font-size:13px;color:var(--text-primary);">
                    {{ number_format($periods->sum('total_gross')) }}
                </td>
                <td style="padding:12px 16px;font-weight:700;color:var(--red);">
                    {{ number_format($periods->sum('total_deductions')) }}
                </td>
                <td style="padding:12px 16px;font-size:14px;font-weight:700;color:var(--green);">
                    {{ number_format($periods->sum('total_net')) }}
                </td>
                <td style="padding:12px 16px;font-weight:700;color:var(--yellow);">
                    {{ number_format($periods->sum('total_tax')) }}
                </td>
                <td style="padding:12px 16px;font-weight:700;color:var(--blue);">
                    {{ number_format($periods->sum('total_eobi')) }}
                </td>
                <td></td>
            </tr>
        </tfoot>
        @endif
    </table>
</div>

{{-- FBR Compliance Cards --}}
@if($periods->count())
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;">
    <div class="card" style="background:var(--yellow-bg);border-color:var(--yellow-border);">
        <div class="section-label">Total Tax Deducted (WHT)</div>
        <div style="font-size:22px;font-weight:700;color:var(--yellow);margin:6px 0;">
            PKR {{ number_format($ytdStats['tax']) }}
        </div>
        <div style="font-size:11px;color:var(--text-muted);">
            Avg/month: PKR {{ number_format($periods->count() > 0 ? $ytdStats['tax'] / $periods->count() : 0) }}
        </div>
    </div>
    <div class="card" style="background:var(--purple-bg);border-color:var(--purple-border);">
        <div class="section-label">Total EOBI Contribution</div>
        <div style="font-size:22px;font-weight:700;color:var(--blue);margin:6px 0;">
            PKR {{ number_format($ytdStats['eobi']) }}
        </div>
        <div style="font-size:11px;color:var(--text-muted);">Employee + Employer combined</div>
    </div>
    <div class="card" style="background:var(--green-bg);border-color:var(--green-border);">
        <div class="section-label">Total Cost to Company (CTC)</div>
        <div style="font-size:22px;font-weight:700;color:var(--green);margin:6px 0;">
            PKR {{ number_format($ytdStats['gross'] + $ytdStats['eobi']) }}
        </div>
        <div style="font-size:11px;color:var(--text-muted);">Gross + Employer EOBI</div>
    </div>
</div>
@endif

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
@if($periods->count())
var periodsData = @json($periods->values()->map(fn($p) => [
    'label' => \Carbon\Carbon::create($p->year, $p->month)->format('M Y'),
    'gross' => (float) $p->total_gross,
    'net'   => (float) $p->total_net,
    'tax'   => (float) $p->total_tax,
]));

new Chart(document.getElementById('monthlyChart'), {
    type: 'bar',
    data: {
        labels: periodsData.map(function(p) { return p.label; }),
        datasets: [
            {
                label: 'Gross',
                data: periodsData.map(function(p) { return p.gross; }),
                backgroundColor: 'rgba(194,83,27,.2)',
                borderColor: '#C2531B',
                borderWidth: 1.5,
                borderRadius: 4,
                order: 2
            },
            {
                label: 'Net',
                data: periodsData.map(function(p) { return p.net; }),
                backgroundColor: 'rgba(34,197,94,.15)',
                borderColor: '#22C55E',
                borderWidth: 1.5,
                borderRadius: 4,
                order: 3
            },
            {
                label: 'Tax',
                data: periodsData.map(function(p) { return p.tax; }),
                type: 'line',
                borderColor: '#F59E0B',
                borderWidth: 2,
                pointRadius: 4,
                tension: 0.4,
                fill: false,
                order: 1,
                yAxisID: 'y1'
            }
        ]
    },
    options: {
        responsive: true,
        interaction: { mode: 'index', intersect: false },
        plugins: {
            legend: { labels: { color: '#A89080', font: { family: 'Plus Jakarta Sans', size: 11 } } },
            tooltip: { callbacks: { label: function(c) { return ' ' + c.dataset.label + ': PKR ' + Math.round(c.raw).toLocaleString(); } } }
        },
        scales: {
            x:  { grid: { color: '#F0EAE2' }, ticks: { color: '#A89080', font: { size: 11 } } },
            y:  { grid: { color: '#F0EAE2' }, ticks: { color: '#A89080', font: { size: 11 }, callback: function(v) { return 'PKR ' + (v/1000).toFixed(0) + 'K'; } } },
            y1: { position: 'right', grid: { display: false }, ticks: { color: '#F59E0B', font: { size: 10 }, callback: function(v) { return 'PKR ' + (v/1000).toFixed(0) + 'K'; } } }
        }
    }
});

new Chart(document.getElementById('deductionChart'), {
    type: 'doughnut',
    data: {
        labels: ['Income Tax', 'EOBI', 'Other'],
        datasets: [{
            data: [{{ $ytdStats['tax'] }}, {{ $ytdStats['eobi'] }}, {{ max(0, $ytdStats['gross'] - $ytdStats['net'] - $ytdStats['tax'] - $ytdStats['eobi']) }}],
            backgroundColor: ['#F59E0B', '#8B5CF6', '#E8DDD5'],
            borderColor:     ['#D97706', '#7C3AED', '#D1C5BB'],
            borderWidth: 1.5,
            hoverOffset: 6
        }]
    },
    options: {
        cutout: '68%',
        plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: function(c) { return ' PKR ' + Math.round(c.raw).toLocaleString(); } } }
        }
    }
});
@endif
</script>
@endpush

@endsection