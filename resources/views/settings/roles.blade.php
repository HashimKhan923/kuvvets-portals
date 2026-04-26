@extends('layouts.app')
@section('title', 'Roles & Permissions')
@section('page-title', 'Roles & Permissions')
@section('breadcrumb', 'Settings · Roles & Permissions')

@section('content')

<div style="display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start;">

    {{-- LEFT: Role Cards + Permission Matrix --}}
    <div style="display:flex;flex-direction:column;gap:16px;">

        {{-- Role Cards --}}
        @php
        $roleColors = [
            'super_admin'         => ['color'=>'var(--accent)',  'bg'=>'var(--accent-bg)',  'border'=>'var(--accent-border)'],
            'hr_manager'          => ['color'=>'var(--green)',   'bg'=>'var(--green-bg)',   'border'=>'var(--green-border)'],
            'payroll_manager'     => ['color'=>'var(--green)',   'bg'=>'var(--green-bg)',   'border'=>'var(--green-border)'],
            'department_manager'  => ['color'=>'var(--blue)',    'bg'=>'var(--blue-bg)',    'border'=>'var(--blue-border)'],
            'recruitment_officer' => ['color'=>'var(--purple)',  'bg'=>'var(--purple-bg)',  'border'=>'var(--purple-border)'],
            'employee'            => ['color'=>'var(--text-muted)','bg'=>'var(--bg-muted)','border'=>'var(--border)'],
        ];
        $roleDescs = [
            'super_admin'         => 'Full system access. Can manage all settings, users, and data.',
            'hr_manager'          => 'Manages employees, attendance, leave, recruitment and HR policies.',
            'payroll_manager'     => 'Processes payroll, manages salary structures and generates payslips.',
            'department_manager'  => 'Views and manages their own department employees and performance.',
            'recruitment_officer' => 'Manages job postings, applicants, interviews and offer letters.',
            'employee'            => 'Employee self-service access only. View own data.',
        ];
        $protected = ['super_admin','hr_manager','payroll_manager','department_manager','recruitment_officer','employee'];
        @endphp

        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:12px;">
            @foreach($roles as $role)
            @php
                $rc   = $roleColors[$role->name] ?? ['color'=>'var(--text-muted)','bg'=>'var(--bg-muted)','border'=>'var(--border)'];
                $desc = $roleDescs[$role->name]  ?? 'Custom role with assigned permissions.';
                $isProtected = in_array($role->name, $protected);
            @endphp
            <div class="card" style="transition:transform .2s,border-color .2s;"
                 onmouseover="this.style.transform='translateY(-2px)';this.style.borderColor='var(--accent-border)'"
                 onmouseout="this.style.transform='';this.style.borderColor='var(--border)'">

                <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:10px;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="width:38px;height:38px;background:{{ $rc['bg'] }};
                                    border:1px solid {{ $rc['border'] }};border-radius:9px;
                                    display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <i class="fa-solid fa-shield-halved" style="font-size:15px;color:{{ $rc['color'] }};"></i>
                        </div>
                        <div>
                            <div style="font-size:13px;font-weight:700;color:var(--text-primary);">
                                {{ ucwords(str_replace('_', ' ', $role->name)) }}
                            </div>
                            <div style="font-size:11px;color:var(--text-muted);">
                                {{ $role->users_count }} user(s) · {{ $role->permissions->count() }} permission(s)
                            </div>
                        </div>
                    </div>
                    <div style="display:flex;gap:5px;flex-shrink:0;">
                        <button onclick="openPermissionsModal(
                                    {{ $role->id }},
                                    '{{ addslashes(ucwords(str_replace('_', ' ', $role->name))) }}',
                                    {{ json_encode($role->permissions->pluck('name')) }}
                                )"
                                class="action-btn" title="Edit Permissions">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </button>
                        @if(!$isProtected)
                        <form method="POST"
                              action="{{ route('settings.roles.destroy', $role) }}"
                              onsubmit="return confirm('Delete role {{ $role->name }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="action-btn danger" title="Delete">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                        @else
                        <div class="action-btn" title="Built-in role — cannot be deleted"
                             style="cursor:default;opacity:.5;">
                            <i class="fa-solid fa-lock"></i>
                        </div>
                        @endif
                    </div>
                </div>

                <div style="font-size:11px;color:var(--text-secondary);line-height:1.5;margin-bottom:10px;">
                    {{ $desc }}
                </div>

                {{-- Permission Tags --}}
                @if($role->permissions->count())
                <div style="display:flex;flex-wrap:wrap;gap:4px;max-height:64px;overflow:hidden;">
                    @foreach($role->permissions->take(6) as $perm)
                    <span style="font-size:9px;background:{{ $rc['bg'] }};color:{{ $rc['color'] }};
                                 border:1px solid {{ $rc['border'] }};border-radius:20px;padding:1px 7px;">
                        {{ $perm->name }}
                    </span>
                    @endforeach
                    @if($role->permissions->count() > 6)
                    <span style="font-size:9px;background:var(--bg-muted);color:var(--text-muted);
                                 border:1px solid var(--border);border-radius:20px;padding:1px 7px;">
                        +{{ $role->permissions->count() - 6 }} more
                    </span>
                    @endif
                </div>
                @else
                <div style="font-size:11px;color:var(--text-muted);font-style:italic;">No permissions assigned</div>
                @endif
            </div>
            @endforeach
        </div>

        {{-- Permission Matrix --}}
        <div class="card card-flush">
            <div style="padding:14px 20px;border-bottom:1px solid var(--border);
                        display:flex;align-items:center;justify-content:space-between;">
                <div class="card-title" style="margin-bottom:0;">
                    <i class="fa-solid fa-table-cells"></i> Permission Matrix
                </div>
                <span style="font-size:11px;color:var(--text-muted);">
                    {{ $allPermissions->flatten()->count() }} total permissions
                </span>
            </div>
            <div style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;min-width:700px;">
                    <thead>
                        <tr style="background:var(--bg-muted);border-bottom:1px solid var(--border);">
                            <th style="padding:10px 14px;text-align:left;font-size:10px;color:var(--text-muted);
                                       letter-spacing:.7px;font-weight:700;text-transform:uppercase;
                                       min-width:180px;position:sticky;left:0;background:var(--bg-muted);z-index:1;">
                                Permission
                            </th>
                            @foreach($roles as $role)
                            @php $rc = $roleColors[$role->name] ?? ['color'=>'var(--text-muted)']; @endphp
                            <th style="padding:10px 8px;text-align:center;font-size:10px;
                                       color:{{ $rc['color'] }};font-weight:700;min-width:100px;">
                                {{ ucwords(str_replace('_', ' ', $role->name)) }}
                            </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($allPermissions as $module => $perms)
                        {{-- Module header --}}
                        <tr style="background:var(--accent-bg);border-bottom:1px solid var(--accent-border);">
                            <td colspan="{{ $roles->count() + 1 }}"
                                style="padding:7px 14px;font-size:10px;font-weight:700;
                                       color:var(--accent);letter-spacing:.7px;text-transform:uppercase;">
                                <i class="fa-solid fa-layer-group" style="margin-right:6px;font-size:10px;"></i>
                                {{ ucfirst($module) }}
                            </td>
                        </tr>
                        @foreach($perms as $perm)
                        <tr style="border-bottom:1px solid var(--border);">
                            <td style="padding:9px 14px;font-size:12px;color:var(--text-secondary);
                                       position:sticky;left:0;background:var(--bg-card);z-index:1;
                                       border-right:1px solid var(--border);">
                                {{ $perm->name }}
                            </td>
                            @foreach($roles as $role)
                            @php $hasIt = $role->permissions->contains('name', $perm->name); @endphp
                            <td style="padding:9px 8px;text-align:center;font-size:15px;">
                                {{ $hasIt ? '✅' : '—' }}
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    {{-- RIGHT: Create Role + Add Permission + List --}}
    <div style="display:flex;flex-direction:column;gap:16px;position:sticky;top:0;">

        {{-- Create Role --}}
        <div class="card">
            <div class="form-section"><i class="fa-solid fa-plus-circle"></i> Create New Role</div>
            <form method="POST" action="{{ route('settings.roles.store') }}">
                @csrf
                <div style="display:flex;flex-direction:column;gap:10px;">
                    <div>
                        <label class="form-label">Role Name <span style="color:var(--red);">*</span></label>
                        <input type="text" name="name" required placeholder="e.g. Finance Manager" class="form-input">
                        <div style="font-size:10px;color:var(--text-muted);margin-top:3px;">
                            Will be saved as: finance_manager
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
                        <i class="fa-solid fa-plus"></i> Create Role
                    </button>
                </div>
            </form>
        </div>

        {{-- Add Permission --}}
        <div class="card">
            <div class="form-section"><i class="fa-solid fa-key"></i> Add Permission</div>
            <form method="POST" action="{{ route('settings.permissions.store') }}">
                @csrf
                <div style="display:flex;flex-direction:column;gap:10px;">
                    <div>
                        <label class="form-label">Permission Name <span style="color:var(--red);">*</span></label>
                        <input type="text" name="name" required placeholder="e.g. view employees" class="form-input">
                        <div style="font-size:10px;color:var(--text-muted);margin-top:3px;">
                            Convention: "action module" e.g. "create payroll"
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
                        <i class="fa-solid fa-plus"></i> Add Permission
                    </button>
                </div>
            </form>
        </div>

        {{-- All Permissions List --}}
        <div class="card" style="max-height:400px;overflow-y:auto;">
            <div class="form-section" style="margin-bottom:10px;">
                <i class="fa-solid fa-list"></i>
                All Permissions ({{ $allPermissions->flatten()->count() }})
            </div>
            @foreach($allPermissions as $module => $perms)
            <div style="margin-bottom:10px;">
                <div class="section-label" style="margin-bottom:5px;">{{ ucfirst($module) }}</div>
                @foreach($perms as $perm)
                <div style="font-size:11px;color:var(--text-secondary);padding:3px 8px;
                            background:var(--bg-muted);border-radius:5px;margin-bottom:3px;">
                    {{ $perm->name }}
                </div>
                @endforeach
            </div>
            @endforeach
        </div>

    </div>
