@extends('layouts.app')
@section('title', $session->title)
@section('page-title', $session->title)
@section('breadcrumb', 'Training · Sessions · ' . $session->session_code)

@section('content')

{{-- Session Header --}}
@php $sBadge = $session->status_badge; @endphp
<div class="card" style="margin-bottom:20px;">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:14px;">
        <div>
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:8px;">
                <span class="badge" style="background:{{ $sBadge['bg'] }};color:{{ $sBadge['color'] }};border:1px solid {{ $sBadge['border'] }};font-size:12px;padding:4px 12px;">
                    {{ ucfirst($session->status) }}
                </span>
                <span style="font-size:12px;color:var(--text-muted);">{{ $session->session_code }}</span>
                <span class="badge badge-accent">{{ $session->program->title }}</span>
            </div>
            <div style="display:flex;gap:16px;flex-wrap:wrap;font-size:12px;color:var(--text-secondary);">
                <span>
                    <i class="fa-solid fa-calendar" style="color:var(--accent);margin-right:5px;"></i>
                    {{ $session->start_date->format('d M Y') }}
                    @if($session->start_time) · {{ date('h:i A', strtotime($session->start_time)) }} – {{ date('h:i A', strtotime($session->end_time ?? '')) }} @endif
                </span>
                @if($session->venue)
                <span>
                    <i class="fa-solid fa-location-dot" style="color:var(--accent);margin-right:5px;"></i>
                    {{ $session->venue }}
                </span>
                @endif
                @if($session->trainer_name)
                <span>
                    <i class="fa-solid fa-chalkboard-user" style="color:var(--accent);margin-right:5px;"></i>
                    {{ $session->trainer_name }}
                </span>
                @endif
                <span>
                    <i class="fa-solid fa-users" style="color:var(--accent);margin-right:5px;"></i>
                    {{ $session->enrolled_count }}/{{ $session->max_participants }} enrolled
                </span>
            </div>
        </div>
        <form method="POST" action="{{ route('training.sessions.status', $session) }}"
              style="display:flex;gap:6px;">
            @csrf @method('PATCH')
            <select name="status" class="form-select" style="width:auto;">
                @foreach(['scheduled','ongoing','completed','cancelled','postponed'] as $s)
                <option value="{{ $s }}" {{ $session->status === $s ? 'selected' : '' }}>
                    {{ ucfirst($s) }}
                </option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary btn-sm">Update</button>
        </form>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 300px;gap:20px;align-items:start;">

    {{-- Left --}}
    <div>

        {{-- Attendance Marking --}}
        @if(in_array($session->status, ['ongoing','scheduled']) && $session->enrollments->count())
        <div class="card" style="margin-bottom:16px;">
            <div class="card-title">
                <i class="fa-solid fa-clipboard-check"></i> Mark Attendance & Scores
            </div>
            <form method="POST" action="{{ route('training.sessions.attendance', $session) }}">
                @csrf
                <div style="display:flex;flex-direction:column;gap:8px;margin-bottom:16px;">
                    @foreach($session->enrollments as $enrollment)
                    <div style="background:var(--bg-muted);border-radius:9px;padding:12px 16px;
                                display:flex;align-items:center;gap:12px;">
                        <input type="checkbox" name="attended[]"
                               value="{{ $enrollment->id }}"
                               {{ $enrollment->status === 'attended' ? 'checked' : '' }}
                               style="accent-color:var(--accent);width:16px;height:16px;flex-shrink:0;">
                        <img src="{{ $enrollment->employee->avatar_url }}"
                             class="avatar avatar-sm" style="flex-shrink:0;">
                        <div style="flex:1;">
                            <div style="font-size:13px;font-weight:600;color:var(--text-primary);">
                                {{ $enrollment->employee->full_name }}
                            </div>
                            <div style="font-size:11px;color:var(--text-muted);">
                                {{ $enrollment->employee->department?->name ?? '—' }} ·
                                {{ $enrollment->employee->employee_id }}
                            </div>
                        </div>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <label style="font-size:11px;color:var(--text-muted);">Score (%)</label>
                            <input type="number" name="scores[{{ $enrollment->id }}]"
                                   value="{{ $enrollment->score }}"
                                   min="0" max="100" placeholder="—"
                                   class="form-input" style="width:70px;text-align:center;">
                        </div>
                        @php $eBadge = $enrollment->status_badge; @endphp
                        <span class="badge" style="background:{{ $eBadge['bg'] }};color:{{ $eBadge['color'] }};border:1px solid {{ $eBadge['border'] }};font-size:10px;flex-shrink:0;">
                            {{ ucfirst($enrollment->status) }}
                        </span>
                    </div>
                    @endforeach
                </div>

                <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
                    <button type="button" class="btn btn-success btn-sm"
                            onclick="document.querySelectorAll('input[name=\'attended[]\']').forEach(function(c){c.checked=true})">
                        ✓ Mark All Present
                    </button>
                    <button type="button" class="btn btn-danger btn-sm"
                            onclick="document.querySelectorAll('input[name=\'attended[]\']').forEach(function(c){c.checked=false})">
                        ✗ Mark All Absent
                    </button>
                </div>

                <button type="submit" class="btn btn-primary"
                        onclick="return confirm('Submit attendance? Certificates will be auto-generated for attendees.')">
                    <i class="fa-solid fa-check-double"></i>
                    Submit Attendance & Generate Certificates
                </button>
            </form>
        </div>
        @endif

        {{-- Completed Results --}}
        @if($session->status === 'completed')
        <div class="card card-flush">
            <div style="padding:14px 20px;border-bottom:1px solid var(--border);">
                <div class="card-title" style="margin-bottom:0;">
                    <i class="fa-solid fa-list-check"></i> Attendance Results
                </div>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Status</th>
                        <th>Score</th>
                        <th>Passed</th>
                        <th>Certificate</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($session->enrollments as $enrollment)
                    @php $eBadge = $enrollment->status_badge; @endphp
                    <tr>
                        <td>
                            <div class="td-employee">
                                <img src="{{ $enrollment->employee->avatar_url }}" class="avatar avatar-sm">
                                <div>
                                    <div class="td-employee name">{{ $enrollment->employee->full_name }}</div>
                                    <div class="td-employee id">{{ $enrollment->employee->employee_id }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge" style="background:{{ $eBadge['bg'] }};color:{{ $eBadge['color'] }};border:1px solid {{ $eBadge['border'] }};">
                                {{ ucfirst($enrollment->status) }}
                            </span>
                        </td>
                        <td style="font-size:13px;font-weight:600;color:var(--text-primary);">
                            {{ $enrollment->score ? $enrollment->score . '%' : '—' }}
                        </td>
                        <td>
                            @if($enrollment->passed !== null)
                            <span style="font-size:13px;">{{ $enrollment->passed ? '✅' : '❌' }}</span>
                            @else
                            <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td style="font-size:11px;color:var(--accent);">
                            {{ $enrollment->certificate_number ?? '—' }}
                            @if($enrollment->certificate_expiry)
                            <div style="font-size:10px;color:var(--text-muted);">
                                Exp: {{ $enrollment->certificate_expiry->format('d M Y') }}
                            </div>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

    </div>

    {{-- Right --}}
    <div style="display:flex;flex-direction:column;gap:14px;">

        {{-- Enroll Form --}}
        @if(!$session->isFull() && !in_array($session->status, ['completed','cancelled']))
        <div class="card">
            <div class="form-section">
                <i class="fa-solid fa-user-plus"></i> Enroll Employees
            </div>
            <form method="POST" action="{{ route('training.sessions.enroll', $session) }}">
                @csrf
                <div style="margin-bottom:10px;">
                    <label class="form-label">Select Employees</label>
                    <select name="employee_ids[]" multiple required
                            class="form-select" style="height:160px;">
                        @foreach($employees as $emp)
                        <option value="{{ $emp->id }}">
                            {{ $emp->full_name }} ({{ $emp->department?->name ?? 'No dept' }})
                        </option>
                        @endforeach
                    </select>
                    <div style="font-size:10px;color:var(--text-muted);margin-top:4px;">
                        Hold Ctrl/Cmd to select multiple
                    </div>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
                    <i class="fa-solid fa-user-plus"></i> Enroll Selected
                </button>
            </form>
        </div>
        @elseif($session->isFull())
        <div style="background:var(--red-bg);border:1px solid var(--red-border);
                    border-radius:10px;padding:14px;text-align:center;">
            <i class="fa-solid fa-users-slash" style="font-size:20px;color:var(--red);display:block;margin-bottom:6px;"></i>
            <div style="font-size:13px;color:var(--red);font-weight:600;">Session is Full</div>
        </div>
        @endif

        {{-- Session Stats --}}
        <div class="card">
            <div class="section-label">Session Stats</div>
            @php
                $attended = $session->enrollments->where('status', 'attended')->count();
                $absent   = $session->enrollments->where('status', 'absent')->count();
                $enrolled = $session->enrollments->where('status', 'enrolled')->count();
            @endphp
            @foreach([
                ['Enrolled',   $enrolled,               'blue'],
                ['Attended',   $attended,               'green'],
                ['Absent',     $absent,                 'red'],
                ['Seats Left', $session->seat_available,'accent'],
            ] as [$l, $v, $c])
            <div style="display:flex;justify-content:space-between;padding:8px 0;
                        border-bottom:1px solid var(--border);font-size:13px;">
                <span style="color:var(--text-secondary);">{{ $l }}</span>
                <span style="font-weight:700;color:var(--{{ $c }});">{{ $v }}</span>
            </div>
            @endforeach
            @if($session->actual_cost > 0)
            <div style="display:flex;justify-content:space-between;padding:8px 0;font-size:13px;">
                <span style="color:var(--text-secondary);">Total Cost</span>
                <span style="font-weight:700;color:var(--accent);">
                    PKR {{ number_format($session->actual_cost) }}
                </span>
            </div>
            @endif
        </div>

    </div>
</div>

@endsection