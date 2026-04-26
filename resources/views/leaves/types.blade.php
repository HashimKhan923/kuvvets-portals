@extends('layouts.app')
@section('title', 'Leave Types')
@section('page-title', 'Leave Types')
@section('breadcrumb', 'Leaves · Types')

@section('content')

<div style="display:grid;grid-template-columns:1fr 360px;gap:20px;align-items:start;">

    {{-- Leave Type Cards --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:14px;align-content:start;">
        @forelse($leaveTypes as $lt)
        <div class="card" style="border-left:3px solid {{ $lt->color }};transition:transform .2s;"
             onmouseover="this.style.transform='translateY(-2px)'"
             onmouseout="this.style.transform=''">

            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                <div>
                    <div style="font-size:14px;font-weight:700;color:var(--text-primary);">
                        {{ $lt->name }}
                    </div>
                    <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">
                        Code: <span style="color:var(--accent);">{{ $lt->code }}</span>
                    </div>
                </div>
                <div class="detail-block" style="text-align:center;min-width:60px;">
                    <div style="font-size:22px;font-weight:700;color:{{ $lt->color }};">
                        {{ $lt->days_per_year }}
                    </div>
                    <div class="detail-block-label" style="margin-bottom:0;">days/yr</div>
                </div>
            </div>

            <div style="display:flex;flex-wrap:wrap;gap:5px;margin-bottom:10px;">
                @if($lt->is_paid)
                    <span class="badge badge-green" style="font-size:10px;">Paid</span>
                @else
                    <span class="badge badge-red" style="font-size:10px;">Unpaid</span>
                @endif
                @if($lt->requires_document)
                    <span class="badge badge-blue" style="font-size:10px;">Doc Required</span>
                @endif
                @if($lt->can_carry_forward)
                    <span class="badge badge-purple" style="font-size:10px;">Carry Forward</span>
                @endif
                @if($lt->applicable_to_male)
                    <span class="badge badge-accent" style="font-size:10px;">Male</span>
                @endif
                @if($lt->applicable_to_female)
                    <span class="badge badge-pink" style="font-size:10px;">Female</span>
                @endif
            </div>

            @if($lt->description)
            <div style="font-size:11px;color:var(--text-muted);line-height:1.5;margin-bottom:8px;">
                {{ $lt->description }}
            </div>
            @endif

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:4px;font-size:11px;color:var(--text-muted);">
                @if($lt->min_days_notice)
                <div>
                    <i class="fa-solid fa-clock" style="font-size:9px;color:var(--accent);margin-right:4px;"></i>
                    {{ $lt->min_days_notice }}d notice
                </div>
                @endif
                @if($lt->max_consecutive_days)
                <div>
                    <i class="fa-solid fa-calendar" style="font-size:9px;color:var(--accent);margin-right:4px;"></i>
                    Max {{ $lt->max_consecutive_days }}d at once
                </div>
                @endif
                @if($lt->can_carry_forward && $lt->max_carry_forward_days)
                <div>
                    <i class="fa-solid fa-forward" style="font-size:9px;color:var(--accent);margin-right:4px;"></i>
                    Carry {{ $lt->max_carry_forward_days }}d max
                </div>
                @endif
            </div>

        </div>
        @empty
        <div class="card" style="grid-column:1/-1;">
            <div class="empty-state">
                <i class="fa-solid fa-tags"></i>
                No leave types yet.
            </div>
        </div>
        @endforelse
    </div>

    {{-- Create Form --}}
    <div class="card" style="position:sticky;top:0;">
        <div class="form-section">
            <i class="fa-solid fa-plus-circle"></i> New Leave Type
        </div>
        <form method="POST" action="{{ route('leaves.types.store') }}">
            @csrf
            <div style="display:flex;flex-direction:column;gap:11px;">

                <div>
                    <label class="form-label">Name <span style="color:var(--red);">*</span></label>
                    <input type="text" name="name" required
                           placeholder="e.g. Annual Leave" class="form-input">
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                    <div>
                        <label class="form-label">Code <span style="color:var(--red);">*</span></label>
                        <input type="text" name="code" required
                               placeholder="AL" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Days/Year <span style="color:var(--red);">*</span></label>
                        <input type="number" name="days_per_year" required
                               min="0" value="15" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Min Notice (days)</label>
                        <input type="number" name="min_days_notice"
                               min="0" value="1" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Max Consecutive</label>
                        <input type="number" name="max_consecutive_days"
                               min="1" placeholder="No limit" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Carry Forward (days)</label>
                        <input type="number" name="max_carry_forward_days"
                               min="0" value="0" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Colour</label>
                        <input type="color" name="color" value="#C2531B"
                               style="width:100%;height:37px;background:var(--bg-input);
                                      border:1px solid var(--border-strong);border-radius:8px;
                                      padding:3px;cursor:pointer;outline:none;">
                    </div>
                </div>

                <div style="display:flex;flex-direction:column;gap:7px;padding:10px;
                            background:var(--bg-muted);border-radius:8px;">
                    @foreach([
                        ['is_paid',              '1', 'Paid Leave',            true],
                        ['requires_document',    '1', 'Requires Document',     false],
                        ['can_carry_forward',    '1', 'Carry Forward Enabled', false],
                        ['applicable_to_male',   '1', 'Applicable to Male',    true],
                        ['applicable_to_female', '1', 'Applicable to Female',  true],
                    ] as [$name, $val, $label, $checked])
                    <label style="display:flex;align-items:center;gap:7px;font-size:12px;
                                  color:var(--text-secondary);cursor:pointer;">
                        <input type="checkbox" name="{{ $name }}" value="{{ $val }}"
                               {{ $checked ? 'checked' : '' }}
                               style="accent-color:var(--accent);">
                        {{ $label }}
                    </label>
                    @endforeach
                </div>

                <div>
                    <label class="form-label">Description</label>
                    <textarea name="description" rows="2" class="form-textarea"></textarea>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-plus"></i> Create Leave Type
                </button>

            </div>
        </form>
    </div>

</div>
@endsection