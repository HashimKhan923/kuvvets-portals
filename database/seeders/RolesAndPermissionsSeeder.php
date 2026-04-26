<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder {
    public function run(): void {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Dashboard
            'dashboard.view',

            // Employee
            'employees.view', 'employees.create', 'employees.edit',
            'employees.delete', 'employees.export', 'employees.salary.view',

            // Departments & Designations
            'departments.manage', 'designations.manage',

            // Attendance
            'attendance.view', 'attendance.manage', 'attendance.report',
            'attendance.override',

            // Leave
            'leaves.view', 'leaves.apply', 'leaves.approve',
            'leaves.manage', 'leaves.report',

            // Payroll
            'payroll.view', 'payroll.process', 'payroll.approve',
            'payroll.export', 'payslip.view', 'payslip.download',

            // Recruitment
            'recruitment.view', 'recruitment.manage',

            // Performance
            'performance.view', 'performance.manage', 'performance.review',

            // Assets
            'assets.view', 'assets.manage', 'assets.assign',

            // Training
            'training.view', 'training.manage',

            // Documents
            'documents.view', 'documents.manage', 'documents.delete',

            // Reports
            'reports.hr', 'reports.payroll', 'reports.attendance', 'reports.all',

            // System
            'settings.view', 'settings.manage',
            'roles.manage', 'users.manage', 'audit_logs.view',
            'announcements.manage',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // ─── ROLES ────────────────────────────────────────────────────
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());

        $hrManager = Role::firstOrCreate(['name' => 'hr_manager', 'guard_name' => 'web']);
        $hrManager->syncPermissions([
            'dashboard.view',
            'employees.view','employees.create','employees.edit','employees.export',
            'departments.manage','designations.manage',
            'attendance.view','attendance.manage','attendance.report','attendance.override',
            'leaves.view','leaves.approve','leaves.manage','leaves.report',
            'payroll.view','payslip.view','payslip.download',
            'recruitment.view','recruitment.manage',
            'performance.view','performance.manage','performance.review',
            'documents.view','documents.manage',
            'training.view','training.manage',
            'reports.hr','reports.attendance',
            'announcements.manage',
        ]);

        $payrollManager = Role::firstOrCreate(['name' => 'payroll_manager', 'guard_name' => 'web']);
        $payrollManager->syncPermissions([
            'dashboard.view',
            'employees.view','employees.salary.view',
            'attendance.view','attendance.report',
            'payroll.view','payroll.process','payroll.approve','payroll.export',
            'payslip.view','payslip.download',
            'reports.payroll','reports.attendance',
        ]);

        $deptManager = Role::firstOrCreate(['name' => 'department_manager', 'guard_name' => 'web']);
        $deptManager->syncPermissions([
            'dashboard.view',
            'employees.view',
            'attendance.view','attendance.report',
            'leaves.view','leaves.approve',
            'performance.view','performance.review',
            'documents.view',
            'training.view',
        ]);

        $recruitmentOfficer = Role::firstOrCreate(['name' => 'recruitment_officer', 'guard_name' => 'web']);
        $recruitmentOfficer->syncPermissions([
            'dashboard.view',
            'employees.view','employees.create',
            'recruitment.view','recruitment.manage',
            'documents.view','documents.manage',
            'training.view',
        ]);

        $employee = Role::firstOrCreate(['name' => 'employee', 'guard_name' => 'web']);
        $employee->syncPermissions([
            'dashboard.view',
            'leaves.view','leaves.apply',
            'payslip.view','payslip.download',
            'documents.view',
            'training.view',
        ]);

        $this->command->info('✅ Roles & Permissions seeded successfully.');
    }
}