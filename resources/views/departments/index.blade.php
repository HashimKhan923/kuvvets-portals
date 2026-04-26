@extends('layouts.app')
@section('title', 'Departments')
@section('page-title', 'Departments')
@section('breadcrumb', 'Workforce · Departments')

@section('content')

<div style="display:grid;grid-template-columns:1fr 360px;gap:20px;align-items:start;">

    {{-- Departments Table --}}
    <div class="card card-flush">

        <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 20px;border-bottom:1px solid var(--border);">
            <div class="card-title" style="margin-bottom:0;">
                <i class="fa-solid fa-sitemap"></i>
                All Departments ({{ $departments->count() }})
            </div>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Department</th>
                    <th>Head of Dept.</th>
                    <th>Employees</th>
                    <th>Designations</th>
                    <th>Status</th>
                    <th class="center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($departments as $dept)
                <tr>
                    <td>
                        <div style="font-size:13px;font-weight:600;color:var(--text-primary);">
                            {{ $dept->name }}
                        </div>
                        @if($dept->description)
                        <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">
                            {{ Str::limit($dept->description, 60) }}
                        </div>
                        @endif
                    </td>
                    <td class="muted">{{ $dept->headOfDepartment?->full_name ?? '—' }}</td>
                    <td>
                        <span class="badge badge-accent">
                            {{ $dept->employees_count ?? $dept->employees->count() }}
                        </span>
                    </td>
                    <td class="muted">{{ $dept->designations->count() }}</td>
                    <td>
                        <span class="badge {{ $dept->is_active ? 'badge-green' : 'badge-muted' }}">
                            {{ $dept->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="center">
                        <div style="display:flex;align-items:center;justify-content:center;gap:6px;">
                            <a href="{{ route('departments.show', $dept) }}"
                               class="action-btn" title="View">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            <button type="button" class="action-btn" title="Edit"
                                    onclick="openEditDept(
                                        {{ $dept->id }},
                                        '{{ addslashes($dept->name) }}',
                                        '{{ addslashes($dept->description ?? '') }}',
                                        {{ $dept->head_of_department_id ?? 'null' }}
                                    )">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <form method="POST" action="{{ route('departments.toggle', $dept) }}">
                                @csrf
                                <button type="submit"
                                        class="action-btn {{ $dept->is_active ? 'danger' : 'success' }}"
                                        title="{{ $dept->is_active ? 'Deactivate' : 'Activate' }}">
                                    <i class="fa-solid fa-{{ $dept->is_active ? 'pause' : 'play' }}"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <i class="fa-solid fa-sitemap"></i>
                            No departments yet. Create your first one.
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

    </div>

    {{-- Right Column --}}
    <div>

        {{-- New Department Form --}}
        <div class="card" style="margin-bottom:16px;">
            <div class="form-section">
                <i class="fa-solid fa-plus-circle"></i> New Department
            </div>
            <form method="POST" action="{{ route('departments.store') }}">
                @csrf
                <div style="display:flex;flex-direction:column;gap:12px;">
                    <div>
                        <label class="form-label">Name <span style="color:var(--red);">*</span></label>
                        <input type="text" name="name" required
                               placeholder="e.g. Engineering" class="form-input">
                        @error('name')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                    <div>
                        <label class="form-label">Head of Department</label>
                        <select name="head_of_department_id" class="form-select">
                            <option value="">Select HOD (optional)</option>
                            @if(!empty($employees) && $employees->count())
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Description</label>
                        <textarea name="description" rows="2"
                                  class="form-textarea"
                                  placeholder="Optional description"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-plus"></i> Create Department
                    </button>
                </div>
            </form>
        </div>

        {{-- Summary Cards --}}
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;">
            <div class="stat-card">
                <div class="stat-label">Total</div>
                <div class="stat-num">{{ $departments->count() }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Active</div>
                <div class="stat-num" style="color:var(--green);">
                    {{ $departments->where('is_active', true)->count() }}
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Employees</div>
                <div class="stat-num" style="color:var(--accent);">
                    {{ $departments->sum(fn($d) => $d->employees_count ?? $d->employees->count()) }}
                </div>
            </div>
        </div>

    </div>

</div>

{{-- Edit Department Modal --}}
<div id="editDeptModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-title">
            <i class="fa-solid fa-pen"></i> Edit Department
        </div>
        <form id="editDeptForm" method="POST">
            @csrf
            @method('PUT')
            <div style="display:flex;flex-direction:column;gap:12px;">
                <div>
                    <label class="form-label">Name <span style="color:var(--red);">*</span></label>
                    <input type="text" name="name" id="editDeptName" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Head of Department</label>
                    <select name="head_of_department_id" id="editDeptHod" class="form-select">
                        <option value="">Select HOD (optional)</option>
                        @if(!empty($employees) && $employees->count())
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div>
                    <label class="form-label">Description</label>
                    <textarea name="description" id="editDeptDesc" rows="2"
                              class="form-textarea"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                        onclick="document.getElementById('editDeptModal').classList.remove('open')">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openEditDept(id, name, desc, hod) {
    document.getElementById('editDeptForm').action = '/admin/departments/' + id;
    document.getElementById('editDeptName').value  = name;
    document.getElementById('editDeptDesc').value  = desc;
    var hodSelect = document.getElementById('editDeptHod');
    if (hod && hodSelect) hodSelect.value = hod;
    document.getElementById('editDeptModal').classList.add('open');
}

document.getElementById('editDeptModal').addEventListener('click', function(e) {
    if (e.target === this) this.classList.remove('open');
});
</script>
@endpush

@endsection