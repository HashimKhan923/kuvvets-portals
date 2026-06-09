<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>

/* ═══════════════════════════════════════════════════════════════
   KUVVETS PAYSLIP  —  matches admin portal exactly
   Portal palette (hardcoded, no CSS variables for DomPDF):
     Near-black:  #0F0901
     Gold:        #D4A843   (primary brand accent)
     Gold-light:  #F0D080   (lighter gold text)
     Gold-dim:    #A07830   (muted gold)
     Cream-bg:    #FAF7F0   (page body)
     Cream-card:  #F5F0E8   (card fills)
     Border:      #E8DDD5
     Text-dark:   #1A0F09
     Text-muted:  #8A7060
     Green:       #16A34A
     Red:         #DC2626
     Orange:      #C2531B   (PKR highlights)
     Blue:        #2563EB
     Amber:       #D97706
═══════════════════════════════════════════════════════════════ */

* { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: 'DejaVu Sans', sans-serif;
    font-size: 9.5px;
    color: #1A0F09;
    background: #ffffff;
    line-height: 1.4;
}

.page { width: 750px; margin: 0 auto; }

/* ─── HEADER ─────────────────────────────────────────────────── */
.hdr {
    background: #0F0901;
    padding: 18px 28px 16px;
    display: table;
    width: 100%;
}
.hdr-logo { display: table-cell; vertical-align: middle; width: 55%; }
.hdr-logo img { height: 52px; width: auto; display: block; }
.hdr-right {
    display: table-cell;
    vertical-align: middle;
    text-align: right;
    width: 45%;
}
.slip-label {
    font-size: 13px;
    font-weight: bold;
    letter-spacing: 3px;
    color: #D4A843;
    text-transform: uppercase;
}
.slip-num {
    font-size: 10px;
    color: #F0D080;
    margin-top: 4px;
    letter-spacing: .5px;
}
.slip-period {
    font-size: 10px;
    color: #8A7060;
    margin-top: 2px;
}