</div>

{{-- ═══ EDIT PERMISSIONS MODAL ═══════════════════════════════════════════════ --}}
<div id="permissionsModal" class="modal-overlay">
    <div class="modal-box" style="width:680px;max-height:88vh;overflow-y:auto;">

        <div class="modal-title">
            <i class="fa-solid fa-shield-halved"></i>
            Edit Permissions —
            <span id="modalRoleName" style="color:var(--accent);"></span>
        </div>

        {{-- Select All / None --}}
        <div style="display:flex;gap:8px;align-items:center;margin-bottom:16px;">
            <button type="button" class="btn btn-success btn-sm" onclick="selectAllPermissions(true)">
                <i class="fa-solid fa-check-double"></i> Select All
            </button>
            <button type="button" class="btn btn-danger btn-sm" onclick="selectAllPermissions(false)">
                <i class="fa-solid fa-xmark"></i> Deselect All
            </button>
            <span id="selectedCount" style="font-size:12px;color:var(--text-muted);margin-left:4px;"></span>
        </div>

        <form id="permissionsForm" method="POST">
            @csrf @method('POST')

            @foreach($allPermissions as $module => $perms)
            <div style="margin-bottom:16px;padding:14px;background:var(--bg-muted);
                        border-radius:10px;border:1px solid var(--border);">

                {{-- Module header --}}
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
                    <div style="font-size:11px;font-weight:700;color:var(--accent);
                                letter-spacing:.6px;text-transform:uppercase;">
                        <i class="fa-solid fa-layer-group" style="margin-right:5px;font-size:10px;"></i>
                        {{ ucfirst($module) }}
                        <span style="font-size:10px;color:var(--text-muted);font-weight:400;
                                     text-transform:none;margin-left:5px;">({{ $perms->count() }})</span>
                    </div>
                    <button type="button"
                            onclick="toggleModule('{{ $module }}')"
                            class="btn btn-secondary btn-xs">
                        Toggle All
                    </button>
                </div>

                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:6px;"
                     id="module-{{ $module }}">
                    @foreach($perms as $perm)
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;
                                  padding:7px 10px;background:var(--bg-card);
                                  border:1px solid var(--border);border-radius:7px;
                                  transition:border-color .15s;font-size:12px;color:var(--text-primary);"
                           onmouseover="this.style.borderColor='var(--accent-border)'"
                           onmouseout="this.style.borderColor='var(--border)'">
                        <input type="checkbox" name="permissions[]"
                               value="{{ $perm->name }}"
                               class="perm-checkbox module-{{ $module }}"
                               style="accent-color:var(--accent);width:14px;height:14px;"
                               onchange="updateSelectedCount()">
                        <span>{{ $perm->name }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            @endforeach

            <div style="position:sticky;bottom:0;background:var(--bg-card);
                        padding-top:14px;border-top:1px solid var(--border);">
                <div class="modal-footer" style="margin:0;">
                    <button type="button" class="btn btn-secondary" onclick="closePermissionsModal()">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-floppy-disk"></i> Save Permissions
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openPermissionsModal(roleId, roleName, currentPerms) {
    document.getElementById('permissionsForm').action =
        '{{ url("admin/settings/roles") }}/' + roleId + '/permissions';
    document.getElementById('modalRoleName').textContent = roleName;
    document.querySelectorAll('.perm-checkbox').forEach(function(cb) {
        cb.checked = currentPerms.indexOf(cb.value) !== -1;
    });
    updateSelectedCount();
    document.getElementById('permissionsModal').classList.add('open');
}

function closePermissionsModal() {
    document.getElementById('permissionsModal').classList.remove('open');
}

function selectAllPermissions(state) {
    document.querySelectorAll('.perm-checkbox').forEach(function(cb) { cb.checked = state; });
    updateSelectedCount();
}

function toggleModule(module) {
    var checkboxes = document.querySelectorAll('.module-' + module);
    var allChecked = true;
    checkboxes.forEach(function(cb) { if (!cb.checked) allChecked = false; });
    checkboxes.forEach(function(cb) { cb.checked = !allChecked; });
    updateSelectedCount();
}

function updateSelectedCount() {
    var total   = document.querySelectorAll('.perm-checkbox').length;
    var checked = document.querySelectorAll('.perm-checkbox:checked').length;
    document.getElementById('selectedCount').textContent =
        checked + ' of ' + total + ' permissions selected';
}

document.getElementById('permissionsModal').addEventListener('click', function(e) {
    if (e.target === this) closePermissionsModal();
});
</script>
@endpush

@endsection