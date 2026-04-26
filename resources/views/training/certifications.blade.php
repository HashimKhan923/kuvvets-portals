@extends('layouts.app')
@section('title', 'Certifications')
@section('page-title', 'Employee Certifications')
@section('breadcrumb', 'Training · Certifications')

@section('content')

{{-- Stats --}}
<div class="stats-grid-4">
    @foreach([
        ['Total Certs',    $stats['total'],        'fa-certificate',  'accent'],
        ['Valid',          $stats['valid'],         'fa-circle-check', 'green'],
        ['Expiring (30d)', $stats['expiring_soon'], 'fa-clock',        'yellow'],
        ['Expired',        $stats['expired'],       'fa-circle-xmark', 'red'],
    ] as [$label, $val, $icon, $color])
    <div class="stat-card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
            <div class="stat-label">{{ $label }}</div>
            <div class="stat-icon stat-icon-{{ $color }}"><i class="fa-solid {{ $icon }}"></i></div>
        </div>
        <div class="stat-num">{{ $val }}</div>
    </div>
    @endforeach
</div>

{{-- Filter --}}
<div class="card card-sm" style="margin-bottom:18px;">
    <div class="toolbar">
        <form method="GET" action="{{ route('training.certifications') }}" class="toolbar" style="flex:1;">
            <div class="toolbar-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search employee or certificate…" class="form-input">
            </div>
            <select name="status" class="form-select">
                <option value="">All Status</option>
                <option value="valid"         {{ request('status') === 'valid'         ? 'selected' : '' }}>Valid</option>
                <option value="expiring_soon" {{ request('status') === 'expiring_soon' ? 'selected' : '' }}>Expiring Soon</option>
                <option value="expired"       {{ request('status') === 'expired'       ? 'selected' : '' }}>Expired</option>
            </select>
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-filter"></i> Filter
            </button>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card card-flush">
    <table class="data-table">
        <thead>
            <tr>
                <th>Employee</th>
                <th>Certificate</th>
                <th>Issued By</th>
                <th>Cert Number</th>
                <th>Issue Date</th>
                <th>Expiry</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($certifications as $cert)
            @php $expStatus = $cert->expiry_status; @endphp
            <tr>
                <td>
                    <div class="td-employee">
                        <img src="{{ $cert->employee->avatar_url }}" class="avatar avatar-sm">
                        <div>
                            <div class="td-employee name">{{ $cert->employee->full_name }}</div>
                            <div class="td-employee id">{{ $cert->employee->employee_id }}</div>
                        </div>
                    </div>
                </td>
                <td>
                    <div style="font-size:13px;font-weight:600;color:var(--text-primary);">
                        {{ $cert->certificate_name }}
                    </div>
                    <div style="font-size:10px;color:var(--text-muted);">
                        {{ $cert->employee->department?->name ?? '—' }}
                    </div>
                </td>
                <td class="muted">{{ $cert->issued_by }}</td>
                <td class="muted" style="font-size:11px;">{{ $cert->certificate_number ?? '—' }}</td>
                <td class="muted">{{ $cert->issue_date->format('d M Y') }}</td>
                <td>
                    @if($cert->expiry_date)
                    <span style="font-weight:500;color:{{ $expStatus['color'] }};">
                        {{ $cert->expiry_date->format('d M Y') }}
                    </span>
                    @if($cert->isExpiringSoon())
                    <div style="font-size:10px;color:var(--yellow);">
                        Expires in {{ $cert->expiry_date->diffInDays(now()) }} days
                    </div>
                    @endif
                    @else
                    <span class="text-muted">No Expiry</span>
                    @endif
                </td>
                <td>
                    <span class="badge" style="background:{{ $expStatus['bg'] }};color:{{ $expStatus['color'] }};border:1px solid {{ $expStatus['color'] }}30;">
                        {{ $expStatus['label'] }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7">
                    <div class="empty-state">
                        <i class="fa-solid fa-certificate"></i>
                        No certifications found.
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($certifications->hasPages())
    <div class="pagination">
        <span class="pagination-info">
            Showing {{ $certifications->firstItem() }}–{{ $certifications->lastItem() }}
            of {{ $certifications->total() }}
        </span>
        <div class="pagination-btns">
            @if($certifications->onFirstPage())
                <span class="page-btn disabled">← Prev</span>
            @else
                <a href="{{ $certifications->previousPageUrl() }}" class="page-btn">← Prev</a>
            @endif
            @if($certifications->hasMorePages())
                <a href="{{ $certifications->nextPageUrl() }}" class="page-btn active">Next →</a>
            @else
                <span class="page-btn disabled">Next →</span>
            @endif
        </div>
    </div>
    @endif
</div>

@endsection