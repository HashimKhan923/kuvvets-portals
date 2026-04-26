@extends('layouts.app')
@section('title', 'Recruitment')
@section('page-title', 'Recruitment Dashboard')
@section('breadcrumb', 'Recruitment · Overview')

@section('content')

{{-- Stats --}}
<div class="stats-grid-4">
    <div class="stat-card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
            <div class="stat-label">Open Positions</div>
            <div class="stat-icon stat-icon-accent"><i class="fa-solid fa-briefcase"></i></div>
        </div>
        <div class="stat-num">{{ $stats['open_jobs'] }}</div>
    </div>
    <div class="stat-card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
            <div class="stat-label">Total Applicants</div>
            <div class="stat-icon stat-icon-blue"><i class="fa-solid fa-users"></i></div>
        </div>
        <div class="stat-num">{{ $stats['total_apps'] }}</div>
    </div>
    <div class="stat-card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
            <div class="stat-label">Upcoming Interviews</div>
            <div class="stat-icon stat-icon-purple"><i class="fa-solid fa-calendar-check"></i></div>
        </div>
        <div class="stat-num">{{ $stats['interviews'] }}</div>
    </div>
    <div class="stat-card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
            <div class="stat-label">Hired This Month</div>
            <div class="stat-icon stat-icon-green"><i class="fa-solid fa-user-check"></i></div>
        </div>
        <div class="stat-num">{{ $stats['hired_month'] }}</div>
    </div>
</div>

{{-- Pipeline Funnel --}}
<div class="card" style="margin-bottom:20px;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
        <div class="card-title" style="margin-bottom:0;">
            <i class="fa-solid fa-filter"></i> Recruitment Pipeline
        </div>
        <a href="{{ route('recruitment.jobs') }}"
           style="font-size:12px;color:var(--accent);text-decoration:none;font-weight:500;">
            View all jobs →
        </a>
    </div>
    @php
        $total = max(array_sum($pipeline), 1);
        $pipelineStages = [
            ['Applied',     $pipeline['applied'],             'var(--text-muted)'],
            ['Screening',   $pipeline['screening'],           'var(--blue)'],
            ['Shortlisted', $pipeline['shortlisted'],         'var(--accent-light)'],
            ['Interview',   $pipeline['interview_scheduled'], 'var(--purple)'],
            ['Offer Sent',  $pipeline['offer_sent'],          'var(--accent)'],
            ['Hired',       $pipeline['hired'],               'var(--green)'],
        ];
    @endphp
    <div style="display:grid;grid-template-columns:repeat(6,1fr);gap:8px;">
        @foreach($pipelineStages as [$label, $count, $color])
        @php $pct = round($count / $total * 100); @endphp
        <div style="text-align:center;">
            <div style="font-size:24px;font-weight:700;color:{{ $color }};margin-bottom:6px;">
                {{ $count }}
            </div>
            <div class="progress-track" style="margin-bottom:6px;">
                <div class="progress-fill" style="width:{{ $pct }}%;background:{{ $color }};"></div>
            </div>
            <div style="font-size:10px;color:var(--text-muted);letter-spacing:.4px;">{{ $label }}</div>
        </div>
        @endforeach
    </div>
</div>

