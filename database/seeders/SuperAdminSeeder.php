<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Company;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::firstOrCreate(
            ['ntn' => '0000000-0'],
            [
                'name'        => 'KUVVET Private Limited',
                'legal_name'  => 'KUVVET Private Limited',
                'email'       => 'info@kuvvet.com',
                'phone'       => '+92-21-00000000',
                'address'     => 'Karachi, Pakistan',
                'city'        => 'Karachi',
                'province'    => 'Sindh',
                'country'     => 'Pakistan',
                'currency'    => 'PKR',
                'timezone'    => 'Asia/Karachi',
                'is_active'   => true,
            ]
        );

        // ── Super Admin ───────────────────────────────────
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@kuvvet.com'],
            [
                'company_id'          => $company->id,
                'name'                => 'KUVVET Administrator',
                'username'            => 'admin',
                'password'            => bcrypt('password'),
                'user_type'           => 'super_admin',
                'portal_access'       => 'admin',
                'email_verified_at'   => now(),
                'is_active'           => true,
                'password_changed_at' => now(),
            ]
        );
        $superAdmin->assignRole('super_admin');

        // ── HR Manager ────────────────────────────────────
        $hrManager = User::firstOrCreate(
            ['email' => 'hr@kuvvet.com'],
            [
                'company_id'          => $company->id,
                'name'                => 'HR Manager',
                'username'            => 'hrmanager',
                'password'            => bcrypt('Kuvvet@2024!'),
                'user_type'           => 'admin',
                'portal_access'       => 'admin',
                'email_verified_at'   => now(),
                'is_active'           => true,
                'password_changed_at' => now(),
            ]
        );
        $hrManager->assignRole('hr_manager');

        // ── Payroll Manager ───────────────────────────────
        $payrollManager = User::firstOrCreate(
            ['email' => 'payroll@kuvvet.com'],
            [
                'company_id'          => $company->id,
                'name'                => 'Payroll Manager',
                'username'            => 'payroll',
                'password'            => bcrypt('Kuvvet@2024!'),
                'user_type'           => 'admin',
                'portal_access'       => 'admin',
                'email_verified_at'   => now(),
                'is_active'           => true,
                'password_changed_at' => now(),
            ]
        );
        $payrollManager->assignRole('payroll_manager');

        $this->command->info('✅ Admin users seeded:');
        $this->command->info('   super_admin  → admin@kuvvet.com    / Kuvvet@2024!');
        $this->command->info('   hr_manager   → hr@kuvvet.com       / Kuvvet@2024!');
        $this->command->info('   payroll_mgr  → payroll@kuvvet.com  / Kuvvet@2024!');
    }
}