@extends('layouts.app')
@section('title','HR Settings')
@section('page-title','HR Policy Settings')
@section('breadcrumb','Settings · HR Policies')

@section('content')
<div style="max-width:860px;">
<form method="POST" action="{{ route('settings.hr.update') }}">
@csrf

{{-- Employment Policies --}}
<div class="card card-gold" style="padding:26px;margin-bottom:16px;">
    <div class="section-title">
        <i class="fa-solid fa-briefcase"></i> Employment Policies
    </div>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;">
        @foreach([
            ['probation_period_months', 'Probation Period (months)', '3', 'number'],
            ['notice_period_days',      'Notice Period (days)',       '30','number'],
            ['working_hours_per_day',   'Working Hours / Day',        '8', 'number'],
            ['working_days_per_week',   'Working Days / Week',        '5', 'number'],
            ['overtime_rate_multiplier','Overtime Rate Multiplier',   '2', 'number'],
        ] as [$key, $label, $default, $type])
        <div>
            <label class="form-label">{{ $label }}</label>
            <input type="{{ $type }}" name="{{ $key }}"
                   value="{{ old($key, $settings[$key] ?? $default) }}"
                   step="{{ $type === 'number' ? '0.5' : '1' }}"
                   class="form-input">
        </div>
        @endforeach

        <div>
            <label class="form-label">Employee ID Prefix</label>
            <input type="text" name="employee_id_prefix"
                   value="{{ old('employee_id_prefix', $settings['employee_id_prefix'] ?? 'KVT') }}"
                   placeholder="KVT" class="form-input">
        </div>
        <div>
            <label class="form-label">Employee ID Digits</label>
            <input type="number" name="employee_id_digits"
                   value="{{ old('employee_id_digits', $settings['employee_id_digits'] ?? '4') }}"
                   min="3" max="8" class="form-input">
        </div>
    </div>
</div>

{{-- Leave Policies --}}
<div class="card card-gold" style="padding:26px;margin-bottom:16px;">
    <div class="section-title">
        <i class="fa-solid fa-calendar-check"></i> Leave Policies
    </div>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;">
        @foreach([
            ['annual_leave_days',  'Annual Leave (days)',  '14'],
            ['casual_leave_days',  'Casual Leave (days)',  '10'],
            ['sick_leave_days',    'Sick Leave (days)',    '8'],
            ['max_carry_forward_days','Max Carry Forward', '10'],
        ] as [$key, $label, $default])
        <div>
            <label class="form-label">{{ $label }}</label>
            <input type="number" name="{{ $key }}"
                   value="{{ old($key, $settings[$key] ?? $default) }}"
                   min="0" class="form-input">
        </div>
        @endforeach

        <div style="display:flex;flex-direction:column;gap:14px;grid-column:span 3;">
            @foreach([
                ['allow_negative_leave_balance', 'Allow employees to go into negative leave balance'],
                ['carry_forward_leaves',         'Enable leave carry-forward to next year'],
            ] as [$key, $label])
            <label style="display:flex;align-items:center;gap:10px;
                          font-size:13px;color:var(--text-primary);cursor:pointer;">
                <input type="checkbox" name="{{ $key }}" value="1"
                       {{ ($settings[$key] ?? '0') === '1' ? 'checked' : '' }}
                       style="accent-color:var(--gold);width:16px;height:16px;">
                {{ $label }}
            </label>
            @endforeach
        </div>
    </div>
</div>

{{-- Weekend Days --}}
<div class="card card-gold" style="padding:26px;margin-bottom:16px;">
    <div class="section-title">
        <i class="fa-solid fa-calendar-week"></i> Weekend Days
    </div>
    @php
        $weekendDays = json_decode($settings['weekend_days'] ?? '["Saturday","Sunday"]', true) ?? ['Saturday','Sunday'];
    @endphp
    <div style="display:flex;gap:12px;flex-wrap:wrap;">
        @foreach(['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $day)
        <label style="display:flex;align-items:center;gap:7px;font-size:13px;
                      color:var(--text-primary);cursor:pointer;background:var(--cream-warm);
                      border:1px solid var(--border);border-radius:8px;padding:9px 14px;
                      transition:border-color .2s;"
               onmouseover="this.style.borderColor='var(--gold)'"
               onmouseout="this.style.borderColor='var(--border)'">
            <input type="checkbox" name="weekend_days[]" value="{{ $day }}"
                   {{ in_array($day, $weekendDays) ? 'checked' : '' }}
                   style="accent-color:var(--gold);width:14px;height:14px;">
            {{ $day }}
        </label>
        @endforeach
    </div>
</div>

<div style="display:flex;justify-content:space-between;align-items:center;">
    <a href="{{ route('settings.index') }}" class="btn-outline">
        <i class="fa-solid fa-arrow-left"></i> Back
    </a>
    <button type="submit" class="btn-gold">
        <i class="fa-solid fa-floppy-disk"></i> Save HR Settings
    </button>
</div>
</form>
</div>
@endsection