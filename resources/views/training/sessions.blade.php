@extends('layouts.app')
@section('title', 'Training Sessions')
@section('page-title', 'Training Sessions')
@section('breadcrumb', 'Training · Sessions')

@section('content')

{{-- Toolbar --}}
<div class="card card-sm" style="margin-bottom:18px;">
    <div class="toolbar">
        <form method="GET" action="{{ route('training.sessions') }}" class="toolbar" style="flex:1;">
            <select name="status" class="form-select">
                <option value="">All Status</option>
                @foreach(['scheduled','ongoing','completed','cancelled','postponed'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>
                    {{ ucfirst($s) }}
                </option>
                @endforeach
            </select>
            <select name="program" class="form-select" style="min-width:180px;">
                <option value="">All Programs</option>
                @foreach($programs as $p)
                <option value="{{ $p->id }}" {{ request('program') == $p->id ? 'selected' : '' }}>
                    {{ $p->title }}
                </option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-filter"></i> Filter
            </button>
            @if(request()->hasAny(['status','program']))
            <a href="{{ route('training.sessions') }}" class="btn btn-secondary btn-sm">
                <i class="fa-solid fa-xmark"></i> Clear
            </a>
            @endif
        </form>
        <a href="{{ route('training.sessions.create') }}" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus"></i> New Session
        </a>
    </div>
</div>

{{-- Session Cards --}}
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:16px;">
    @forelse($sessions as $session)
    @php $sBadge = $session->status_badge; @endphp
    <div class="card" style="display:flex;flex-direction:column;transition:transform .2s,border-color .2s;"
         onmouseover="this.style.transform='translateY(-2px)';this.style.borderColor='var(--accent-border)'"
         onmouseout="this.style.transform='';this.style.borderColor='var(--border)'">

        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:12px;">
            <div style="flex:1;">
                <div style="font-size:14px;font-weight:700;color:var(--text-primary);margin-bottom:4px;">
                    {{ $session->title }}
                </div>
                <div style="font-size:11px;color:var(--accent);">
                    <i class="fa-solid fa-book" style="margin-right:4px;"></i>
                    {{ $session->program->title }}
                </div>
                <div style="font-size:10px;color:var(--text-muted);margin-top:1px;">
                    {{ $session->session_code }}
                </div>
            </div>
            <span class="badge" style="background:{{ $sBadge['bg'] }};color:{{ $sBadge['color'] }};border:1px solid {{ $sBadge['border'] }};flex-shrink:0;">
                {{ ucfirst($session->status) }}
            </span>
        </div>

        <div style="display:flex;flex-direction:column;gap:5px;margin-bottom:14px;font-size:12px;color:var(--text-secondary);">
            <div style="display:flex;align-items:center;gap:7px;">
                <i class="fa-solid fa-calendar" style="color:var(--accent);width:14px;text-align:center;"></i>
                {{ $session->start_date->format('d M Y') }}
                @if(!$session->start_date->eq($session->end_date)) – {{ $session->end_date->format('d M Y') }} @endif
                @if($session->start_time) · {{ date('h:i A', strtotime($session->start_time)) }} @endif
            </div>
            @if($session->venue)
            <div style="display:flex;align-items:center;gap:7px;">
                <i class="fa-solid fa-location-dot" style="color:var(--accent);width:14px;text-align:center;"></i>
                {{ $session->venue }}
            </div>
            @endif
            @if($session->trainer_name)
            <div style="display:flex;align-items:center;gap:7px;">
                <i class="fa-solid fa-chalkboard-user" style="color:var(--accent);width:14px;text-align:center;"></i>
                {{ $session->trainer_name }}
            </div>
            @endif
        </div>

        <div style="margin-bottom:14px;">
            <div style="display:flex;justify-content:space-between;margin-bottom:5px;">
                <span style="font-size:11px;color:var(--text-muted);">Enrollment</span>
                <span style="font-size:12px;font-weight:700;color:var(--accent);">
                    {{ $session->enrolled_count }}/{{ $session->max_participants }}
                    @if($session->isFull())
                        <span style="font-size:10px;color:var(--red);">(Full)</span>
                    @else
                        <span style="font-size:10px;color:var(--text-muted);">({{ $session->seat_available }} left)</span>
                    @endif
                </span>
            </div>
            @php $pct = $session->max_participants > 0 ? round($session->enrolled_count / $session->max_participants * 100) : 0; @endphp
            <div class="progress-track">
                <div class="progress-fill"
                     style="width:{{ $pct }}%;
                            background:{{ $session->isFull() ? 'var(--red)' : 'var(--accent)' }};"></div>
            </div>
        </div>

        @if($session->average_rating)
        <div style="font-size:11px;color:var(--accent);margin-bottom:10px;">
            <i class="fa-solid fa-star" style="margin-right:3px;"></i>
            Avg Rating: {{ $session->average_rating }}/5
        </div>
        @endif

        <a href="{{ route('training.session', $session) }}"
           class="btn btn-secondary btn-sm" style="justify-content:center;margin-top:auto;">
            Manage Session →
        </a>
    </div>
    @empty
    <div class="card" style="grid-column:1/-1;">
        <div class="empty-state">
            <i class="fa-solid fa-calendar-xmark"></i>
            No training sessions found.
            <a href="{{ route('training.sessions.create') }}">Schedule the first session</a>
        </div>
    </div>
    @endforelse
</div>

{{-- Pagination --}}
@if($sessions->hasPages())
<div class="pagination" style="border:none;padding-top:20px;">
    <span class="pagination-info">
        Showing {{ $sessions->firstItem() }}–{{ $sessions->lastItem() }} of {{ $sessions->total() }}
    </span>
    <div class="pagination-btns">
        @if($sessions->onFirstPage())
            <span class="page-btn disabled">← Prev</span>
        @else
            <a href="{{ $sessions->previousPageUrl() }}" class="page-btn">← Prev</a>
        @endif
        @if($sessions->hasMorePages())
            <a href="{{ $sessions->nextPageUrl() }}" class="page-btn active">Next →</a>
        @else
            <span class="page-btn disabled">Next →</span>
        @endif
    </div>
</div>
@endif

@endsection