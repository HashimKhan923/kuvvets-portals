@extends('layouts.app')
@section('title', 'Locations')

@section('content')
<div class="page-wrapper">

    {{-- Page header --}}
    <div class="page-head">
        <div>
            <h1 class="page-title">Locations</h1>
            <p class="page-sub">Manage warehouses, offices, and check-in points</p>
        </div>
        @can('locations.manage')
            <a href="{{ route('locations.create') }}" class="btn btn-primary">
                <i class="fa-solid fa-plus"></i> New Location
            </a>
        @endcan
    </div>

    {{-- Stats --}}
    <div class="stats-grid-4">
        <div class="stat-card">
            <div class="stat-ico-wrap" style="background:rgba(194,83,27,.12);color:#C2531B;"><i class="fa-solid fa-map-location-dot"></i></div>
            <div>
                <div class="stat-val">{{ $stats['total'] }}</div>
                <div class="stat-lbl">Total locations</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-ico-wrap" style="background:rgba(22,163,74,.12);color:#16A34A;"><i class="fa-solid fa-circle-check"></i></div>
            <div>
                <div class="stat-val">{{ $stats['active'] }}</div>
                <div class="stat-lbl">Active</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-ico-wrap" style="background:rgba(37,99,235,.12);color:#2563EB;"><i class="fa-solid fa-warehouse"></i></div>
            <div>
                <div class="stat-val">{{ $stats['warehouse'] }}</div>
                <div class="stat-lbl">Warehouses</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-ico-wrap" style="background:rgba(245,158,11,.12);color:#F59E0B;"><i class="fa-solid fa-building"></i></div>
            <div>
                <div class="stat-val">{{ $stats['office'] }}</div>
                <div class="stat-lbl">Offices</div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="filter-bar">
        <div class="filter-search">
            <i class="fa-solid fa-search"></i>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, code, city...">
        </div>
        <select name="type" onchange="this.form.submit()">
            <option value="">All types</option>
            @foreach(['warehouse'=>'Warehouse','office'=>'Office','site'=>'Site','branch'=>'Branch','other'=>'Other'] as $v=>$l)
                <option value="{{ $v }}" {{ request('type')===$v?'selected':'' }}>{{ $l }}</option>
            @endforeach
        </select>
        <select name="status" onchange="this.form.submit()">
            <option value="">All status</option>
            <option value="active" {{ request('status')==='active'?'selected':'' }}>Active</option>
            <option value="inactive" {{ request('status')==='inactive'?'selected':'' }}>Inactive</option>
        </select>
        <button type="submit" class="btn btn-secondary">Filter</button>
    </form>

    {{-- Grid --}}
    @if($locations->count())
        <div class="loc-grid">
            @foreach($locations as $loc)
                <a href="{{ route('locations.show', $loc) }}" class="loc-card">
                    <div class="loc-card-hd">
                        <div class="loc-card-ico" style="background: {{ match($loc->type) {
                            'warehouse' => 'rgba(37,99,235,.12);color:#2563EB',
                            'office' => 'rgba(245,158,11,.12);color:#F59E0B',
                            'site' => 'rgba(194,83,27,.12);color:#C2531B',
                            'branch' => 'rgba(124,58,237,.12);color:#7C3AED',
                            default => 'rgba(107,83,71,.12);color:#6B5347',
                        } }};">
                            <i class="fa-solid {{ $loc->typeIcon() }}"></i>
                        </div>
                        @if(!$loc->is_active)
                            <span class="badge badge-gray">Inactive</span>
                        @endif
                    </div>
                    <div class="loc-card-name">{{ $loc->name }}</div>
                    <div class="loc-card-code">{{ $loc->code }}</div>
                    <div class="loc-card-meta">
                        <div><i class="fa-solid fa-location-dot"></i> {{ $loc->city ?? '—' }}, {{ $loc->province ?? '' }}</div>
                        <div><i class="fa-solid fa-circle-nodes"></i> {{ $loc->radius_meters }}m radius</div>
                        <div><i class="fa-solid fa-users"></i> {{ $loc->employees_count }} employee(s)</div>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="pagination-wrap">{{ $locations->links() }}</div>
    @else
        <div class="empty-state">
            <i class="fa-solid fa-map-location-dot"></i>
            <div class="empty-title">No locations yet</div>
            <div class="empty-sub">Create your first location to enable GPS-verified attendance.</div>
            @can('locations.manage')
                <a href="{{ route('locations.create') }}" class="btn btn-primary">
                    <i class="fa-solid fa-plus"></i> Create Location
                </a>
            @endcan
        </div>
    @endif
