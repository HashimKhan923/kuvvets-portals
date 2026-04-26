@extends('layouts.app')
@section('title', 'Maintenance Records')
@section('page-title', 'Maintenance Records')
@section('breadcrumb', 'Assets · Maintenance')

@section('content')

{{-- Stats --}}
<div class="stats-grid-4">
    @foreach([
        ['Scheduled',   $stats['scheduled'],  'fa-calendar-check', 'blue'],
        ['In Progress', $stats['in_progress'],'fa-spinner',        'yellow'],
        ['Completed',   $stats['completed'],  'fa-circle-check',   'green'],
        ['Total Cost',  'PKR ' . number_format($stats['total_cost']), 'fa-money-bill', 'accent'],
    ] as [$label, $val, $icon, $color])
    <div class="stat-card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
            <div class="stat-label">{{ $label }}</div>
            <div class="stat-icon stat-icon-{{ $color }}"><i class="fa-solid {{ $icon }}"></i></div>
        </div>
        <div style="font-size:22px;font-weight:700;color:var(--text-primary);">{{ $val }}</div>
    </div>
    @endforeach
</div>

{{-- Filter --}}
<div class="card card-sm" style="margin-bottom:18px;">
    <div class="toolbar">
        <form method="GET" action="{{ route('assets.maintenance') }}" class="toolbar" style="flex:1;">
            <select name="status" class="form-select">
                <option value="">All Status</option>
                @foreach(['scheduled','in_progress','completed','cancelled'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>
                    {{ ucfirst(str_replace('_', ' ', $s)) }}
                </option>
                @endforeach
            </select>
            <select name="type" class="form-select">
                <option value="">All Types</option>
                @foreach(['routine','preventive','corrective','emergency','inspection','calibration'] as $t)
                <option value="{{ $t }}" {{ request('type') === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-filter"></i>
            </button>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card card-flush">
    <table class="data-table">
        <thead>
            <tr>
                <th>Asset</th><th>Type</th><th>Status</th><th>Scheduled</th>
                <th>Completed</th><th>Vendor</th><th>Cost</th><th class="center">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $record)
            @php $mBadge = $record->status_badge; $tBadge = $record->type_badge; @endphp
            <tr>
                <td>
                    <div style="display:flex;align-items:center;gap:9px;">
                        <div style="width:32px;height:32px;background:var(--accent-bg);border:1px solid var(--accent-border);
                                    border-radius:7px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <i class="fa-solid {{ $record->asset->type_icon }}" style="font-size:12px;color:var(--accent);"></i>
                        </div>
                        <div>
                            <a href="{{ route('assets.show', $record->asset) }}" style="font-size:13px;font-weight:600;color:var(--text-primary);text-decoration:none;">{{ $record->asset->name }}</a>
                            <div style="font-size:10px;color:var(--accent);">{{ $record->asset->asset_code }}</div>
                        </div>
                    </div>
                </td>
                <td><span class="badge" style="background:{{ $tBadge['bg'] }};color:{{ $tBadge['color'] }};border:1px solid {{ $tBadge['border'] }};">{{ ucfirst($record->type) }}</span></td>
                <td><span class="badge" style="background:{{ $mBadge['bg'] }};color:{{ $mBadge['color'] }};border:1px solid {{ $mBadge['border'] }};">{{ ucfirst(str_replace('_', ' ', $record->status)) }}</span></td>
                <td style="font-size:12px;font-weight:{{ $record->scheduled_date->isPast() && $record->status === 'scheduled' ? '700' : '400' }};
                    color:{{ $record->scheduled_date->isPast() && $record->status === 'scheduled' ? 'var(--red)' : 'var(--text-secondary)' }};">
                    {{ $record->scheduled_date->format('d M Y') }}
                    @if($record->scheduled_date->isPast() && $record->status === 'scheduled')
                    <span style="font-size:10px;"> ⚠️ Overdue</span>
                    @endif
                </td>
                <td class="muted">{{ $record->completed_date?->format('d M Y') ?? '—' }}</td>
                <td class="muted">{{ $record->vendor ?? $record->performed_by ?? '—' }}</td>
                <td style="font-size:13px;font-weight:700;color:var(--accent);">PKR {{ number_format($record->cost) }}</td>
                <td class="center">
                    <a href="{{ route('assets.show', $record->asset) }}" class="action-btn" title="View Asset">
                        <i class="fa-solid fa-eye"></i>
                    </a>
                </td>
            </tr>
            @empty
            <tr><td colspan="8"><div class="empty-state"><i class="fa-solid fa-wrench"></i>No maintenance records found.</div></td></tr>
            @endforelse
        </tbody>
    </table>

    @if($records->hasPages())
    <div class="pagination">
        <span class="pagination-info">Showing {{ $records->firstItem() }}–{{ $records->lastItem() }} of {{ $records->total() }}</span>
        <div class="pagination-btns">
            @if($records->onFirstPage())<span class="page-btn disabled">← Prev</span>
            @else<a href="{{ $records->previousPageUrl() }}" class="page-btn">← Prev</a>@endif
            @if($records->hasMorePages())<a href="{{ $records->nextPageUrl() }}" class="page-btn active">Next →</a>
            @else<span class="page-btn disabled">Next →</span>@endif
        </div>
    </div>
    @endif
</div>

@endsection