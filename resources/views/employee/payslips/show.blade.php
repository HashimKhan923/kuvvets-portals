@extends('employee.layouts.app')
@section('title', 'Payslip — ' . $payslip->period->month_name)
@section('page-title', $payslip->period->month_name)
@section('page-sub', 'Payslip #' . $payslip->payslip_number)

@push('styles')
<style>
    /* ═══════════════════════════════════════════════════
       PAYSLIP DETAIL
    ═══════════════════════════════════════════════════ */
    .ps-wrap {
        max-width: 900px;
    }

    /* Top action bar */
    .ps-actions {
        display: flex; align-items: center; justify-content: space-between;
        gap: 10px; margin-bottom: 18px; flex-wrap: wrap;
    }

    /* Hero net salary card */
    .ps-hero {
        background: linear-gradient(135deg, #2D1F14 0%, #3d2a1c 100%);
        color: #fff; border-radius: 20px; padding: 28px;
        position: relative; overflow: hidden;
        margin-bottom: 18px;
    }
    .ps-hero::before {
        content:''; position:absolute; top:-50%; right:-10%;
        width: 380px; height: 380px;
        background: radial-gradient(circle, rgba(76,175,80,.2), transparent 60%);
    }
    .ps-hero-grid {
        position:relative; z-index:1;
        display:grid; grid-template-columns: 1fr auto; gap:18px; align-items:center;
    }
    @media (max-width:640px) { .ps-hero-grid { grid-template-columns: 1fr; text-align:center; } }

    .ps-hero-lbl {
        font-size: 10.5px; letter-spacing: 1.2px; text-transform: uppercase;
        color: rgba(255,255,255,.55); font-weight: 600;
    }
    .ps-hero-amt {
        font-family:'Space Grotesk',sans-serif;
        font-size: 46px; font-weight: 700; line-height: 1;
        margin-top: 6px;
        font-variant-numeric: tabular-nums;
    }
    .ps-hero-amt .cur { font-size: 18px; color: rgba(255,255,255,.55); font-weight: 500; margin-right: 4px; }
    .ps-hero-words {
        font-size: 11.5px; color: rgba(255,255,255,.7);
        font-style: italic; margin-top: 10px;
        line-height: 1.5;
    }

    .ps-hero-badge {
        display:inline-flex; align-items:center; gap:6px;
        padding: 8px 14px; border-radius: 999px;
        font-size: 12px; font-weight: 700;
        background: rgba(76,175,80,.15);
        border: 1px solid rgba(76,175,80,.3);
        color: #86efac;
    }
    .ps-hero-badge.approved {
        background: rgba(245,158,11,.15);
        border-color: rgba(245,158,11,.3);
        color: #fde68a;
    }

    /* Main grid */
    .ps-grid {
        display: grid; grid-template-columns: 1fr 1fr; gap: 16px;
    }
    @media (max-width: 780px) { .ps-grid { grid-template-columns: 1fr; } }

    /* Card */
    .ps-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 16px;
        overflow: hidden;
    }
    .ps-card-hd {
        padding: 14px 18px;
        border-bottom: 1px solid var(--border);
        display: flex; align-items: center; justify-content: space-between;
    }
    .ps-card-title {
        font-family: 'Space Grotesk', sans-serif;
        font-size: 13px; font-weight: 700;
        letter-spacing: .4px;
    }
    .ps-card-title i { margin-right: 6px; }
    .ps-card-body { padding: 8px 0; }

    /* Line item rows */
    .ps-row {
        display: flex; align-items: center; justify-content: space-between;
        padding: 10px 18px;
        font-size: 13px;
    }
    .ps-row-lbl { color: var(--text-secondary); display: flex; align-items: center; gap: 6px; }
    .ps-row-val {
        font-weight: 600; color: var(--text-primary);
        font-variant-numeric: tabular-nums;
    }
    .ps-row-val .cur { font-size: 10px; color: var(--text-muted); font-weight: 500; margin-right: 2px; }

    .ps-total {
        display: flex; align-items: center; justify-content: space-between;
        padding: 14px 18px;
        background: var(--bg-muted);
        border-top: 1px solid var(--border);
        font-family: 'Space Grotesk', sans-serif;
        font-weight: 700;
    }
    .ps-total-lbl { font-size: 12px; text-transform: uppercase; letter-spacing: .6px; }
    .ps-total-val {
        font-size: 16px; font-variant-numeric: tabular-nums;
    }
    .ps-total-val .cur { font-size: 11px; font-weight: 500; color: var(--text-muted); margin-right: 2px; }

    .ps-card.earn .ps-total { background: var(--green-bg); color: var(--green); }
    .ps-card.earn .ps-total-val { color: var(--green); }
    .ps-card.earn .ps-total-val .cur { color: var(--green); opacity:.7; }

    .ps-card.ded .ps-total { background: var(--red-bg); color: var(--red); }
    .ps-card.ded .ps-total-val { color: var(--red); }
    .ps-card.ded .ps-total-val .cur { color: var(--red); opacity:.7; }

    /* Attendance / info grid */
    .info-grid {
        display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px;
        padding: 14px;
    }
    @media (max-width: 520px) { .info-grid { grid-template-columns: repeat(2, 1fr); } }

    .info-tile {
        background: var(--bg-muted);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 12px;
        text-align: center;
    }
    .info-tile-lbl {
        font-size: 10px; color: var(--text-muted);
        letter-spacing: .5px; text-transform: uppercase; font-weight: 600;
    }
    .info-tile-val {
        font-family: 'Space Grotesk', sans-serif;
        font-size: 20px; font-weight: 700; margin-top: 5px;
        font-variant-numeric: tabular-nums;
    }
    .info-tile.good .info-tile-val { color: var(--green); }
    .info-tile.warn .info-tile-val { color: var(--yellow); }
    .info-tile.bad  .info-tile-val { color: var(--red); }

    /* Meta info */
    .meta-card {
        padding: 18px;
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 16px;
        display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px;
        margin-bottom: 18px;
    }
    @media (max-width: 640px) { .meta-card { grid-template-columns: repeat(2, 1fr); } }

    .meta-lbl {
        font-size: 10.5px; color: var(--text-muted);
        letter-spacing: .6px; text-transform: uppercase; font-weight: 600;
    }
    .meta-val {
        font-size: 13.5px; font-weight: 700; margin-top: 4px;
        color: var(--text-primary);
    }

    /* Tax info */
    .tax-info {
        padding: 14px 18px;
        background: var(--blue-bg);
        border-radius: 12px;
        margin: 14px 18px 18px;
        display: flex; align-items: center; gap: 12px;
        font-size: 12px; color: var(--blue); line-height: 1.5;
    }
    .tax-info i { font-size: 16px; flex-shrink: 0; }

    /* Remarks */
    .remarks-card {
        padding: 14px 18px;
        background: var(--yellow-bg);
        border: 1px solid var(--yellow-border);
        border-radius: 12px;
        margin-top: 14px;
        font-size: 13px; color: var(--yellow);
    }
    .remarks-card-lbl { font-weight: 700; margin-bottom: 4px; letter-spacing: .3px; }
