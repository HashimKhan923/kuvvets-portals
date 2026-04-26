@extends('layouts.app')
@section('title', 'Users & Access')
@section('page-title', 'Users & Access Management')
@section('breadcrumb', 'Settings · Users')

@section('content')

<div style="display:grid;grid-template-columns:1fr 380px;gap:20px;align-items:start;">

    {{-- Users Table --}}
    <div class="card card-flush">
        <div style="padding:14px 20px;border-bottom:1px solid var(--border);">
            <div class="card-title" style="margin-bottom:0;">
                <i class="fa-solid fa-users-gear"></i>
                Admin Portal Users ({{ $users->count() }})
            </div>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Role</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th class="center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td>
                        <div class="td-employee">
                            <img src="{{ $user->avatar_url }}" class="avatar avatar-sm">
                            <div>
                                <div style="font-size:13px;font-weight:600;color:var(--text-primary);">
                                    {{ $user->name }}
                                    @if($user->id === auth()->id())
                                    <span class="badge badge-accent" style="font-size:9px;margin-left:4px;">You</span>
                                    @endif
                                </div>
                                <div style="font-size:11px;color:var(--text-muted);">{{ $user->email }}</div>
                                <div style="font-size:10px;color:var(--text-muted);">@{{ $user->username }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge badge-accent" style="font-size:11px;">
                            {{ $user->display_role }}
                        </span>
                    </td>
                    <td>
                        <span class="badge badge-blue" style="font-size:11px;">
                            {{ ucfirst(str_replace('_', ' ', $user->user_type)) }}
                        </span>
                    </td>
                    <td>
                        <span class="badge {{ $user->is_active ? 'badge-green' : 'badge-red' }}">
                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="muted" style="font-size:11px;">
                        {{ $user->last_login_at?->diffForHumans() ?? 'Never' }}
                    </td>
                    <td class="center">
                        <div style="display:flex;align-items:center;justify-content:center;gap:5px;">
                            <button onclick="openEditUser(
                                        {{ $user->id }},
                                        '{{ addslashes($user->name) }}',
                                        '{{ $user->user_type }}',
                                        '{{ $user->roles->first()?->name }}',
                                        {{ $user->is_active ? 'true' : 'false' }})"
                                    class="action-btn" title="Edit">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            @if($user->id !== auth()->id())
                            <form method="POST" action="{{ route('settings.users.toggle', $user) }}">
                                @csrf
                                <button type="submit" class="action-btn"
                                        title="{{ $user->is_active ? 'Deactivate' : 'Activate' }}">
                                    <i class="fa-solid {{ $user->is_active ? 'fa-ban' : 'fa-check' }}"
                                       style="color:{{ $user->is_active ? 'var(--red)' : 'var(--green)' }};"></i>
                                </button>
                            </form>
                            <form method="POST"
                                  action="{{ route('settings.users.destroy', $user) }}"
                                  onsubmit="return confirm('Delete {{ $user->name }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="action-btn danger" title="Delete">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="empty-state">No users found.</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Create User Form --}}
    <div class="card" style="position:sticky;top:0;">
        <div class="form-section">
            <i class="fa-solid fa-user-plus"></i> Create Admin User
        </div>
        <form method="POST" action="{{ route('settings.users.store') }}">
            @csrf
            <div style="display:flex;flex-direction:column;gap:11px;">
                <div>
                    <label class="form-label">Full Name <span style="color:var(--red);">*</span></label>
                    <input type="text" name="name" required value="{{ old('name') }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Email <span style="color:var(--red);">*</span></label>
                    <input type="email" name="email" required value="{{ old('email') }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Username <span style="color:var(--red);">*</span></label>
                    <input type="text" name="username" required value="{{ old('username') }}"
                           placeholder="No spaces" class="form-input">
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                    <div>
                        <label class="form-label">Role <span style="color:var(--red);">*</span></label>
                        <select name="role" required class="form-select">
                            @foreach($roles as $role)
                            <option value="{{ $role->name }}">
                                {{ ucwords(str_replace('_', ' ', $role->name)) }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Type</label>
                        <select name="user_type" class="form-select">
                            <option value="admin">Admin</option>
                            <option value="super_admin">Super Admin</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="form-label">Password <span style="color:var(--red);">*</span></label>
                    <input type="password" name="password" required
                           placeholder="Min 8 characters" class="form-input">
                </div>
                <div>
                    <label class="form-label">Confirm Password <span style="color:var(--red);">*</span></label>
                    <input type="password" name="password_confirmation" required class="form-input">
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
                    <i class="fa-solid fa-user-plus"></i> Create User
                </button>
            </div>
        </form>
    </div>

</div>

{{-- Edit User Modal --}}
<div id="editUserModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-title">
            <i class="fa-solid fa-user-pen"></i> Edit User
        </div>
        <form id="editUserForm" method="POST">
            @csrf @method('PUT')
            <div style="display:flex;flex-direction:column;gap:11px;margin-bottom:4px;">
                <div>
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" id="editName" required class="form-input">
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                    <div>
                        <label class="form-label">Role</label>
                        <select name="role" id="editRole" class="form-select">
                            @foreach($roles as $role)
                            <option value="{{ $role->name }}">
                                {{ ucwords(str_replace('_', ' ', $role->name)) }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Type</label>
                        <select name="user_type" id="editType" class="form-select">
                            <option value="admin">Admin</option>
                            <option value="super_admin">Super Admin</option>
                            <option value="employee">Employee</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="form-label">New Password <span style="color:var(--text-muted);font-weight:400;">(leave blank to keep)</span></label>
                    <input type="password" name="password" placeholder="Min 8 characters" class="form-input">
                </div>
                <div>
                    <label class="form-label">Confirm New Password</label>
                    <input type="password" name="password_confirmation" class="form-input">
                </div>
                <label style="display:flex;align-items:center;gap:8px;font-size:13px;
                               color:var(--text-secondary);cursor:pointer;">
                    <input type="checkbox" name="is_active" value="1" id="editActive"
                           style="accent-color:var(--accent);width:15px;height:15px;">
                    Account is Active
                </label>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeEditUser()">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openEditUser(id, name, type, role, isActive) {
    document.getElementById('editName').value     = name;
    document.getElementById('editType').value     = type;
    document.getElementById('editRole').value     = role || '';
    document.getElementById('editActive').checked = isActive;
    document.getElementById('editUserForm').action = '{{ url("admin/settings/users") }}/' + id;
    document.getElementById('editUserModal').classList.add('open');
}
function closeEditUser() {
    document.getElementById('editUserModal').classList.remove('open');
}
document.getElementById('editUserModal').addEventListener('click', function(e) {
    if (e.target === this) closeEditUser();
});
</script>
@endpush

@endsection