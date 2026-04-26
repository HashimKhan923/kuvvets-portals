@extends('layouts.app')
@section('title', $department->name)
@section('page-title', $department->name)
@section('breadcrumb', 'Departments · ' . $department->name)

@section('content')

<div style="display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start;">

    {{-- LEFT COLUMN --}}
    <div style="display:flex;flex-direction:column;gap:16px;">

        {{-- Department Info --}}
        <div class="card">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:20px;">
                <div style="display:flex;align-items:center;gap:14px;">
                    <div style="width:50px;height:50px;background:var(--accent-bg);border:1.5px solid var(--accent-border);
                                border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fa-solid fa-sitemap" style="font-size:20px;color:var(--accent);"></i>
                    </div>
                    <div>
                        <div style="font-size:18px;font-weight:700;color:var(--text-primary);">
                            {{ $department->name }}
                        </div>
                        <div style="display:flex;align-items:center;gap:8px;margin-top:5px;flex-wrap:wrap;">
                            @if($department->code)
                            <span style="font-size:11px;background:var(--bg-muted);color:var(--text-muted);
                                         border:1px solid var(--border);border-radius:4px;padding:1px 8px;">
                                {{ $department->code }}
                            </span>
                            @endif
                            <span class="badge {{ $department->is_active ? 'badge-green' : 'badge-red' }}">
                                {{ $department->is_active ? 'Active' : 'Inactive' }}
                            </span>
                            @if($department->parent)
                            <span style="font-size:11px;color:var(--text-muted);">
                                <i class="fa-solid fa-turn-up" style="font-size:10px;color:var(--accent);"></i>
                                Under:
                                <a href="{{ route('departments.show', $department->parent) }}"
                                   style="color:var(--accent);text-decoration:none;">
                                    {{ $department->parent->name }}
                                </a>
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
                <form method="POST" action="{{ route('departments.toggle', $department) }}">
                    @csrf
                    <button type="submit" class="btn btn-secondary btn-sm">
                        <i class="fa-solid fa-power-off"></i>
                        {{ $department->is_active ? 'Deactivate' : 'Activate' }}
                    </button>
                </form>
            </div>

            @if($department->description)
            <div class="note-block" style="margin-bottom:20px;">
                <div class="note-block-text">{{ $department->description }}</div>
            </div>
            @endif

            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;">
                @foreach([
                    ['Active Employees', $department->employees_count,         'accent'],
                    ['Sub-departments',  $department->children->count(),        'blue'],
                    ['Designations',     $department->designations->count(),    'purple'],
                ] as [$label, $value, $color])
                <div class="detail-block" style="text-align:center;">
                    <div style="font-size:28px;font-weight:700;color:var(--{{ $color }});margin-bottom:4px;">
                        {{ $value }}
                    </div>
                    <div class="detail-block-label" style="margin-bottom:0;">{{ $label }}</div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Sub-departments --}}
        @if($department->children->isNotEmpty())
        <div class="card">
            <div class="form-section">
                <i class="fa-solid fa-diagram-subtask"></i> Sub-departments
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:10px;">
                @foreach($department->children as $child)
                <a href="{{ route('departments.show', $child) }}"
                   style="background:var(--bg-muted);border:1px solid var(--border);border-radius:8px;
                          padding:14px;text-decoration:none;display:block;transition:border-color .2s;"
                   onmouseover="this.style.borderColor='var(--accent-border)'"
                   onmouseout="this.style.borderColor='var(--border)'">
                    <div style="font-size:13px;font-weight:600;color:var(--text-primary);margin-bottom:4px;">
                        {{ $child->name }}
                    </div>
                    <div style="font-size:11px;color:var(--accent);">
                        {{ $child->employees()->where('employment_status', 'active')->count() }} staff
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Employees Table --}}
        <div class="card card-flush">
            <div style="display:flex;align-items:center;justify-content:space-between;
                        padding:14px 20px;border-bottom:1px solid var(--border);">
                <div class="card-title" style="margin-bottom:0;">
                    <i class="fa-solid fa-users"></i>
                    Active Employees ({{ $department->employees->count() }})
                </div>
                <a href="{{ route('employees.create') }}"
                   style="font-size:11px;color:var(--accent);text-decoration:none;font-weight:500;">
                    <i class="fa-solid fa-plus"></i> Add Employee
                </a>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Designation</th>
                        <th>Manager</th>
                        <th>Joined</th>
                        <th>Type</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($department->employees as $emp)
                    <tr>
                        <td>
                            <div class="td-employee">
                                <img src="{{ $emp->avatar_url }}"
                                     class="avatar avatar-sm"
                                     alt="{{ $emp->full_name }}">
                                <div>
                                    <a href="{{ route('employees.show', $emp) }}"
                                       class="td-employee name">
                                        {{ $emp->full_name }}
                                    </a>
                                    <div class="td-employee id">{{ $emp->employee_id }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="muted">{{ $emp->designation?->title ?? '—' }}</td>
                        <td class="muted">{{ $emp->manager?->full_name ?? '—' }}</td>
                        <td class="muted">{{ $emp->joining_date?->format('d M Y') ?? '—' }}</td>
                        <td>
                            <span class="badge type-{{ $emp->employment_type }}">
                                {{ ucfirst(str_replace('_', ' ', $emp->employment_type)) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5">
                            <div class="empty-state" style="padding:28px;">
                                No active employees in this department.
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

    {{-- RIGHT COLUMN — Designations --}}
    <div style="position:sticky;top:0;display:flex;flex-direction:column;gap:14px;">

        {{-- Add Designation Form --}}
        <div class="card">
            <div class="form-section">
                <i class="fa-solid fa-plus-circle"></i> Add Designation
            </div>
            <form method="POST" action="{{ route('departments.designations.store', $department) }}">
                @csrf
                <div style="display:flex;flex-direction:column;gap:10px;">
                    <div>
                        <label class="form-label">Title <span style="color:var(--red);">*</span></label>
                        <input type="text" name="title" required
                               placeholder="e.g. Senior Engineer" class="form-input">
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                        <div>
                            <label class="form-label">Grade</label>
                            <input type="text" name="grade"
                                   placeholder="e.g. G-4" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Level <span style="color:var(--red);">*</span></label>
                            <select name="level" required class="form-select">
                                @foreach(['junior'=>'Junior','mid'=>'Mid','senior'=>'Senior','lead'=>'Lead','manager'=>'Manager','director'=>'Director','c_level'=>'C-Level'] as $v => $l)
                                    <option value="{{ $v }}">{{ $l }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                        <div>
                            <label class="form-label">Min Salary</label>
                            <input type="number" name="min_salary"
                                   placeholder="PKR" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Max Salary</label>
                            <input type="number" name="max_salary"
                                   placeholder="PKR" class="form-input">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-plus"></i> Add Designation
                    </button>
                </div>
            </form>
        </div>

        {{-- Designation List --}}
        <div class="card">
            <div class="form-section">
                <i class="fa-solid fa-id-badge"></i>
                Designations ({{ $department->designations->count() }})
            </div>

            @forelse($department->designations as $desig)
            <div style="background:var(--bg-muted);border:1px solid var(--border);border-radius:8px;
                        padding:12px;margin-bottom:8px;
                        display:flex;align-items:flex-start;justify-content:space-between;">
                <div>
                    <div style="font-size:13px;font-weight:600;color:var(--text-primary);">
                        {{ $desig->title }}
                    </div>
                    <div style="display:flex;align-items:center;gap:6px;margin-top:5px;flex-wrap:wrap;">
                        @if($desig->grade)
                        <span style="font-size:10px;background:var(--bg-card);color:var(--text-muted);
                                     border:1px solid var(--border);border-radius:3px;padding:1px 6px;">
                            {{ $desig->grade }}
                        </span>
                        @endif
                        @php
                        $levelBadge = match($desig->level) {
                            'c_level', 'director' => 'badge-red',
                            'manager', 'lead'     => 'badge-accent',
                            'senior'              => 'badge-blue',
                            'mid'                 => 'badge-purple',
                            default               => 'badge-muted',
                        };
                        @endphp
                        <span class="badge {{ $levelBadge }}" style="font-size:10px;">
                            {{ ucfirst(str_replace('_', ' ', $desig->level)) }}
                        </span>
                    </div>
                    @if($desig->min_salary || $desig->max_salary)
                    <div style="font-size:10px;color:var(--text-muted);margin-top:4px;">
                        PKR {{ number_format($desig->min_salary) }} – {{ number_format($desig->max_salary) }}
                    </div>
                    @endif
                    <div style="font-size:10px;color:var(--text-muted);margin-top:2px;">
                        {{ $desig->employees()->count() }} assigned
                    </div>
                </div>
                <form method="POST" action="{{ route('departments.designations.destroy', $desig) }}"
                      onsubmit="return confirm('Remove this designation?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="action-btn danger" title="Delete">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </form>
            </div>
            @empty
            <div class="empty-state" style="padding:24px;">
                <i class="fa-solid fa-id-badge"></i>
                No designations yet
            </div>
            @endforelse
        </div>

    </div>

</div>

@endsection