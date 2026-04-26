@extends('layouts.app')
@section('title','Workforce Report')
@section('page-title','Workforce Report')
@section('breadcrumb','Reports · Workforce')

@section('content')

{{-- Filters --}}
<div class="card card-gold" style="padding:14px 18px;margin-bottom:20px;
     display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
    <form method="GET" action="{{ route('reports.workforce') }}"
          style="display:flex;gap:10px;align-items:center;flex:1;flex-wrap:wrap;">
        <select name="department"
                style="background:var(--white);border:1px solid var(--border);border-radius:7px;
                       padding:8px 12px;color:var(--text-secondary);font-size:13px;outline:none;
                       min-width:180px;">
            <option value="">All Departments</option>
            @foreach($departments as $d)
            <option value="{{ $d->id }}" {{ request('department')==$d->id?'selected':'' }}>
                {{ $d->name }}
            </option>
            @endforeach
        </select>
        <select name="status"
                style="background:var(--white);border:1px solid var(--border);border-radius:7px;
                       padding:8px 12px;color:var(--text-secondary);font-size:13px;outline:none;">
            <option value="">All Status</option>
            @foreach(['active'=>'Active','on_notice'=>'On Notice',
                      'terminated'=>'Terminated','resigned'=>'Resigned'] as $v=>$l)
            <option value="{{ $v }}" {{ request('status')===$v?'selected':'' }}>{{ $l }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn-gold" style="padding:8px 14px;font-size:13px;">
            <i class="fa-solid fa-filter"></i> Filter
        </button>
    </form>
    <a href="{{ route('reports.export.employees', request()->all()) }}"
       style="display:flex;align-items:center;gap:7px;padding:8px 16px;
              background:var(--success-bg);border:1px solid var(--success-border);border-radius:8px;
              color:var(--success);font-size:12px;font-weight:600;text-decoration:none;">
        <i class="fa-solid fa-file-csv"></i> Export CSV
    </a>
</div>

{{-- Summary Cards --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:22px;">
    @foreach([
        ['Total Active',   $employees->where('employment_status','active')->count(),  '#2D7A4F','#F0FBF4'],
        ['Full Time',      $employees->where('employment_type','full_time')->count(),  '#2B6CB0','#EBF8FF'],
        ['Part Time',      $employees->where('employment_type','part_time')->count(),  '#B7791F','#FFFBEB'],
        ['On Probation',   $employees->where('employment_status','probation')->count(),'#C49A3C','#FBF5E6'],
    ] as [$l,$v,$c,$bg])
    <div class="stat-card">
        <div style="font-size:10px;font-weight:600;color:var(--text-muted);
                    letter-spacing:.7px;text-transform:uppercase;margin-bottom:8px;">{{ $l }}</div>
        <div style="font-size:30px;font-weight:700;color:{{ $c }};
                    font-family:'Space Grotesk',sans-serif;">{{ $v }}</div>
    </div>
    @endforeach
</div>

{{-- Charts Row --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:18px;margin-bottom:22px;">

    {{-- Headcount by Department --}}
    <div class="card card-gold" style="padding:22px;">
        <div style="font-family:'Space Grotesk',sans-serif;font-size:13px;font-weight:600;
                    color:var(--text-primary);margin-bottom:14px;">
            <i class="fa-solid fa-sitemap" style="color:var(--gold);margin-right:7px;"></i>
            By Department
        </div>
        <canvas id="deptChart" height="200"></canvas>
    </div>

    {{-- Gender Breakdown --}}
    <div class="card card-gold" style="padding:22px;">
        <div style="font-family:'Space Grotesk',sans-serif;font-size:13px;font-weight:600;
                    color:var(--text-primary);margin-bottom:14px;">
            <i class="fa-solid fa-venus-mars" style="color:var(--gold);margin-right:7px;"></i>
            Gender Distribution
        </div>
        <canvas id="genderChart" height="200"></canvas>
    </div>

    {{-- Age Groups --}}
    <div class="card card-gold" style="padding:22px;">
        <div style="font-family:'Space Grotesk',sans-serif;font-size:13px;font-weight:600;
                    color:var(--text-primary);margin-bottom:14px;">
            <i class="fa-solid fa-cake-candles" style="color:var(--gold);margin-right:7px;"></i>
            Age Distribution
        </div>
        <canvas id="ageChart" height="200"></canvas>
    </div>
</div>

{{-- Tenure + Joining Trend --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:22px;">

    {{-- Tenure --}}
    <div class="card card-gold" style="padding:22px;">
        <div style="font-family:'Space Grotesk',sans-serif;font-size:13px;font-weight:600;
                    color:var(--text-primary);margin-bottom:14px;">
            <i class="fa-solid fa-hourglass-half" style="color:var(--gold);margin-right:7px;"></i>
            Tenure Distribution
        </div>
        @foreach($tenureGroups as $label => $count)
        @php $pct = $employees->count() > 0 ? round($count/$employees->count()*100) : 0; @endphp
        <div style="margin-bottom:10px;">
            <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                <span style="font-size:12px;color:var(--text-secondary);">{{ $label }}</span>
                <span style="font-size:12px;font-weight:600;color:var(--gold-dark);
                             font-family:'Space Grotesk',sans-serif;">
                    {{ $count }} ({{ $pct }}%)
                </span>
            </div>
            <div style="height:7px;background:var(--border-light);border-radius:4px;overflow:hidden;">
                <div style="height:100%;width:{{ $pct }}%;
                            background:linear-gradient(90deg,var(--gold),var(--gold-light));
                            border-radius:4px;transition:width .6s;"></div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- 12-Month Joining Trend --}}
    <div class="card card-gold" style="padding:22px;">
        <div style="font-family:'Space Grotesk',sans-serif;font-size:13px;font-weight:600;
                    color:var(--text-primary);margin-bottom:14px;">
            <i class="fa-solid fa-user-plus" style="color:var(--gold);margin-right:7px;"></i>
            Monthly Joining Trend (12 months)
        </div>
        <canvas id="joiningChart" height="160"></canvas>
    </div>
</div>

{{-- Employee Table --}}
<div class="card card-gold" style="overflow:hidden;">
    <div style="padding:14px 20px;border-bottom:1px solid var(--border-light);
                display:flex;align-items:center;justify-content:space-between;">
        <div style="font-family:'Space Grotesk',sans-serif;font-size:14px;font-weight:600;
                    color:var(--text-primary);">
            <i class="fa-solid fa-users" style="color:var(--gold);margin-right:7px;"></i>
            Employee Directory ({{ $employees->count() }})
        </div>
    </div>
    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="background:#F7F9FC;border-bottom:1px solid var(--border-light);">
                @foreach(['Employee','Department','Designation','Type','Status',
                          'Joined','Tenure'] as $h)
                <th style="padding:10px 14px;text-align:left;font-size:10px;color:var(--text-muted);
                           letter-spacing:.7px;font-weight:600;text-transform:uppercase;">{{ $h }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($employees as $emp)
            <tr class="table-row">
                <td style="padding:10px 14px;">
                    <div style="display:flex;align-items:center;gap:9px;">
                        <img src="{{ $emp->avatar_url }}"
                             style="width:30px;height:30px;border-radius:50%;
                                    object-fit:cover;border:2px solid var(--gold-mid);">
                        <div>
                            <a href="{{ route('employees.show', $emp) }}"
                               style="font-size:12px;color:var(--text-primary);font-weight:500;
                                      text-decoration:none;">{{ $emp->full_name }}</a>
                            <div style="font-size:10px;color:var(--gold-dark);">
                                {{ $emp->employee_id }}
                            </div>
                        </div>
                    </div>
                </td>
                <td style="padding:10px 14px;font-size:12px;color:var(--text-secondary);">
                    {{ $emp->department?->name ?? '—' }}
                </td>
                <td style="padding:10px 14px;font-size:12px;color:var(--text-secondary);">
                    {{ $emp->designation?->title ?? '—' }}
                </td>
                <td style="padding:10px 14px;">
                    <span style="font-size:11px;background:var(--gold-faint);color:var(--gold-dark);
                                 border:1px solid var(--gold-mid);border-radius:20px;padding:2px 8px;">
                        {{ ucfirst(str_replace('_',' ',$emp->employment_type)) }}
                    </span>
                </td>
                <td style="padding:10px 14px;">
                    @php
                        $statusColors = [
                            'active'     => ['bg'=>'var(--success-bg)','color'=>'var(--success)','border'=>'var(--success-border)'],
                            'probation'  => ['bg'=>'var(--warning-bg)','color'=>'var(--warning)','border'=>'var(--warning-border)'],
                            'on_notice'  => ['bg'=>'var(--danger-bg)','color'=>'var(--danger)','border'=>'var(--danger-border)'],
                            'terminated' => ['bg'=>'#F7FAFC','color'=>'#718096','border'=>'#E2E8F0'],
                            'resigned'   => ['bg'=>'#F7FAFC','color'=>'#718096','border'=>'#E2E8F0'],
                        ];
                        $sc = $statusColors[$emp->employment_status] ?? $statusColors['terminated'];
                    @endphp
                    <span class="badge"
                          style="background:{{ $sc['bg'] }};color:{{ $sc['color'] }};
                                 border:1px solid {{ $sc['border'] }};">
                        {{ ucfirst(str_replace('_',' ',$emp->employment_status)) }}
                    </span>
                </td>
                <td style="padding:10px 14px;font-size:12px;color:var(--text-secondary);">
                    {{ $emp->joining_date?->format('d M Y') ?? '—' }}
                </td>
                <td style="padding:10px 14px;font-size:12px;color:var(--text-secondary);">
                    @if($emp->joining_date)
                    {{ $emp->joining_date->diffForHumans(null, true, true, 2) }}
                    @else —
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7"
                    style="padding:40px;text-align:center;color:var(--text-light);font-size:13px;">
                    No employees found.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
const chartDefaults = {
    plugins: {
        legend: { labels: { color:'#718096', font:{ family:'Sora', size:11 }, boxWidth:10, padding:8 } },
        tooltip: { backgroundColor:'#1C2331', titleColor:'#F0D080', bodyColor:'#94A3B8' }
    }
};

// Dept chart
const deptData = @json($headcountByDept->map(fn($d) => ['name' => $d->name, 'count' => $d->active_count]));
new Chart(document.getElementById('deptChart'), {
    type: 'bar',
    data: {
        labels: deptData.map(d => d.name),
        datasets: [{ label: 'Employees', data: deptData.map(d => d.count),
            backgroundColor: 'rgba(196,154,60,.2)', borderColor: '#C49A3C',
            borderWidth: 2, borderRadius: 4 }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        plugins: { ...chartDefaults.plugins, legend: { display: false } },
        scales: {
            x: { grid: { color:'#F0EBD8' }, ticks: { color:'#718096', font:{ size:11 } } },
            y: { grid: { display:false }, ticks: { color:'#718096', font:{ size:11 } } }
        }
    }
});

// Gender chart
const gData = @json($byGender);
new Chart(document.getElementById('genderChart'), {
    type: 'doughnut',
    data: {
        labels: Object.keys(gData).map(k => k.charAt(0).toUpperCase() + k.slice(1)),
        datasets: [{ data: Object.values(gData),
            backgroundColor: ['#2B6CB0','#C49A3C','#6B46C1'],
            borderColor: '#FFFFFF', borderWidth: 2 }]
    },
    options: { cutout:'65%', ...chartDefaults }
});

// Age chart
const ageData = @json($ageGroups);
new Chart(document.getElementById('ageChart'), {
    type: 'doughnut',
    data: {
        labels: Object.keys(ageData),
        datasets: [{ data: Object.values(ageData),
            backgroundColor: ['#2D7A4F','#2B6CB0','#C49A3C','#B7791F','#C53030'],
            borderColor: '#FFFFFF', borderWidth: 2 }]
    },
    options: { cutout:'65%', ...chartDefaults }
});

// Joining trend
const jData = @json($joiningTrend);
new Chart(document.getElementById('joiningChart'), {
    type: 'line',
    data: {
        labels: jData.map(d => d.month),
        datasets: [{ label: 'New Joiners', data: jData.map(d => d.count),
            borderColor: '#C49A3C', backgroundColor: 'rgba(196,154,60,.1)',
            borderWidth: 2, pointRadius: 4, tension: 0.4, fill: true }]
    },
    options: {
        responsive: true,
        plugins: { ...chartDefaults.plugins, legend: { display:false } },
        scales: {
            x: { grid:{ color:'#F0EBD8' }, ticks:{ color:'#718096', font:{ size:10 } } },
            y: { grid:{ color:'#F0EBD8' }, ticks:{ color:'#718096', font:{ size:11 }, stepSize:1 } }
        }
    }
});
</script>
@endpush
@endsection