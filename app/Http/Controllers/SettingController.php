<?php
namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Company;
use App\Models\User;
use App\Models\AuditLog;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Str;

class SettingController extends Controller
{
    // ── SETTINGS HUB ─────────────────────────────────────────
    public function index()
    {
        $companyId = auth()->user()->company_id;
        $company   = Company::findOrFail($companyId);

        $settings = Setting::where('company_id', $companyId)
            ->get()->keyBy('key');

        return view('settings.index', compact('company', 'settings'));
    }

    // ── COMPANY PROFILE ───────────────────────────────────────
    public function company()
    {
        $companyId = auth()->user()->company_id;
        $company   = Company::findOrFail($companyId);
        return view('settings.company', compact('company'));
    }

    public function updateCompany(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $company   = Company::findOrFail($companyId);

        $request->validate([
            'name'          => 'required|string|max:200',
            'email'         => 'required|email|max:200',
            'phone'         => 'nullable|string|max:30',
            'address'       => 'nullable|string|max:500',
            'city'          => 'nullable|string|max:100',
            'province'      => 'nullable|string|max:100',
            'ntn'           => 'nullable|string|max:30',
            'strn'          => 'nullable|string|max:30',
            'website'       => 'nullable|url|max:200',
        ]);

        if ($request->hasFile('logo')) {
            $request->validate(['logo' => 'image|max:2048']);
            if ($company->logo) Storage::disk('public')->delete($company->logo);
            $company->logo = $request->file('logo')->store('company', 'public');
        }

        $company->update($request->only([
            'name', 'legal_name', 'email', 'phone', 'address',
            'city', 'province', 'country', 'ntn', 'strn',
            'website', 'currency', 'timezone',
        ]));

        if ($company->logo && !$request->hasFile('logo')) {
            // keep existing
        }

        if ($request->hasFile('logo')) {
            $company->save();
        }

        AuditLog::log('company_updated', $company);
        Cache::flush();

        return back()->with('success', 'Company profile updated successfully.');
    }

    // ── HR SETTINGS ───────────────────────────────────────────
    public function hr()
    {
        $companyId = auth()->user()->company_id;
        $settings  = Setting::getGroup('hr', $companyId);
        return view('settings.hr', compact('settings'));
    }

    public function updateHr(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $group     = 'hr';

        $fields = [
            'probation_period_months'      => ['type' => 'integer', 'label' => 'Probation Period (months)'],
            'notice_period_days'           => ['type' => 'integer', 'label' => 'Notice Period (days)'],
            'working_hours_per_day'        => ['type' => 'integer', 'label' => 'Working Hours/Day'],
            'working_days_per_week'        => ['type' => 'integer', 'label' => 'Working Days/Week'],
            'overtime_rate_multiplier'     => ['type' => 'string',  'label' => 'OT Rate Multiplier'],
            'annual_leave_days'            => ['type' => 'integer', 'label' => 'Annual Leave Days'],
            'casual_leave_days'            => ['type' => 'integer', 'label' => 'Casual Leave Days'],
            'sick_leave_days'              => ['type' => 'integer', 'label' => 'Sick Leave Days'],
            'employee_id_prefix'           => ['type' => 'string',  'label' => 'Employee ID Prefix'],
            'employee_id_digits'           => ['type' => 'integer', 'label' => 'Employee ID Digits'],
            'allow_negative_leave_balance' => ['type' => 'boolean', 'label' => 'Allow Negative Leave Balance'],
            'carry_forward_leaves'         => ['type' => 'boolean', 'label' => 'Carry Forward Leaves'],
            'max_carry_forward_days'       => ['type' => 'integer', 'label' => 'Max Carry Forward Days'],
            'weekend_days'                 => ['type' => 'json',    'label' => 'Weekend Days'],
        ];

        foreach ($fields as $key => $meta) {
            $value = $request->input($key);
            if ($meta['type'] === 'boolean') {
                $value = $request->boolean($key) ? '1' : '0';
            } elseif ($meta['type'] === 'json' && is_array($value)) {
                $value = json_encode($value);
            }
            Setting::set($key, $value, $group, $companyId);
        }

        AuditLog::log('hr_settings_updated');
        return back()->with('success', 'HR settings updated successfully.');
    }

