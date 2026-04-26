@extends('layouts.app')
@section('title', 'Payslip — ' . $payslip->payslip_number)
@section('page-title', 'Payslip — ' . $payslip->payslip_number)
@section('breadcrumb', 'Payroll · ' . $payslip->period->month_name . ' · ' . $payslip->employee->full_name)

@section('content')
<div style="max-width:820px;">

    {{-- Action Buttons --}}
    <div style="display:flex;justify-content:flex-end;gap:10px;margin-bottom:16px;">
        <a href="{{ route('payroll.payslip.pdf', $payslip) }}" class="btn btn-blue btn-sm">
            <i class="fa-solid fa-file-pdf"></i> Download PDF
        </a>
        <a href="{{ route('payroll.period', $payslip->period) }}" class="btn btn-secondary btn-sm">
            <i class="fa-solid fa-arrow-left"></i> Back to Period
        </a>
    </div>

    <div class="card card-flush">

        {{-- Company Header --}}
        <div style="display:flex;align-items:center;justify-content:space-between;
                    padding:20px 28px;background:var(--bg-muted);
                    border-bottom:2px solid var(--accent-border);">
            <div style="display:flex;align-items:center;gap:14px;">
                <div class="sidebar-logo">
                    <i class="fa-solid fa-grid-2"></i>
                </div>
                <div>
                    <div style="font-size:18px;font-weight:700;color:var(--accent);letter-spacing:1px;">
                        KUVVET
                    </div>
                    <div style="font-size:11px;color:var(--text-muted);">
                        Private Limited · Karachi, Pakistan
                    </div>
                </div>
            </div>
            <div style="text-align:right;">
                <div style="font-size:14px;font-weight:700;color:var(--text-primary);">SALARY SLIP</div>
                <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">
                    {{ $payslip->payslip_number }}
                </div>
                <div style="font-size:11px;color:var(--accent);margin-top:1px;">
                    {{ $payslip->period->month_name }}
                </div>
            </div>
        </div>

        {{-- Employee Info --}}
        <div style="padding:20px 28px;border-bottom:1px solid var(--border);
                    display:grid;grid-template-columns:repeat(3,1fr);gap:14px;">
            @foreach([
                ['Employee',   $payslip->employee->full_name],
                ['Employee ID', $payslip->employee->employee_id],
                ['Department', $payslip->employee->department?->name ?? '—'],
                ['Designation', $payslip->employee->designation?->title ?? '—'],
                ['Bank',       $payslip->employee->bank_name ?? '—'],
                ['IBAN',       $payslip->employee->bank_iban ?? '—'],
                ['CNIC',       $payslip->employee->formatted_cnic ?? '—'],
                ['EOBI No.',   $payslip->employee->eobi_number ?? '—'],
                ['Pay Period', $payslip->period->month_name],
            ] as [$l, $v])
            <div>
                <div class="detail-block-label">{{ $l }}</div>
                <div style="font-size:12px;color:var(--text-secondary);margin-top:2px;">{{ $v }}</div>
            </div>
            @endforeach
        </div>

        {{-- Attendance Summary --}}
        <div style="padding:16px 28px;border-bottom:1px solid var(--border);background:var(--bg-muted);">
            <div class="section-label" style="margin-bottom:12px;">Attendance Summary</div>
            <div style="display:flex;gap:16px;flex-wrap:wrap;">
                @foreach([
                    ['Working Days', $payslip->working_days, 'accent'],
                    ['Present',      $payslip->present_days, 'green'],
                    ['Absent',       $payslip->absent_days,  'red'],
                    ['On Leave',     $payslip->leave_days,   'blue'],
                    ['OT Hours',     $payslip->overtime_hours,'yellow'],
                ] as [$l, $v, $c])
                <div class="detail-block" style="min-width:90px;text-align:center;">
                    <div style="font-size:20px;font-weight:700;color:var(--{{ $c }});margin-bottom:4px;">
                        {{ $v }}
                    </div>
                    <div class="detail-block-label" style="margin-bottom:0;">{{ $l }}</div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Earnings & Deductions --}}
        <div style="padding:20px 28px;display:grid;grid-template-columns:1fr 1fr;
                    gap:24px;border-bottom:1px solid var(--border);">

            {{-- Earnings --}}
            <div>
                <div class="form-section" style="color:var(--green);">
                    <i class="fa-solid fa-plus-circle"></i> Earnings
                </div>
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
                <div style="display:flex;justify-content:space-between;padding:5px 0;
                            border-bottom:1px solid var(--border);font-size:12px;">
                    <span style="color:var(--text-muted);">{{ $label }}</span>
                    <span style="font-weight:500;color:var(--text-primary);">{{ number_format($amount) }}</span>
                </div>
                @endif
                @endforeach

                @if($payslip->absent_deduction > 0)
                <div style="display:flex;justify-content:space-between;padding:5px 0;
                            border-bottom:1px solid var(--border);font-size:12px;">
                    <span style="color:var(--red);">Absent Deduction</span>
                    <span style="color:var(--red);font-weight:600;">
                        -{{ number_format($payslip->absent_deduction) }}
                    </span>
                </div>
                @endif

                <div style="display:flex;justify-content:space-between;padding:8px 0;margin-top:4px;
                            border-top:1px solid var(--accent-border);font-size:13px;font-weight:700;">
                    <span style="color:var(--green);">GROSS SALARY</span>
                    <span style="color:var(--green);">PKR {{ number_format($payslip->gross_salary) }}</span>
                </div>
            </div>

            {{-- Deductions --}}
            <div>
                <div class="form-section" style="color:var(--red);">
                    <i class="fa-solid fa-minus-circle"></i> Deductions
                </div>
                @foreach([
                    ['Income Tax (FBR)',    $payslip->income_tax,     'yellow'],
                    ['EOBI (Employee 1%)', $payslip->eobi_employee,  'blue'],
                    ['PESSI (Employee)',   $payslip->pessi_employee, 'blue'],
                    ['Loan Deduction',     $payslip->loan_deduction, 'red'],
                    ['Other Deductions',   $payslip->other_deduction,'red'],
                ] as [$label, $amount, $color])
                @if($amount > 0)
                <div style="display:flex;justify-content:space-between;padding:5px 0;
                            border-bottom:1px solid var(--border);font-size:12px;">
                    <span style="color:var(--text-muted);">{{ $label }}</span>
                    <span style="font-weight:700;color:var(--{{ $color }});">{{ number_format($amount) }}</span>
                </div>
                @endif
                @endforeach

                @if($payslip->eobi_employer > 0)
                <div style="display:flex;justify-content:space-between;padding:5px 0;
                            border-bottom:1px solid var(--border);font-size:11px;">
                    <span style="color:var(--text-muted);font-style:italic;">EOBI (Employer — info only)</span>
                    <span style="color:var(--text-muted);">{{ number_format($payslip->eobi_employer) }}</span>
                </div>
                @endif

                <div style="display:flex;justify-content:space-between;padding:8px 0;margin-top:4px;
                            border-top:1px solid var(--red-border);font-size:13px;font-weight:700;">
                    <span style="color:var(--red);">TOTAL DEDUCTIONS</span>
                    <span style="color:var(--red);">PKR {{ number_format($payslip->total_deductions) }}</span>
                </div>

                @if($payslip->income_tax > 0)
                <div class="note-block" style="margin-top:14px;">
                    <div class="note-block-label" style="margin-bottom:8px;">TAX COMPUTATION</div>
                    <div style="font-size:11px;color:var(--text-muted);line-height:1.9;">
                        Annual Taxable:
                        <span style="color:var(--accent);">PKR {{ number_format($payslip->annual_taxable_income) }}</span><br>
                        Annual Tax:
                        <span style="color:var(--yellow);">PKR {{ number_format($payslip->annual_tax) }}</span><br>
                        Monthly Tax:
                        <span style="color:var(--yellow);">PKR {{ number_format($payslip->monthly_tax) }}</span><br>
                        Slab: <span style="color:var(--accent);">{{ $payslip->tax_slab }}</span>
                    </div>
                </div>
                @endif
            </div>

        </div>

        {{-- Net Salary Footer --}}
        <div style="display:flex;align-items:center;justify-content:space-between;
                    padding:22px 28px;background:var(--accent-bg);
                    border-top:2px solid var(--accent-border);">
            <div>
                <div class="section-label">Net Salary Payable</div>
                <div style="font-size:11px;color:var(--text-muted);margin-top:6px;">
                    {{ $payslip->net_in_words }}
                </div>
            </div>
            <div style="font-size:30px;font-weight:700;color:var(--accent);letter-spacing:1px;">
                PKR {{ number_format($payslip->net_salary) }}
            </div>
        </div>

        {{-- Footer Note --}}
        <div style="display:flex;justify-content:space-between;padding:12px 28px;
                    background:var(--bg-muted);border-top:1px solid var(--border);
                    font-size:10px;color:var(--text-muted);">
            <span>This is a computer-generated payslip. No signature required.</span>
            <span>Generated: {{ now()->setTimezone('Asia/Karachi')->format('d M Y · h:i A') }} PKT</span>
        </div>

    </div>
</div>
@endsection