</style>
@endpush

@section('content')

<div class="ps-wrap">

    {{-- ═══════════ Actions ═══════════ --}}
    <div class="ps-actions">
        <a href="{{ route('employee.payslips.index') }}" class="btn btn-ghost">
            <i class="fa-solid fa-arrow-left"></i> Back to Payslips
        </a>

        <div style="display:flex;gap:8px;">
            <a href="{{ route('employee.payslips.pdf', $payslip) }}" class="btn btn-primary">
                <i class="fa-solid fa-file-pdf"></i> Download PDF
            </a>
            <button onclick="window.print()" class="btn btn-secondary">
                <i class="fa-solid fa-print"></i> Print
            </button>
        </div>
    </div>

    {{-- ═══════════ Net salary hero ═══════════ --}}
    <div class="ps-hero">
        <div class="ps-hero-grid">
            <div>
                <div class="ps-hero-lbl">Net Salary for {{ $payslip->period->month_name }}</div>
                <div class="ps-hero-amt">
                    <span class="cur">PKR</span>{{ number_format((float)$payslip->net_salary, 2) }}
                </div>
                <div class="ps-hero-words">{{ $payslip->net_in_words }}</div>
            </div>
            <div style="display:flex;flex-direction:column;gap:10px;align-items:flex-end;">
                @if($payslip->status === 'paid')
                    <span class="ps-hero-badge">
                        <i class="fa-solid fa-circle-check"></i>
                        Paid
                    </span>
                @elseif($payslip->status === 'approved')
                    <span class="ps-hero-badge approved">
                        <i class="fa-solid fa-hourglass-half"></i>
                        Approved
                    </span>
                @endif
                @if($payslip->period->payment_date)
                    <div style="font-size:11px;color:rgba(255,255,255,.65);text-align:right;">
                        Payment: {{ $payslip->period->payment_date->format('M j, Y') }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ═══════════ Meta ═══════════ --}}
    <div class="meta-card">
        <div>
            <div class="meta-lbl">Payslip #</div>
            <div class="meta-val">{{ $payslip->payslip_number }}</div>
        </div>
        <div>
            <div class="meta-lbl">Period</div>
            <div class="meta-val">{{ $payslip->period->month_name }}</div>
        </div>
        <div>
            <div class="meta-lbl">Employee ID</div>
            <div class="meta-val">{{ $payslip->employee->employee_id }}</div>
        </div>
    </div>

    {{-- ═══════════ Earnings + Deductions ═══════════ --}}
    <div class="ps-grid">

        {{-- EARNINGS --}}
        <div class="ps-card earn">
            <div class="ps-card-hd">
                <div class="ps-card-title">
                    <i class="fa-solid fa-arrow-trend-up" style="color:var(--green);"></i>
                    Earnings
                </div>
                <span style="font-size:11px;color:var(--text-muted);">{{ count($earnings) }} items</span>
            </div>
            <div class="ps-card-body">
                @forelse($earnings as $label => $amt)
                    <div class="ps-row">
                        <span class="ps-row-lbl"><i class="fa-solid fa-plus" style="color:var(--green);font-size:9px;"></i>{{ $label }}</span>
                        <span class="ps-row-val"><span class="cur">PKR</span>{{ number_format($amt, 2) }}</span>
                    </div>
                @empty
                    <div style="padding: 40px 18px; text-align:center; color:var(--text-muted); font-size:12.5px;">
                        No earnings data
                    </div>
                @endforelse
            </div>
            <div class="ps-total">
                <span class="ps-total-lbl">Gross Salary</span>
                <span class="ps-total-val"><span class="cur">PKR</span>{{ number_format((float)$payslip->gross_salary, 2) }}</span>
            </div>
        </div>

        {{-- DEDUCTIONS --}}
        <div class="ps-card ded">
            <div class="ps-card-hd">
                <div class="ps-card-title">
                    <i class="fa-solid fa-arrow-trend-down" style="color:var(--red);"></i>
                    Deductions
                </div>
                <span style="font-size:11px;color:var(--text-muted);">{{ count($deductions) }} items</span>
            </div>
            <div class="ps-card-body">
                @forelse($deductions as $label => $amt)
                    <div class="ps-row">
                        <span class="ps-row-lbl"><i class="fa-solid fa-minus" style="color:var(--red);font-size:9px;"></i>{{ $label }}</span>
                        <span class="ps-row-val"><span class="cur">PKR</span>{{ number_format($amt, 2) }}</span>
                    </div>
                @empty
                    <div style="padding: 40px 18px; text-align:center; color:var(--text-muted); font-size:12.5px;">
                        No deductions
                    </div>
                @endforelse
            </div>
            <div class="ps-total">
                <span class="ps-total-lbl">Total Deductions</span>
                <span class="ps-total-val"><span class="cur">PKR</span>{{ number_format((float)$payslip->total_deductions, 2) }}</span>
            </div>
        </div>
    </div>

    {{-- ═══════════ Attendance summary ═══════════ --}}
    <div class="ps-card" style="margin-top:18px;">
        <div class="ps-card-hd">
            <div class="ps-card-title">
                <i class="fa-solid fa-calendar-check" style="color:var(--accent);"></i>
                Attendance for {{ $payslip->period->month_name }}
            </div>
        </div>
        <div class="info-grid">
            <div class="info-tile">
                <div class="info-tile-lbl">Working Days</div>
                <div class="info-tile-val">{{ $payslip->working_days ?? 0 }}</div>
            </div>
            <div class="info-tile good">
                <div class="info-tile-lbl">Present</div>
                <div class="info-tile-val">{{ $payslip->present_days ?? 0 }}</div>
            </div>
            <div class="info-tile warn">
                <div class="info-tile-lbl">Leave</div>
                <div class="info-tile-val">{{ $payslip->leave_days ?? 0 }}</div>
            </div>
            <div class="info-tile bad">
                <div class="info-tile-lbl">Absent</div>
                <div class="info-tile-val">{{ $payslip->absent_days ?? 0 }}</div>
            </div>
            @if($payslip->overtime_hours > 0)
                <div class="info-tile" style="grid-column: span 2;">
                    <div class="info-tile-lbl">Overtime Hours</div>
                    <div class="info-tile-val" style="color:var(--purple);">{{ number_format((float)$payslip->overtime_hours, 1) }}h</div>
                </div>
            @endif
        </div>
    </div>

    {{-- ═══════════ Tax info ═══════════ --}}
    @if($payslip->income_tax > 0 || $payslip->annual_tax > 0)
        <div class="ps-card" style="margin-top:18px;">
            <div class="ps-card-hd">
                <div class="ps-card-title">
                    <i class="fa-solid fa-landmark" style="color: var(--blue);"></i>
                    Tax Information
                </div>
            </div>
            <div class="ps-card-body">
                @if($payslip->annual_taxable_income > 0)
                    <div class="ps-row">
                        <span class="ps-row-lbl">Annual taxable income</span>
                        <span class="ps-row-val"><span class="cur">PKR</span>{{ number_format((float)$payslip->annual_taxable_income, 2) }}</span>
                    </div>
                @endif
                @if($payslip->annual_tax > 0)
                    <div class="ps-row">
                        <span class="ps-row-lbl">Annual tax</span>
                        <span class="ps-row-val"><span class="cur">PKR</span>{{ number_format((float)$payslip->annual_tax, 2) }}</span>
                    </div>
                @endif
                @if($payslip->monthly_tax > 0)
                    <div class="ps-row">
                        <span class="ps-row-lbl">Monthly tax</span>
                        <span class="ps-row-val"><span class="cur">PKR</span>{{ number_format((float)$payslip->monthly_tax, 2) }}</span>
                    </div>
                @endif
                @if($payslip->tax_slab)
                    <div class="ps-row">
                        <span class="ps-row-lbl">Tax slab</span>
                        <span class="ps-row-val" style="font-family:inherit;">{{ $payslip->tax_slab }}</span>
                    </div>
                @endif
            </div>
            <div class="tax-info">
                <i class="fa-solid fa-circle-info"></i>
                <span>Tax deducted according to Pakistan FBR slabs. For queries contact your HR department.</span>
            </div>
        </div>
    @endif

    {{-- ═══════════ Remarks ═══════════ --}}
    @if($payslip->remarks)
        <div class="remarks-card">
            <div class="remarks-card-lbl"><i class="fa-solid fa-note-sticky"></i> HR Remarks</div>
            <div>{{ $payslip->remarks }}</div>
        </div>
    @endif

    {{-- ═══════════ Footer note ═══════════ --}}
    <div style="text-align:center;margin-top:24px;padding:20px;color:var(--text-muted);font-size:11.5px;line-height:1.7;">
        <div style="font-weight:600;color:var(--text-secondary);margin-bottom:4px;">
            <i class="fa-solid fa-shield-check" style="color:var(--green);"></i> This is a computer-generated payslip
        </div>
        No signature is required. For any discrepancies, please contact HR within 7 days of issue.
    </div>
</div>

@endsection