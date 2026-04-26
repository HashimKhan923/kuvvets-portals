@extends('layouts.app')
@section('title', $jobPosting->title)
@section('page-title', $jobPosting->title)
@section('breadcrumb', 'Recruitment · ' . $jobPosting->reference_no)

@section('content')

{{-- Job Header --}}
@php $badge = $jobPosting->status_badge; @endphp
<div class="card" style="margin-bottom:20px;">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:14px;">
        <div>
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
                <span class="badge" style="background:{{ $badge['bg'] }};color:{{ $badge['color'] }};border:1px solid {{ $badge['border'] }};">
                    {{ ucfirst($jobPosting->status) }}
                </span>
                <span style="font-size:11px;color:var(--text-muted);">{{ $jobPosting->reference_no }}</span>
                <span style="font-size:11px;color:var(--text-muted);">·</span>
                <span style="font-size:11px;color:var(--text-muted);">{{ $jobPosting->department?->name ?? 'No Dept' }}</span>
            </div>
            <div style="display:flex;flex-wrap:wrap;gap:14px;">
                @foreach([
                    ['fa-users',        $jobPosting->total_applications . ' applications'],
                    ['fa-briefcase',    ucfirst(str_replace('_', ' ', $jobPosting->type))],
                    ['fa-money-bill',   $jobPosting->salary_range],
                    ['fa-calendar',     $jobPosting->deadline ? 'Deadline: ' . $jobPosting->deadline->format('d M Y') : 'No deadline'],
                    ['fa-location-dot', $jobPosting->location ?? 'Pakistan'],
                ] as [$icon, $value])
                <span style="font-size:12px;color:var(--text-muted);display:flex;align-items:center;gap:5px;">
                    <i class="fa-solid {{ $icon }}" style="font-size:10px;color:var(--accent);"></i>
                    {{ $value }}
                </span>
                @endforeach
            </div>
        </div>
        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
            {{-- Status changer --}}
            <form method="POST" action="{{ route('recruitment.jobs.status', $jobPosting) }}"
                  style="display:flex;gap:6px;">
                @csrf @method('PATCH')
                <select name="status" class="form-select" style="width:auto;">
                    @foreach(['draft'=>'Draft','open'=>'Open','on_hold'=>'On Hold','closed'=>'Closed','cancelled'=>'Cancelled'] as $v => $l)
                        <option value="{{ $v }}" {{ $jobPosting->status === $v ? 'selected' : '' }}>{{ $l }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-primary btn-sm">Update</button>
            </form>
            <button onclick="document.getElementById('addApplicantModal').classList.add('open')"
                    class="btn btn-primary btn-sm">
                <i class="fa-solid fa-user-plus"></i> Add Applicant
            </button>
        </div>
    </div>
</div>

{{-- Kanban Pipeline --}}
@php
$stageLabels = [
    'applied'             => 'Applied',
    'screening'           => 'Screening',
    'shortlisted'         => 'Shortlisted',
    'interview_scheduled' => 'Interview Scheduled',
    'interviewed'         => 'Interviewed',
    'assessment'          => 'Assessment',
    'offer_sent'          => 'Offer Sent',
    'offer_accepted'      => 'Offer Accepted',
    'hired'               => 'Hired',
];
$stageColors = [
    'applied'             => 'var(--text-muted)',
    'screening'           => 'var(--blue)',
    'shortlisted'         => 'var(--accent-light)',
    'interview_scheduled' => 'var(--purple)',
    'interviewed'         => 'var(--green)',
    'assessment'          => 'var(--pink)',
    'offer_sent'          => 'var(--accent)',
    'offer_accepted'      => 'var(--green)',
    'hired'               => 'var(--green)',
];
@endphp

<div style="display:flex;gap:12px;overflow-x:auto;padding-bottom:12px;align-items:flex-start;">
    @foreach($stages as $stage)
    @php
        $apps  = $applicantsByStage->get($stage, collect());
        $color = $stageColors[$stage] ?? 'var(--text-muted)';
        $label = $stageLabels[$stage] ?? ucfirst($stage);
    @endphp
    <div style="flex-shrink:0;width:220px;background:var(--bg-muted);border:1px solid var(--border);border-radius:10px;overflow:hidden;">

        {{-- Column Header --}}
        <div style="padding:10px 14px;border-bottom:1px solid var(--border);
                    display:flex;align-items:center;justify-content:space-between;">
            <span style="font-size:11px;font-weight:600;color:{{ $color }};letter-spacing:.4px;">
                {{ $label }}
            </span>
            <span style="font-size:11px;background:var(--bg-card);color:{{ $color }};
                         border:1px solid var(--border);border-radius:10px;padding:1px 7px;font-weight:700;">
                {{ $apps->count() }}
            </span>
        </div>

        {{-- Applicant Cards --}}
        <div style="padding:8px;min-height:80px;display:flex;flex-direction:column;gap:7px;">
            @foreach($apps as $app)
            <a href="{{ route('recruitment.applicants.show', $app) }}"
               style="background:var(--bg-card);border:1px solid var(--border);border-radius:7px;
                      padding:10px;text-decoration:none;display:block;transition:border-color .15s;"
               onmouseover="this.style.borderColor='var(--accent-border)'"
               onmouseout="this.style.borderColor='var(--border)'">
                <div style="display:flex;align-items:center;gap:7px;margin-bottom:6px;">
                    <img src="{{ $app->avatar_url }}"
                         class="avatar avatar-sm"
                         style="width:24px;height:24px;">
                    <div style="font-size:12px;font-weight:600;color:var(--text-primary);line-height:1.2;">
                        {{ $app->full_name }}
                    </div>
                </div>
                <div style="font-size:10px;color:var(--text-muted);margin-bottom:4px;">
                    {{ $app->current_employer ?? 'No employer' }}
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-size:10px;color:var(--text-muted);">
                        {{ $app->total_experience_years }}y exp
                    </span>
                    @if($app->rating)
                    <span style="font-size:10px;color:var(--accent);">{{ $app->rating_stars }}</span>
                    @endif
                </div>
                @if($app->expected_salary)
                <div style="font-size:10px;color:var(--accent);margin-top:3px;font-weight:500;">
                    PKR {{ number_format($app->expected_salary) }}
                </div>
                @endif
            </a>
            @endforeach
        </div>

    </div>
    @endforeach
</div>

{{-- Add Applicant Modal --}}
<div id="addApplicantModal" class="modal-overlay">
    <div class="modal-box" style="width:620px;max-height:90vh;overflow-y:auto;">
        <div class="modal-title">
            <i class="fa-solid fa-user-plus"></i>
            Add Applicant — {{ $jobPosting->title }}
        </div>
        <form method="POST" action="{{ route('recruitment.applicants.store', $jobPosting) }}"
              enctype="multipart/form-data">
            @csrf
            <div class="grid-2" style="gap:12px;">
                @foreach([
                    ['first_name',               'First Name',           'text',   true],
                    ['last_name',                'Last Name',            'text',   true],
                    ['email',                    'Email',                'email',  true],
                    ['phone',                    'Phone',                'text',   false],
                    ['total_experience_years',   'Experience (years)',   'number', false],
                    ['current_employer',         'Current Employer',     'text',   false],
                    ['current_designation',      'Current Title',        'text',   false],
                    ['expected_salary',          'Expected Salary (PKR)','number', false],
                    ['notice_period_days',       'Notice Period (days)', 'number', false],
                    ['city',                     'City',                 'text',   false],
                ] as [$name, $label, $type, $req])
                <div>
                    <label class="form-label">
                        {{ $label }}
                        @if($req)<span style="color:var(--red);">*</span>@endif
                    </label>
                    <input type="{{ $type }}" name="{{ $name }}"
                           @if($req) required @endif class="form-input">
                </div>
                @endforeach

                <div>
                    <label class="form-label">Source</label>
                    <select name="source" class="form-select">
                        <option value="">Select</option>
                        @foreach(['Rozee.pk','LinkedIn','Indeed','Referral','Walk-in','Agency','Social Media','Company Website','Other'] as $src)
                            <option>{{ $src }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-label">Upload CV (PDF/DOC)</label>
                    <input type="file" name="cv" accept=".pdf,.doc,.docx" class="form-input">
                </div>

                <div class="col-span-2">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" rows="2" class="form-textarea"></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                        onclick="document.getElementById('addApplicantModal').classList.remove('open')">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-plus"></i> Add Applicant
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('addApplicantModal').addEventListener('click', function(e) {
    if (e.target === this) this.classList.remove('open');
});
</script>
@endpush

@endsection