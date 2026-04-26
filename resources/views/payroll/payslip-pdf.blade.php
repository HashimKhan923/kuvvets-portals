<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>

        /* ── Reset ───────────────────────────────────────────── */
        * { box-sizing: border-box; margin: 0; padding: 0; }

        /* ── Colours (Daybreak palette, hardcoded for DomPDF) ── */
        /* --accent:       #C2531B   terracotta                  */
        /* --accent-light: #E87A45                               */
        /* --accent-bg:    #FEF2EC                               */
        /* --bg-page:      #FBF8F5                               */
        /* --bg-muted:     #F5F0EB                               */
        /* --border:       #F0EAE2                               */
        /* --text-primary: #2D1F14                               */
        /* --text-muted:   #A89080                               */
        /* --green:        #22C55E                               */
        /* --red:          #EF4444                               */
        /* --yellow:       #F59E0B                               */
        /* --blue:         #3B82F6                               */

        /* ── Page ────────────────────────────────────────────── */
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #2D1F14;
            background: #ffffff;
            width: 100%;
        }

        /* ── Outer wrapper — centred with side margins ───────── */
        .page {
            width: 720px;
            margin: 0 auto;
        }

        /* ── Header ─────────────────────────────────────────── */
        .header {
            background: #2D1F14;
            padding: 18px 28px;
        }
        .header-inner {
            display: table;
            width: 100%;
        }
        .header-left  { display: table-cell; vertical-align: middle; }
        .header-right { display: table-cell; vertical-align: middle; text-align: right; }

        .brand-name { font-size: 22px; font-weight: bold; color: #E87A45; letter-spacing: 2px; }
        .brand-sub  { font-size: 9px;  color: #A89080; margin-top: 3px; }
        .slip-title { font-size: 13px; font-weight: bold; color: #ffffff; }
        .slip-num   { font-size: 10px; color: #A89080; margin-top: 2px; }
        .slip-period{ font-size: 10px; color: #E87A45; margin-top: 1px; }

        /* ── Accent bar ─────────────────────────────────────── */
        .accent-bar {
            height: 3px;
            background: #C2531B;
        }

        /* ── Section wrapper ─────────────────────────────────── */
        .section {
            padding: 14px 28px;
            border-bottom: 1px solid #F0EAE2;
        }
        .section-title {
            font-size: 8px;
            font-weight: bold;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            color: #A89080;
            margin-bottom: 10px;
        }

        /* ── Employee info grid (3 columns via table) ────────── */
        .info-grid {
            display: table;
            width: 100%;
            border-collapse: collapse;
        }
        .info-row  { display: table-row; }
        .info-cell {
            display: table-cell;
            width: 33.33%;
            padding: 4px 6px 8px 0;
            vertical-align: top;
        }
        .field-label {
            font-size: 8px;
            color: #A89080;
            text-transform: uppercase;
            letter-spacing: .5px;
            margin-bottom: 2px;
        }
        .field-value {
            font-size: 10px;
            color: #2D1F14;
            font-weight: 600;
        }

        /* ── Attendance row ──────────────────────────────────── */
        .att-table { width: 100%; border-collapse: collapse; }
        .att-table th {
            background: #F5F0EB;
            padding: 7px 10px;
            text-align: center;
            font-size: 8px;
            color: #A89080;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: .5px;
            border: 1px solid #F0EAE2;
        }
        .att-table td {
            padding: 8px 10px;
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            border: 1px solid #F0EAE2;
            background: #FBF8F5;
        }

        /* ── Two-column earnings / deductions ───────────────── */
        .two-col { display: table; width: 100%; }
        .col-left {
            display: table-cell;
            width: 50%;
            padding-right: 14px;
            vertical-align: top;
        }
        .col-right {
            display: table-cell;
            width: 50%;
            padding-left: 14px;
            vertical-align: top;
            border-left: 1px solid #F0EAE2;
        }

        .earn-title  { font-size: 9px; font-weight: bold; letter-spacing: .8px; text-transform: uppercase; color: #22C55E; margin-bottom: 8px; }
        .deduct-title{ font-size: 9px; font-weight: bold; letter-spacing: .8px; text-transform: uppercase; color: #EF4444; margin-bottom: 8px; }

        .line-item { display: table; width: 100%; border-bottom: 1px solid #F0EAE2; padding: 4px 0; }
        .line-label { display: table-cell; font-size: 10px; color: #6B5347; }
        .line-amount{ display: table-cell; text-align: right; font-size: 10px; font-weight: 600; }

        .line-earn   .line-amount { color: #2D7A3A; }
        .line-deduct .line-amount { color: #C03A2B; }
        .line-absent .line-amount { color: #C03A2B; }
        .line-absent .line-label  { color: #C03A2B; }

        .subtotal {
            display: table;
            width: 100%;
            padding: 6px 0;
            margin-top: 2px;
            border-top: 2px solid #F5D5C0;
        }
        .subtotal-label  { display: table-cell; font-size: 11px; font-weight: bold; }
        .subtotal-amount { display: table-cell; text-align: right; font-size: 12px; font-weight: bold; }
        .subtotal-green  .subtotal-label,
        .subtotal-green  .subtotal-amount { color: #22C55E; }
        .subtotal-red    .subtotal-label,
        .subtotal-red    .subtotal-amount { color: #EF4444; border-top-color: #FECACA; }

        /* ── Tax note box ────────────────────────────────────── */
        .tax-note {
            margin-top: 10px;
            padding: 8px 10px;
            background: #FFFBEB;
            border: 1px solid #FDE68A;
            border-radius: 4px;
            font-size: 9px;
            color: #6B5347;
            line-height: 1.8;
        }
        .tax-note strong { color: #2D1F14; }

        /* ── Net salary footer ───────────────────────────────── */
        .net-footer {
            background: #FEF2EC;
            border-top: 2px solid #C2531B;
            padding: 18px 28px;
        }
        .net-inner { display: table; width: 100%; }
        .net-left  { display: table-cell; vertical-align: middle; }
        .net-right { display: table-cell; vertical-align: middle; text-align: right; }

        .net-label  { font-size: 9px; color: #A89080; text-transform: uppercase; letter-spacing: .8px; }
        .net-words  { font-size: 9px; color: #6B5347; margin-top: 4px; font-style: italic; }
        .net-amount { font-size: 26px; font-weight: bold; color: #C2531B; letter-spacing: 1px; }

        /* ── Stamp area ──────────────────────────────────────── */
        .stamp-row {
            display: table;
            width: 100%;
            padding: 20px 28px 14px;
        }
        .stamp-cell {
            display: table-cell;
            width: 50%;
            text-align: center;
            vertical-align: bottom;
        }
        .stamp-line {
            border-top: 1px solid #E8DDD5;
            padding-top: 6px;
            font-size: 9px;
            color: #A89080;
        }

        /* ── Document footer ─────────────────────────────────── */
        .doc-footer {
            background: #F5F0EB;
            border-top: 1px solid #F0EAE2;
            padding: 8px 28px;
            display: table;
            width: 100%;
        }
        .doc-footer-left  { display: table-cell; font-size: 8px; color: #A89080; vertical-align: middle; }
        .doc-footer-right { display: table-cell; font-size: 8px; color: #A89080; text-align: right; vertical-align: middle; }

    </style>
</head>
<body>
<div class="page">

    {{-- ═══ HEADER ═══════════════════════════════════════════ --}}
    <div class="header">
        <div class="header-inner">
            <div class="header-left">
                <div class="brand-name">&#9108; KUVVET</div>
                <div class="brand-sub">Private Limited &middot; Karachi, Pakistan</div>
            </div>
            <div class="header-right">
                <div class="slip-title">SALARY SLIP</div>
                <div class="slip-num">{{ $payslip->payslip_number }}</div>
                <div class="slip-period">{{ $payslip->period->month_name }}</div>
            </div>
        </div>
    </div>
    <div class="accent-bar"></div>

    {{-- ═══ EMPLOYEE INFORMATION ══════════════════════════════ --}}
    <div class="section">
        <div class="section-title">Employee Information</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-cell">
                    <div class="field-label">Employee Name</div>
                    <div class="field-value">{{ $payslip->employee->full_name }}</div>
                </div>
                <div class="info-cell">
                    <div class="field-label">Employee ID</div>
                    <div class="field-value">{{ $payslip->employee->employee_id }}</div>
                </div>
                <div class="info-cell">
                    <div class="field-label">Department</div>
                    <div class="field-value">{{ $payslip->employee->department?->name ?? '—' }}</div>
                </div>
            </div>
            <div class="info-row">
                <div class="info-cell">
                    <div class="field-label">Designation</div>
                    <div class="field-value">{{ $payslip->employee->designation?->title ?? '—' }}</div>
                </div>
                <div class="info-cell">
                    <div class="field-label">CNIC</div>
                    <div class="field-value">{{ $payslip->employee->formatted_cnic ?? '—' }}</div>
                </div>
                <div class="info-cell">
                    <div class="field-label">EOBI Number</div>
                    <div class="field-value">{{ $payslip->employee->eobi_number ?? '—' }}</div>
                </div>
            </div>
            <div class="info-row">
                <div class="info-cell">
                    <div class="field-label">Bank</div>
                    <div class="field-value">{{ $payslip->employee->bank_name ?? '—' }}</div>
                </div>
                <div class="info-cell">
                    <div class="field-label">Account Number</div>
                    <div class="field-value">{{ $payslip->employee->bank_account_no ?? '—' }}</div>
                </div>
                <div class="info-cell">
                    <div class="field-label">Pay Period</div>
                    <div class="field-value" style="color:#C2531B;">{{ $payslip->period->month_name }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══ ATTENDANCE ════════════════════════════════════════ --}}
    <div class="section" style="background:#FBF8F5;">
        <div class="section-title">Attendance Summary</div>
        <table class="att-table">
            <tr>
                <th>Working Days</th>
                <th>Present</th>
                <th>Absent</th>
                <th>On Leave</th>
                <th>OT Hours</th>
                <th>Late (min)</th>
            </tr>
            <tr>
                <td style="color:#2D1F14;">{{ $payslip->working_days }}</td>
                <td style="color:#22C55E;">{{ $payslip->present_days }}</td>
                <td style="color:#EF4444;">{{ $payslip->absent_days }}</td>
                <td style="color:#3B82F6;">{{ $payslip->leave_days }}</td>
                <td style="color:#F59E0B;">{{ $payslip->overtime_hours }}</td>
                <td style="color:#EF4444;">{{ $payslip->late_minutes ?? 0 }}</td>
            </tr>
        </table>
    </div>

    {{-- ═══ EARNINGS + DEDUCTIONS ═════════════════════════════ --}}
    <div class="section">
        <div class="two-col">

            {{-- Earnings --}}
            <div class="col-left">
                <div class="earn-title">&#43; Earnings</div>
                @php
                $earnings = [
                    ['Basic Salary',      $payslip->basic_salary],
                    ['House Rent (HRA)',   $payslip->house_rent],
                    ['Medical Allowance', $payslip->medical],
                    ['Conveyance',        $payslip->conveyance],
                    ['Fuel Allowance',    $payslip->fuel],
                    ['Utility',           $payslip->utility],
                    ['Meal Allowance',    $payslip->meal],
                    ['Special Allowance', $payslip->special_allowance],
                    ['Other Allowance',   $payslip->other_allowance],
                    ['Overtime',          $payslip->overtime_amount],
                    ['Bonus',             $payslip->bonus],
                    ['Arrears',           $payslip->arrears],
                ];
                @endphp

                @foreach($earnings as [$label, $amount])
                @if($amount > 0)
                <div class="line-item line-earn">
                    <div class="line-label">{{ $label }}</div>
                    <div class="line-amount">{{ number_format($amount) }}</div>
                </div>
                @endif
                @endforeach

                @if($payslip->absent_deduction > 0)
                <div class="line-item line-absent">
                    <div class="line-label">Absent Deduction</div>
                    <div class="line-amount">-{{ number_format($payslip->absent_deduction) }}</div>
                </div>
                @endif

                <div class="subtotal subtotal-green">
                    <div class="subtotal-label">GROSS SALARY</div>
                    <div class="subtotal-amount">PKR {{ number_format($payslip->gross_salary) }}</div>
                </div>
            </div>

            {{-- Deductions --}}
            <div class="col-right">
                <div class="deduct-title">&#8722; Deductions</div>

                @foreach([
                    ['Income Tax (FBR)',    $payslip->income_tax],
                    ['EOBI (Employee 1%)', $payslip->eobi_employee],
                    ['PESSI (Employee)',   $payslip->pessi_employee],
                    ['Loan Deduction',     $payslip->loan_deduction],
                    ['Other Deductions',   $payslip->other_deduction],
                ] as [$label, $amount])
                @if($amount > 0)
                <div class="line-item line-deduct">
                    <div class="line-label">{{ $label }}</div>
                    <div class="line-amount">{{ number_format($amount) }}</div>
                </div>
                @endif
                @endforeach

                @if($payslip->eobi_employer > 0)
                <div class="line-item">
                    <div class="line-label" style="color:#A89080;font-style:italic;">
                        EOBI (Employer &mdash; info)
                    </div>
                    <div class="line-amount" style="color:#A89080;">
                        {{ number_format($payslip->eobi_employer) }}
                    </div>
                </div>
                @endif

                <div class="subtotal subtotal-red">
                    <div class="subtotal-label">TOTAL DEDUCTIONS</div>
                    <div class="subtotal-amount">PKR {{ number_format($payslip->total_deductions) }}</div>
                </div>

                @if($payslip->income_tax > 0)
                <div class="tax-note">
                    <strong>FBR Tax Computation (2024-25)</strong><br>
                    Annual Taxable Income: PKR {{ number_format($payslip->annual_taxable_income) }}<br>
                    Annual Tax Liability: PKR {{ number_format($payslip->annual_tax) }}<br>
                    Monthly Tax Deducted: PKR {{ number_format($payslip->monthly_tax) }}<br>
                    Tax Slab Applied: {{ $payslip->tax_slab }}
                </div>
                @endif
            </div>

        </div>
    </div>

    {{-- ═══ NET SALARY ════════════════════════════════════════ --}}
    <div class="net-footer">
        <div class="net-inner">
            <div class="net-left">
                <div class="net-label">Net Salary Payable</div>
                <div class="net-words">{{ $payslip->net_in_words }}</div>
            </div>
            <div class="net-right">
                <div class="net-amount">PKR {{ number_format($payslip->net_salary) }}</div>
            </div>
        </div>
    </div>

    {{-- ═══ SIGNATURES ════════════════════════════════════════ --}}
    <div class="stamp-row">
        <div class="stamp-cell">
            <div style="height:36px;"></div>
            <div class="stamp-line">Employee Signature</div>
        </div>
        <div class="stamp-cell">
            <div style="height:36px;"></div>
            <div class="stamp-line">Authorised Signatory</div>
        </div>
    </div>

    {{-- ═══ FOOTER ════════════════════════════════════════════ --}}
    <div class="doc-footer">
        <div class="doc-footer-left">
            This is a computer-generated payslip and does not require a physical signature.
        </div>
        <div class="doc-footer-right">
            Generated: {{ now()->setTimezone('Asia/Karachi')->format('d M Y &middot; h:i A') }} PKT
        </div>
    </div>

</div>
</body>
</html>