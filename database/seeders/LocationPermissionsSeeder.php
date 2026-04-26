<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class LocationPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $perms = [
            'locations.view',
            'locations.manage',
            'locations.assign',
            'locations.delete',
        ];

        foreach ($perms as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        // Super admin gets everything
        if ($super = Role::where('name', 'super_admin')->first()) {
            $super->givePermissionTo($perms);
        }

        // HR roles get view + assign (admins decide if they can manage)
        foreach (['hr_admin','hr_manager'] as $roleName) {
            if ($role = Role::where('name', $roleName)->first()) {
                $role->givePermissionTo(['locations.view','locations.manage','locations.assign']);
            }
        }
    }
}