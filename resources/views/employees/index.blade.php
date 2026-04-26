@extends('layouts.app')
@section('title', 'Employees')
@section('page-title', 'Employee Management')
@section('breadcrumb', 'Workforce · All Employees')

@section('content')

<div class="stats-grid-4">
    @foreach([
        ['Active Staff',     $stats['total'],     'fa-users',          'green'],
        ['On Probation',     $stats['probation'],  'fa-hourglass-half', 'yellow'],
        ['Contractual',      $stats['contract'],   'fa-file-contract',  'blue'],
        ['Joined This Month',$stats['new_month'],  'fa-user-plus',      'accent'],
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

<div class="card card-sm mb-4">
    <form method="GET" action="{{ route('employees.index') }}" class="toolbar">
        <div class="toolbar-search">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, ID, CNIC…" class="form-input">
        </div>
        <select name="department" class="form-select" style="min-width:160px;">
            <option value="">All Departments</option>
            @foreach($departments as $dept)
                <option value="{{ $dept->id }}" {{ request('department') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
            @endforeach
        </select>
        <select name="status" class="form-select">
            <option value="">All Status</option>
            @foreach(['active'=>'Active','resigned'=>'Resigned','terminated'=>'Terminated','on_leave'=>'On Leave'] as $v=>$l)
                <option value="{{ $v }}" {{ request('status')==$v ? 'selected' : '' }}>{{ $l }}</option>
            @endforeach
        </select>
        <select name="type" class="form-select">
            <option value="">All Types</option>
            @foreach(['permanent'=>'Permanent','contract'=>'Contract','probationary'=>'Probationary','part_time'=>'Part Time','internship'=>'Internship','daily_wages'=>'Daily Wages'] as $v=>$l)
                <option value="{{ $v }}" {{ request('type')==$v ? 'selected' : '' }}>{{ $l }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-filter"></i> Filter</button>
        @if(request()->hasAny(['search','department','status','type']))
            <a href="{{ route('employees.index') }}" class="btn btn-secondary btn-sm"><i class="fa-solid fa-xmark"></i> Clear</a>
        @endif
        @can('employees.create')
        <div class="ml-auto">
            <a href="{{ route('employees.create') }}" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Add Employee</a>
        </div>
        @endcan
    </form>
</div>

<div class="card card-flush">
    <table class="data-table">
        <thead>
            <tr>
                <th>Employee</th><th>Department</th><th>Designation</th>
                <th>Joining Date</th><th>Type</th><th>Status</th><th class="center">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($employees as $emp)
            <tr>
                <td>
                    <div class="td-employee">
                        <img src="{{ $emp->avatar_url }}" class="avatar avatar-md" alt="{{ $emp->full_name }}">
                        <div>
                            <a href="{{ route('employees.show', $emp) }}" class="td-employee name">{{ $emp->full_name }}</a>
                            <div class="td-employee id">{{ $emp->employee_id }}</div>
                        </div>
                    </div>
                </td>
                <td class="muted">{{ $emp->department?->name ?? '—' }}</td>
                <td class="muted">{{ $emp->designation?->title ?? '—' }}</td>
                <td class="muted">{{ $emp->joining_date?->format('d M Y') ?? '—' }}</td>
                <td><span class="badge type-{{ $emp->employment_type }}">{{ ucfirst(str_replace('_',' ',$emp->employment_type)) }}</span></td>
                <td><span class="badge status-{{ $emp->employment_status }}">{{ ucfirst($emp->employment_status) }}</span></td>
                <td class="center">
                    <div class="flex items-center justify-center gap-1">
                        <a href="{{ route('employees.show', $emp) }}" class="action-btn" title="View"><i class="fa-solid fa-eye"></i></a>
                        @can('employees.edit')
                        <a href="{{ route('employees.edit', $emp) }}" class="action-btn" title="Edit"><i class="fa-solid fa-pen"></i></a>
                        @endcan
                        @can('employees.delete')
                        <form method="POST" action="{{ route('employees.destroy', $emp) }}" onsubmit="return confirm('Remove {{ $emp->full_name }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="action-btn danger" title="Delete"><i class="fa-solid fa-trash"></i></button>
                        </form>
                        @endcan
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="7"><div class="empty-state"><i class="fa-solid fa-users-slash"></i>No employees found. <a href="{{ route('employees.create') }}">Add the first one</a></div></td></tr>
            @endforelse
        </tbody>
    </table>

    @if($employees->hasPages())
    <div class="pagination">
        <span class="pagination-info">Showing {{ $employees->firstItem() }}–{{ $employees->lastItem() }} of {{ $employees->total() }} employees</span>
        <div class="pagination-btns">
            @if($employees->onFirstPage())
                <span class="page-btn disabled">← Prev</span>
            @else
                <a href="{{ $employees->previousPageUrl() }}" class="page-btn">← Prev</a>
            @endif
            @if($employees->hasMorePages())
                <a href="{{ $employees->nextPageUrl() }}" class="page-btn active">Next →</a>
            @else
                <span class="page-btn disabled">Next →</span>
            @endif
        </div>
    </div>
    @endif
</div>
@endsection