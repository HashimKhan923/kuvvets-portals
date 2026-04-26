@extends('layouts.app')
@section('title', 'KPI Library')
@section('page-title', 'KPI Library')
@section('breadcrumb', 'Performance · KPI Library')

@section('content')

<div style="display:grid;grid-template-columns:1fr 360px;gap:20px;align-items:start;">

    {{-- KPI Cards --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:14px;align-content:start;">
        @forelse($kpis as $kpi)
        @php $catBadge = $kpi->category_badge; @endphp
        <div class="card" style="transition:transform .2s,border-color .2s;"
             onmouseover="this.style.transform='translateY(-2px)';this.style.borderColor='var(--accent-border)'"
             onmouseout="this.style.transform='';this.style.borderColor='var(--border)'">

            <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:10px;">
                <div style="flex:1;">
                    <div style="font-size:14px;font-weight:700;color:var(--text-primary);margin-bottom:5px;">
                        {{ $kpi->title }}
                    </div>
                    <div style="display:flex;gap:5px;flex-wrap:wrap;">
                        <span class="badge" style="background:{{ $catBadge['bg'] }};color:{{ $catBadge['color'] }};border:1px solid {{ $catBadge['border'] }};font-size:10px;">
                            {{ ucfirst($kpi->category) }}
                        </span>
                        <span class="badge badge-muted" style="font-size:10px;">
                            {{ ucfirst($kpi->measurement_type) }}
                        </span>
                    </div>
                </div>
                <div class="detail-block" style="text-align:center;min-width:60px;margin-left:10px;">
                    <div style="font-size:18px;font-weight:700;color:var(--accent);">
                        {{ $kpi->weight }}%
                    </div>
                    <div class="detail-block-label" style="margin-bottom:0;">weight</div>
                </div>
            </div>

            @if($kpi->description)
            <div style="font-size:12px;color:var(--text-secondary);margin-bottom:10px;line-height:1.5;">
                {{ $kpi->description }}
            </div>
            @endif

            <div style="display:flex;gap:12px;font-size:11px;color:var(--text-muted);
                        padding-top:10px;border-top:1px solid var(--border);flex-wrap:wrap;">
                @if($kpi->target_value)
                <span>
                    <i class="fa-solid fa-bullseye" style="font-size:9px;color:var(--accent);margin-right:3px;"></i>
                    Target: {{ $kpi->target_value }} {{ $kpi->unit }}
                </span>
                @endif
                <span>
                    <i class="fa-solid fa-link" style="font-size:9px;color:var(--accent);margin-right:3px;"></i>
                    {{ $kpi->goals_count }} goals
                </span>
                @if($kpi->department)
                <span>
                    <i class="fa-solid fa-sitemap" style="font-size:9px;color:var(--accent);margin-right:3px;"></i>
                    {{ $kpi->department->name }}
                </span>
                @endif
            </div>
        </div>
        @empty
        <div class="card" style="grid-column:1/-1;">
            <div class="empty-state">
                <i class="fa-solid fa-bullseye"></i>
                No KPIs defined yet.
            </div>
        </div>
        @endforelse
    </div>

    {{-- Create KPI Form --}}
    <div class="card" style="position:sticky;top:0;">
        <div class="form-section">
            <i class="fa-solid fa-plus-circle"></i> New KPI
        </div>
        <form method="POST" action="{{ route('performance.kpis.store') }}">
            @csrf
            <div style="display:flex;flex-direction:column;gap:11px;">
                <div>
                    <label class="form-label">Title <span style="color:var(--red);">*</span></label>
                    <input type="text" name="title" required
                           placeholder="e.g. Monthly Shipments Processed"
                           class="form-input">
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                    <div>
                        <label class="form-label">Category <span style="color:var(--red);">*</span></label>
                        <select name="category" required class="form-select">
                            @foreach(['productivity'=>'Productivity','quality'=>'Quality','attendance'=>'Attendance','customer'=>'Customer','financial'=>'Financial','learning'=>'Learning','leadership'=>'Leadership','other'=>'Other'] as $v => $l)
                            <option value="{{ $v }}">{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Measurement</label>
                        <select name="measurement_type" class="form-select">
                            <option value="number">Number</option>
                            <option value="percentage">Percentage</option>
                            <option value="boolean">Yes/No</option>
                            <option value="rating">Rating</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Target Value</label>
                        <input type="number" name="target_value" placeholder="100" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Unit</label>
                        <input type="text" name="unit" placeholder="shipments / %" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Weight (%)</label>
                        <input type="number" name="weight" value="10" min="1" max="100" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Department</label>
                        <select name="department_id" class="form-select">
                            <option value="">All Departments</option>
                            @foreach($departments as $d)
                            <option value="{{ $d->id }}">{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="form-label">Description</label>
                    <textarea name="description" rows="2" class="form-textarea"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-plus"></i> Create KPI
                </button>
            </div>
        </form>
    </div>

</div>
@endsection