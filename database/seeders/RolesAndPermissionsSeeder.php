<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Claims
            'claims.view',
            'claims.create',
            'claims.edit',
            'claims.delete',
            'claims.approve',
            'claims.reject',
            'claims.forward',
            'claims.close',
            // Documents
            'documents.upload',
            'documents.delete',
            // Payments
            'payments.view',
            'payments.manage',
            // Users
            'users.view',
            'users.manage',
            // Reports
            'reports.view',
            // Configuration
            'configuration.view',
            'configuration.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Admin — full access
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions(Permission::all());

        // PIC — manage claims workflow
        $pic = Role::firstOrCreate(['name' => 'pic']);
        $pic->syncPermissions([
            'claims.view',
            'claims.edit',
            'claims.approve',
            'claims.reject',
            'claims.forward',
            'claims.close',
            'documents.upload',
            'documents.delete',
            'payments.view',
            'payments.manage',
            'reports.view',
        ]);

        // Employer — submit and track claims
        $employer = Role::firstOrCreate(['name' => 'employer']);
        $employer->syncPermissions([
            'claims.view',
            'claims.create',
            'claims.edit',
            'documents.upload',
            'payments.view',
        ]);
    }
}
