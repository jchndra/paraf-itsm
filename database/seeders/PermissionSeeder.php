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

        // 1. Daftar Izin (Permissions)
        $permissions = [
            // RFC Lifecycle
            'rfc.create',
            'rfc.view_own',
            'rfc.view_all',
            'rfc.review_completeness',
            'rfc.assess_risk',
            'rfc.record_decision',
            'rfc.schedule',
            'rfc.assign_release',
            'rfc.record_execution',
            'rfc.close',

            // Master Data & User Management
            'users.manage',
            'roles.manage',
            'master_data.manage',
            'audit_log.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // 2. Mapping Permission ke Roles
        $rolePemohon = Role::firstOrCreate(['name' => 'Pemohon']);
        $rolePemohon->syncPermissions([
            'rfc.create',
            'rfc.view_own',
        ]);

        $roleAdmin = Role::firstOrCreate(['name' => 'Admin']);
        $roleAdmin->syncPermissions([
            'rfc.create',
            'rfc.view_all',
            'rfc.review_completeness',
            'users.manage',
            'master_data.manage',
            'audit_log.view',
        ]);

        $roleChangeManager = Role::firstOrCreate(['name' => 'Change Manager']);
        $roleChangeManager->syncPermissions([
            'rfc.view_all',
            'rfc.assess_risk',
            'rfc.record_decision',
            'rfc.schedule',
            'rfc.assign_release',
            'rfc.record_execution',
            'rfc.close',
            'audit_log.view',
        ]);
    }
}