</div>

<style>
    .page-wrapper { padding: 22px 28px; }
    .page-head { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 22px; }
    .page-title { font-family: 'Space Grotesk', sans-serif; font-size: 22px; font-weight: 700; color: var(--text); }
    .page-sub { font-size: 12.5px; color: var(--muted); margin-top: 3px; }

    .stats-grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 20px; }
    @media (max-width:780px) { .stats-grid-4 { grid-template-columns: repeat(2, 1fr); } }
    .stat-card { background: var(--card); border: 1px solid var(--border); border-radius: 14px; padding: 16px; display: flex; gap: 14px; align-items: center; }
    .stat-ico-wrap { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; }
    .stat-val { font-family: 'Space Grotesk', sans-serif; font-size: 22px; font-weight: 700; line-height: 1; }
    .stat-lbl { font-size: 11px; color: var(--muted); text-transform: uppercase; letter-spacing: .5px; font-weight: 600; margin-top: 3px; }

    .filter-bar { display: flex; gap: 10px; margin-bottom: 18px; flex-wrap: wrap; }
    .filter-search { position: relative; flex: 1; min-width: 240px; }
    .filter-search i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--muted); font-size: 13px; }
    .filter-search input { width: 100%; height: 40px; padding: 0 14px 0 38px; background: var(--card); border: 1px solid var(--border); border-radius: 10px; font: inherit; font-size: 13px; }
    .filter-bar select { height: 40px; padding: 0 34px 0 12px; background: var(--card); border: 1px solid var(--border); border-radius: 10px; font: inherit; font-size: 13px; cursor: pointer; appearance: none; background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236B5347' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 12px center; }

    .loc-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 14px; }
    .loc-card { background: var(--card); border: 1px solid var(--border); border-radius: 14px; padding: 18px; text-decoration: none; color: inherit; display: block; transition: all .18s; }
    .loc-card:hover { border-color: var(--accent, #C2531B); transform: translateY(-2px); box-shadow: 0 10px 24px rgba(45,31,20,.08); }
    .loc-card-hd { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; }
    .loc-card-ico { width: 42px; height: 42px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 16px; }
    .loc-card-name { font-family: 'Space Grotesk', sans-serif; font-size: 15px; font-weight: 700; color: var(--text); }
    .loc-card-code { font-size: 11px; color: var(--muted); letter-spacing: .5px; font-weight: 600; margin-top: 2px; }
    .loc-card-meta { margin-top: 12px; font-size: 11.5px; color: var(--muted); display: flex; flex-direction: column; gap: 4px; }
    .loc-card-meta i { width: 14px; color: var(--accent, #C2531B); }

    .empty-state { text-align: center; padding: 70px 20px; background: var(--card); border: 1px solid var(--border); border-radius: 16px; }
    .empty-state i { font-size: 44px; color: var(--accent, #C2531B); opacity: .35; margin-bottom: 14px; display: block; }
    .empty-title { font-size: 16px; font-weight: 700; color: var(--text); margin-bottom: 4px; }
    .empty-sub { font-size: 12.5px; color: var(--muted); margin-bottom: 16px; }

    .pagination-wrap { margin-top: 20px; display: flex; justify-content: center; }
</style>
@endsection