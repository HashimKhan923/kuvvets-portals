@extends('layouts.app')
@section('title', 'Asset Report')
@section('page-title', 'Asset Report')
@section('breadcrumb', 'Assets · Report')

@section('content')

{{-- Summary --}}
<div class="stats-grid-3">
    @foreach([
        ['Total Assets',     $summary['total_assets'],                         'fa-boxes-stacked', 'blue'],
        ['Total Value',      'PKR ' . number_format($summary['total_value']),  'fa-money-bill',    'green'],
        ['Purchase Value',   'PKR ' . number_format($summary['total_purchase']),'fa-receipt',      'accent'],
        ['Maintenance Cost', 'PKR ' . number_format($summary['maintenance_cost']),'fa-wrench',     'yellow'],
        ['Rental Income',    'PKR ' . number_format($summary['rental_income']), 'fa-arrow-up',     'green'],
        ['Rental Expense',   'PKR ' . number_format($summary['rental_expense']),'fa-arrow-down',   'red'],
    ] as [$l, $v, $icon, $color])
    <div class="stat-card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
            <div class="stat-label">{{ $l }}</div>
            <div class="stat-icon stat-icon-{{ $color }}"><i class="fa-solid {{ $icon }}"></i></div>
        </div>
        <div style="font-size:18px;font-weight:700;color:var(--text-primary);">{{ $v }}</div>
    </div>
    @endforeach
</div>

{{-- Filter --}}
<div class="card card-sm" style="margin-bottom:18px;">
    <div class="toolbar">
        <form method="GET" action="{{ route('assets.report') }}" class="toolbar" style="flex:1;">
            <select name="type" class="form-select">
                <option value="">All Types</option>
                @foreach(['heavy_equipment'=>'Heavy Equipment','vehicle'=>'Vehicle','forklift'=>'Forklift','crane'=>'Crane','warehouse_equipment'=>'Warehouse Equipment','it_equipment'=>'IT Equipment','tools'=>'Tools','safety_equipment'=>'Safety Equipment','other'=>'Other'] as $v => $l)
                <option value="{{ $v }}" {{ request('type') === $v ? 'selected' : '' }}>{{ $l }}</option>
                @endforeach
            </select>
            <select name="department" class="form-select" style="min-width:160px;">
                <option value="">All Departments</option>
                @foreach($departments as $d)
                <option value="{{ $d->id }}" {{ request('department') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-filter"></i> Filter
            </button>
        </form>
        <div style="font-size:12px;color:var(--text-muted);">{{ $assets->count() }} assets</div>
    </div>
</div>

{{-- Table --}}
<div class="card card-flush">
    <table class="data-table">
        <thead>
            <tr>
                <th>Asset</th><th>Type</th><th>Department</th><th>Status</th>
                <th>Purchase Cost</th><th>Current Value</th><th>Dep. Value</th>
                <th>Maintenances</th><th>Assignments</th>
            </tr>
        </thead>
        <tbody>
            @forelse($assets as $asset)
            @php $sBadge = $asset->status_badge; @endphp
            <tr>
                <td>
                    <div style="display:flex;align-items:center;gap:9px;">
                        <div style="width:32px;height:32px;background:var(--accent-bg);border:1px solid var(--accent-border);
                                    border-radius:7px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <i class="fa-solid {{ $asset->type_icon }}" style="font-size:12px;color:var(--accent);"></i>
                        </div>
                        <div>
                            <a href="{{ route('assets.show', $asset) }}" style="font-size:13px;font-weight:600;color:var(--text-primary);text-decoration:none;">{{ $asset->name }}</a>
                            <div style="font-size:10px;color:var(--accent);">{{ $asset->asset_code }}</div>
                        </div>
                    </div>
                </td>
                <td class="muted">{{ ucfirst(str_replace('_', ' ', $asset->type)) }}</td>
                <td class="muted">{{ $asset->department?->name ?? '—' }}</td>
                <td><span class="badge" style="background:{{ $sBadge['bg'] }};color:{{ $sBadge['color'] }};border:1px solid {{ $sBadge['border'] }};">{{ ucfirst(str_replace('_', ' ', $asset->status)) }}</span></td>
                <td class="muted">{{ $asset->purchase_cost ? 'PKR ' . number_format($asset->purchase_cost) : '—' }}</td>
                <td style="font-size:13px;font-weight:700;color:var(--accent);">{{ $asset->current_value ? 'PKR ' . number_format($asset->current_value) : '—' }}</td>
                <td class="muted">PKR {{ number_format($asset->depreciated_value) }}</td>
                <td style="font-size:13px;font-weight:700;color:var(--text-primary);">
                    {{ $asset->maintenance->count() }}
                    <span style="font-size:11px;color:var(--text-muted);font-weight:400;">
                        (PKR {{ number_format($asset->maintenance->sum('cost')) }})
                    </span>
                </td>
                <td style="font-size:13px;font-weight:700;color:var(--text-primary);">{{ $asset->assignments->count() }}</td>
            </tr>
            @empty
            <tr><td colspan="9"><div class="empty-state">No assets found.</div></td></tr>
            @endforelse
        </tbody>
        @if($assets->count())
        <tfoot>
            <tr style="background:var(--bg-muted);border-top:2px solid var(--accent-border);">
                <td colspan="4" style="padding:10px 16px;font-size:11px;font-weight:700;color:var(--accent);">
                    TOTALS ({{ $assets->count() }} assets)
                </td>
                <td style="padding:10px 16px;font-size:12px;font-weight:700;color:var(--text-primary);">
                    PKR {{ number_format($assets->sum('purchase_cost')) }}
                </td>
                <td style="padding:10px 16px;font-size:13px;font-weight:700;color:var(--accent);">
                    PKR {{ number_format($assets->sum('current_value')) }}
                </td>
                <td colspan="3"></td>
            </tr>
        </tfoot>
        @endif
    </table>
</div>

@endsection