@extends('layouts.app')
@section('title', 'All Assets')
@section('page-title', 'Asset Register')
@section('breadcrumb', 'Assets · All Assets')

@section('content')

{{-- Toolbar --}}
<div class="card card-sm" style="margin-bottom:18px;">
    <div class="toolbar">
        <form method="GET" action="{{ route('assets.list') }}" class="toolbar" style="flex:1;">
            <div class="toolbar-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Name, code, serial, registration…" class="form-input">
            </div>
            <select name="type" class="form-select">
                <option value="">All Types</option>
                @foreach(['heavy_equipment'=>'Heavy Equipment','vehicle'=>'Vehicle','forklift'=>'Forklift','crane'=>'Crane','warehouse_equipment'=>'Warehouse Equipment','it_equipment'=>'IT Equipment','furniture'=>'Furniture','tools'=>'Tools','safety_equipment'=>'Safety Equipment','other'=>'Other'] as $v => $l)
                <option value="{{ $v }}" {{ request('type') === $v ? 'selected' : '' }}>{{ $l }}</option>
                @endforeach
            </select>
            <select name="status" class="form-select">
                <option value="">All Status</option>
                @foreach(['available'=>'Available','assigned'=>'Assigned','under_maintenance'=>'Under Maintenance','out_of_service'=>'Out of Service','disposed'=>'Disposed','rented_out'=>'Rented Out'] as $v => $l)
                <option value="{{ $v }}" {{ request('status') === $v ? 'selected' : '' }}>{{ $l }}</option>
                @endforeach
            </select>
            <select name="department" class="form-select">
                <option value="">All Departments</option>
                @foreach($departments as $d)
                <option value="{{ $d->id }}" {{ request('department') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-filter"></i>
            </button>
            @if(request()->hasAny(['search','type','status','department','category']))
            <a href="{{ route('assets.list') }}" class="btn btn-secondary btn-sm">
                <i class="fa-solid fa-xmark"></i> Clear
            </a>
            @endif
        </form>
        <a href="{{ route('assets.create') }}" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus"></i> Register Asset
        </a>
    </div>
</div>

{{-- Table --}}
<div class="card card-flush">
    <table class="data-table">
        <thead>
            <tr>
                <th>Asset</th><th>Type</th><th>Department</th><th>Condition</th>
                <th>Status</th><th>Assigned To</th><th>Insurance</th><th>Value</th>
                <th class="center">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($assets as $asset)
            @php $sBadge = $asset->status_badge; $cBadge = $asset->condition_badge; @endphp
            <tr>
                <td>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="width:36px;height:36px;background:var(--accent-bg);border:1px solid var(--accent-border);
                                    border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <i class="fa-solid {{ $asset->type_icon }}" style="font-size:14px;color:var(--accent);"></i>
                        </div>
                        <div>
                            <a href="{{ route('assets.show', $asset) }}" style="font-size:13px;font-weight:600;color:var(--text-primary);text-decoration:none;">{{ $asset->name }}</a>
                            <div style="font-size:10px;color:var(--accent);">
                                {{ $asset->asset_code }}
                                @if($asset->brand) · {{ $asset->brand }} @endif
                                @if($asset->model) {{ $asset->model }} @endif
                            </div>
                        </div>
                    </div>
                </td>
                <td class="muted">{{ ucfirst(str_replace('_', ' ', $asset->type)) }}</td>
                <td class="muted">{{ $asset->department?->name ?? '—' }}</td>
                <td><span class="badge" style="background:{{ $cBadge['bg'] }};color:{{ $cBadge['color'] }};border:1px solid {{ $cBadge['border'] }};">{{ ucfirst($asset->condition) }}</span></td>
                <td><span class="badge" style="background:{{ $sBadge['bg'] }};color:{{ $sBadge['color'] }};border:1px solid {{ $sBadge['border'] }};">{{ ucfirst(str_replace('_', ' ', $asset->status)) }}</span></td>
                <td class="muted">{{ $asset->currentAssignment?->employee?->full_name ?? '—' }}</td>
                <td>
                    @if($asset->insurance_expiry)
                    <span style="font-size:12px;font-weight:{{ ($asset->isInsuranceExpired() || $asset->isInsuranceExpiring()) ? '700' : '400' }};
                                 color:{{ $asset->isInsuranceExpired() ? 'var(--red)' : ($asset->isInsuranceExpiring() ? 'var(--yellow)' : 'var(--text-secondary)') }};">
                        {{ $asset->insurance_expiry->format('d M Y') }}
                        @if($asset->isInsuranceExpired()) ⚠️ @endif
                    </span>
                    @else
                    <span class="text-muted">—</span>
                    @endif
                </td>
                <td style="font-size:13px;font-weight:700;color:var(--accent);">
                    {{ $asset->current_value ? 'PKR ' . number_format($asset->current_value) : '—' }}
                </td>
                <td class="center">
                    <div style="display:flex;align-items:center;justify-content:center;gap:5px;">
                        <a href="{{ route('assets.show', $asset) }}" class="action-btn" title="View"><i class="fa-solid fa-eye"></i></a>
                        <a href="{{ route('assets.edit', $asset) }}" class="action-btn" title="Edit"><i class="fa-solid fa-pen"></i></a>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="9"><div class="empty-state"><i class="fa-solid fa-boxes-stacked"></i>No assets found.</div></td></tr>
            @endforelse
        </tbody>
    </table>

    @if($assets->hasPages())
    <div class="pagination">
        <span class="pagination-info">Showing {{ $assets->firstItem() }}–{{ $assets->lastItem() }} of {{ $assets->total() }}</span>
        <div class="pagination-btns">
            @if($assets->onFirstPage())<span class="page-btn disabled">← Prev</span>
            @else<a href="{{ $assets->previousPageUrl() }}" class="page-btn">← Prev</a>@endif
            @if($assets->hasMorePages())<a href="{{ $assets->nextPageUrl() }}" class="page-btn active">Next →</a>
            @else<span class="page-btn disabled">Next →</span>@endif
        </div>
    </div>
    @endif
</div>

@endsection