/* ─── GOLD DIVIDER ──────────────────────────────────────────── */
.gold-bar  { height: 3px; background: #D4A843; }
.dark-line { height: 1px; background: #0F0901; }

/* ─── COMPANY STRIP ─────────────────────────────────────────── */
.co-strip {
    background: #F5F0E8;
    border-bottom: 1px solid #E8DDD5;
    padding: 7px 28px;
    display: table;
    width: 100%;
}
.co-left  { display: table-cell; vertical-align: middle; font-size: 9px; color: #8A7060; }
.co-left b { color: #1A0F09; font-size: 10px; }
.co-right { display: table-cell; vertical-align: middle; text-align: right; }
.status-pill {
    display: inline-block;
    padding: 2px 10px 3px;
    border-radius: 20px;
    font-size: 8px;
    font-weight: bold;
    letter-spacing: 1.2px;
    text-transform: uppercase;
}
.pill-paid     { background: #14532D; color: #86EFAC; }
.pill-approved { background: #1E3A8A; color: #BFDBFE; }
.pill-draft    { background: #374151; color: #D1D5DB; }

/* ─── SECTION HEADING ───────────────────────────────────────── */
.sec-head {
    display: table;
    width: 100%;
    margin-bottom: 10px;
}
.sec-lbl {
    display: table-cell;
    white-space: nowrap;
    font-size: 8px;
    font-weight: bold;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: #D4A843;
    vertical-align: middle;
    padding-right: 10px;
}
.sec-rule {
    display: table-cell;
    vertical-align: middle;
    width: 100%;
    border-bottom: 1px solid #E8DDD5;
}

/* ─── EMPLOYEE INFO ──────────────────────────────────────────── */
.emp-section { padding: 16px 28px; border-bottom: 1px solid #E8DDD5; }
.emp-grid { width: 100%; border-collapse: separate; border-spacing: 5px 5px; }
.emp-cell {
    background: #FAF7F0;
    border: 1px solid #E8DDD5;
    border-radius: 4px;
    padding: 8px 10px;
    vertical-align: top;
}
.ef-lbl {
    font-size: 7.5px;
    color: #8A7060;
    text-transform: uppercase;
    letter-spacing: .8px;
    margin-bottom: 3px;
}
.ef-val {
    font-size: 10px;
    font-weight: bold;
    color: #1A0F09;
}
.ef-gold { color: #C2531B; }

/* ─── ATTENDANCE ─────────────────────────────────────────────── */
.att-section {
    background: #F5F0E8;
    border-bottom: 1px solid #E8DDD5;
    padding: 14px 28px;
}
.att-grid { display: table; width: 100%; }
.att-tile {
    display: table-cell;
    text-align: center;
    padding: 10px 6px;
    border-right: 1px solid #E8DDD5;
    background: #FAF7F0;
}
.att-tile:first-child { border-radius: 4px 0 0 4px; }
.att-tile:last-child  { border-right: none; border-radius: 0 4px 4px 0; }
.att-num { font-size: 22px; font-weight: bold; line-height: 1; }
.att-lbl {
    font-size: 7.5px;
    letter-spacing: .7px;
    text-transform: uppercase;
    color: #8A7060;
    margin-top: 4px;
}

/* ─── EARNINGS / DEDUCTIONS ──────────────────────────────────── */
.ed-section { padding: 14px 28px; border-bottom: 1px solid #E8DDD5; }
.two-col    { display: table; width: 100%; }
.col-l { display: table-cell; width: 50%; padding-right: 14px; vertical-align: top; }
.col-r { display: table-cell; width: 50%; padding-left: 14px; vertical-align: top; border-left: 2px solid #E8DDD5; }

.col-badge {
    padding: 5px 10px;
    border-radius: 3px;
    margin-bottom: 8px;
    font-size: 8.5px;
    font-weight: bold;
    letter-spacing: 1px;
    text-transform: uppercase;
}
.badge-earn { background: #F0FDF4; border: 1px solid #BBF7D0; color: #16A34A; }
.badge-ded  { background: #FFF1F2; border: 1px solid #FECDD3; color: #DC2626; }

.li { display: table; width: 100%; padding: 4px 0; border-bottom: 1px solid #F5F0E8; }
.li-lbl { display: table-cell; font-size: 9px; color: #5A3A2A; }
.li-amt { display: table-cell; text-align: right; font-size: 9px; font-weight: 600; }
.c-earn { color: #16A34A; }
.c-ded  { color: #DC2626; }
.c-abs  { color: #DC2626; font-style: italic; }
.c-info { color: #8A7060; font-style: italic; }

.sub-row {
    display: table;
    width: 100%;
    margin-top: 6px;
    padding: 6px 9px;
    border-radius: 3px;
    font-weight: bold;
}
.sub-earn { background: #DCFCE7; border: 1px solid #86EFAC; }
.sub-ded  { background: #FFE4E6; border: 1px solid #FCA5A5; }
.sub-lbl  { display: table-cell; font-size: 8.5px; letter-spacing: .5px; text-transform: uppercase; }
.sub-amt  { display: table-cell; text-align: right; font-size: 11px; }
.sub-earn .sub-lbl, .sub-earn .sub-amt { color: #16A34A; }
.sub-ded  .sub-lbl, .sub-ded  .sub-amt { color: #DC2626; }

.tax-box {
    background: #FFFBEB;
    border: 1px solid #FDE68A;
    border-left: 3px solid #D97706;
    border-radius: 3px;
    padding: 7px 9px;
    margin-top: 8px;
    font-size: 8.5px;
    color: #5A3A2A;
    line-height: 2;
}
.tax-ttl { font-size: 8px; font-weight: bold; color: #D97706; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 2px; }

/* ─── NET SALARY ─────────────────────────────────────────────── */
.net-wrap { background: #0F0901; padding: 18px 28px; }
.net-inner { display: table; width: 100%; }
.net-left  { display: table-cell; vertical-align: middle; }
.net-right { display: table-cell; vertical-align: middle; text-align: right; }
.net-lbl   { font-size: 8px; text-transform: uppercase; letter-spacing: 1.5px; color: #8A7060; }
.net-words { font-size: 8.5px; color: #D4A843; margin-top: 5px; font-style: italic; max-width: 360px; }
.net-curr  { font-size: 14px; color: #A07830; }
.net-amt   { font-size: 32px; font-weight: bold; color: #D4A843; letter-spacing: 1px; }

/* ─── SIGNATURES ─────────────────────────────────────────────── */
.sig-wrap { padding: 20px 28px 14px; display: table; width: 100%; }
.sig-cell { display: table-cell; width: 33.33%; text-align: center; vertical-align: bottom; }
.sig-box  { margin-top: 30px; border-top: 1px solid #D4A843; padding-top: 6px; }
.sig-name { font-size: 9px; color: #1A0F09; font-weight: bold; }
.sig-role { font-size: 7.5px; color: #8A7060; text-transform: uppercase; letter-spacing: .5px; margin-top: 2px; }

/* ─── DOC FOOTER ─────────────────────────────────────────────── */
.foot {
    background: #F5F0E8;
    border-top: 2px solid #D4A843;
    padding: 7px 28px;
    display: table;
    width: 100%;
}
.foot-l { display: table-cell; font-size: 7.5px; color: #8A7060; vertical-align: middle; }
.foot-r { display: table-cell; font-size: 7.5px; color: #8A7060; text-align: right; vertical-align: middle; }
.foot-r b { color: #D4A843; }

</style>
</head>
<body>
<div class="page">

{{-- ═══════════════ HEADER ═══════════════ --}}
<div class="hdr">
    <div class="hdr-logo">
        @php $logoPath = str_replace('\\', '/', public_path('kuvvet_logo.png')); @endphp
        <img src="https://portals.kuvvets.com/kuvvet_dark_logo.jpeg" width="160" alt="KUVVET">
    </div>
    <div class="hdr-right">
        <div class="slip-label">Salary Slip</div>
        <div class="slip-num">{{ $payslip->payslip_number }}</div>
        <div class="slip-period">{{ $payslip->period->month_name }}</div>
    </div>
</div>
<div class="gold-bar"></div>

{{-- ═══════════════ COMPANY STRIP ════════ --}}
<div class="co-strip">
    <div class="co-left">
        <b>{{ $payslip->company->name }}</b>
        @if($payslip->company->ntn) &nbsp;·&nbsp; NTN: {{ $payslip->company->ntn }} @endif
        @if($payslip->company->city) &nbsp;·&nbsp; {{ $payslip->company->city }}, Pakistan @endif
        @if($payslip->company->email) &nbsp;·&nbsp; {{ $payslip->company->email }} @endif
    </div>
    <div class="co-right">
        @php
            $pillClass = match($payslip->status) {
                'paid'     => 'pill-paid',
                'approved' => 'pill-approved',
                default    => 'pill-draft',
            };
        @endphp
        <span class="status-pill {{ $pillClass }}">{{ strtoupper($payslip->status) }}</span>
    </div>
</div>

{{-- ═══════════════ EMPLOYEE INFO ════════ --}}
<div class="emp-section">
    <div class="sec-head">
        <div class="sec-lbl">Employee Information</div>
        <div class="sec-rule"></div>
    </div>
    <table class="emp-grid" cellspacing="5" cellpadding="0">
        <tr>
            <td class="emp-cell" style="width:32%;">
                <div class="ef-lbl">Full Name</div>
                <div class="ef-val">{{ $payslip->employee->full_name }}</div>
            </td>
            <td class="emp-cell" style="width:17%;">
                <div class="ef-lbl">Employee ID</div>
                <div class="ef-val ef-gold">{{ $payslip->employee->employee_id }}</div>
            </td>
            <td class="emp-cell" style="width:26%;">
                <div class="ef-lbl">Department</div>
                <div class="ef-val">{{ $payslip->employee->department?->name ?? '—' }}</div>
            </td>
            <td class="emp-cell" style="width:25%;">
                <div class="ef-lbl">Designation</div>
                <div class="ef-val">{{ $payslip->employee->designation?->title ?? '—' }}</div>
            </td>
        </tr>
        <tr>
            <td class="emp-cell">
                <div class="ef-lbl">CNIC</div>
                <div class="ef-val">{{ $payslip->employee->formatted_cnic ?? '—' }}</div>
            </td>
            <td class="emp-cell">
                <div class="ef-lbl">EOBI No.</div>
                <div class="ef-val">{{ $payslip->employee->eobi_number ?? '—' }}</div>
            </td>
            <td class="emp-cell">
                <div class="ef-lbl">Bank &amp; Account</div>
                <div class="ef-val">
                    {{ $payslip->employee->bank_name ?? '—' }}
                    @if($payslip->employee->bank_account_no)
                        <span style="color:#8A7060;font-weight:normal;"> — {{ $payslip->employee->bank_account_no }}</span>
                    @endif
                </div>
            </td>
            <td class="emp-cell">
                <div class="ef-lbl">Pay Period</div>
                <div class="ef-val ef-gold">{{ $payslip->period->month_name }}</div>
            </td>
        </tr>
    </table>
</div>

{{-- ═══════════════ ATTENDANCE ════════════ --}}
<div class="att-section">
    <div class="sec-head">
        <div class="sec-lbl">Attendance Summary</div>
        <div class="sec-rule"></div>
    </div>
    <div class="att-grid">
        <div class="att-tile">
            <div class="att-num" style="color:#1A0F09;">{{ $payslip->working_days }}</div>
            <div class="att-lbl">Working Days</div>
        </div>
        <div class="att-tile">
            <div class="att-num" style="color:#16A34A;">{{ $payslip->present_days }}</div>
            <div class="att-lbl">Present</div>
        </div>
        <div class="att-tile">
            <div class="att-num" style="color:#DC2626;">{{ $payslip->absent_days }}</div>
            <div class="att-lbl">Absent</div>
        </div>
        <div class="att-tile">
            <div class="att-num" style="color:#2563EB;">{{ $payslip->leave_days }}</div>
            <div class="att-lbl">On Leave</div>
        </div>
        <div class="att-tile">
            <div class="att-num" style="color:#D97706;">{{ $payslip->overtime_hours ?? 0 }}</div>
            <div class="att-lbl">OT Hours</div>
        </div>
        <div class="att-tile">
            @php $lm = (int)($payslip->late_minutes ?? 0); @endphp
            <div class="att-num" style="color:{{ $lm > 0 ? '#DC2626' : '#8A7060' }};">
                {{ $lm >= 60 ? floor($lm/60).'h '.($lm%60).'m' : $lm.'m' }}
            </div>
            <div class="att-lbl">Late Time</div>
        </div>
    </div>
</div>

{{-- ═══════════════ EARNINGS & DEDUCTIONS ══ --}}
<div class="ed-section">
    <div class="sec-head">
        <div class="sec-lbl">Earnings &amp; Deductions</div>
        <div class="sec-rule"></div>
    </div>
    <div class="two-col">

        {{-- EARNINGS --}}
        <div class="col-l">
            <div class="col-badge badge-earn">+ Earnings</div>
            @php
            $earns = [
                ['Basic Salary',       $payslip->basic_salary],
                ['House Rent (HRA)',   $payslip->house_rent],
                ['Medical Allowance',  $payslip->medical],
                ['Conveyance',         $payslip->conveyance],
                ['Fuel Allowance',     $payslip->fuel],
                ['Utility Allowance',  $payslip->utility],
                ['Meal Allowance',     $payslip->meal],
                ['Special Allowance',  $payslip->special_allowance],
                ['Other Allowance',    $payslip->other_allowance],
                ['Overtime',           $payslip->overtime_amount],
                ['Performance Bonus',  $payslip->bonus],
                ['Arrears',            $payslip->arrears],
            ];
            @endphp
            @foreach($earns as [$lbl, $val])
            @if((float)$val > 0)
            <div class="li">
                <div class="li-lbl">{{ $lbl }}</div>
                <div class="li-amt c-earn">{{ number_format($val) }}</div>
            </div>
            @endif
            @endforeach
            @if((float)$payslip->absent_deduction > 0)
            <div class="li">
                <div class="li-lbl c-abs">Absent Deduction</div>
                <div class="li-amt c-abs">({{ number_format($payslip->absent_deduction) }})</div>
            </div>
            @endif
            <div class="sub-row sub-earn">
                <div class="sub-lbl">Gross Salary</div>
                <div class="sub-amt">PKR {{ number_format($payslip->gross_salary) }}</div>
            </div>
        </div>

        {{-- DEDUCTIONS --}}
        <div class="col-r">
            <div class="col-badge badge-ded">– Deductions</div>
            @foreach([
                ['Income Tax (WHT / FBR)', $payslip->income_tax],
                ['EOBI — Employee (1%)',   $payslip->eobi_employee],
                ['PESSI — Employee',       $payslip->pessi_employee],
                ['Loan Deduction',         $payslip->loan_deduction],
                ['Other Deductions',       $payslip->other_deduction],
            ] as [$lbl, $val])
            @if((float)$val > 0)
            <div class="li">
                <div class="li-lbl">{{ $lbl }}</div>
                <div class="li-amt c-ded">{{ number_format($val) }}</div>
            </div>
            @endif
            @endforeach
            @if((float)$payslip->eobi_employer > 0)
            <div class="li">
                <div class="li-lbl c-info">EOBI — Employer (for reference)</div>
                <div class="li-amt c-info">{{ number_format($payslip->eobi_employer) }}</div>
            </div>
            @endif
            <div class="sub-row sub-ded">
                <div class="sub-lbl">Total Deductions</div>
                <div class="sub-amt">PKR {{ number_format($payslip->total_deductions) }}</div>
            </div>
            @if((float)$payslip->income_tax > 0)
            <div class="tax-box">
                <div class="tax-ttl">FBR Tax Computation — FY 2024-25</div>
                Annual Taxable Income: <b>PKR {{ number_format($payslip->annual_taxable_income) }}</b><br>
                Annual Tax Liability:&nbsp;&nbsp; <b>PKR {{ number_format($payslip->annual_tax) }}</b><br>
                Monthly Tax This Slip:&nbsp; <b>PKR {{ number_format($payslip->monthly_tax) }}</b><br>
                Tax Slab Applied:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <b>{{ $payslip->tax_slab }}</b>
            </div>
            @endif
        </div>

    </div>
</div>

{{-- ═══════════════ NET SALARY ════════════ --}}
<div class="gold-bar"></div>
<div class="net-wrap">
    <div class="net-inner">
        <div class="net-left">
            <div class="net-lbl">Net Salary Payable</div>
            <div class="net-words">{{ $payslip->net_in_words }}</div>
        </div>
        <div class="net-right">
            <span class="net-curr">PKR&nbsp;</span><span class="net-amt">{{ number_format($payslip->net_salary) }}</span>
        </div>
    </div>
</div>
<div class="gold-bar"></div>

{{-- ═══════════════ SIGNATURES ════════════ --}}
<div class="sig-wrap">
    <div class="sig-cell">
        <div class="sig-box">
            <div class="sig-name">{{ $payslip->employee->full_name }}</div>
            <div class="sig-role">Employee Signature</div>
        </div>
    </div>
    <div class="sig-cell">
        <div class="sig-box">
            <div class="sig-name">&nbsp;</div>
            <div class="sig-role">HR Department</div>
        </div>
    </div>
    <div class="sig-cell">
        <div class="sig-box">
            <div class="sig-name">&nbsp;</div>
            <div class="sig-role">Authorised Signatory</div>
        </div>
    </div>
</div>

{{-- ═══════════════ FOOTER ═════════════════ --}}
<div class="foot">
    <div class="foot-l">
        This is a computer-generated document and does not require a physical signature.
    </div>
    <div class="foot-r">
        <b>{{ $payslip->payslip_number }}</b>
        &nbsp;·&nbsp;
        {{ now()->format('d M Y, h:i A') }} PKT
    </div>
</div>

</div>
</body>
</html>
