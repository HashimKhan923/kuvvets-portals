@extends('layouts.app')
@section('title', 'Assets & Equipment')
@section('page-title', 'Assets & Equipment')
@section('breadcrumb', 'Operations · Asset Management')

@section('content')

{{-- Stats --}}
<div class="stats-grid-4">
    @foreach([
        ['Total Assets',       $stats['total'],             'fa-boxes-stacked',       'blue'],
        ['Available',          $stats['available'],         'fa-circle-check',         'green'],
        ['Assigned',           $stats['assigned'],          'fa-user-check',           'accent'],
        ['Under Maintenance',  $stats['under_maintenance'], 'fa-wrench',               'yellow'],
        ['Total Value (PKR)',  'PKR ' . number_format($stats['total_value']), 'fa-money-bill', 'green'],
        ['Maintenance Due',    $stats['maintenance_due'],   'fa-calendar-check',       'red'],
        ['Insurance Expiring', $stats['insurance_expiring'],'fa-shield-exclamation',  'yellow'],
        ['Overdue Returns',    $stats['overdue_returns'],   'fa-clock',                'red'],
    ] as [$label, $val, $icon, $color])
    <div class="stat-card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
            <div class="stat-label">{{ $label }}</div>
            <div class="stat-icon stat-icon-{{ $color }}"><i class="fa-solid {{ $icon }}"></i></div>
        </div>
        <div style="font-size:{{ strlen((string)$val) > 10 ? '16' : '26' }}px;font-weight:700;color:var(--text-primary);">
            {{ $val }}
        </div>
    </div>
    @endforeach
</div>

{{-- Quick Links --}}
<div class="quick-links">
    <a href="{{ route('assets.list') }}"        class="quick-link ql-blue"><i class="fa-solid fa-list"></i> All Assets</a>
    <a href="{{ route('assets.maintenance') }}" class="quick-link ql-yellow"><i class="fa-solid fa-wrench"></i> Maintenance</a>
    <a href="{{ route('assets.categories') }}"  class="quick-link ql-accent"><i class="fa-solid fa-tags"></i> Categories</a>
    <a href="{{ route('assets.report') }}"      class="quick-link ql-green"><i class="fa-solid fa-chart-bar"></i> Report</a>
    <a href="{{ route('assets.create') }}" class="btn btn-primary btn-sm" style="margin-left:auto;">
        <i class="fa-solid fa-plus"></i> Register Asset
    </a>
</div>

<div class="grid-2" style="margin-bottom:20px;">

    {{-- Type Distribution --}}
    <div class="card">
        <div class="card-title"><i class="fa-solid fa-chart-pie"></i> Assets by Type</div>
        @if($typeDist->sum() > 0)
        <canvas id="typeChart" height="180"></canvas>
        @else
        <div class="empty-state" style="padding:32px;">No assets yet</div>
        @endif
    </div>

    {{-- Monthly Maintenance Cost --}}
    <div class="card">
        <div class="card-title"><i class="fa-solid fa-chart-bar"></i> Monthly Maintenance Cost (PKR)</div>
        <canvas id="maintenanceChart" height="180"></canvas>
    </div>

</div>

<div class="grid-2" style="margin-bottom:20px;">

    {{-- Upcoming Maintenance --}}
    <div class="card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
            <div class="card-title" style="margin-bottom:0;"><i class="fa-solid fa-wrench"></i> Upcoming Maintenance</div>
            <a href="{{ route('assets.maintenance') }}" style="font-size:11px;color:var(--accent);text-decoration:none;font-weight:500;">View all →</a>
        </div>
        @forelse($upcomingMaintenance as $record)
        @php $tBadge = $record->type_badge; @endphp
        <div style="padding:12px;background:var(--bg-muted);border-radius:9px;margin-bottom:8px;
                    border:1px solid var(--border);border-left:3px solid {{ $tBadge['color'] }};">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px;">
                <div style="font-size:13px;font-weight:600;color:var(--text-primary);">{{ $record->asset->name }}</div>
                <span class="badge" style="background:{{ $tBadge['bg'] }};color:{{ $tBadge['color'] }};border:1px solid {{ $tBadge['border'] }};font-size:10px;">
                    {{ ucfirst($record->type) }}
                </span>
            </div>
            <div style="font-size:11px;color:var(--text-muted);">
                <i class="fa-solid fa-calendar" style="color:var(--accent);margin-right:4px;"></i>
                {{ $record->scheduled_date->format('d M Y') }}
                @if($record->vendor) · {{ $record->vendor }} @endif
                · PKR {{ number_format($record->cost) }}
            </div>
            @if($record->description)
            <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">{{ Str::limit($record->description, 60) }}</div>
            @endif
        </div>
        @empty
        <div class="empty-state" style="padding:28px;"><i class="fa-solid fa-wrench"></i>No upcoming maintenance</div>
        @endforelse
    </div>

    {{-- Active Rentals --}}
    <div class="card">
        <div class="card-title"><i class="fa-solid fa-handshake"></i> Active Rental Contracts</div>
        @forelse($activeRentals as $rental)
        <div style="padding:12px;background:var(--bg-muted);border-radius:9px;margin-bottom:8px;border:1px solid var(--border);">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;">
                <div>
                    <div style="font-size:13px;font-weight:600;color:var(--text-primary);">{{ $rental->asset->name }}</div>
                    <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">
                        {{ $rental->party_name }} ·
                        <span style="color:{{ $rental->rental_type === 'outbound' ? 'var(--green)' : 'var(--red)' }};font-weight:600;">
                            {{ ucfirst($rental->rental_type) }}
                        </span>
                    </div>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:13px;font-weight:700;color:var(--accent);">PKR {{ number_format($rental->rate_per_day) }}/day</div>
                    <div style="font-size:10px;color:{{ $rental->isExpiringSoon() ? 'var(--yellow)' : 'var(--text-muted)' }};">
                        Until {{ $rental->end_date->format('d M Y') }}
                        @if($rental->isExpiringSoon()) ⚠️ @endif
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="empty-state" style="padding:28px;"><i class="fa-solid fa-handshake"></i>No active rental contracts</div>
        @endforelse
    </div>

</div>

{{-- Recent Assets --}}
<div class="card card-flush">
    <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 20px;border-bottom:1px solid var(--border);">
        <div class="card-title" style="margin-bottom:0;"><i class="fa-solid fa-boxes-stacked"></i> Recently Registered Assets</div>
        <a href="{{ route('assets.list') }}" style="font-size:11px;color:var(--accent);text-decoration:none;font-weight:500;">View all →</a>
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Asset</th><th>Type</th><th>Condition</th><th>Status</th>
                <th>Location</th><th>Assigned To</th><th>Value</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentAssets as $asset)
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
                            <div style="font-size:10px;color:var(--accent);">{{ $asset->asset_code }}</div>
                        </div>
                    </div>
                </td>
                <td class="muted">{{ ucfirst(str_replace('_', ' ', $asset->type)) }}</td>
                <td><span class="badge" style="background:{{ $cBadge['bg'] }};color:{{ $cBadge['color'] }};border:1px solid {{ $cBadge['border'] }};">{{ ucfirst($asset->condition) }}</span></td>
                <td><span class="badge" style="background:{{ $sBadge['bg'] }};color:{{ $sBadge['color'] }};border:1px solid {{ $sBadge['border'] }};">{{ ucfirst(str_replace('_', ' ', $asset->status)) }}</span></td>
                <td class="muted">{{ $asset->location ?? '—' }}</td>
                <td class="muted">{{ $asset->currentAssignment?->employee?->full_name ?? '—' }}</td>
                <td style="font-size:13px;font-weight:700;color:var(--accent);">{{ $asset->current_value ? 'PKR ' . number_format($asset->current_value) : '—' }}</td>
            </tr>
            @empty
            <tr><td colspan="7"><div class="empty-state"><i class="fa-solid fa-boxes-stacked"></i>No assets registered yet. <a href="{{ route('assets.create') }}">Register first →</a></div></td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
@if($typeDist->sum() > 0)
var typeLabels = @json($typeDist->keys()->map(fn($k) => ucfirst(str_replace('_', ' ', $k))));
var typeData   = @json($typeDist->values());
new Chart(document.getElementById('typeChart'), {
    type: 'doughnut',
    data: { labels: typeLabels, datasets: [{ data: typeData, backgroundColor: ['#3B82F6','#22C55E','#F59E0B','#EF4444','#8B5CF6','#06B6D4','#C2531B','#A89080','#EC4899'], borderColor: '#FFFFFF', borderWidth: 2, hoverOffset: 5 }] },
    options: { cutout: '62%', plugins: { legend: { position: 'right', labels: { color: '#A89080', font: { family: 'Plus Jakarta Sans', size: 11 }, boxWidth: 10, padding: 8 } }, tooltip: { backgroundColor: '#FFFFFF', borderColor: '#F0EAE2', borderWidth: 1, titleColor: '#2D1F14', bodyColor: '#6B5347' } } }
});
@endif
var mcData = @json($maintenanceCost);
new Chart(document.getElementById('maintenanceChart'), {
    type: 'bar',
    data: { labels: mcData.map(function(d){return d.month;}), datasets: [{ label: 'Maintenance Cost (PKR)', data: mcData.map(function(d){return d.cost;}), backgroundColor: 'rgba(194,83,27,.2)', borderColor: '#C2531B', borderWidth: 2, borderRadius: 5 }] },
    options: { responsive: true, plugins: { legend: { display: false }, tooltip: { backgroundColor: '#FFFFFF', borderColor: '#F0EAE2', borderWidth: 1, titleColor: '#2D1F14', bodyColor: '#6B5347', callbacks: { label: function(ctx){ return ' PKR ' + ctx.raw.toLocaleString(); } } } }, scales: { x: { grid: { color: '#F0EAE2' }, ticks: { color: '#A89080', font: { size: 11 } } }, y: { grid: { color: '#F0EAE2' }, ticks: { color: '#A89080', font: { size: 11 }, callback: function(v){ return 'PKR '+(v/1000).toFixed(0)+'K'; } } } } }
});
</script>
@endpush

@endsection