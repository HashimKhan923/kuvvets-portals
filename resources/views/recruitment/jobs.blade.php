@extends('layouts.app')
@section('title', 'Job Postings')
@section('page-title', 'Job Postings')
@section('breadcrumb', 'Recruitment · All Jobs')

@section('content')

{{-- Toolbar --}}
<div class="card card-sm" style="margin-bottom:18px;">
    <div class="toolbar">
        <form method="GET" action="{{ route('recruitment.jobs') }}" class="toolbar" style="flex:1;">
            <div class="toolbar-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search title or reference…" class="form-input">
            </div>
            <select name="status" class="form-select">
                <option value="">All Status</option>
                @foreach(['draft'=>'Draft','open'=>'Open','on_hold'=>'On Hold','closed'=>'Closed','cancelled'=>'Cancelled'] as $v => $l)
                    <option value="{{ $v }}" {{ request('status') === $v ? 'selected' : '' }}>{{ $l }}</option>
                @endforeach
            </select>
            <select name="department" class="form-select">
                <option value="">All Departments</option>
                @foreach($departments as $d)
                    <option value="{{ $d->id }}" {{ request('department') == $d->id ? 'selected' : '' }}>
                        {{ $d->name }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-filter"></i> Filter
            </button>
        </form>
        <a href="{{ route('recruitment.jobs.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Post New Job
        </a>
    </div>
</div>

{{-- Job Cards --}}
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:16px;">

    @forelse($jobs as $job)
    @php $badge = $job->status_badge; @endphp
    <div class="card" style="display:flex;flex-direction:column;transition:border-color .2s,transform .2s;"
         onmouseover="this.style.borderColor='var(--accent-border)';this.style.transform='translateY(-2px)'"
         onmouseout="this.style.borderColor='var(--border)';this.style.transform=''">

        {{-- Header --}}
        <div style="margin-bottom:14px;">
            <div style="display:flex;align-items:center;gap:7px;margin-bottom:7px;">
                <span class="badge" style="background:{{ $badge['bg'] }};color:{{ $badge['color'] }};border:1px solid {{ $badge['border'] }};">
                    {{ ucfirst($job->status) }}
                </span>
                @if($job->isExpired())
                    <span class="badge badge-red">Expired</span>
                @elseif($job->days_remaining !== null && $job->days_remaining <= 7)
                    <span class="badge badge-yellow">{{ $job->days_remaining }}d left</span>
                @endif
            </div>
            <div style="font-size:15px;font-weight:700;color:var(--text-primary);">
                {{ $job->title }}
            </div>
            <div style="font-size:11px;color:var(--text-muted);margin-top:3px;">
                {{ $job->reference_no }} · {{ $job->department?->name ?? 'No Department' }}
            </div>
        </div>

        {{-- Meta --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:7px;margin-bottom:14px;">
            @foreach([
                ['fa-briefcase',    ucfirst(str_replace('_', ' ', $job->type))],
                ['fa-chart-line',   ucfirst($job->experience_level)],
                ['fa-users',        $job->vacancies . ' ' . Str::plural('vacancy', $job->vacancies)],
                ['fa-money-bill',   $job->salary_range],
                ['fa-calendar',     $job->deadline?->format('d M Y') ?? 'No deadline'],
                ['fa-location-dot', $job->location ?? 'Pakistan'],
            ] as [$icon, $value])
            <div style="display:flex;align-items:center;gap:6px;font-size:11px;color:var(--text-muted);">
                <i class="fa-solid {{ $icon }}" style="font-size:10px;color:var(--accent);width:12px;flex-shrink:0;"></i>
                {{ $value }}
            </div>
            @endforeach
        </div>

        {{-- Applications bar --}}
        <div style="margin-bottom:14px;">
            <div style="display:flex;justify-content:space-between;margin-bottom:5px;">
                <span style="font-size:11px;color:var(--text-muted);">Applications</span>
                <span style="font-size:11px;color:var(--accent);font-weight:600;">
                    {{ $job->applicants_count }}
                </span>
            </div>
            @php $fill = $job->vacancies > 0 ? min(100, round($job->applicants_count / max($job->vacancies, 1) * 10)) : 0; @endphp
            <div class="progress-track">
                <div class="progress-fill" style="width:{{ $fill }}%;"></div>
            </div>
        </div>

        {{-- Actions --}}
        <div style="display:flex;gap:8px;margin-top:auto;">
            <a href="{{ route('recruitment.jobs.show', $job) }}"
               class="btn btn-secondary btn-sm" style="flex:1;justify-content:center;">
                <i class="fa-solid fa-eye"></i> View Pipeline
            </a>
            <form method="POST" action="{{ route('recruitment.jobs.status', $job) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="status"
                       value="{{ $job->status === 'open' ? 'on_hold' : 'open' }}">
                <button type="submit" class="action-btn"
                        title="{{ $job->status === 'open' ? 'Put on hold' : 'Reopen' }}">
                    <i class="fa-solid fa-{{ $job->status === 'open' ? 'pause' : 'play' }}"></i>
                </button>
            </form>
        </div>

    </div>
    @empty
    <div class="card" style="grid-column:1/-1;">
        <div class="empty-state">
            <i class="fa-solid fa-briefcase"></i>
            No job postings found.
            <a href="{{ route('recruitment.jobs.create') }}">Create your first job posting</a>
        </div>
    </div>
    @endforelse

</div>

{{-- Pagination --}}
@if($jobs->hasPages())
<div class="pagination" style="border:none;padding-top:20px;">
    <span class="pagination-info">
        Showing {{ $jobs->firstItem() }}–{{ $jobs->lastItem() }} of {{ $jobs->total() }}
    </span>
    <div class="pagination-btns">
        @if($jobs->onFirstPage())
            <span class="page-btn disabled">← Prev</span>
        @else
            <a href="{{ $jobs->previousPageUrl() }}" class="page-btn">← Prev</a>
        @endif
        @if($jobs->hasMorePages())
            <a href="{{ $jobs->nextPageUrl() }}" class="page-btn active">Next →</a>
        @else
            <span class="page-btn disabled">Next →</span>
        @endif
    </div>
</div>
@endif

@endsection