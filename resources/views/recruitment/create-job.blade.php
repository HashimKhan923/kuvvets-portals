@extends('layouts.app')
@section('title', 'Post New Job')
@section('page-title', 'Post New Job')
@section('breadcrumb', 'Recruitment · Jobs · New Posting')

@section('content')
<div style="max-width:860px;">
<form method="POST" action="{{ route('recruitment.jobs.store') }}">
@csrf

{{-- Validation errors --}}
@if($errors->any())
<div class="error-box" style="margin-bottom:18px;">
    <div class="error-box-title">
        <i class="fa-solid fa-triangle-exclamation"></i> Fix these errors:
    </div>
    <ul style="padding-left:16px;">
        @foreach($errors->all() as $e)
            <li>{{ $e }}</li>
        @endforeach
    </ul>
</div>
@endif

{{-- Job Details --}}
<div class="card" style="margin-bottom:16px;">
    <div class="form-section">
        <i class="fa-solid fa-briefcase"></i> Job Details
    </div>
    <div style="display:grid;grid-template-columns:2fr 1fr 1fr;gap:16px;margin-bottom:16px;">
        <div>
            <label class="form-label">Job Title <span style="color:var(--red);">*</span></label>
            <input type="text" name="title" required value="{{ old('title') }}"
                   placeholder="e.g. Senior Warehouse Supervisor" class="form-input">
        </div>
        <div>
            <label class="form-label">Department</label>
            <select name="department_id" class="form-select">
                <option value="">Select</option>
                @foreach($departments as $d)
                    <option value="{{ $d->id }}" {{ old('department_id') == $d->id ? 'selected' : '' }}>
                        {{ $d->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Designation</label>
            <select name="designation_id" class="form-select">
                <option value="">Select</option>
                @foreach($designations as $d)
                    <option value="{{ $d->id }}" {{ old('designation_id') == $d->id ? 'selected' : '' }}>
                        {{ $d->title }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;">
        <div>
            <label class="form-label">Employment Type <span style="color:var(--red);">*</span></label>
            <select name="type" required class="form-select">
                @foreach(['permanent'=>'Permanent','contract'=>'Contract','part_time'=>'Part Time','internship'=>'Internship','daily_wages'=>'Daily Wages'] as $v => $l)
                    <option value="{{ $v }}" {{ old('type') === $v ? 'selected' : '' }}>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Experience Level <span style="color:var(--red);">*</span></label>
            <select name="experience_level" required class="form-select">
                @foreach(['entry'=>'Entry Level','junior'=>'Junior (1-2y)','mid'=>'Mid (3-5y)','senior'=>'Senior (5+y)','lead'=>'Lead','manager'=>'Manager'] as $v => $l)
                    <option value="{{ $v }}" {{ old('experience_level') === $v ? 'selected' : '' }}>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Vacancies <span style="color:var(--red);">*</span></label>
            <input type="number" name="vacancies" required value="{{ old('vacancies', 1) }}"
                   min="1" class="form-input">
        </div>
        <div>
            <label class="form-label">Application Deadline</label>
            <input type="date" name="deadline" value="{{ old('deadline') }}" class="form-input">
        </div>
    </div>
</div>

{{-- Compensation & Location --}}
<div class="card" style="margin-bottom:16px;">
    <div class="form-section">
        <i class="fa-solid fa-money-bill-wave"></i> Compensation & Location
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:16px;align-items:end;">
        <div>
            <label class="form-label">Min Salary (PKR)</label>
            <input type="number" name="salary_min" value="{{ old('salary_min') }}"
                   placeholder="30000" class="form-input">
        </div>
        <div>
            <label class="form-label">Max Salary (PKR)</label>
            <input type="number" name="salary_max" value="{{ old('salary_max') }}"
                   placeholder="80000" class="form-input">
        </div>
        <div>
            <label class="form-label">Location</label>
            <input type="text" name="location" value="{{ old('location', 'Karachi, Pakistan') }}"
                   class="form-input">
        </div>
        <div style="padding-bottom:2px;">
            <label style="display:flex;align-items:center;gap:8px;font-size:12px;color:var(--text-secondary);cursor:pointer;">
                <input type="checkbox" name="salary_disclosed" value="1"
                       {{ old('salary_disclosed') ? 'checked' : '' }}
                       style="accent-color:var(--accent);">
                Show salary to applicants
            </label>
        </div>
    </div>
</div>

{{-- Job Description --}}
<div class="card" style="margin-bottom:16px;">
    <div class="form-section">
        <i class="fa-solid fa-file-lines"></i> Job Description
    </div>
    @foreach([
        ['description',      'Job Description',              'Describe the overall role and context…'],
        ['responsibilities', 'Key Responsibilities',         "• Manage warehouse operations\n• Supervise loading/unloading…"],
        ['requirements',     'Requirements & Qualifications', "• Bachelor's degree preferred\n• 3+ years experience…"],
    ] as [$name, $label, $placeholder])
    <div style="margin-bottom:16px;">
        <label class="form-label">{{ $label }}</label>
        <textarea name="{{ $name }}" rows="4"
                  placeholder="{{ $placeholder }}"
                  class="form-textarea">{{ old($name) }}</textarea>
    </div>
    @endforeach
</div>

{{-- Submit --}}
<div style="display:flex;align-items:center;justify-content:space-between;">
    <a href="{{ route('recruitment.jobs') }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Cancel
    </a>
    <div style="display:flex;gap:10px;">
        <button type="submit" name="status" value="draft" class="btn btn-secondary">
            <i class="fa-solid fa-floppy-disk"></i> Save as Draft
        </button>
        <button type="submit" name="status" value="open" class="btn btn-primary">
            <i class="fa-solid fa-paper-plane"></i> Post Job
        </button>
    </div>
</div>

</form>
</div>
@endsection