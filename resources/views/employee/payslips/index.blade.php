@extends('employee.layouts.app')
@section('title', 'Payslips')
@section('page-title', 'My Payslips')
@section('page-sub', 'Salary slips and payment history')

@push('styles')
<style>
    /* ═══════════════════════════════════════════════════
       PAYSLIPS PAGE
    ═══════════════════════════════════════════════════ */

    /* YTD banner */
    .ytd-hero {
        background: linear-gradient(135deg, #2D1F14 0%, #3d2a1c 100%);
        color: #fff; border-radius: 20px; padding: 26px;
        position: relative; overflow: hidden;
        margin-bottom: 22px;
    }
    .ytd-hero::before {
        content:''; position:absolute; top:-50%; right:-15%;
        width:420px; height:420px;
        background: radial-gradient(circle, rgba(76,175,80,.18), transparent 60%);
        pointer-events:none;
    }
    .ytd-hero::after {
        content:''; position:absolute; bottom:-40%; left:-10%;
        width:320px; height:320px;
        background: radial-gradient(circle, rgba(232,122,69,.13), transparent 60%);
        pointer-events:none;
    }
    .ytd-row {
        position:relative; z-index:1;
        display: flex; align-items: start; justify-content: space-between;
        gap: 16px; flex-wrap: wrap; margin-bottom: 22px;
    }
    .ytd-label { font-size: 10.5px; letter-spacing: 1.2px; text-transform: uppercase; color: rgba(255,255,255,.55); font-weight: 600; }
    .ytd-title { font-family:'Space Grotesk',sans-serif; font-size: 18px; font-weight: 700; margin-top: 4px; }

    .ytd-year-sel {
        background: rgba(255,255,255,.1);
        border: 1px solid rgba(255,255,255,.18);
        color: #fff; padding: 8px 14px;
        border-radius: 10px;
        font: inherit; font-size: 13px; font-weight: 600; cursor: pointer;
        appearance: none;
        background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23fff' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        padding-right: 34px;
    }
    .ytd-year-sel option { background: #2D1F14; color: #fff; }

    .ytd-metrics {
        position:relative; z-index:1;
        display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px;
    }
    @media (max-width:780px) { .ytd-metrics { grid-template-columns: repeat(2, 1fr); } }

    .ytd-metric {
        background: rgba(255,255,255,.05);
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 12px;
        padding: 14px 16px;
    }
    .ytd-metric-lbl {
        font-size: 10px; color: rgba(255,255,255,.55);
        letter-spacing: .6px; text-transform: uppercase; font-weight: 600;
        display: flex; align-items: center; gap: 5px;
    }
    .ytd-metric-val {
        font-family:'Space Grotesk',sans-serif;
        font-size: 22px; font-weight: 700; margin-top: 6px;
        font-variant-numeric: tabular-nums;
        line-height: 1.1;
    }
    .ytd-metric-val .cur {
        font-size: 11px; color: rgba(255,255,255,.5); font-weight: 500;
        margin-right: 2px;
    }
    .ytd-metric.net { background: rgba(76,175,80,.12); border-color: rgba(76,175,80,.25); }
    .ytd-metric.net .ytd-metric-val { color: #86efac; }

    /* Latest card + list */
    .section-title {
        font-size: 12px; font-weight: 700; color: var(--text-primary);
        letter-spacing: .5px; text-transform: uppercase;
        font-family: 'Space Grotesk', sans-serif;
        margin-bottom: 12px;
        display: flex; align-items: center; justify-content: space-between;
    }

    .slip-grid {
        display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 14px;
    }

    .slip-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 18px;
        text-decoration: none;
        color: inherit;
        display: block;
        transition: all .18s;
        position: relative;
        overflow: hidden;
    }
    .slip-card:hover {
        border-color: var(--accent);
        transform: translateY(-2px);
        box-shadow: 0 12px 24px rgba(45,31,20,.08);
    }
    .slip-card::before {
        content:''; position: absolute; left: 0; top: 0; bottom: 0;
        width: 4px; background: var(--accent-grad);
    }

    .slip-hd { display: flex; align-items: start; justify-content: space-between; margin-bottom: 14px; }
    .slip-month {
        font-family: 'Space Grotesk', sans-serif;
        font-size: 16px; font-weight: 700; color: var(--text-primary);
        letter-spacing: -.2px;
    }
    .slip-num { font-size: 10.5px; color: var(--text-muted); letter-spacing: .3px; margin-top: 2px; }

    .slip-amt-lbl { font-size: 10px; color: var(--text-muted); text-transform: uppercase; letter-spacing: .6px; font-weight: 600; }
    .slip-amt {
        font-family: 'Space Grotesk', sans-serif;
        font-size: 24px; font-weight: 700;
        color: var(--text-primary);
        line-height: 1;
        font-variant-numeric: tabular-nums;
        margin-top: 4px;
    }
    .slip-amt .cur { font-size: 11px; color: var(--text-muted); font-weight: 500; margin-right: 2px; }

    .slip-ftr {
        display: flex; align-items: center; justify-content: space-between;
        margin-top: 14px; padding-top: 14px;
        border-top: 1px dashed var(--border);
    }
    .slip-dl {
        font-size: 11px; color: var(--accent);
        font-weight: 700; letter-spacing: .3px;
        display: flex; align-items: center; gap: 4px;
    }

    .slip-gross {
        font-size: 11px; color: var(--text-muted);
    }
    .slip-gross strong { color: var(--text-secondary); font-weight: 600; }

    /* Latest highlight */
    .latest-card {
        background: linear-gradient(135deg, var(--accent-bg), #fff);
        border: 1.5px solid var(--accent-border);
        border-radius: 18px;
        padding: 22px;
        display: flex; align-items: center; gap: 18px;
        margin-bottom: 22px;
        position: relative;
        overflow: hidden;
    }
    .latest-card::after {
        content:''; position:absolute; top:-40%; right:-10%;
        width: 300px; height: 300px;
        background: radial-gradient(circle, rgba(194,83,27,.08), transparent 60%);
        pointer-events:none;
    }
    .latest-ico {
        width: 56px; height: 56px; border-radius: 14px;
        background: var(--accent-grad);
        color: #fff; display: flex; align-items: center; justify-content: center;
        font-size: 22px;
        box-shadow: 0 8px 20px rgba(194,83,27,.3);
        flex-shrink: 0;
        position:relative; z-index:1;
    }
    .latest-body { flex: 1; min-width: 0; position:relative; z-index:1; }
    .latest-body-lbl { font-size: 10.5px; color: var(--text-muted); letter-spacing: .8px; text-transform: uppercase; font-weight: 600; }
    .latest-body-title {
        font-family:'Space Grotesk',sans-serif;
        font-size: 18px; font-weight: 700; margin-top: 3px;
    }
    .latest-body-sub {
        font-size: 12px; color: var(--text-muted); margin-top: 3px;
    }
    .latest-amt {
        text-align: right; position:relative; z-index:1;
    }
    .latest-amt-val {
        font-family:'Space Grotesk',sans-serif;
        font-size: 28px; font-weight: 700; color: var(--accent);
        line-height: 1; font-variant-numeric: tabular-nums;
    }
    .latest-amt-val .cur { font-size: 13px; color: var(--text-muted); font-weight: 500; margin-right: 2px; }
    .latest-actions { display: flex; gap: 8px; margin-top: 10px; justify-content: flex-end; flex-wrap: wrap; }

    @media (max-width:640px) {
        .latest-card { flex-direction: column; align-items: stretch; text-align: center; }
        .latest-amt { text-align: center; }
        .latest-actions { justify-content: center; }
    }

    /* Empty state */
    .empty-state {
        text-align: center; padding: 80px 20px;
        color: var(--text-muted);
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 16px;
    }
    .empty-state i.big { font-size: 44px; opacity: .3; margin-bottom: 14px; display: block; color: var(--accent); }
</style>
@endpush

@section('content')

{{-- ═══════════ YTD Hero ═══════════ --}}
<div class="ytd-hero">
    <div class="ytd-row">
        <div>
            <div class="ytd-label">Year to Date</div>
            <div class="ytd-title">{{ $year }} Earnings Summary</div>
        </div>

        <select class="ytd-year-sel" onchange="window.location.href=this.value">
            @foreach($availableYears as $y)
                <option value="{{ route('employee.payslips.index', ['year'=>$y]) }}" {{ $year == $y ? 'selected' : '' }}>
                    Year {{ $y }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="ytd-metrics">
        <div class="ytd-metric">
            <div class="ytd-metric-lbl"><i class="fa-solid fa-money-bill-trend-up"></i> Gross</div>
            <div class="ytd-metric-val"><span class="cur">PKR</span>{{ number_format((float)$ytd->total_gross, 0) }}</div>
        </div>
        <div class="ytd-metric">
            <div class="ytd-metric-lbl"><i class="fa-solid fa-receipt"></i> Deductions</div>
            <div class="ytd-metric-val"><span class="cur">PKR</span>{{ number_format((float)$ytd->total_deductions, 0) }}</div>
        </div>
        <div class="ytd-metric">
            <div class="ytd-metric-lbl"><i class="fa-solid fa-landmark"></i> Income Tax</div>
            <div class="ytd-metric-val"><span class="cur">PKR</span>{{ number_format((float)$ytd->total_tax, 0) }}</div>
        </div>
        <div class="ytd-metric net">
            <div class="ytd-metric-lbl" style="color: #86efac;"><i class="fa-solid fa-wallet"></i> Net Received</div>
            <div class="ytd-metric-val"><span class="cur" style="color:#86efac;">PKR</span>{{ number_format((float)$ytd->total_net, 0) }}</div>
        </div>
    </div>
</div>

{{-- ═══════════ Latest payslip card ═══════════ --}}
@if($latest)
    @php
        $latestStatus = match($latest->status) {
            'paid'     => ['badge-green', 'Paid'],
            'approved' => ['badge-yellow', 'Approved'],
            default    => ['badge-gray', ucfirst($latest->status)],
        };
    @endphp
    <div class="section-title">
        <span>Most Recent Payslip</span>
    </div>
    <div class="latest-card">
        <div class="latest-ico"><i class="fa-solid fa-receipt"></i></div>
        <div class="latest-body">
            <div class="latest-body-lbl">Payslip</div>
            <div class="latest-body-title">{{ $latest->period->month_name }}</div>
            <div class="latest-body-sub">
                {{ $latest->payslip_number }}
                <span style="margin:0 6px;color:var(--border-strong);">•</span>
                <span class="badge {{ $latestStatus[0] }}" style="font-size:10px;">{{ $latestStatus[1] }}</span>
                @if($latest->period->payment_date)
                    <span style="margin:0 6px;color:var(--border-strong);">•</span>
                    Paid {{ $latest->period->payment_date->format('M j, Y') }}
                @endif
            </div>
        </div>
        <div class="latest-amt">
            <div style="font-size:10.5px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.6px;font-weight:600;">Net Amount</div>
            <div class="latest-amt-val"><span class="cur">PKR</span>{{ number_format((float)$latest->net_salary, 0) }}</div>
            <div class="latest-actions">
                <a href="{{ route('employee.payslips.show', $latest) }}" class="btn btn-secondary" style="padding:8px 14px;font-size:12px;">
                    <i class="fa-solid fa-eye"></i> View
                </a>
                <a href="{{ route('employee.payslips.pdf', $latest) }}" class="btn btn-primary" style="padding:8px 14px;font-size:12px;">
                    <i class="fa-solid fa-download"></i> PDF
                </a>
            </div>
        </div>
    </div>
@endif

{{-- ═══════════ All payslips grid ═══════════ --}}
<div class="section-title">
    <span>All Payslips — {{ $year }}</span>
    <span style="font-size:11px;color:var(--text-muted);font-weight:500;text-transform:none;letter-spacing:0;">
        {{ $payslips->count() }} payslip{{ $payslips->count() === 1 ? '' : 's' }}
    </span>
</div>

@if($payslips->count())
    <div class="slip-grid">
        @foreach($payslips as $p)
            @php
                $statusBadge = match($p->status) {
                    'paid'     => ['badge-green', 'fa-circle-check', 'Paid'],
                    'approved' => ['badge-yellow', 'fa-hourglass-half', 'Approved'],
                    default    => ['badge-gray', 'fa-circle', ucfirst($p->status)],
                };
            @endphp
            <a href="{{ route('employee.payslips.show', $p) }}" class="slip-card">
                <div class="slip-hd">
                    <div>
                        <div class="slip-month">{{ $p->period->month_name }}</div>
                        <div class="slip-num">{{ $p->payslip_number }}</div>
                    </div>
                    <span class="badge {{ $statusBadge[0] }}">
                        <i class="fa-solid {{ $statusBadge[1] }}" style="font-size:9px;"></i>
                        {{ $statusBadge[2] }}
                    </span>
                </div>

                <div class="slip-amt-lbl">Net salary</div>
                <div class="slip-amt"><span class="cur">PKR</span>{{ number_format((float)$p->net_salary, 0) }}</div>

                <div class="slip-ftr">
                    <div class="slip-gross">
                        <strong>Gross:</strong> PKR {{ number_format((float)$p->gross_salary, 0) }}
                    </div>
                    <div class="slip-dl">
                        View <i class="fa-solid fa-arrow-right" style="font-size:9px;"></i>
                    </div>
                </div>
            </a>
        @endforeach
    </div>
@else
    <div class="empty-state">
        <i class="fa-solid fa-receipt big"></i>
        <div style="font-size:15px;font-weight:700;color:var(--text-secondary);margin-bottom:6px;">
            No payslips for {{ $year }}
        </div>
        <div style="font-size:12.5px;">
            Your payslips will appear here once HR processes the payroll.
        </div>
    </div>
@endif

@endsection