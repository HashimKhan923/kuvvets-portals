@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('breadcrumb', 'Overview · ' . now()->format('l, d M Y'))

@section('content')

<div class="stats-grid-4">
    <div class="stat-card">
        <div class="flex items-center justify-between mb-3">
            <div class="stat-label">Active Employees</div>
            <div class="stat-icon stat-icon-green"><i class="fa-solid fa-users"></i></div>
        </div>
        <div class="stat-num">{{ number_format($stats['total_employees']) }}</div>
        <div class="stat-sub">Total headcount</div>
    </div>
    <div class="stat-card">
        <div class="flex items-center justify-between mb-3">
            <div class="stat-label">Departments</div>
            <div class="stat-icon stat-icon-accent"><i class="fa-solid fa-sitemap"></i></div>
        </div>
        <div class="stat-num">{{ $stats['departments'] }}</div>
        <div class="stat-sub">Active departments</div>
    </div>
    <div class="stat-card">
        <div class="flex items-center justify-between mb-3">
            <div class="stat-label">On Probation</div>
            <div class="stat-icon stat-icon-yellow"><i class="fa-solid fa-hourglass-half"></i></div>
        </div>
        <div class="stat-num">{{ $stats['on_probation'] }}</div>
        <div class="stat-sub">Pending confirmation</div>
    </div>
    <div class="stat-card">
        <div class="flex items-center justify-between mb-3">
            <div class="stat-label">New This Month</div>
            <div class="stat-icon stat-icon-blue"><i class="fa-solid fa-user-plus"></i></div>
        </div>
        <div class="stat-num">{{ $stats['new_this_month'] }}</div>
        <div class="stat-sub">Joined in {{ now()->format('F') }}</div>
    </div>
</div>

<div class="grid-2-1">
    <div class="card">
        <div class="card-title"><i class="fa-solid fa-chart-area"></i> Workforce Overview</div>
        <canvas id="workforceChart" height="80"></canvas>
    </div>
    <div class="card">
        <div class="card-title"><i class="fa-solid fa-bell"></i> Recent Activity</div>
        <div class="empty-state">
            <i class="fa-solid fa-clock-rotate-left"></i>
            <div style="font-size:13px;">Activity feed loads here</div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('workforceChart'), {
    type: 'bar',
    data: {
        labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
        datasets: [{
            label: 'Active Employees',
            data: [0,0,0,0,0,0,0,0,0,0,0,{{ $stats['total_employees'] }}],
            backgroundColor: 'rgba(194,83,27,.15)',
            borderColor: '#C2531B',
            borderWidth: 1.5,
            borderRadius: 5,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { labels: { color: '#A89080', font: { family: 'Plus Jakarta Sans', size: 11 } } } },
        scales: {
            x: { grid: { color: '#F0EAE2' }, ticks: { color: '#A89080', font: { size: 11 } } },
            y: { grid: { color: '#F0EAE2' }, ticks: { color: '#A89080', font: { size: 11 } } },
        }
    }
});
</script>
@endpush