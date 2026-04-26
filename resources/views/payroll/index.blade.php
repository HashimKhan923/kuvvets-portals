@extends('layouts.app')
@section('title', 'Payroll')
@section('page-title', 'Payroll Management')
@section('breadcrumb', 'Finance · Payroll Dashboard')

@section('content')

{{-- Stats --}}
<div class="stats-grid-4">
    @foreach([
        ['YTD Net Paid',     'PKR ' . number_format($stats['total_paid_ytd']),    'fa-money-bill-wave', 'green'],
        ['Current Month',    'PKR ' . number_format($stats['current_month_net']), 'fa-calendar',        'accent'],
        ['Pending Approval', $stats['pending_approval'] . ' periods',             'fa-clock',           'yellow'],
        ['On Payroll',       $stats['active_employees'] . ' employees',           'fa-users',           'blue'],
    ] as [$label, $val, $icon, $color])
    <div class="stat-card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
            <div class="stat-label">{{ $label }}</div>
            <div class="stat-icon stat-icon-{{ $color }}"><i class="fa-solid {{ $icon }}"></i></div>
        </div>
        <div class="stat-num" style="font-size:20px;">{{ $val }}</div>
    </div>
    @endforeach
</div>

{{-- Quick Links --}}
<div class="quick-links">
    <a href="{{ route('payroll.salary-structures') }}" class="quick-link ql-accent">
        <i class="fa-solid fa-sliders"></i> Salary Structures
    </a>
    <a href="{{ route('payroll.tax-calculator') }}" class="quick-link ql-blue">
        <i class="fa-solid fa-calculator"></i> Tax Calculator
    </a>
    <a href="{{ route('payroll.report') }}" class="quick-link ql-purple">
        <i class="fa-solid fa-file-chart-line"></i> Payroll Report
    </a>
</div>

