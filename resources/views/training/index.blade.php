@extends('layouts.app')
@section('title', 'Training')
@section('page-title', 'Training & Development')
@section('breadcrumb', 'Training · Dashboard')

@section('content')

{{-- Stats --}}
<div class="stats-grid-3">
    @foreach([
        ['Active Programs',      $stats['total_programs'],          'fa-book-open',           'blue'],
        ['Upcoming Sessions',    $stats['upcoming_sessions'],       'fa-calendar-plus',        'green'],
        ['Enrolled This Month',  $stats['enrolled_this_month'],     'fa-user-graduate',        'accent'],
        ['Completed This Month', $stats['completed_this_month'],    'fa-circle-check',         'green'],
        ['Certs Expiring (30d)', $stats['certifications_expiring'], 'fa-certificate',          'red'],
        ['Mandatory Programs',   $stats['mandatory_programs'],      'fa-shield-check',         'yellow'],
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

{{-- Quick Links --}}
<div class="quick-links">
    <a href="{{ route('training.programs') }}"       class="quick-link ql-blue"><i class="fa-solid fa-book-open"></i> Programs</a>
    <a href="{{ route('training.sessions') }}"       class="quick-link ql-green"><i class="fa-solid fa-calendar"></i> Sessions</a>
    <a href="{{ route('training.certifications') }}" class="quick-link ql-yellow"><i class="fa-solid fa-certificate"></i> Certifications</a>
    <a href="{{ route('training.skill-matrix') }}"   class="quick-link ql-purple"><i class="fa-solid fa-table-cells"></i> Skill Matrix</a>
    <a href="{{ route('training.report') }}"         class="quick-link ql-accent"><i class="fa-solid fa-chart-bar"></i> Report</a>
    <a href="{{ route('training.sessions.create') }}" class="btn btn-primary btn-sm" style="margin-left:auto;">
        <i class="fa-solid fa-plus"></i> Schedule Session
    </a>
</div>

<div class="grid-2" style="margin-bottom:20px;">

    {{-- Upcoming Sessions --}}
    <div class="card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
            <div class="card-title" style="margin-bottom:0;">
                <i class="fa-solid fa-calendar-days"></i> Upcoming Sessions
            </div>
            <a href="{{ route('training.sessions') }}"
               style="font-size:11px;color:var(--accent);text-decoration:none;font-weight:500;">
                View all →
            </a>
        </div>

        @forelse($upcomingSessions as $session)
        @php $sBadge = $session->status_badge; @endphp
        <a href="{{ route('training.session', $session) }}"
           style="display:block;padding:12px;background:var(--bg-muted);border-radius:9px;
                  margin-bottom:8px;text-decoration:none;border:1px solid var(--border);
                  transition:border-color .15s;"
           onmouseover="this.style.borderColor='var(--accent-border)'"
           onmouseout="this.style.borderColor='var(--border)'">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;">
                <div style="flex:1;">
                    <div style="font-size:13px;color:var(--text-primary);font-weight:600;margin-bottom:3px;">
                        {{ $session->title }}
                    </div>
                    <div style="font-size:11px;color:var(--text-muted);">
                        <i class="fa-solid fa-book" style="font-size:10px;color:var(--accent);margin-right:4px;"></i>
                        {{ $session->program->title }}
                    </div>
                    <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">
                        <i class="fa-solid fa-calendar" style="font-size:10px;color:var(--accent);margin-right:4px;"></i>
                        {{ $session->start_date->format('d M Y') }}
                        @if($session->start_time) · {{ date('h:i A', strtotime($session->start_time)) }} @endif
                        @if($session->venue) · <i class="fa-solid fa-location-dot" style="font-size:9px;"></i> {{ $session->venue }} @endif
                    </div>
                </div>
                <div style="text-align:right;flex-shrink:0;">
                    <div style="font-size:12px;font-weight:700;color:var(--accent);">
                        {{ $session->enrolled_count }}/{{ $session->max_participants }}
                    </div>
                    <div style="font-size:10px;color:var(--text-muted);">enrolled</div>
                    <div class="progress-track" style="width:60px;margin-top:4px;margin-left:auto;">
                        <div class="progress-fill"
                             style="width:{{ $session->max_participants > 0 ? round($session->enrolled_count / $session->max_participants * 100) : 0 }}%;
                                    background:{{ $session->isFull() ? 'var(--red)' : 'var(--accent)' }};"></div>
                    </div>
                </div>
            </div>
        </a>
        @empty
        <div class="empty-state" style="padding:28px;">
            <i class="fa-solid fa-calendar-xmark"></i>
            No upcoming sessions
        </div>
        @endforelse
    </div>

    {{-- Category Chart --}}
    <div class="card">
        <div class="card-title">
            <i class="fa-solid fa-chart-pie"></i> Programs by Category
        </div>
        @if($categoryDist->sum() > 0)
        <canvas id="categoryChart" height="200"></canvas>
        @else
        <div class="empty-state" style="padding:32px;">No data yet</div>
        @endif
    </div>

</div>

{{-- Expiring Certifications Alert --}}
@if($expiringCerts->count())
<div style="background:var(--yellow-bg);border:1px solid var(--yellow-border);
            border-radius:10px;padding:16px 20px;margin-bottom:20px;">
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
        <i class="fa-solid fa-triangle-exclamation" style="color:var(--yellow);font-size:16px;"></i>
        <span style="font-size:14px;font-weight:700;color:var(--yellow);">
            {{ $expiringCerts->count() }} Certifications Expiring Within 60 Days
        </span>
        <a href="{{ route('training.certifications') }}"
           style="margin-left:auto;font-size:12px;color:var(--yellow);text-decoration:none;font-weight:600;">
            View all →
        </a>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:8px;">
        @foreach($expiringCerts as $cert)
        @php $expStatus = $cert->expiry_status; @endphp
        <div style="background:var(--bg-card);border:1px solid var(--yellow-border);
                    border-radius:8px;padding:10px 14px;
                    display:flex;align-items:center;gap:9px;">
            <img src="{{ $cert->employee->avatar_url }}"
                 class="avatar avatar-sm" style="flex-shrink:0;">
            <div style="flex:1;min-width:0;">
                <div style="font-size:12px;font-weight:600;color:var(--text-primary);
                            overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                    {{ $cert->employee->full_name }}
                </div>
                <div style="font-size:10px;color:var(--text-muted);
                            overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                    {{ $cert->certificate_name }}
                </div>
                <div style="font-size:10px;font-weight:700;color:{{ $expStatus['color'] }};margin-top:1px;">
                    Expires: {{ $cert->expiry_date->format('d M Y') }}
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Recent Enrollments --}}
<div class="card card-flush">
    <div style="padding:14px 20px;border-bottom:1px solid var(--border);">
        <div class="card-title" style="margin-bottom:0;">
            <i class="fa-solid fa-user-graduate"></i> Recent Enrollments
        </div>
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Employee</th>
                <th>Program</th>
                <th>Session</th>
                <th>Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentEnrollments as $enr)
            @php $badge = $enr->status_badge; @endphp
            <tr>
                <td>
                    <div class="td-employee">
                        <img src="{{ $enr->employee->avatar_url }}" class="avatar avatar-sm">
                        <div>
                            <div class="td-employee name">{{ $enr->employee->full_name }}</div>
                            <div class="td-employee id">{{ $enr->employee->employee_id }}</div>
                        </div>
                    </div>
                </td>
                <td class="muted">{{ $enr->session?->program?->title ?? '—' }}</td>
                <td class="muted">{{ $enr->session?->title ?? '—' }}</td>
                <td class="muted">{{ $enr->created_at->format('d M Y') }}</td>
                <td>
                    <span class="badge" style="background:{{ $badge['bg'] }};color:{{ $badge['color'] }};border:1px solid {{ $badge['border'] }};">
                        {{ ucfirst($enr->status) }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5">
                    <div class="empty-state">No enrollments yet.</div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
@if($categoryDist->sum() > 0)
var catLabels = @json($categoryDist->keys()->map(fn($k) => ucfirst(str_replace('_', ' ', $k))));
var catData   = @json($categoryDist->values());
new Chart(document.getElementById('categoryChart'), {
    type: 'doughnut',
    data: {
        labels: catLabels,
        datasets: [{
            data: catData,
            backgroundColor: ['#EF4444','#3B82F6','#8B5CF6','#F59E0B','#C2531B','#22C55E','#06B6D4','#A89080'],
            borderColor: '#FFFFFF',
            borderWidth: 2,
            hoverOffset: 5
        }]
    },
    options: {
        cutout: '60%',
        plugins: {
            legend: {
                position: 'bottom',
                labels: { color: '#A89080', font: { family: 'Plus Jakarta Sans', size: 11 }, boxWidth: 10, padding: 8 }
            },
            tooltip: {
                backgroundColor: '#FFFFFF',
                borderColor: '#F0EAE2',
                borderWidth: 1,
                titleColor: '#2D1F14',
                bodyColor: '#6B5347'
            }
        }
    }
});
@endif
</script>
@endpush

@endsection