{{-- Active Jobs + Upcoming Interviews --}}
<div class="grid-2" style="margin-bottom:20px;">

    {{-- Active Jobs --}}
    <div class="card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
            <div class="card-title" style="margin-bottom:0;">
                <i class="fa-solid fa-briefcase"></i> Active Jobs
            </div>
            <a href="{{ route('recruitment.jobs.create') }}" class="btn btn-primary btn-xs">
                <i class="fa-solid fa-plus"></i> New
            </a>
        </div>

        @forelse($jobs as $job)
        @php $badge = $job->status_badge; @endphp
        <a href="{{ route('recruitment.jobs.show', $job) }}"
           style="display:flex;align-items:center;justify-content:space-between;padding:10px 12px;
                  background:var(--bg-muted);border:1px solid var(--border);border-radius:8px;
                  margin-bottom:7px;text-decoration:none;transition:border-color .15s;"
           onmouseover="this.style.borderColor='var(--accent-border)'"
           onmouseout="this.style.borderColor='var(--border)'">
            <div>
                <div style="font-size:13px;font-weight:600;color:var(--text-primary);">
                    {{ $job->title }}
                </div>
                <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">
                    {{ $job->department?->name ?? 'No dept' }} ·
                    {{ $job->vacancies }} {{ Str::plural('vacancy', $job->vacancies) }} ·
                    {{ $job->reference_no }}
                </div>
            </div>
            <div style="text-align:right;flex-shrink:0;margin-left:12px;">
                <span class="badge" style="background:{{ $badge['bg'] }};color:{{ $badge['color'] }};border:1px solid {{ $badge['border'] }};">
                    {{ ucfirst($job->status) }}
                </span>
                <div style="font-size:11px;color:var(--accent);margin-top:3px;">
                    {{ $job->applicants_count }} applicants
                </div>
            </div>
        </a>
        @empty
        <div class="empty-state" style="padding:28px;">
            <i class="fa-solid fa-briefcase"></i>
            No active job postings
        </div>
        @endforelse
    </div>

    {{-- Upcoming Interviews --}}
    <div class="card">
        <div class="card-title">
            <i class="fa-solid fa-calendar-check"></i> Upcoming Interviews
        </div>

        @forelse($upcomingInterviews as $iv)
        <div style="padding:10px 12px;background:var(--bg-muted);border:1px solid var(--border);
                    border-radius:8px;margin-bottom:7px;">
            <div style="display:flex;align-items:center;justify-content:space-between;">
                <div>
                    <div style="font-size:13px;font-weight:600;color:var(--text-primary);">
                        {{ $iv->applicant->full_name }}
                    </div>
                    <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">
                        {{ $iv->jobPosting->title }} · Round {{ $iv->round }}
                    </div>
                </div>
                <div style="text-align:right;flex-shrink:0;margin-left:12px;">
                    <div style="font-size:12px;font-weight:600;color:var(--accent);">
                        {{ $iv->scheduled_at->setTimezone('Asia/Karachi')->format('d M') }}
                    </div>
                    <div style="font-size:11px;color:var(--text-muted);">
                        {{ $iv->scheduled_at->setTimezone('Asia/Karachi')->format('h:i A') }}
                    </div>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:8px;margin-top:7px;">
                <span class="badge badge-purple" style="font-size:10px;">
                    {{ ucfirst($iv->type) }}
                </span>
                @if($iv->location)
                <span style="font-size:10px;color:var(--text-muted);">
                    <i class="fa-solid fa-location-dot" style="font-size:9px;"></i>
                    {{ $iv->location }}
                </span>
                @endif
            </div>
        </div>
        @empty
        <div class="empty-state" style="padding:28px;">
            <i class="fa-solid fa-calendar-xmark"></i>
            No upcoming interviews
        </div>
        @endforelse
    </div>

</div>

{{-- Recent Applicants --}}
<div class="card card-flush">
    <div style="display:flex;align-items:center;padding:14px 20px;border-bottom:1px solid var(--border);">
        <div class="card-title" style="margin-bottom:0;">
            <i class="fa-solid fa-users"></i> Recent Applicants
        </div>
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Applicant</th>
                <th>Applied For</th>
                <th>Experience</th>
                <th>Expected Salary</th>
                <th>Source</th>
                <th>Stage</th>
                <th>Applied</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentApplicants as $app)
            @php $badge = $app->stage_badge; @endphp
            <tr>
                <td>
                    <div class="td-employee">
                        <img src="{{ $app->avatar_url }}" class="avatar avatar-sm">
                        <div>
                            <a href="{{ route('recruitment.applicants.show', $app) }}"
                               class="td-employee name">
                                {{ $app->full_name }}
                            </a>
                            <div style="font-size:10px;color:var(--text-muted);">{{ $app->email }}</div>
                        </div>
                    </div>
                </td>
                <td class="muted">{{ $app->jobPosting->title }}</td>
                <td class="muted">{{ $app->total_experience_years }}y</td>
                <td class="muted">
                    {{ $app->expected_salary ? 'PKR ' . number_format($app->expected_salary) : '—' }}
                </td>
                <td class="muted" style="font-size:11px;">{{ $app->source ?? '—' }}</td>
                <td>
                    <span class="badge" style="background:{{ $badge['bg'] }};color:{{ $badge['color'] }};border:1px solid {{ $badge['border'] }};">
                        {{ $badge['label'] }}
                    </span>
                </td>
                <td class="muted" style="font-size:11px;">{{ $app->created_at->diffForHumans() }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7">
                    <div class="empty-state">No applicants yet.</div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection