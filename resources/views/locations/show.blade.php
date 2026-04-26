@extends('layouts.app')
@section('title', $location->name)

@section('content')
<div class="page-wrapper" x-data="{ assignOpen: false }">

    {{-- Header --}}
    <div class="page-head">
        <div style="flex:1;min-width:0;">
            <a href="{{ route('locations.index') }}" style="font-size:12px;color:var(--muted);text-decoration:none;">
                <i class="fa-solid fa-arrow-left"></i> Back to Locations
            </a>
            <div style="display:flex;align-items:center;gap:12px;margin-top:8px;flex-wrap:wrap;">
                <div class="type-ico-big" style="background: {{ match($location->type) {
                    'warehouse' => 'rgba(37,99,235,.12);color:#2563EB',
                    'office' => 'rgba(245,158,11,.12);color:#F59E0B',
                    'site' => 'rgba(194,83,27,.12);color:#C2531B',
                    'branch' => 'rgba(124,58,237,.12);color:#7C3AED',
                    default => 'rgba(107,83,71,.12);color:#6B5347',
                } }};">
                    <i class="fa-solid {{ $location->typeIcon() }}"></i>
                </div>
                <div>
                    <h1 class="page-title">{{ $location->name }}</h1>
                    <p class="page-sub">
                        <span style="font-family:'Space Grotesk',monospace;">{{ $location->code }}</span> ·
                        {{ $location->typeLabel() }}
                        @if(!$location->is_active)
                            · <span style="color:#DC2626;font-weight:700;">Inactive</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            @can('locations.manage')
                <a href="{{ route('locations.edit', $location) }}" class="btn btn-secondary">
                    <i class="fa-solid fa-pen"></i> Edit
                </a>
                <form method="POST" action="{{ route('locations.rotate-qr', $location) }}"
                      onsubmit="return confirm('Regenerate QR code? Old printed QR codes will stop working.')" style="display:inline;">
                    @csrf
                    <button class="btn btn-secondary">
                        <i class="fa-solid fa-rotate"></i> Rotate QR
                    </button>
                </form>
            @endcan
            @can('locations.delete')
                <form method="POST" action="{{ route('locations.destroy', $location) }}"
                      onsubmit="return confirm('Delete this location? This cannot be undone.')" style="display:inline;">
                    @csrf @method('DELETE')
                    <button class="btn btn-danger">
                        <i class="fa-solid fa-trash"></i> Delete
                    </button>
                </form>
            @endcan
        </div>
    </div>

    <div class="show-grid">

        {{-- LEFT: Details --}}
        <div>
            <div class="info-card">
                <div class="info-card-hd">
                    <div class="info-card-title"><i class="fa-solid fa-circle-info"></i>Details</div>
                </div>
                <div class="info-grid">
                    <div class="info-row">
                        <div class="info-lbl">Address</div>
                        <div class="info-val">{{ $location->address ?: '—' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-lbl">City</div>
                        <div class="info-val">{{ $location->city ?? '—' }}@if($location->province), {{ $location->province }}@endif</div>
                    </div>
                    <div class="info-row">
                        <div class="info-lbl">Latitude</div>
                        <div class="info-val" style="font-family:'Space Grotesk',monospace;">{{ number_format((float)$location->latitude, 7) }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-lbl">Longitude</div>
                        <div class="info-val" style="font-family:'Space Grotesk',monospace;">{{ number_format((float)$location->longitude, 7) }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-lbl">Geofence radius</div>
                        <div class="info-val">{{ $location->radius_meters }} meters</div>
                    </div>
                    <div class="info-row">
                        <div class="info-lbl">Status</div>
                        <div class="info-val">
                            @if($location->is_active)
                                <span class="badge badge-green"><i class="fa-solid fa-circle-check"></i> Active</span>
                            @else
                                <span class="badge badge-gray"><i class="fa-solid fa-pause"></i> Inactive</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div style="padding:14px;border-top:1px solid var(--border);display:flex;gap:8px;flex-wrap:wrap;">
                    <a href="https://www.google.com/maps?q={{ $location->latitude }},{{ $location->longitude }}" target="_blank" rel="noopener" class="btn btn-secondary" style="flex:1;">
                        <i class="fa-solid fa-map"></i> Open in Google Maps
                    </a>
                    <a href="https://www.google.com/maps/dir/?api=1&destination={{ $location->latitude }},{{ $location->longitude }}" target="_blank" rel="noopener" class="btn btn-secondary" style="flex:1;">
                        <i class="fa-solid fa-route"></i> Get Directions
                    </a>
                </div>
            </div>

            @if($location->notes)
                <div class="info-card">
                    <div class="info-card-hd">
                        <div class="info-card-title"><i class="fa-solid fa-note-sticky"></i>Notes</div>
                    </div>
                    <div style="padding: 14px 18px; font-size: 13px; line-height: 1.6; color: var(--text); white-space: pre-wrap;">{{ $location->notes }}</div>
                </div>
            @endif

            {{-- Assigned employees --}}
            <div class="info-card">
                <div class="info-card-hd">
                    <div>
                        <div class="info-card-title"><i class="fa-solid fa-users"></i>Assigned Employees</div>
                        <div class="info-card-sub">{{ $location->employees->count() }} employee(s) can check in here</div>
                    </div>
                    @can('locations.assign')
                        <button class="btn btn-primary" style="padding:8px 14px;font-size:12px;" @click="assignOpen = true">
                            <i class="fa-solid fa-user-plus"></i> Assign
                        </button>
                    @endcan
                </div>
                @if($location->employees->count())
                    <div style="padding: 6px 0;">
                        @foreach($location->employees as $emp)
                            <div style="display:flex;align-items:center;gap:12px;padding:10px 18px;border-bottom:1px solid var(--border);">
                                <img src="{{ $emp->avatar ? asset('storage/'.$emp->avatar) : 'https://ui-avatars.com/api/?name='.urlencode($emp->full_name).'&background=C2531B&color=fff' }}" style="width:36px;height:36px;border-radius:50%;object-fit:cover;">
                                <div style="flex:1;min-width:0;">
                                    <div style="font-size:13px;font-weight:700;">{{ $emp->full_name }} @if($emp->pivot->is_primary)<span style="color:#F59E0B;" title="Primary location">★</span>@endif</div>
                                    <div style="font-size:11px;color:var(--muted);">{{ $emp->employee_id }} · {{ $emp->designation?->name ?? '—' }}</div>
                                </div>
                                @can('locations.assign')
                                    <form method="POST" action="{{ route('locations.unassign', [$location, $emp]) }}" onsubmit="return confirm('Unassign {{ $emp->first_name }} from this location?')" style="display:inline;">
                                        @csrf @method('DELETE')
                                        <button style="background:none;border:none;color:#DC2626;cursor:pointer;padding:6px;" title="Unassign">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        @endforeach
                    </div>
                @else
                    <div style="text-align:center;padding:30px 20px;color:var(--muted);font-size:13px;">
                        <i class="fa-solid fa-user-plus" style="font-size:28px;opacity:.3;display:block;margin-bottom:10px;"></i>
                        No employees assigned yet.
                    </div>
                @endif
            </div>
        </div>

        {{-- RIGHT: QR --}}
        <div>
            <div class="info-card qr-card">
                <div class="info-card-hd">
                    <div class="info-card-title"><i class="fa-solid fa-qrcode"></i>Check-in QR Code</div>
                </div>
                <div style="padding: 24px; text-align: center;">
                    <div class="qr-wrap">
                        <img src="{{ route('locations.qr', $location) }}" alt="QR Code" style="width:100%;max-width:260px;display:block;margin:0 auto;">
                    </div>
                    <div style="font-size:11px;color:var(--muted);margin-top:14px;line-height:1.6;">
                        Employees scan this code at <strong style="color:var(--text);">{{ $location->name }}</strong> to check in/out.
                        @if($location->qr_rotated_at)
                            <br>Last rotated: {{ $location->qr_rotated_at->diffForHumans() }}
                        @endif
                    </div>
                    <div style="display:flex;gap:8px;margin-top:16px;">
                        <a href="{{ route('locations.print', $location) }}" target="_blank" class="btn btn-primary" style="flex:1;">
                            <i class="fa-solid fa-print"></i> Print QR
                        </a>
                        <a href="{{ route('locations.qr', $location) }}" download="qr-{{ $location->code }}.svg" class="btn btn-secondary" style="flex:1;">
                            <i class="fa-solid fa-download"></i> Download
                        </a>
                    </div>
                </div>
            </div>

            {{-- Recent activity --}}
            @if($recentAttendance->count())
                <div class="info-card">
                    <div class="info-card-hd">
                        <div class="info-card-title"><i class="fa-solid fa-clock-rotate-left"></i>Recent Activity</div>
                    </div>
                    <div style="padding: 6px 0;">
                        @foreach($recentAttendance as $att)
                            <div style="display:flex;align-items:center;gap:10px;padding:10px 18px;border-bottom:1px solid var(--border);font-size:12px;">
                                <div style="flex:1;min-width:0;">
                                    <div style="font-weight:700;">{{ $att->employee->full_name }}</div>
                                    <div style="color:var(--muted);font-size:11px;">
                                        {{ $att->date->format('M j') }} · {{ $att->check_in?->format('h:i A') }}
                                        @if($att->check_out) → {{ $att->check_out->format('h:i A') }} @endif
                                    </div>
                                </div>
                                @if($att->check_in_method === 'qr' || $att->check_in_method === 'qr+gps')
                                    <span class="badge badge-accent" style="font-size:9px;"><i class="fa-solid fa-qrcode"></i></span>
                                @else
                                    <span class="badge badge-gray" style="font-size:9px;"><i class="fa-solid fa-location-crosshairs"></i></span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- ═══ Assign employees modal ═══ --}}
@can('locations.assign')
    <template x-teleport="body">
        <div x-show="assignOpen" style="display:none;" x-cloak>
            <div class="kvt-assign-overlay" @click.self="assignOpen = false">
                <div class="kvt-assign-modal">

                    {{-- Header --}}
                    <div class="kvt-modal-hd">
                        <div class="kvt-modal-title">
                            <i class="fa-solid fa-user-plus"></i>
                            Assign Employees to {{ $location->name }}
                        </div>
                        <button type="button" class="kvt-modal-close" @click="assignOpen = false">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('locations.assign', $location) }}" x-data="{ search: '', selected: [] }" style="display:flex;flex-direction:column;flex:1;min-height:0;">
                        @csrf

                        {{-- Search --}}
                        <div class="kvt-modal-search">
                            <label class="kvt-search-label">SEARCH EMPLOYEES</label>
                            <div class="kvt-search-input-wrap">
                                <i class="fa-solid fa-search kvt-search-icon"></i>
                                <input type="text" x-model="search" placeholder="Type name or ID...">
                            </div>
                        </div>

                        {{-- Employee list --}}
                        @php
                            $currentlyAssigned = $location->employees->pluck('id')->all();
                            $allEmployees = \App\Models\Employee::where('company_id', auth()->user()->company_id)
                                ->where('employment_status','active')
                                ->orderBy('first_name')
                                ->get();
                        @endphp

                        <div class="kvt-emp-list">
                            @forelse($allEmployees as $emp)
                                @php $isAssigned = in_array($emp->id, $currentlyAssigned); @endphp
                                <label class="kvt-emp-row {{ $isAssigned ? 'is-assigned' : '' }}"
                                       x-show="!search || '{{ strtolower($emp->full_name . ' ' . $emp->employee_id) }}'.includes(search.toLowerCase())">
                                    <input type="checkbox" name="employee_ids[]" value="{{ $emp->id }}" {{ $isAssigned ? 'disabled' : '' }} x-model="selected">
                                    <img src="{{ $emp->avatar ? asset('storage/'.$emp->avatar) : 'https://ui-avatars.com/api/?name='.urlencode($emp->full_name).'&background=C2531B&color=fff' }}" alt="" class="kvt-emp-avatar">
                                    <div class="kvt-emp-info">
                                        <div class="kvt-emp-name">{{ $emp->full_name }}</div>
                                        <div class="kvt-emp-meta">{{ $emp->employee_id }} · {{ $emp->department?->name ?? 'No department' }}</div>
                                    </div>
                                    @if($isAssigned)
                                        <span class="kvt-assigned-badge">
                                            <i class="fa-solid fa-check"></i> Assigned
                                        </span>
                                    @endif
                                </label>
                            @empty
                                <div style="padding:30px;text-align:center;color:#A89080;font-size:13px;">
                                    No active employees found.
                                </div>
                            @endforelse
                        </div>

                        {{-- Primary toggle --}}
                        <div class="kvt-primary-row">
                            <label>
                                <input type="checkbox" name="is_primary" value="1">
                                <span>Make this their <strong>primary</strong> location <span style="color:#A89080;">(unsets other primary assignments)</span></span>
                            </label>
                        </div>

                        {{-- Footer --}}
                        <div class="kvt-modal-ftr">
                            <button type="button" class="kvt-btn kvt-btn-secondary" @click="assignOpen = false">Cancel</button>
                            <button type="submit" class="kvt-btn kvt-btn-primary" :disabled="selected.length === 0">
                                <i class="fa-solid fa-check"></i>
                                Assign <span x-show="selected.length > 0" x-text="`(${selected.length})`"></span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </template>
    @endcan
</div>

<style>
    .page-wrapper { padding: 22px 28px; }
    .page-head { display: flex; align-items: start; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 22px; }
    .page-title { font-family: 'Space Grotesk', sans-serif; font-size: 22px; font-weight: 700; color: var(--text); line-height: 1.2; }
    .page-sub { font-size: 12.5px; color: var(--muted); margin-top: 3px; }

    .type-ico-big { width: 54px; height: 54px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0; }

    .show-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
    @media (max-width: 960px) { .show-grid { grid-template-columns: 1fr; } }

    .info-card { background: var(--card); border: 1px solid var(--border); border-radius: 14px; margin-bottom: 14px; overflow: hidden; }
    .info-card-hd { padding: 14px 18px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; gap: 10px; }
    .info-card-title { font-family: 'Space Grotesk', sans-serif; font-size: 13px; font-weight: 700; }
    .info-card-title i { margin-right: 6px; color: var(--accent, #C2531B); }
    .info-card-sub { font-size: 11px; color: var(--muted); margin-top: 2px; }

    .info-grid { display: grid; grid-template-columns: 1fr 1fr; }
    .info-row { padding: 12px 18px; border-bottom: 1px solid var(--border); border-right: 1px solid var(--border); }
    .info-grid > .info-row:nth-child(2n) { border-right: none; }
    .info-lbl { font-size: 10px; color: var(--muted); letter-spacing: .6px; text-transform: uppercase; font-weight: 600; }
    .info-val { font-size: 13px; font-weight: 600; color: var(--text); margin-top: 3px; }

    .qr-card .qr-wrap { padding: 16px; background: #fff; border-radius: 14px; border: 2px dashed var(--border); }

    .modal-overlay { position: fixed; inset: 0; z-index: 100; background: rgba(15,10,5,.6); backdrop-filter: blur(5px); display: flex; align-items: center; justify-content: center; padding: 16px; animation: fadeIn .2s ease; }
    @keyframes fadeIn { from{opacity:0} to{opacity:1} }
    .modal-box { background: var(--card); border-radius: 20px; width: 100%; max-height: 90vh; overflow: hidden; display: flex; flex-direction: column; animation: slideUp .25s cubic-bezier(.22,.61,.36,1); }
    @keyframes slideUp { from{opacity:0;transform:translateY(30px) scale(.96)} to{opacity:1;transform:translateY(0) scale(1)} }
    .modal-hd { padding: 16px 22px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
    .modal-title { font-family:'Space Grotesk',sans-serif; font-size: 15px; font-weight: 700; }
    .modal-close { width: 32px; height: 32px; border-radius: 10px; background: #F5F0EB; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; }


    /* ═══════════════════════════════════════════════════
       ASSIGN MODAL — scoped, isolated styles
    ═══════════════════════════════════════════════════ */
    [x-cloak] { display: none !important; }

    .kvt-assign-overlay {
        position: fixed;
        inset: 0;
        z-index: 9999;
        background: rgba(15, 10, 5, 0.65);
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        animation: kvtFadeIn 0.2s ease;
    }
    @keyframes kvtFadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .kvt-assign-modal {
        background: #FFFFFF !important;
        border-radius: 18px;
        width: 100%;
        max-width: 580px;
        max-height: 88vh;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        box-shadow: 0 24px 60px rgba(0, 0, 0, 0.35);
        animation: kvtSlideUp 0.25s cubic-bezier(0.22, 0.61, 0.36, 1);
        font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
        color: #2D1F14;
    }
    @keyframes kvtSlideUp {
        from { opacity: 0; transform: translateY(30px) scale(0.96); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }

    /* Header */
    .kvt-modal-hd {
        padding: 18px 22px;
        border-bottom: 1px solid #F0EAE2;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        background: #FFFFFF;
        flex-shrink: 0;
    }
    .kvt-modal-title {
        font-family: 'Space Grotesk', system-ui, sans-serif;
        font-size: 16px;
        font-weight: 700;
        color: #2D1F14;
        display: flex;
        align-items: center;
        gap: 10px;
        line-height: 1.2;
    }
    .kvt-modal-title i {
        color: #C2531B;
        font-size: 16px;
    }
    .kvt-modal-close {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        background: #F5F0EB;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6B5347;
        font-size: 14px;
        flex-shrink: 0;
        transition: all 0.15s;
    }
    .kvt-modal-close:hover {
        background: #FEF2F2;
        color: #DC2626;
    }

    /* Search */
    .kvt-modal-search {
        padding: 18px 22px 12px;
        background: #FFFFFF;
    }
    .kvt-search-label {
        display: block;
        font-size: 11px;
        font-weight: 700;
        color: #6B5347;
        letter-spacing: 0.6px;
        text-transform: uppercase;
        margin-bottom: 8px;
    }
    .kvt-search-input-wrap {
        position: relative;
    }
    .kvt-search-icon {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #A89080;
        font-size: 13px;
        pointer-events: none;
    }
    .kvt-search-input-wrap input {
        width: 100%;
        height: 44px;
        padding: 0 14px 0 40px;
        background: #F7F3EF;
        border: 1.5px solid transparent;
        border-radius: 10px;
        font-family: inherit;
        font-size: 13.5px;
        color: #2D1F14;
        transition: all 0.15s;
        box-sizing: border-box;
    }
    .kvt-search-input-wrap input:focus {
        outline: none;
        border-color: #C2531B;
        background: #FFFFFF;
        box-shadow: 0 0 0 3px rgba(194, 83, 27, 0.08);
    }

    /* Employee list */
    .kvt-emp-list {
        flex: 1;
        overflow-y: auto;
        margin: 0 22px;
        border: 1px solid #F0EAE2;
        border-radius: 12px;
        background: #FFFFFF;
        min-height: 240px;
        max-height: 340px;
    }

    .kvt-emp-row {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 14px;
        border-bottom: 1px solid #F5F0EB;
        cursor: pointer;
        transition: background 0.12s;
        background: #FFFFFF;
    }
    .kvt-emp-row:last-child { border-bottom: none; }
    .kvt-emp-row:hover:not(.is-assigned) {
        background: #FEF2EC;
    }
    .kvt-emp-row.is-assigned {
        opacity: 0.55;
        cursor: not-allowed;
        background: #FAFAFA;
    }
    .kvt-emp-row input[type="checkbox"] {
        width: 18px;
        height: 18px;
        accent-color: #C2531B;
        cursor: pointer;
        margin: 0;
        flex-shrink: 0;
    }
    .kvt-emp-row.is-assigned input[type="checkbox"] {
        cursor: not-allowed;
    }

    .kvt-emp-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        object-fit: cover;
        flex-shrink: 0;
        border: 1.5px solid #F0EAE2;
    }
    .kvt-emp-info {
        flex: 1;
        min-width: 0;
    }
    .kvt-emp-name {
        font-size: 13.5px;
        font-weight: 700;
        color: #2D1F14;
        line-height: 1.3;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .kvt-emp-meta {
        font-size: 11px;
        color: #A89080;
        margin-top: 2px;
        line-height: 1.3;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .kvt-assigned-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        border-radius: 999px;
        background: #DCFCE7;
        color: #16A34A;
        font-size: 10.5px;
        font-weight: 700;
        white-space: nowrap;
        flex-shrink: 0;
        border: 1px solid #BBF7D0;
    }
    .kvt-assigned-badge i {
        font-size: 9px;
    }

    /* Primary toggle */
    .kvt-primary-row {
        margin: 14px 22px 0;
        padding: 12px 14px;
        background: #FEF2EC;
        border: 1px solid #F5D5C0;
        border-radius: 10px;
    }
    .kvt-primary-row label {
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        font-size: 12.5px;
        color: #2D1F14;
        line-height: 1.4;
    }
    .kvt-primary-row input {
        width: 16px;
        height: 16px;
        accent-color: #C2531B;
        cursor: pointer;
        flex-shrink: 0;
    }
    .kvt-primary-row strong {
        font-weight: 700;
        color: #C2531B;
    }

    /* Footer */
    .kvt-modal-ftr {
        padding: 16px 22px;
        margin-top: 16px;
        border-top: 1px solid #F0EAE2;
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        background: #FFFFFF;
        flex-shrink: 0;
    }
    .kvt-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 10px 20px;
        border-radius: 10px;
        border: none;
        cursor: pointer;
        font-family: inherit;
        font-size: 13px;
        font-weight: 600;
        transition: all 0.15s;
    }
    .kvt-btn-secondary {
        background: #F5F0EB;
        color: #2D1F14;
    }
    .kvt-btn-secondary:hover {
        background: #E8DDD5;
    }
    .kvt-btn-primary {
        background: linear-gradient(135deg, #C2531B, #E87A45);
        color: #FFFFFF;
        box-shadow: 0 4px 12px rgba(194, 83, 27, 0.25);
    }
    .kvt-btn-primary:hover:not(:disabled) {
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(194, 83, 27, 0.35);
    }
    .kvt-btn-primary:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    /* Mobile */
    @media (max-width: 640px) {
        .kvt-assign-overlay { padding: 12px; }
        .kvt-assign-modal { max-height: 92vh; }
        .kvt-modal-hd { padding: 14px 16px; }
        .kvt-modal-title { font-size: 14px; }
        .kvt-modal-search,
        .kvt-emp-list,
        .kvt-primary-row { margin-left: 16px; margin-right: 16px; padding-left: 0; padding-right: 0; }
        .kvt-modal-ftr { padding: 12px 16px; }
    }
</style>
@endsection