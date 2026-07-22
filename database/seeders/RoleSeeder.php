<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'manage-clients',
            'manage-surveys',
            'view-analytics',
            'send-campaigns',
            'manage-settings',
            'manage-staff',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $superAdmin = Role::findOrCreate('super_admin', 'web');
        $superAdmin->syncPermissions($permissions);

        $survyraAdmin = Role::findOrCreate('survyra_admin', 'web');
        $survyraAdmin->syncPermissions([
            'manage-clients',
            'manage-surveys',
            'view-analytics',
            'send-campaigns',
        ]);
    }
}