<div class="grid-1-340">

    {{-- Left: Chart + Periods Table --}}
    <div style="display:flex;flex-direction:column;gap:18px;">

        <div class="card">
            <div class="card-title">
                <i class="fa-solid fa-chart-area"></i> 6-Month Payroll Trend
            </div>
            <canvas id="trendChart" height="70"></canvas>
        </div>

        <div class="card card-flush">
            <div style="padding:14px 20px;border-bottom:1px solid var(--border);">
                <div class="card-title" style="margin-bottom:0;">
                    <i class="fa-solid fa-list-check"></i> Payroll Periods
                </div>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Period</th>
                        <th>Employees</th>
                        <th>Gross</th>
                        <th>Deductions</th>
                        <th>Net</th>
                        <th>Tax</th>
                        <th>Status</th>
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
                            <div style="font-size:10px;color:var(--text-muted);">{{ $p->title }}</div>
                        </td>
                        <td style="font-size:13px;font-weight:700;color:var(--accent);">
                            {{ $p->employee_count }}
                        </td>
                        <td class="muted">PKR {{ number_format($p->total_gross) }}</td>
                        <td style="font-size:12px;color:var(--red);">
                            PKR {{ number_format($p->total_deductions) }}
                        </td>
                        <td style="font-size:13px;font-weight:700;color:var(--green);">
                            PKR {{ number_format($p->total_net) }}
                        </td>
                        <td style="font-size:12px;color:var(--yellow);">
                            PKR {{ number_format($p->total_tax) }}
                        </td>
                        <td>
                            <span class="badge" style="background:{{ $badge['bg'] }};color:{{ $badge['color'] }};border:1px solid {{ $badge['border'] }};">
                                {{ ucfirst($p->status) }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('payroll.period', $p) }}" class="btn btn-secondary btn-xs">
                                Open →
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <i class="fa-solid fa-file-invoice-dollar"></i>
                                No payroll periods yet.
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

    {{-- Right: Create Period + Tax Slabs --}}
    <div class="card" style="position:sticky;top:0;">
        <div class="form-section">
            <i class="fa-solid fa-plus-circle"></i> New Payroll Period
        </div>
        <form method="POST" action="{{ route('payroll.periods.create') }}">
            @csrf
            <div style="display:flex;flex-direction:column;gap:12px;">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                    <div>
                        <label class="form-label">Month <span style="color:var(--red);">*</span></label>
                        <select name="month" required class="form-select">
                            @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}" {{ now()->month == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create(null, $m)->format('F') }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Year <span style="color:var(--red);">*</span></label>
                        <select name="year" required class="form-select">
                            @foreach([now()->year + 1, now()->year, now()->year - 1] as $y)
                            <option value="{{ $y }}" {{ now()->year == $y ? 'selected' : '' }}>
                                {{ $y }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="form-label">Payment Date <span style="color:var(--red);">*</span></label>
                    <input type="date" name="payment_date" required
                           value="{{ now()->endOfMonth()->format('Y-m-d') }}"
                           class="form-input">
                </div>
                <div>
                    <label class="form-label">Notes</label>
                    <textarea name="notes" rows="2" class="form-textarea"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-plus"></i> Create Period
                </button>
            </div>
        </form>

        <hr class="divider">
        <div class="section-label">FBR Tax Slabs 2024-25</div>
        @foreach(\App\Services\TaxCalculator::getSlabs() as $slab)
        <div style="display:flex;justify-content:space-between;padding:5px 0;
                    border-bottom:1px solid var(--border);font-size:11px;">
            <span style="color:var(--text-muted);">{{ $slab['label'] }}</span>
            <span style="color:var(--accent);font-weight:700;">{{ $slab['rate'] * 100 }}%</span>
        </div>
        @endforeach
        <a href="{{ route('payroll.tax-calculator') }}"
           style="display:block;text-align:center;margin-top:10px;font-size:11px;
                  color:var(--accent);text-decoration:none;">
            Open Tax Calculator →
        </a>
    </div>

</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
var trendData = @json($trend);
new Chart(document.getElementById('trendChart'), {
    type: 'bar',
    data: {
        labels: trendData.map(function(t) { return t.month; }),
        datasets: [
            {
                label: 'Gross',
                data: trendData.map(function(t) { return t.gross; }),
                backgroundColor: 'rgba(194,83,27,.2)',
                borderColor: '#C2531B',
                borderWidth: 1.5,
                borderRadius: 4
            },
            {
                label: 'Net',
                data: trendData.map(function(t) { return t.net; }),
                backgroundColor: 'rgba(34,197,94,.15)',
                borderColor: '#22C55E',
                borderWidth: 1.5,
                borderRadius: 4
            },
            {
                label: 'Tax',
                data: trendData.map(function(t) { return t.tax; }),
                type: 'line',
                borderColor: '#F59E0B',
                borderWidth: 2,
                tension: 0.4,
                fill: false,
                pointRadius: 3,
                yAxisID: 'y1'
            }
        ]
    },
    options: {
        responsive: true,
        interaction: { mode: 'index', intersect: false },
        plugins: {
            legend: { labels: { color: '#A89080', font: { family: 'Plus Jakarta Sans', size: 11 } } },
            tooltip: { callbacks: { label: function(c) { return ' ' + c.dataset.label + ': PKR ' + c.raw.toLocaleString(); } } }
        },
        scales: {
            x:  { grid: { color: '#F0EAE2' }, ticks: { color: '#A89080', font: { size: 11 } } },
            y:  { grid: { color: '#F0EAE2' }, ticks: { color: '#A89080', font: { size: 11 }, callback: function(v) { return 'PKR ' + (v/1000).toFixed(0) + 'K'; } } },
            y1: { position: 'right', grid: { display: false }, ticks: { color: '#F59E0B', font: { size: 10 }, callback: function(v) { return 'PKR ' + (v/1000).toFixed(0) + 'K'; } } }
        }
    }
});
</script>
@endpush

@endsection