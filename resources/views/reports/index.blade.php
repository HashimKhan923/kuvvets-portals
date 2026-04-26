@extends('layouts.app')
@section('title','Reports Center')
@section('page-title','Reports Center')
@section('breadcrumb','Reports · Overview')

@section('content')

{{-- Overview Stats --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:28px;">
    @foreach([
        ['Active Employees', number_format($overview['employees']),  'fa-users',              '#2D7A4F','#F0FBF4','#B8E4CA'],
        ['Departments',      $overview['departments'],               'fa-sitemap',            '#2B6CB0','#EBF8FF','#BEE3F8'],
        ['Payroll YTD',      'PKR '.number_format($overview['payroll_ytd']), 'fa-money-check','#C49A3C','#FBF5E6','#E8D5A3'],
        ['Pending Leaves',   $overview['open_leaves'],              'fa-calendar-xmark',     '#C53030','#FFF5F5','#FEB2B2'],
        ['Total Assets',     $overview['assets'],                   'fa-boxes-stacked',      '#B7791F','#FFFBEB','#F6E05E'],
        ['Documents',        $overview['documents'],                'fa-file-lines',         '#6B46C1','#FAF5FF','#D6BCFA'],
    ] as [$label,$val,$icon,$color,$bg,$border])
    <div class="stat-card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
            <span style="font-size:10px;font-weight:600;color:var(--text-muted);
                         letter-spacing:.7px;text-transform:uppercase;">{{ $label }}</span>
            <div style="width:34px;height:34px;background:{{ $bg }};border:1px solid {{ $border }};
                        border-radius:8px;display:flex;align-items:center;justify-content:center;">
                <i class="fa-solid {{ $icon }}" style="font-size:13px;color:{{ $color }};"></i>
            </div>
        </div>
        <div style="font-size:24px;font-weight:700;color:var(--text-primary);
                    font-family:'Space Grotesk',sans-serif;">{{ $val }}</div>
    </div>
    @endforeach
</div>

{{-- Report Cards --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:18px;">

    @php
    $reports = [
        [
            'title'   => 'Workforce Report',
            'desc'    => 'Headcount by department, gender breakdown, age distribution, tenure analysis and joining trends.',
            'icon'    => 'fa-users',
            'color'   => '#2D7A4F',
            'bg'      => '#F0FBF4',
            'border'  => '#B8E4CA',
            'url'     => route('reports.workforce'),
            'metrics' => ['Headcount', 'Gender Split', 'Age Groups', 'Tenure', 'Joining Trend'],
            'export'  => route('reports.export.employees'),
        ],
        [
            'title'   => 'Attendance Report',
            'desc'    => 'Monthly attendance analysis, daily trends, late arrivals, overtime hours and per-employee breakdown.',
            'icon'    => 'fa-clock',
            'color'   => '#2B6CB0',
            'bg'      => '#EBF8FF',
            'border'  => '#BEE3F8',
            'url'     => route('reports.attendance'),
            'metrics' => ['Daily Trend', 'Attendance %', 'Late Arrivals', 'Overtime', 'Absent Days'],
            'export'  => null,
        ],
        [
            'title'   => 'Leave Report',
            'desc'    => 'Annual leave analysis by type, monthly trends, department breakdown and per-employee leave consumption.',
            'icon'    => 'fa-calendar-check',
            'color'   => '#B7791F',
            'bg'      => '#FFFBEB',
            'border'  => '#F6E05E',
            'url'     => route('reports.leave'),
            'metrics' => ['By Leave Type', 'Monthly Trend', 'Days Consumed', 'Pending', 'Rejected'],
            'export'  => null,
        ],
        [
            'title'   => 'Payroll Report',
            'desc'    => 'Annual payroll summary with gross, net, FBR tax, EOBI contributions and cost to company analysis.',
            'icon'    => 'fa-money-check-dollar',
            'color'   => '#C49A3C',
            'bg'      => '#FBF5E6',
            'border'  => '#E8D5A3',
            'url'     => route('payroll.report'),
            'metrics' => ['Gross vs Net', 'Tax (FBR)', 'EOBI', 'Bank Export', 'Cost to Company'],
            'export'  => null,
        ],
        [
            'title'   => 'Performance Report',
            'desc'    => 'Appraisal scores, ratings distribution, KPI achievements, goal completion and increment recommendations.',
            'icon'    => 'fa-chart-line',
            'color'   => '#6B46C1',
            'bg'      => '#FAF5FF',
            'border'  => '#D6BCFA',
            'url'     => route('performance.report'),
            'metrics' => ['Score Rankings', 'Ratings', 'Goals', 'Increments', 'Promotions'],
            'export'  => null,
        ],
        [
            'title'   => 'Training Report',
            'desc'    => 'Training completion rates, certification tracking, skill matrix analysis and per-employee training hours.',
            'icon'    => 'fa-graduation-cap',
            'color'   => '#2C7A7B',
            'bg'      => '#E6FFFA',
            'border'  => '#81E6D9',
            'url'     => route('training.report'),
            'metrics' => ['Completion Rate', 'Hours', 'Certifications', 'Skill Matrix', 'Cost'],
            'export'  => null,
        ],
        [
            'title'   => 'Asset Report',
            'desc'    => 'Asset register with current values, depreciation analysis, maintenance costs and rental income/expense.',
            'icon'    => 'fa-boxes-stacked',
            'color'   => '#C53030',
            'bg'      => '#FFF5F5',
            'border'  => '#FEB2B2',
            'url'     => route('assets.report'),
            'metrics' => ['Asset Value', 'Depreciation', 'Maintenance Cost', 'Rentals', 'Status'],
            'export'  => null,
        ],
        [
            'title'   => 'Payroll Compliance',
            'desc'    => 'FBR withholding tax summary, EOBI contributions, PESSI deductions and statutory compliance overview.',
            'icon'    => 'fa-file-shield',
            'color'   => '#276749',
            'bg'      => '#F0FFF4',
            'border'  => '#9AE6B4',
            'url'     => route('payroll.tax-calculator'),
            'metrics' => ['WHT Summary', 'EOBI', 'PESSI', 'FBR Slabs', 'Compliance'],
            'export'  => null,
        ],
        [
            'title'   => 'Document Report',
            'desc'    => 'Document inventory, expiry tracking, access level breakdown and category-wise distribution.',
            'icon'    => 'fa-folder-open',
            'color'   => '#8B6914',
            'bg'      => '#FBF5E6',
            'border'  => '#E8D5A3',
            'url'     => route('documents.index'),
            'metrics' => ['Expiring Docs', 'By Category', 'By Type', 'Storage', 'Access Levels'],
            'export'  => null,
        ],
    ];
    @endphp

    @foreach($reports as $report)
    <div class="card card-gold" style="padding:24px;display:flex;flex-direction:column;
                transition:all .25s;cursor:pointer;"
         onclick="window.location='{{ $report['url'] }}'"
         onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 8px 24px rgba(196,154,60,.15)'"
         onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='none'">

        {{-- Icon + Title --}}
        <div style="display:flex;align-items:flex-start;gap:14px;margin-bottom:14px;">
            <div style="width:48px;height:48px;flex-shrink:0;border-radius:12px;
                        background:{{ $report['bg'] }};border:1px solid {{ $report['border'] }};
                        display:flex;align-items:center;justify-content:center;">
                <i class="fa-solid {{ $report['icon'] }}"
                   style="font-size:20px;color:{{ $report['color'] }};"></i>
            </div>
            <div>
                <div style="font-family:'Space Grotesk',sans-serif;font-size:15px;font-weight:700;
                            color:var(--text-primary);margin-bottom:4px;">
                    {{ $report['title'] }}
                </div>
                <div style="font-size:12px;color:var(--text-secondary);line-height:1.5;">
                    {{ $report['desc'] }}
                </div>
            </div>
        </div>

        {{-- Metric Tags --}}
        <div style="display:flex;flex-wrap:wrap;gap:5px;margin-bottom:16px;flex:1;">
            @foreach($report['metrics'] as $metric)
            <span style="font-size:10px;background:{{ $report['bg'] }};
                         color:{{ $report['color'] }};border:1px solid {{ $report['border'] }};
                         border-radius:20px;padding:2px 9px;font-weight:500;">
                {{ $metric }}
            </span>
            @endforeach
        </div>

        {{-- Actions --}}
        <div style="display:flex;gap:8px;margin-top:auto;">
            <a href="{{ $report['url'] }}"
               style="flex:1;padding:9px;background:{{ $report['bg'] }};
                      border:1px solid {{ $report['border'] }};border-radius:8px;
                      color:{{ $report['color'] }};font-size:12px;font-weight:600;
                      text-decoration:none;text-align:center;transition:opacity .2s;"
               onclick="event.stopPropagation()"
               onmouseover="this.style.opacity='.8'" onmouseout="this.style.opacity='1'">
                <i class="fa-solid fa-chart-bar" style="margin-right:5px;"></i>View Report
            </a>
            @if($report['export'])
            <a href="{{ $report['export'] }}"
               style="padding:9px 14px;background:var(--white);border:1px solid var(--border);
                      border-radius:8px;color:var(--text-secondary);font-size:12px;
                      text-decoration:none;display:flex;align-items:center;gap:5px;
                      transition:border-color .15s;"
               onclick="event.stopPropagation()"
               onmouseover="this.style.borderColor='var(--gold)'"
               onmouseout="this.style.borderColor='var(--border)'">
                <i class="fa-solid fa-download" style="font-size:11px;"></i> CSV
            </a>
            @endif
        </div>
    </div>
    @endforeach
</div>
@endsection