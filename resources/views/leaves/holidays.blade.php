@extends('layouts.app')
@section('title', 'Public Holidays')
@section('page-title', 'Public Holidays')
@section('breadcrumb', 'Leaves · Holidays · ' . $year)

@section('content')

<div style="display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:start;">

    {{-- Holidays List --}}
    <div>
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
            <div style="font-size:13px;font-weight:600;color:var(--text-primary);">
                <i class="fa-solid fa-star" style="color:var(--accent);margin-right:7px;"></i>
                {{ $year }} Holidays — {{ $holidays->count() }} total
                ({{ $holidays->sum('days_count') }} days)
            </div>
            <form method="GET" action="{{ route('leaves.holidays') }}" style="display:flex;gap:8px;">
                <select name="year" class="form-select" style="width:auto;">
                    @for($y = now()->year + 1; $y >= 2022; $y--)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
                <button type="submit" class="btn btn-primary btn-sm">Go</button>
            </form>
        </div>

        <div style="display:flex;flex-direction:column;gap:8px;">
            @forelse($holidays as $holiday)
            @php $badge = $holiday->type_badge; @endphp
            <div class="card card-sm" style="display:flex;align-items:center;justify-content:space-between;
                         transition:border-color .2s;"
                 onmouseover="this.style.borderColor='var(--accent-border)'"
                 onmouseout="this.style.borderColor='var(--border)'">
                <div style="display:flex;align-items:center;gap:14px;">
                    <div style="text-align:center;min-width:46px;background:var(--accent-bg);
                                border-radius:8px;padding:8px;flex-shrink:0;">
                        <div style="font-size:18px;font-weight:700;color:var(--accent);line-height:1;">
                            {{ $holiday->date->format('d') }}
                        </div>
                        <div style="font-size:10px;color:var(--text-muted);">
                            {{ $holiday->date->format('M Y') }}
                        </div>
                    </div>
                    <div>
                        <div style="font-size:14px;font-weight:600;color:var(--text-primary);">
                            {{ $holiday->name }}
                        </div>
                        <div style="display:flex;align-items:center;gap:8px;margin-top:4px;flex-wrap:wrap;">
                            <span class="badge" style="background:{{ $badge['bg'] }};color:{{ $badge['color'] }};border:1px solid {{ $badge['border'] }};font-size:10px;">
                                {{ ucfirst($holiday->type) }}
                            </span>
                            @if($holiday->days_count > 1)
                            <span style="font-size:11px;color:var(--accent);">
                                {{ $holiday->days_count }} days
                            </span>
                            @endif
                            <span style="font-size:11px;color:var(--text-muted);">
                                {{ $holiday->date->format('l') }}
                                @if($holiday->date_to)
                                    – {{ $holiday->date_to->format('l, d M') }}
                                @endif
                            </span>
                        </div>
                        @if($holiday->description)
                        <div style="font-size:11px;color:var(--text-muted);margin-top:3px;">
                            {{ $holiday->description }}
                        </div>
                        @endif
                    </div>
                </div>
                <form method="POST" action="{{ route('leaves.holidays.destroy', $holiday) }}"
                      onsubmit="return confirm('Remove this holiday?')"
                      style="flex-shrink:0;margin-left:12px;">
                    @csrf @method('DELETE')
                    <button type="submit" class="action-btn danger" title="Delete">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </form>
            </div>
            @empty
            <div class="card">
                <div class="empty-state">
                    <i class="fa-solid fa-calendar-xmark"></i>
                    No holidays defined for {{ $year }}.
                </div>
            </div>
            @endforelse
        </div>
    </div>

    {{-- Add Holiday Form --}}
    <div class="card" style="position:sticky;top:0;">
        <div class="form-section">
            <i class="fa-solid fa-plus-circle"></i> Add Holiday
        </div>
        <form method="POST" action="{{ route('leaves.holidays.store') }}">
            @csrf
            <input type="hidden" name="year" value="{{ $year }}">
            <div style="display:flex;flex-direction:column;gap:11px;">

                <div>
                    <label class="form-label">Holiday Name <span style="color:var(--red);">*</span></label>
                    <input type="text" name="name" required
                           placeholder="e.g. Eid ul Fitr" class="form-input">
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                    <div>
                        <label class="form-label">From Date <span style="color:var(--red);">*</span></label>
                        <input type="date" name="date" required class="form-input">
                    </div>
                    <div>
                        <label class="form-label">To Date <span style="color:var(--text-muted);">multi-day</span></label>
                        <input type="date" name="date_to" class="form-input">
                    </div>
                </div>

                <div>
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select">
                        <option value="national">National</option>
                        <option value="religious">Religious</option>
                        <option value="company">Company</option>
                        <option value="optional">Optional</option>
                    </select>
                </div>

                <div>
                    <label class="form-label">Description</label>
                    <input type="text" name="description"
                           placeholder="Optional note…" class="form-input">
                </div>

                <label style="display:flex;align-items:center;gap:7px;font-size:12px;
                               color:var(--text-secondary);cursor:pointer;">
                    <input type="checkbox" name="is_recurring" value="1" checked
                           style="accent-color:var(--accent);">
                    Recurring annually
                </label>

                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-plus"></i> Add Holiday
                </button>

            </div>
        </form>

        {{-- Pakistan Quick-Add --}}
        <div style="margin-top:16px;padding-top:14px;border-top:1px solid var(--border);">
            <div class="section-label">Pakistan Standard Holidays</div>
            @foreach([
                ['Pakistan Day',    $year . '-03-23', '23 Mar'],
                ['Independence Day',$year . '-08-14', '14 Aug'],
                ['Quaid-e-Azam Day',$year . '-12-25', '25 Dec'],
            ] as [$name, $date, $label])
            <form method="POST" action="{{ route('leaves.holidays.store') }}"
                  style="margin-bottom:5px;">
                @csrf
                <input type="hidden" name="year"         value="{{ $year }}">
                <input type="hidden" name="name"         value="{{ $name }}">
                <input type="hidden" name="date"         value="{{ $date }}">
                <input type="hidden" name="type"         value="national">
                <input type="hidden" name="is_recurring" value="1">
                <button type="submit"
                        style="width:100%;padding:7px 10px;background:var(--bg-muted);
                               border:1px solid var(--border);border-radius:6px;
                               color:var(--text-secondary);font-size:11px;cursor:pointer;
                               text-align:left;transition:border-color .15s;"
                        onmouseover="this.style.borderColor='var(--accent-border)'"
                        onmouseout="this.style.borderColor='var(--border)'">
                    <i class="fa-solid fa-plus" style="font-size:9px;color:var(--accent);margin-right:6px;"></i>
                    {{ $name }} ({{ $label }})
                </button>
            </form>
            @endforeach
        </div>

    </div>

</div>
@endsection