    // ── PAYROLL SETTINGS ──────────────────────────────────────
    public function payroll()
    {
        $companyId = auth()->user()->company_id;
        $settings  = Setting::getGroup('payroll', $companyId);
        return view('settings.payroll', compact('settings'));
    }

    public function updatePayroll(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $group     = 'payroll';

        $fields = [
            'payroll_cycle'             => 'string',
            'payroll_payment_day'       => 'integer',
            'eobi_enabled'              => 'boolean',
            'pessi_enabled'             => 'boolean',
            'income_tax_enabled'        => 'boolean',
            'minimum_wage'              => 'string',
            'currency_symbol'           => 'string',
            'payslip_footer_note'       => 'string',
            'bank_name'                 => 'string',
            'bank_account'              => 'string',
            'bank_branch_code'          => 'string',
        ];

        foreach ($fields as $key => $type) {
            $value = $type === 'boolean'
                ? ($request->boolean($key) ? '1' : '0')
                : $request->input($key);
            Setting::set($key, $value, $group, $companyId);
        }

        AuditLog::log('payroll_settings_updated');
        return back()->with('success', 'Payroll settings updated.');
    }

    // ── USERS MANAGEMENT ──────────────────────────────────────
    public function users()
    {
        $companyId = auth()->user()->company_id;

        $users = User::where('company_id', $companyId)
            ->with('roles')
            ->orderBy('name')
            ->get();

        $roles = Role::orderBy('name')->get();

        return view('settings.users', compact('users', 'roles'));
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:150',
            'email'      => 'required|email|unique:users,email',
            'username'   => 'required|string|unique:users,username|max:50',
            'password'   => 'required|min:8|confirmed',
            'role'       => 'required|string|exists:roles,name',
            'user_type'  => 'required|in:admin,super_admin',
        ]);

        $user = User::create([
            'company_id'    => auth()->user()->company_id,
            'name'          => $request->name,
            'email'         => $request->email,
            'username'      => $request->username,
            'password'      => bcrypt($request->password),
            'user_type'     => $request->user_type,
            'portal_access' => 'admin',
            'is_active'     => true,
        ]);

        $user->assignRole($request->role);
        AuditLog::log('user_created', $user);

        return back()->with('success', "User \"{$user->name}\" created successfully.");
    }

    public function updateUser(Request $request, User $user)
    { 
        if ($user->company_id !== auth()->user()->company_id) abort(403);

        $request->validate([
            'name'      => 'required|string|max:150',
            'is_active' => 'nullable|boolean',
            'role'      => 'nullable|string|exists:roles,name',
            'user_type' => 'nullable|in:admin,super_admin,employee',
        ]);

        $user->update([
            'name'       => $request->name,
            'user_type'  => $request->user_type ?? $user->user_type,
            'is_active'  => $request->boolean('is_active'),
        ]);

        if ($request->filled('role')) {
            $user->syncRoles([$request->role]);
        }

        if ($request->filled('password')) {
            $request->validate(['password' => 'min:8|confirmed']);
            $user->update(['password' => bcrypt($request->password)]);
        }

        AuditLog::log('user_updated', $user);
        return back()->with('success', "User \"{$user->name}\" updated.");
    }

    public function toggleUser(User $user)
    {
        if ($user->company_id !== auth()->user()->company_id) abort(403);
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $user->update(['is_active' => !$user->is_active]);
        $status = $user->is_active ? 'activated' : 'deactivated';

        AuditLog::log('user_' . $status, $user);
        return back()->with('success', "User \"{$user->name}\" {$status}.");
    }

    public function destroyUser(User $user)
    {
        if ($user->company_id !== auth()->user()->company_id) abort(403);
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        AuditLog::log('user_deleted', $user);
        $user->delete();
        return back()->with('success', 'User deleted.');
    }

    // ── ROLES & PERMISSIONS ───────────────────────────────────
    public function roles()
    {
        $roles = Role::with('permissions')
            ->withCount('users')
            ->get();

        $allPermissions = Permission::orderBy('name')->get()
            ->groupBy(function ($p) {
                // Group by second word (the module name)
                $parts = explode(' ', $p->name);
                return $parts[1] ?? $parts[0] ?? 'other';
            });

        return view('settings.roles', compact('roles', 'allPermissions'));
    }

    // ── AUDIT LOG ─────────────────────────────────────────────
    public function auditLog(Request $request)
    {
        $companyId = auth()->user()->company_id;

        // Get all user IDs belonging to this company
        $companyUserIds = User::where('company_id', $companyId)->pluck('id');

        $query = AuditLog::with(['user'])
            ->whereIn('user_id', $companyUserIds)
            ->latest();

        if ($request->filled('user'))
            $query->where('user_id', $request->user);

        if ($request->filled('action'))
            $query->where('action', 'like', "%{$request->action}%");

        if ($request->filled('date_from'))
            $query->whereDate('created_at', '>=', $request->date_from);

        if ($request->filled('date_to'))
            $query->whereDate('created_at', '<=', $request->date_to);

        $logs  = $query->paginate(25)->withQueryString();
        $users = User::where('company_id', $companyId)->orderBy('name')->get();

        return view('settings.audit-log', compact('logs', 'users'));
    }

    // ── MY PROFILE ────────────────────────────────────────────
    public function profile()
    {
        return view('settings.profile', ['user' => auth()->user()]);
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name'  => 'required|string|max:150',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:30',
        ]);

        if ($request->hasFile('avatar')) {
            $request->validate(['avatar' => 'image|max:2048']);
            if ($user->avatar) Storage::disk('public')->delete($user->avatar);
            $user->avatar = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($request->only(['name', 'email', 'phone', 'username']));
        if ($request->hasFile('avatar')) $user->save();

        return back()->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password'  => 'required',
            'password'          => 'required|min:8|confirmed|different:current_password',
        ]);

        if (!Hash::check($request->current_password, auth()->user()->password)) {
            return back()->with('error', 'Current password is incorrect.');
        }

        auth()->user()->update([
            'password'                  => bcrypt($request->password),
            'password_changed_at'       => now(),
            'last_password_changed_at'  => now(),
        ]);

        AuditLog::log('password_changed');
        return back()->with('success', 'Password changed successfully.');
    }


        // ── STORE ROLE ────────────────────────────────────────────
    public function storeRole(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:roles,name',
        ]);

        Role::create([
            'name'       => Str::slug($request->name, '_'),
            'guard_name' => 'web',
        ]);

        return back()->with('success', "Role \"{$request->name}\" created.");
    }

    // ── UPDATE ROLE PERMISSIONS ───────────────────────────────
    public function updateRolePermissions(Request $request, Role $role)
    {
        $request->validate([
            'permissions'   => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role->syncPermissions($request->permissions ?? []);

        AuditLog::log('role_permissions_updated', null, [], [
            'role'        => $role->name,
            'permissions' => $request->permissions ?? [],
        ]);

        return back()->with('success', "Permissions updated for role \"{$role->name}\".");
    }

    // ── DELETE ROLE ───────────────────────────────────────────
    public function destroyRole(Role $role)
    {
        $protected = ['super_admin','hr_manager','payroll_manager',
                    'department_manager','recruitment_officer','employee'];

        if (in_array($role->name, $protected)) {
            return back()->with('error', "Cannot delete built-in role \"{$role->name}\".");
        }

        $role->delete();
        return back()->with('success', "Role deleted.");
    }

    // ── STORE PERMISSION ──────────────────────────────────────
    public function storePermission(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:permissions,name',
        ]);

        Permission::create([
            'name'       => $request->name,
            'guard_name' => 'web',
        ]);

        return back()->with('success', "Permission \"{$request->name}\" created.");
    }
}