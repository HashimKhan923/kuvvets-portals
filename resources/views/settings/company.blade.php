@extends('layouts.app')
@section('title','Company Profile')
@section('page-title','Company Profile')
@section('breadcrumb','Settings · Company Profile')

@section('content')
<div style="width:100%">
<form method="POST" action="{{ route('settings.company.update') }}" enctype="multipart/form-data">
@csrf

{{-- Logo + Name --}}
<div class="card card-gold" style="padding:26px;margin-bottom:16px;">
    <div class="section-title">
        <i class="fa-solid fa-building"></i> Company Identity
    </div>
    <div style="display:grid;grid-template-columns:180px 1fr;gap:24px;align-items:start;">
        {{-- Logo --}}
        <div style="display:flex;flex-direction:column;align-items:center;gap:10px;">
            <img src="{{ $company->logo_url }}" alt="Logo"
                 style="width:120px;height:120px;border-radius:12px;object-fit:contain;
                        border:2px solid var(--border);background:var(--cream-warm);padding:6px;">
            <label class="form-label" style="margin:0;">Company Logo</label>
            <input type="file" name="logo" accept="image/*" class="form-input" style="font-size:11px;">
            <div style="font-size:10px;color:var(--text-muted);">Max 2MB · JPG/PNG</div>
        </div>
        {{-- Names --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div>
                <label class="form-label">Company Name <span style="color:var(--red)">*</span></label>
                <input type="text" name="name" required
                       value="{{ old('name', $company->name) }}"
                       class="form-input" placeholder="Kuvvets">
            </div>
            <div>
                <label class="form-label">Legal / Registered Name</label>
                <input type="text" name="legal_name"
                       value="{{ old('legal_name', $company->legal_name) }}"
                       class="form-input" placeholder="Kuvvets Pvt. Ltd.">
            </div>
            <div>
                <label class="form-label">Registration No.</label>
                <input type="text" name="registration_no"
                       value="{{ old('registration_no', $company->registration_no ?? '') }}"
                       class="form-input" placeholder="SECP-XXXX">
            </div>
            <div>
                <label class="form-label">Website</label>
                <input type="url" name="website"
                       value="{{ old('website', $company->website) }}"
                       class="form-input" placeholder="https://kuvvets.com">
            </div>
        </div>
    </div>
</div>

{{-- Contact --}}
<div class="card card-gold" style="padding:26px;margin-bottom:16px;">
    <div class="section-title">
        <i class="fa-solid fa-address-card"></i> Contact Information
    </div>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;">
        <div>
            <label class="form-label">Email <span style="color:var(--red)">*</span></label>
            <input type="email" name="email" required
                   value="{{ old('email', $company->email) }}"
                   class="form-input" placeholder="info@company.com">
        </div>
        <div>
            <label class="form-label">Phone</label>
            <input type="text" name="phone"
                   value="{{ old('phone', $company->phone) }}"
                   class="form-input" placeholder="+92 300 0000000">
        </div>
        <div>
            <label class="form-label">Address</label>
            <input type="text" name="address"
                   value="{{ old('address', $company->address) }}"
                   class="form-input" placeholder="Street, Area">
        </div>
        <div>
            <label class="form-label">City</label>
            <input type="text" name="city"
                   value="{{ old('city', $company->city) }}"
                   class="form-input" placeholder="Islamabad">
        </div>
        <div>
            <label class="form-label">Province</label>
            <input type="text" name="province"
                   value="{{ old('province', $company->province) }}"
                   class="form-input" placeholder="Punjab">
        </div>
        <div>
            <label class="form-label">Country</label>
            <input type="text" name="country"
                   value="{{ old('country', $company->country ?? 'Pakistan') }}"
                   class="form-input" placeholder="Pakistan">
        </div>
    </div>
</div>

{{-- Tax / Compliance --}}
<div class="card card-gold" style="padding:26px;margin-bottom:16px;">
    <div class="section-title">
        <i class="fa-solid fa-file-invoice"></i> Tax &amp; Compliance (FBR)
    </div>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;">
        <div>
            <label class="form-label">NTN (National Tax Number)</label>
            <input type="text" name="ntn"
                   value="{{ old('ntn', $company->ntn) }}"
                   class="form-input" placeholder="1234567-8">
        </div>
        <div>
            <label class="form-label">STRN (Sales Tax Reg. No.)</label>
            <input type="text" name="strn"
                   value="{{ old('strn', $company->strn) }}"
                   class="form-input" placeholder="12-34-5678901234">
        </div>
        <div>
            <label class="form-label">Currency</label>
            <select name="currency" class="form-select">
                @foreach(['PKR' => 'PKR — Pakistani Rupee', 'USD' => 'USD — US Dollar', 'GBP' => 'GBP — Pound Sterling', 'AED' => 'AED — UAE Dirham'] as $code => $label)
                <option value="{{ $code }}" {{ old('currency', $company->currency ?? 'PKR') === $code ? 'selected' : '' }}>
                    {{ $label }}
                </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Timezone</label>
            <select name="timezone" class="form-select">
                @foreach(['Asia/Karachi' => 'Asia/Karachi (PKT UTC+5)', 'UTC' => 'UTC', 'Asia/Dubai' => 'Asia/Dubai (GST UTC+4)', 'Asia/Kolkata' => 'Asia/Kolkata (IST UTC+5:30)'] as $tz => $label)
                <option value="{{ $tz }}" {{ old('timezone', $company->timezone ?? 'Asia/Karachi') === $tz ? 'selected' : '' }}>
                    {{ $label }}
                </option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<div style="display:flex;justify-content:space-between;align-items:center;">
    <a href="{{ route('settings.index') }}" class="btn-outline">
        <i class="fa-solid fa-arrow-left"></i> Back to Settings
    </a>
    <button type="submit" class="btn-gold">
        <i class="fa-solid fa-floppy-disk"></i> Save Company Profile
    </button>
</div>
</form>
</div>
@endsection
