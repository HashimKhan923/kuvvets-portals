@extends('layouts.app')
@section('title','Payroll Settings')
@section('page-title','Payroll Settings')
@section('breadcrumb','Settings · Payroll')

@section('content')
<div style="max-width:860px;">
<form method="POST" action="{{ route('settings.payroll.update') }}">
@csrf

<div class="card card-gold" style="padding:26px;margin-bottom:16px;">
    <div class="section-title">
        <i class="fa-solid fa-money-check-dollar"></i> Payroll Configuration
    </div>
    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:16px;">
        <div>
            <label class="form-label">Payroll Cycle</label>
            <select name="payroll_cycle"
                    style="width:100%;background:var(--white);border:1px solid var(--border);
                           border-radius:8px;padding:9px 12px;color:var(--text-primary);
                           font-size:13px;outline:none;">
                @foreach(['monthly'=>'Monthly','bi_monthly'=>'Bi-Monthly','weekly'=>'Weekly'] as $v=>$l)
                <option value="{{ $v }}"
                        {{ ($settings['payroll_cycle'] ?? 'monthly') === $v ? 'selected' : '' }}>
                    {{ $l }}
                </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Payment Day of Month</label>
            <input type="number" name="payroll_payment_day"
                   value="{{ old('payroll_payment_day', $settings['payroll_payment_day'] ?? '28') }}"
                   min="1" max="31" class="form-input">
            <div style="font-size:10px;color:var(--text-muted);margin-top:3px;">
                Day of month when salaries are paid (1–31)
            </div>
        </div>
        <div>
            <label class="form-label">Minimum Wage (PKR)</label>
            <input type="number" name="minimum_wage"
                   value="{{ old('minimum_wage', $settings['minimum_wage'] ?? '37000') }}"
                   class="form-input">
            <div style="font-size:10px;color:var(--text-muted);margin-top:3px;">
                Current Pakistan minimum wage: PKR 37,000
            </div>
        </div>
        <div>
            <label class="form-label">Currency Symbol</label>
            <input type="text" name="currency_symbol"
                   value="{{ old('currency_symbol', $settings['currency_symbol'] ?? 'PKR') }}"
                   class="form-input">
        </div>
    </div>
</div>

<div class="card card-gold" style="padding:26px;margin-bottom:16px;">
    <div class="section-title">
        <i class="fa-solid fa-shield-halved"></i> Statutory Deductions
    </div>
    <div style="display:flex;flex-direction:column;gap:14px;">
        @foreach([
            ['eobi_enabled',        'Enable EOBI Deduction', 'Employee 1% + Employer 5% of minimum wage (PKR 1,850/mo)'],
            ['pessi_enabled',       'Enable PESSI/SESSI Deduction', 'Sindh Employees Social Security (0.9% max PKR 400)'],
            ['income_tax_enabled',  'Enable FBR Income Tax Deduction', 'Auto-calculate tax based on FBR 2024-25 slabs'],
        ] as [$key, $label, $desc])
        <div style="display:flex;align-items:center;justify-content:space-between;
                    padding:14px;background:var(--cream-warm);border-radius:9px;
                    border:1px solid var(--border-light);">
            <div>
                <div style="font-size:13px;font-weight:500;color:var(--text-primary);">
                    {{ $label }}
                </div>
                <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">
                    {{ $desc }}
                </div>
            </div>
            <label style="position:relative;display:inline-block;width:44px;height:24px;flex-shrink:0;">
                <input type="checkbox" name="{{ $key }}" value="1"
                       {{ ($settings[$key] ?? '1') === '1' ? 'checked' : '' }}
                       style="opacity:0;width:0;height:0;">
                <span onclick="toggleSwitch(this)"
                      style="position:absolute;cursor:pointer;inset:0;
                             background:{{ ($settings[$key] ?? '1') === '1' ? 'var(--gold)' : 'var(--border)' }};
                             border-radius:12px;transition:background .3s;">
                    <span style="position:absolute;content:'';height:18px;width:18px;
                                 left:{{ ($settings[$key] ?? '1') === '1' ? '23px' : '3px' }};
                                 bottom:3px;background:white;border-radius:50%;
                                 transition:left .3s;"></span>
                </span>
            </label>
        </div>
        @endforeach
    </div>
</div>

<div class="card card-gold" style="padding:26px;margin-bottom:16px;">
    <div class="section-title">
        <i class="fa-solid fa-building-columns"></i> Company Bank Details
    </div>
    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:16px;">
        @foreach([
            ['bank_name',        'Bank Name',        'e.g. HBL / MCB / UBL'],
            ['bank_account',     'Account Number',   '0000000000000000'],
            ['bank_branch_code', 'Branch Code',      '0000'],
        ] as [$key, $label, $placeholder])
        <div>
            <label class="form-label">{{ $label }}</label>
            <input type="text" name="{{ $key }}"
                   value="{{ old($key, $settings[$key] ?? '') }}"
                   placeholder="{{ $placeholder }}" class="form-input">
        </div>
        @endforeach
        <div>
            <label class="form-label">Payslip Footer Note</label>
            <input type="text" name="payslip_footer_note"
                   value="{{ old('payslip_footer_note', $settings['payslip_footer_note'] ?? 'This is a computer generated payslip.') }}"
                   class="form-input">
        </div>
    </div>
</div>

<div style="display:flex;justify-content:space-between;align-items:center;">
    <a href="{{ route('settings.index') }}" class="btn-outline">
        <i class="fa-solid fa-arrow-left"></i> Back
    </a>
    <button type="submit" class="btn-gold">
        <i class="fa-solid fa-floppy-disk"></i> Save Payroll Settings
    </button>
</div>
</form>
</div>
@endsection