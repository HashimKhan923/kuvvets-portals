@extends('layouts.app')
@section('title', $cycle->name)
@section('page-title', $cycle->name)
@section('breadcrumb', 'Performance · Cycles · ' . $cycle->name)

@section('content')

{{-- Cycle Header --}}
@php $badge = $cycle->status_badge; @endphp
<div class="card" style="margin-bottom:20px;">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
        <div>
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:8px;">
                <span class="badge" style="background:{{ $badge['bg'] }};color:{{ $badge['color'] }};border:1px solid {{ $badge['border'] }};font-size:12px;padding:4px 12px;">
                    {{ ucfirst($cycle->status) }}
                </span>
                <span style="font-size:12px;color:var(--text-muted);">{{ $cycle->duration }}</span>
                <span class="badge badge-accent">
                    {{ ucfirst(str_replace('_', ' ', $cycle->type)) }}
                </span>
            </div>
            <div style="display:flex;gap:18px;flex-wrap:wrap;font-size:12px;color:var(--text-secondary);">
                <span>
                    <i class="fa-solid fa-clipboard-list" style="color:var(--accent);margin-right:5px;"></i>
                    {{ $appraisals->count() }} Appraisals
                </span>
                <span>
                    <i class="fa-solid fa-bullseye" style="color:var(--accent);margin-right:5px;"></i>
                    {{ $goals->count() }} Goals
                </span>
                <span>
                    <i class="fa-solid fa-user" style="color:var(--accent);margin-right:5px;"></i>
                    Created by {{ $cycle->creator->name }}
                </span>
            </div>
        </div>
        <div style="display:flex;gap:8px;align-items:center;">
            @if($cycle->status === 'active')
            <form method="POST" action="{{ route('performance.cycles.generate', $cycle) }}">
                @csrf
                <button type="submit" class="btn btn-blue btn-sm"
                        onclick="return confirm('Generate appraisals for all active employees?')">
                    <i class="fa-solid fa-gears"></i>
                    {{ $appraisals->count() > 0 ? 'Re-Generate' : 'Generate Appraisals' }}
                </button>
            </form>
            @endif
            <form method="POST" action="{{ route('performance.cycles.status', $cycle) }}"
                  style="display:flex;gap:6px;">
                @csrf @method('PATCH')
                <select name="status" class="form-select" style="width:auto;">
                    @foreach(['draft'=>'Draft','active'=>'Active','review'=>'Review','completed'=>'Completed','cancelled'=>'Cancelled'] as $v => $l)
                    <option value="{{ $v }}" {{ $cycle->status === $v ? 'selected' : '' }}>{{ $l }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-primary btn-sm">Update</button>
            </form>
        </div>
    </div>
</div>

{{-- Tabs --}}
<div class="tab-nav">
    <button type="button" class="tab-btn" id="ctab-appraisals" onclick="switchCycleTab('appraisals')">
        <i class="fa-solid fa-clipboard-list"></i> Appraisals ({{ $appraisals->count() }})
    </button>
    <button type="button" class="tab-btn" id="ctab-goals" onclick="switchCycleTab('goals')">
        <i class="fa-solid fa-bullseye"></i> Goals ({{ $goals->count() }})
    </button>
</div>

{{-- APPRAISALS PANE --}}
<div id="cpane-appraisals">
    <div class="card card-flush">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Department</th>
                    <th>Score</th>
                    <th>Rating</th>
                    <th>Status</th>
                    <th class="center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($appraisals as $apr)
                @php
                    $sBadge = $apr->status_badge;
                    $rBadge = $apr->rating_badge;
                @endphp
                <tr>
                    <td>
                        <div class="td-employee">
                            <img src="{{ $apr->employee->avatar_url }}" class="avatar avatar-sm">
                            <div>
                                <a href="{{ route('performance.appraisal', $apr) }}"
                                   class="td-employee name">
                                    {{ $apr->employee->full_name }}
                                </a>
                                <div class="td-employee id">{{ $apr->employee->employee_id }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="muted">{{ $apr->employee->department?->name ?? '—' }}</td>
                    <td>
                        @if($apr->overall_score)
                        <div style="font-size:16px;font-weight:700;color:var(--accent);">
                            {{ number_format($apr->overall_score, 1) }}
                            <span style="font-size:11px;color:var(--text-muted);font-weight:400;">/5</span>
                        </div>
                        <div class="progress-track" style="width:80px;margin-top:3px;">
                            <div class="progress-fill"
                                 style="width:{{ ($apr->overall_score / 5) * 100 }}%;"></div>
                        </div>
                        @else
                        <span class="text-muted" style="font-size:12px;">Not scored</span>
                        @endif
                    </td>
                    <td>
                        @if($apr->overall_rating)
                        <span class="badge" style="background:{{ $rBadge['bg'] }};color:{{ $rBadge['color'] }};border:1px solid {{ $rBadge['border'] }};font-size:10px;">
                            {{ $rBadge['label'] }}
                        </span>
                        @else
                        <span class="text-muted">Pending</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge" style="background:{{ $sBadge['bg'] }};color:{{ $sBadge['color'] }};border:1px solid {{ $sBadge['border'] }};">
                            {{ ucfirst(str_replace('_', ' ', $apr->status)) }}
                        </span>
                    </td>
                    <td class="center">
                        <a href="{{ route('performance.appraisal', $apr) }}" class="action-btn" title="View/Score">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <i class="fa-solid fa-clipboard-list"></i>
                            No appraisals yet. Click "Generate Appraisals" above.
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>

            @php
                $completed = $appraisals->where('status', 'completed');
                $avgScore  = $completed->avg('overall_score');
            @endphp
            @if($completed->count() > 0)
            <tfoot>
                <tr style="background:var(--bg-muted);border-top:2px solid var(--accent-border);">
                    <td colspan="2" style="padding:10px 16px;font-size:11px;font-weight:700;color:var(--accent);">
                        COMPLETED: {{ $completed->count() }}
                    </td>
                    <td style="padding:10px 16px;font-size:14px;font-weight:700;color:var(--accent);">
                        {{ number_format($avgScore, 2) }}/5 avg
                    </td>
                    <td colspan="3"></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

{{-- GOALS PANE --}}
<div id="cpane-goals" style="display:none;">
    <div style="display:grid;grid-template-columns:1fr 380px;gap:18px;align-items:start;">

        {{-- Goals List --}}
        <div>
            @if($goals->isEmpty())
            <div class="card">
                <div class="empty-state">
                    <i class="fa-solid fa-bullseye"></i>
                    No goals assigned yet. Use the form to assign goals.
                </div>
            </div>
            @endif

            @foreach($goals->groupBy(fn($g) => $g->employee->full_name) as $empName => $empGoals)
            <div class="card card-flush" style="margin-bottom:12px;">
                <div style="padding:12px 18px;background:var(--bg-muted);border-bottom:1px solid var(--border);
                            display:flex;align-items:center;gap:10px;">
                    <img src="{{ $empGoals->first()->employee->avatar_url }}"
                         class="avatar avatar-sm">
                    <div style="font-size:13px;font-weight:700;color:var(--text-primary);">
                        {{ $empName }}
                    </div>
                    <span style="font-size:11px;color:var(--text-muted);">
                        {{ $empGoals->count() }} goal(s)
                    </span>
                </div>

                @foreach($empGoals as $goal)
                @php $gBadge = $goal->status_badge; @endphp
                <div style="padding:14px 18px;border-bottom:1px solid var(--border);">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:8px;">
                        <div style="flex:1;">
                            <div style="font-size:13px;font-weight:600;color:var(--text-primary);">
                                {{ $goal->title }}
                            </div>
                            <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">
                                {{ ucfirst($goal->category) }}
                                @if($goal->target_value) · Target: {{ $goal->target_value }} {{ $goal->unit }} @endif
                                @if($goal->due_date) · Due: {{ $goal->due_date->format('d M Y') }} @endif
                            </div>
                        </div>
                        <span class="badge" style="background:{{ $gBadge['bg'] }};color:{{ $gBadge['color'] }};border:1px solid {{ $gBadge['border'] }};font-size:10px;flex-shrink:0;margin-left:8px;">
                            {{ ucfirst(str_replace('_', ' ', $goal->status)) }}
                        </span>
                    </div>

                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
                        <div class="progress-track" style="flex:1;">
                            <div class="progress-fill"
                                 style="width:{{ $goal->progress }}%;
                                        background:{{ $goal->progress >= 80 ? 'var(--green)' : ($goal->progress >= 50 ? 'var(--yellow)' : 'var(--red)') }};">
                            </div>
                        </div>
                        <span style="font-size:12px;font-weight:700;color:var(--text-primary);min-width:36px;">
                            {{ $goal->progress }}%
                        </span>
                    </div>

                    <form method="POST" action="{{ route('performance.goals.update', $goal) }}"
                          style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                        @csrf @method('PATCH')
                        <input type="range" name="progress" min="0" max="100" step="5"
                               value="{{ $goal->progress }}"
                               style="flex:1;min-width:100px;accent-color:var(--accent);"
                               oninput="this.nextElementSibling.textContent = this.value + '%'">
                        <span style="font-size:11px;color:var(--text-muted);min-width:36px;">
                            {{ $goal->progress }}%
                        </span>
                        <select name="status" class="form-select" style="width:auto;">
                            @foreach(['not_started'=>'Not Started','in_progress'=>'In Progress','completed'=>'Completed','cancelled'=>'Cancelled'] as $v => $l)
                            <option value="{{ $v }}" {{ $goal->status === $v ? 'selected' : '' }}>{{ $l }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-primary btn-sm">Save</button>
                    </form>
                </div>
                @endforeach
            </div>
            @endforeach
        </div>

        {{-- Assign Goal Form --}}
        <div class="card" style="position:sticky;top:0;">
            <div class="form-section">
                <i class="fa-solid fa-bullseye"></i> Assign Goal
            </div>
            <form method="POST" action="{{ route('performance.goals.assign', $cycle) }}">
                @csrf
                <div style="display:flex;flex-direction:column;gap:11px;">
                    <div>
                        <label class="form-label">Employee <span style="color:var(--red);">*</span></label>
                        <select name="employee_id" required class="form-select">
                            <option value="">Select Employee</option>
                            @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">
                                {{ $emp->full_name }} — {{ $emp->department?->name ?? 'No dept' }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Goal Title <span style="color:var(--red);">*</span></label>
                        <input type="text" name="title" required
                               placeholder="e.g. Process 1000 shipments/month"
                               class="form-input">
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                        <div>
                            <label class="form-label">Category</label>
                            <select name="category" required class="form-select">
                                @foreach(['productivity'=>'Productivity','quality'=>'Quality','attendance'=>'Attendance','customer'=>'Customer','financial'=>'Financial','learning'=>'Learning','leadership'=>'Leadership','other'=>'Other'] as $v => $l)
                                <option value="{{ $v }}">{{ $l }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Weight (%)</label>
                            <input type="number" name="weight" value="10" min="1" max="100" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Target Value</label>
                            <input type="number" name="target_value" placeholder="100" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Unit</label>
                            <input type="text" name="unit" placeholder="shipments / %" class="form-input">
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Due Date</label>
                        <input type="date" name="due_date" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Description</label>
                        <textarea name="description" rows="2" class="form-textarea"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-plus"></i> Assign Goal
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>

@push('scripts')
<script>
function switchCycleTab(active) {
    ['appraisals', 'goals'].forEach(function(t) {
        document.getElementById('cpane-' + t).style.display = t === active ? 'block' : 'none';
        document.getElementById('ctab-' + t).classList.toggle('active', t === active);
    });
}
switchCycleTab('appraisals');
</script>
@endpush

@endsection