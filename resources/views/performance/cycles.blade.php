@extends('layouts.app')
@section('title', 'Performance Cycles')
@section('page-title', 'Performance Cycles')
@section('breadcrumb', 'Performance · Cycles')

@section('content')

<div style="display:grid;grid-template-columns:1fr 380px;gap:20px;align-items:start;">

    {{-- Cycles List --}}
    <div style="display:flex;flex-direction:column;gap:12px;">
        @forelse($cycles as $cycle)
        @php $badge = $cycle->status_badge; @endphp
        <div class="card" style="transition:border-color .2s;"
             onmouseover="this.style.borderColor='var(--accent-border)'"
             onmouseout="this.style.borderColor='var(--border)'">

            <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:12px;">
                <div>
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:5px;flex-wrap:wrap;">
                        <span style="font-size:15px;font-weight:700;color:var(--text-primary);">
                            {{ $cycle->name }}
                        </span>
                        <span class="badge" style="background:{{ $badge['bg'] }};color:{{ $badge['color'] }};border:1px solid {{ $badge['border'] }};">
                            {{ ucfirst($cycle->status) }}
                        </span>
                        <span class="badge badge-accent">
                            {{ ucfirst(str_replace('_', ' ', $cycle->type)) }}
                        </span>
                    </div>
                    <div style="font-size:12px;color:var(--text-muted);">{{ $cycle->duration }}</div>
                    @if($cycle->description)
                    <div style="font-size:12px;color:var(--text-secondary);margin-top:4px;">
                        {{ $cycle->description }}
                    </div>
                    @endif
                </div>
                <div style="display:flex;gap:8px;align-items:center;flex-shrink:0;margin-left:12px;">
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
                    <a href="{{ route('performance.cycle', $cycle) }}" class="btn btn-secondary btn-sm">
                        Open →
                    </a>
                </div>
            </div>

            <div style="display:flex;gap:20px;flex-wrap:wrap;padding-top:12px;
                        border-top:1px solid var(--border);">
                @foreach([
                    ['fa-clipboard-list', 'Appraisals', $cycle->appraisals_count],
                    ['fa-bullseye',       'Goals',      $cycle->goals_count],
                    ['fa-calendar',       'Review',
                        $cycle->review_start_date
                            ? $cycle->review_start_date->format('d M') . ' – ' . $cycle->review_end_date?->format('d M Y')
                            : 'Not set'
                    ],
                ] as [$icon, $label, $value])
                <div style="display:flex;align-items:center;gap:6px;font-size:12px;color:var(--text-secondary);">
                    <i class="fa-solid {{ $icon }}" style="font-size:11px;color:var(--accent);width:14px;text-align:center;"></i>
                    <span style="color:var(--text-muted);">{{ $label }}:</span>
                    <span style="font-weight:600;color:var(--text-primary);">{{ $value }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @empty
        <div class="card">
            <div class="empty-state">
                <i class="fa-solid fa-rotate"></i>
                No performance cycles yet.
            </div>
        </div>
        @endforelse
    </div>

    {{-- Create Cycle Form --}}
    <div class="card" style="position:sticky;top:0;">
        <div class="form-section">
            <i class="fa-solid fa-plus-circle"></i> New Performance Cycle
        </div>
        <form method="POST" action="{{ route('performance.cycles.store') }}">
            @csrf
            <div style="display:flex;flex-direction:column;gap:13px;">
                <div>
                    <label class="form-label">Cycle Name <span style="color:var(--red);">*</span></label>
                    <input type="text" name="name" required
                           placeholder="e.g. Annual Review 2025" class="form-input">
                </div>
                <div>
                    <label class="form-label">Type <span style="color:var(--red);">*</span></label>
                    <select name="type" required class="form-select">
                        @foreach(['annual'=>'Annual Review','semi_annual'=>'Semi-Annual','quarterly'=>'Quarterly','monthly'=>'Monthly','probation'=>'Probation Review'] as $v => $l)
                        <option value="{{ $v }}">{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                    <div>
                        <label class="form-label">Start Date <span style="color:var(--red);">*</span></label>
                        <input type="date" name="start_date" required class="form-input">
                    </div>
                    <div>
                        <label class="form-label">End Date <span style="color:var(--red);">*</span></label>
                        <input type="date" name="end_date" required class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Review Start</label>
                        <input type="date" name="review_start_date" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Review End</label>
                        <input type="date" name="review_end_date" class="form-input">
                    </div>
                </div>
                <div>
                    <label class="form-label">Description</label>
                    <textarea name="description" rows="2"
                              placeholder="Brief description…"
                              class="form-textarea"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-plus"></i> Create Cycle
                </button>
            </div>
        </form>
    </div>

</div>
@endsection