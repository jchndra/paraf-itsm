<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cache permission
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Daftar seluruh permission sistem PARAF
        $permissions = [
            'rfc.create',
            'rfc.view_own',
            'rfc.view_all',
            'rfc.review_completeness',
            'rfc.assess_risk',
            'rfc.record_decision',
            'rfc.assign_release',
            'rfc.close',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        // 1. Role Pemohon (User OPD)
        $pemohonRole = Role::findOrCreate('Pemohon', 'web');
        $pemohonRole->syncPermissions([
            'rfc.create',
            'rfc.view_own',
        ]);

        // 2. Role Admin / Frontdesk (Diskominfostandi)
        $adminRole = Role::findOrCreate('Admin', 'web');
        $adminRole->syncPermissions([
            'rfc.view_all',
            'rfc.review_completeness',
        ]);

        // 3. Role Change Manager
        $cmRole = Role::findOrCreate('Change Manager', 'web');
        $cmRole->syncPermissions([
            'rfc.view_all',
            'rfc.assess_risk',
            'rfc.record_decision',
            'rfc.assign_release',
            'rfc.close',
        ]);

        // 4. Role Super Admin
        $superAdminRole = Role::findOrCreate('Super Admin', 'web');
        $superAdminRole->syncPermissions($permissions);
    }
}
