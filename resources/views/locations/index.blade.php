@extends('layouts.app')
@section('title', 'Locations')
@section('page-title', 'Locations')
@section('breadcrumb', 'Time & Leave · Locations')


@section('content')

<div class="stats-grid-4">
    @foreach([
        ['Total',     $stats['total'],     'fa-map-location-dot',          'accent'],
        ['Active',     $stats['active'] ,  'fa-circle-check', 'green'],
        ['Warehouses',      $stats['warehouse'],   'fa-warehouse',  'yellow'],
        ['Offices',$stats['office'],  'fa-building',      'blue'],
    ] as [$label,$val,$icon,$color])
    <div class="stat-card">
        <div class="flex items-center justify-between mb-2">
            <div class="stat-label">{{ $label }}</div>
            <div class="stat-icon stat-icon-{{ $color }}"><i class="fa-solid {{ $icon }}"></i></div>
        </div>
        <div class="stat-num">{{ $val }}</div>
    </div>
    @endforeach
</div>
 <!-- @can('locations.manage')
            <a href="{{ route('locations.create') }}" class="btn btn-primary">
                <i class="fa-solid fa-plus"></i> New Location
            </a>
        @endcan -->

<div class="card card-sm mb-4">
    <form method="GET" class="toolbar">
        <div class="toolbar-search">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, code, city..." class="form-input">
        </div>
        <select name="type" class="form-select" style="max-width:160px;">
            <option value="">All Types</option>
           @foreach(['warehouse'=>'Warehouse','office'=>'Office','site'=>'Site','branch'=>'Branch','other'=>'Other'] as $v=>$l)
                <option value="{{ $v }}" {{ request('type')===$v?'selected':'' }}>{{ $l }}</option>
            @endforeach
        </select>
        <select name="status" class="form-select" style="max-width:160px;">
            <option value="">All Status</option>
            
            <option value="active" {{ request('status')==='active'?'selected':'' }}>Active</option>
            <option value="inactive" {{ request('status')==='inactive'?'selected':'' }}>Inactive</option>
        </select>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-filter"></i> Filter</button>
        @can('locations.manage')
            <a href="{{ route('locations.create') }}" class="btn btn-primary">
                <i class="fa-solid fa-plus"></i> Add Location
            </a>
        @endcan
    </form>
</div>

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
          
        </div>
    @endif


<style>

   
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