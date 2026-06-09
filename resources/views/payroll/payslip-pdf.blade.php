<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        /* ═══════════════════════════════════════════════════════════
           KUVVETS PAYSLIP PDF  —  Daybreak Theme (DomPDF-safe CSS)
           Palette:
             Mahogany:    #1A0F09   (header bg)
             Terracotta:  #C2531B   (primary accent)
             Gold:        #D4A843   (secondary accent)
             Warm BG:     #FBF8F5
             Cream:       #F5F0EB
             Border:      #E8DDD5
             Text:        #2D1F14
             Muted:       #A89080
             Green:       #16A34A
             Red:         #DC2626
             Blue:        #2563EB
             Amber:       #D97706
        ═══════════════════════════════════════════════════════════ */

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9.5px;
            color: #2D1F14;
            background: #ffffff;
        }

        .page { width: 740px; margin: 0 auto; background: #ffffff; }

        /* ── HEADER ─────────────────────────────────────────────── */
        .header {
            background: #1A0F09;
            padding: 0;
        }
        .header-top {
            display: table;
            width: 100%;
            padding: 16px 28px 14px;
        }
        .header-logo-cell {
            display: table-cell;
            vertical-align: middle;
            width: 50%;
        }
        .header-logo-cell img {
            height: 44px;
            width: auto;
        }
        .header-title-cell {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            width: 50%;
        }
        .slip-badge {
            display: inline-block;
            background: #C2531B;
            color: #ffffff;
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 2.5px;
            padding: 4px 14px;
            border-radius: 3px;
        }
        .slip-meta {
            margin-top: 5px;
            font-size: 9px;
            color: #A89080;
            line-height: 1.6;
        }
        .slip-meta span { color: #D4A843; font-weight: bold; }

        /* ── ACCENT STRIPE ──────────────────────────────────────── */
        .stripe-outer {
            height: 5px;
            background: #1A0F09;
        }
        .stripe-inner {
            height: 3px;
            background: #C2531B;
        }
        .stripe-gold {
            height: 2px;
            background: #D4A843;
        }

        /* ── COMPANY BAR ────────────────────────────────────────── */
        .company-bar {
            background: #F5F0EB;
            border-bottom: 1px solid #E8DDD5;
            padding: 7px 28px;
            display: table;
            width: 100%;
        }
        .company-bar-left {
            display: table-cell;
            vertical-align: middle;
            font-size: 9px;
            color: #A89080;
        }
        .company-bar-right {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            font-size: 9px;
            color: #A89080;
        }
        .company-bar b { color: #2D1F14; }

        /* ── SECTION ────────────────────────────────────────────── */
        .section {
            padding: 14px 28px;
            border-bottom: 1px solid #E8DDD5;
        }
        .section-head {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }
        .section-label {
            display: table-cell;
            font-size: 8px;
            font-weight: bold;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #A89080;
            vertical-align: middle;
        }
        .section-rule {
            display: table-cell;
            vertical-align: middle;
            width: 99%;
            padding-left: 10px;
        }
        .section-rule hr {
            border: none;
            border-top: 1px solid #E8DDD5;
        }

        /* ── EMPLOYEE INFO GRID ─────────────────────────────────── */
        .info-table { width: 100%; border-collapse: collapse; }
        .info-card {
            background: #FBF8F5;
            border: 1px solid #E8DDD5;
            border-radius: 4px;
            padding: 8px 10px;
            vertical-align: top;
        }
        .info-label {
            font-size: 7.5px;
            color: #A89080;
            text-transform: uppercase;
            letter-spacing: .8px;
            margin-bottom: 3px;
        }
        .info-value {
            font-size: 10px;
            color: #2D1F14;
            font-weight: bold;
        }
        .info-accent { color: #C2531B; }

        /* ── ATTENDANCE BOXES ───────────────────────────────────── */
        .att-outer {
            padding: 14px 28px;
            background: #F5F0EB;
            border-bottom: 1px solid #E8DDD5;
        }
        .att-row { display: table; width: 100%; border-collapse: separate; border-spacing: 0; }
        .att-box {
            display: table-cell;
            text-align: center;
            padding: 10px 8px;
            border-right: 1px solid #E8DDD5;
        }
        .att-box:last-child { border-right: none; }
        .att-num {
            font-size: 20px;
            font-weight: bold;
            line-height: 1;
        }
        .att-lbl {
            font-size: 7.5px;
            text-transform: uppercase;
            letter-spacing: .7px;
            color: #A89080;
            margin-top: 4px;
        }

        /* ── EARNINGS / DEDUCTIONS ──────────────────────────────── */
        .two-col { display: table; width: 100%; border-collapse: collapse; }
        .col-earn {
            display: table-cell;
            width: 50%;
            padding-right: 16px;
            vertical-align: top;
        }
        .col-ded {
            display: table-cell;
            width: 50%;
            padding-left: 16px;
            vertical-align: top;
            border-left: 2px solid #E8DDD5;
        }

        .col-header {
            display: table;
            width: 100%;
            padding: 6px 8px;
            border-radius: 3px;
            margin-bottom: 8px;
        }
        .col-header-earn { background: #F0FDF4; border: 1px solid #BBF7D0; }
        .col-header-ded  { background: #FEF2F2; border: 1px solid #FECACA; }
        .col-header-lbl {
            display: table-cell;
            font-size: 9px;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
            vertical-align: middle;
        }
        .col-header-earn .col-header-lbl { color: #16A34A; }
        .col-header-ded  .col-header-lbl { color: #DC2626; }

        .line { display: table; width: 100%; padding: 4px 0; border-bottom: 1px solid #F5F0EB; }
        .line-lbl  { display: table-cell; font-size: 9.5px; color: #5C3D2E; vertical-align: middle; }
        .line-amt  { display: table-cell; text-align: right; font-size: 9.5px; font-weight: 600; vertical-align: middle; }
        .earn-amt  { color: #16A34A; }
        .ded-amt   { color: #DC2626; }
        .absent-lbl, .absent-amt { color: #DC2626; }

        .subtotal-row {
            display: table;
            width: 100%;
            padding: 7px 8px;
            border-radius: 3px;
            margin-top: 6px;
        }
        .subtotal-earn { background: #F0FDF4; border: 1px solid #86EFAC; }
        .subtotal-ded  { background: #FEF2F2; border: 1px solid #FCA5A5; }
        .sub-lbl { display: table-cell; font-size: 9px; font-weight: bold; letter-spacing: .5px; text-transform: uppercase; vertical-align: middle; }
        .sub-amt { display: table-cell; text-align: right; font-size: 11px; font-weight: bold; vertical-align: middle; }
        .subtotal-earn .sub-lbl, .subtotal-earn .sub-amt { color: #16A34A; }
        .subtotal-ded  .sub-lbl, .subtotal-ded  .sub-amt { color: #DC2626; }

        /* ── TAX NOTE ───────────────────────────────────────────── */
        .tax-box {
            background: #FFFBEB;
            border: 1px solid #FDE68A;
            border-left: 3px solid #D97706;
            border-radius: 3px;
            padding: 7px 10px;
            margin-top: 8px;
            font-size: 8.5px;
            color: #6B5347;
            line-height: 1.9;
        }
        .tax-box-title {
            font-size: 8.5px;
            font-weight: bold;
            color: #D97706;
            text-transform: uppercase;
            letter-spacing: .5px;
            margin-bottom: 3px;
        }

        /* ── NET SALARY FOOTER ──────────────────────────────────── */
        .net-bar {
            background: #1A0F09;
            padding: 18px 28px;
        }
        .net-inner { display: table; width: 100%; }
        .net-left  { display: table-cell; vertical-align: middle; }
        .net-right { display: table-cell; vertical-align: middle; text-align: right; }
        .net-label {
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #A89080;
        }
        .net-words {
            font-size: 8.5px;
            color: #D4A843;
            margin-top: 5px;
            font-style: italic;
            max-width: 380px;
        }
        .net-amount {
            font-size: 30px;
            font-weight: bold;
            color: #C2531B;
            letter-spacing: 1px;
        }
        .net-currency {
            font-size: 13px;
            color: #A89080;
            margin-right: 4px;
        }
        .net-status-badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 10px;
            font-size: 8px;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-top: 6px;
        }
        .status-paid     { background: #166534; color: #86EFAC; }
        .status-approved { background: #1D4ED8; color: #BFDBFE; }
        .status-draft    { background: #374151; color: #D1D5DB; }

        /* ── SIGNATURE BLOCK ────────────────────────────────────── */
        .sig-section {
            padding: 20px 28px 16px;
            display: table;
            width: 100%;
        }
        .sig-cell {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            vertical-align: bottom;
        }
        .sig-line {
            border-top: 1px solid #D4A843;
            padding-top: 6px;
            font-size: 8.5px;
            color: #A89080;
            margin-top: 28px;
        }
        .sig-title {
            font-size: 7.5px;
            color: #C2531B;
            text-transform: uppercase;
            letter-spacing: .5px;
            margin-top: 2px;
        }

        /* ── DOCUMENT FOOTER ────────────────────────────────────── */
        .doc-footer {
            background: #F5F0EB;
            border-top: 2px solid #D4A843;
            padding: 7px 28px;
            display: table;
            width: 100%;
        }
        .doc-footer-l {
            display: table-cell;
            font-size: 7.5px;
            color: #A89080;
            vertical-align: middle;
        }
        .doc-footer-r {
            display: table-cell;
            font-size: 7.5px;
            color: #A89080;
            text-align: right;
            vertical-align: middle;
        }
        .doc-footer-r b { color: #C2531B; }

    </style>
</head>
<body>
<div class="page">

    {{-- ══════════════════════════════════════════════════════════
         HEADER
    ══════════════════════════════════════════════════════════ --}}
    <div class="header">
        <div class="header-top">
            <div class="header-logo-cell">
                <img src="{{ public_path('kuvvet_logo.png') }}" alt="Kuvvets">
            </div>
            <div class="header-title-cell">
                <div class="slip-badge">SALARY SLIP</div>
                <div class="slip-meta">
                    <span>{{ $payslip->payslip_number }}</span>
                    &nbsp;&middot;&nbsp;
                    {{ $payslip->period->month_name }}
                </div>
            </div>
        </div>
    </div>
    <div class="stripe-inner"></div>
    <div class="stripe-gold"></div>

    {{-- ── Company Bar ─────────────────────────────────────────── --}}
    <div class="company-bar">
        <div class="company-bar-left">
            <b>{{ $payslip->company->name }}</b>
            @if($payslip->company->ntn)
                &nbsp;&middot;&nbsp; NTN: {{ $payslip->company->ntn }}
            @endif
            @if($payslip->company->address)
                &nbsp;&middot;&nbsp; {{ $payslip->company->city }}, Pakistan
            @endif
        </div>
        <div class="company-bar-right">
            @php
                $badgeClass = match($payslip->status) {
                    'paid'     => 'status-paid',
                    'approved' => 'status-approved',
                    default    => 'status-draft',
                };
            @endphp
            <span class="net-status-badge {{ $badgeClass }}">
                {{ strtoupper($payslip->status) }}
            </span>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════
         EMPLOYEE INFORMATION
    ══════════════════════════════════════════════════════════ --}}
    <div class="section">
        <div class="section-head">
            <div class="section-label">Employee Information</div>
            <div class="section-rule"><hr></div>
        </div>
        <table class="info-table" cellspacing="4" cellpadding="0">
            <tr>
                <td class="info-card" style="width:34%;">
                    <div class="info-label">Full Name</div>
                    <div class="info-value">{{ $payslip->employee->full_name }}</div>
                </td>
                <td style="width:2%;"></td>
                <td class="info-card" style="width:20%;">
                    <div class="info-label">Employee ID</div>
                    <div class="info-value info-accent">{{ $payslip->employee->employee_id }}</div>
                </td>
                <td style="width:2%;"></td>
                <td class="info-card" style="width:22%;">
                    <div class="info-label">Department</div>
                    <div class="info-value">{{ $payslip->employee->department?->name ?? '—' }}</div>
                </td>
                <td style="width:2%;"></td>
                <td class="info-card" style="width:18%;">
                    <div class="info-label">Designation</div>
                    <div class="info-value">{{ $payslip->employee->designation?->title ?? '—' }}</div>
                </td>
            </tr>
            <tr><td colspan="7" style="height:5px;"></td></tr>
            <tr>
                <td class="info-card" style="width:34%;">
                    <div class="info-label">CNIC</div>
                    <div class="info-value">{{ $payslip->employee->formatted_cnic ?? '—' }}</div>
                </td>
                <td style="width:2%;"></td>
                <td class="info-card" style="width:20%;">
                    <div class="info-label">EOBI No.</div>
                    <div class="info-value">{{ $payslip->employee->eobi_number ?? '—' }}</div>
                </td>
                <td style="width:2%;"></td>
                <td class="info-card" style="width:22%;">
                    <div class="info-label">Bank</div>
                    <div class="info-value">{{ $payslip->employee->bank_name ?? '—' }}</div>
                </td>
                <td style="width:2%;"></td>
                <td class="info-card" style="width:18%;">
                    <div class="info-label">Pay Period</div>
                    <div class="info-value info-accent">{{ $payslip->period->month_name }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- ══════════════════════════════════════════════════════════
         ATTENDANCE SUMMARY
    ══════════════════════════════════════════════════════════ --}}
    <div class="att-outer">
        <div class="section-head" style="margin-bottom:10px;">
            <div class="section-label">Attendance Summary</div>
            <div class="section-rule"><hr></div>
        </div>
        <div class="att-row">
            <div class="att-box">
                <div class="att-num" style="color:#2D1F14;">{{ $payslip->working_days }}</div>
                <div class="att-lbl">Working Days</div>
            </div>
            <div class="att-box">
                <div class="att-num" style="color:#16A34A;">{{ $payslip->present_days }}</div>
                <div class="att-lbl">Present</div>
            </div>
            <div class="att-box">
                <div class="att-num" style="color:#DC2626;">{{ $payslip->absent_days }}</div>
                <div class="att-lbl">Absent</div>
            </div>
            <div class="att-box">
                <div class="att-num" style="color:#2563EB;">{{ $payslip->leave_days }}</div>
                <div class="att-lbl">On Leave</div>
            </div>
            <div class="att-box">
                <div class="att-num" style="color:#D97706;">{{ $payslip->overtime_hours ?? '0' }}</div>
                <div class="att-lbl">OT Hours</div>
            </div>
            <div class="att-box">
                @php $lm = (int)($payslip->late_minutes ?? 0); @endphp
                <div class="att-num" style="color:{{ $lm > 0 ? '#DC2626' : '#A89080' }};">
                    {{ $lm >= 60 ? floor($lm/60).'h '.($lm%60).'m' : $lm.'m' }}
                </div>
                <div class="att-lbl">Late Time</div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════
         EARNINGS & DEDUCTIONS
    ══════════════════════════════════════════════════════════ --}}
    <div class="section">
        <div class="section-head">
            <div class="section-label">Earnings &amp; Deductions</div>
            <div class="section-rule"><hr></div>
        </div>
        <div class="two-col">

            {{-- ── EARNINGS ──────────────────────────────────── --}}
            <div class="col-earn">
                <div class="col-header col-header-earn">
                    <div class="col-header-lbl">&#x2B; Earnings</div>
                </div>

                @php
                $earnings = [
                    ['Basic Salary',       $payslip->basic_salary],
                    ['House Rent (HRA)',   $payslip->house_rent],
                    ['Medical Allowance',  $payslip->medical],
                    ['Conveyance',         $payslip->conveyance],
                    ['Fuel Allowance',     $payslip->fuel],
                    ['Utility Allowance',  $payslip->utility],
                    ['Meal Allowance',     $payslip->meal],
                    ['Special Allowance',  $payslip->special_allowance],
                    ['Other Allowance',    $payslip->other_allowance],
                    ['Overtime Amount',    $payslip->overtime_amount],
                    ['Performance Bonus',  $payslip->bonus],
                    ['Arrears',            $payslip->arrears],
                ];
                @endphp

                @foreach($earnings as [$label, $amount])
                @if((float)$amount > 0)
                <div class="line">
                    <div class="line-lbl">{{ $label }}</div>
                    <div class="line-amt earn-amt">{{ number_format($amount) }}</div>
                </div>
                @endif
                @endforeach

                @if((float)$payslip->absent_deduction > 0)
                <div class="line">
                    <div class="line-lbl absent-lbl">Absent Deduction</div>
                    <div class="line-amt absent-amt">({{ number_format($payslip->absent_deduction) }})</div>
                </div>
                @endif

                <div class="subtotal-row subtotal-earn">
                    <div class="sub-lbl">Gross Salary</div>
                    <div class="sub-amt">PKR {{ number_format($payslip->gross_salary) }}</div>
                </div>
            </div>

            {{-- ── DEDUCTIONS ─────────────────────────────────── --}}
            <div class="col-ded">
                <div class="col-header col-header-ded">
                    <div class="col-header-lbl">&#x2212; Deductions</div>
                </div>

                @foreach([
                    ['Income Tax (WHT/FBR)',  $payslip->income_tax],
                    ['EOBI — Employee (1%)',  $payslip->eobi_employee],
                    ['PESSI — Employee',      $payslip->pessi_employee],
                    ['Loan Deduction',        $payslip->loan_deduction],
                    ['Other Deductions',      $payslip->other_deduction],
                ] as [$label, $amount])
                @if((float)$amount > 0)
                <div class="line">
                    <div class="line-lbl">{{ $label }}</div>
                    <div class="line-amt ded-amt">{{ number_format($amount) }}</div>
                </div>
                @endif
                @endforeach

                @if((float)$payslip->eobi_employer > 0)
                <div class="line">
                    <div class="line-lbl" style="color:#A89080;font-style:italic;">
                        EOBI — Employer (info only)
                    </div>
                    <div class="line-amt" style="color:#A89080;">
                        {{ number_format($payslip->eobi_employer) }}
                    </div>
                </div>
                @endif

                <div class="subtotal-row subtotal-ded">
                    <div class="sub-lbl">Total Deductions</div>
                    <div class="sub-amt">PKR {{ number_format($payslip->total_deductions) }}</div>
                </div>

                @if((float)$payslip->income_tax > 0)
                <div class="tax-box">
                    <div class="tax-box-title">FBR Tax Computation — FY 2024-25</div>
                    Annual Taxable Income: <b>PKR {{ number_format($payslip->annual_taxable_income) }}</b><br>
                    Annual Tax Liability: <b>PKR {{ number_format($payslip->annual_tax) }}</b><br>
                    Monthly Tax This Slip: <b>PKR {{ number_format($payslip->monthly_tax) }}</b><br>
                    Applicable Tax Slab: <b>{{ $payslip->tax_slab }}</b>
                </div>
                @endif
            </div>

        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════
         NET SALARY
    ══════════════════════════════════════════════════════════ --}}
    <div class="net-bar">
        <div class="net-inner">
            <div class="net-left">
                <div class="net-label">Net Salary Payable</div>
                <div class="net-words">{{ $payslip->net_in_words }}</div>
            </div>
            <div class="net-right">
                <span class="net-currency">PKR</span><span class="net-amount">{{ number_format($payslip->net_salary) }}</span>
            </div>
        </div>
    </div>
    <div class="stripe-gold"></div>

    {{-- ══════════════════════════════════════════════════════════
         SIGNATURES
    ══════════════════════════════════════════════════════════ --}}
    <div class="sig-section">
        <div class="sig-cell">
            <div class="sig-line">
                {{ $payslip->employee->full_name }}
                <div class="sig-title">Employee Signature</div>
            </div>
        </div>
        <div class="sig-cell">
            <div class="sig-line">
                &nbsp;
                <div class="sig-title">HR Department</div>
            </div>
        </div>
        <div class="sig-cell">
            <div class="sig-line">
                &nbsp;
                <div class="sig-title">Authorised Signatory</div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════
         DOCUMENT FOOTER
    ══════════════════════════════════════════════════════════ --}}
    <div class="doc-footer">
        <div class="doc-footer-l">
            This is a system-generated document. &nbsp;&middot;&nbsp;
            {{ $payslip->company->name }}
            @if($payslip->company->email)
                &nbsp;&middot;&nbsp; {{ $payslip->company->email }}
            @endif
        </div>
        <div class="doc-footer-r">
            <b>{{ $payslip->payslip_number }}</b>
            &nbsp;&middot;&nbsp;
            Generated: {{ now()->format('d M Y, h:i A') }} PKT
        </div>
    </div>

</div>
</body>
</html>
