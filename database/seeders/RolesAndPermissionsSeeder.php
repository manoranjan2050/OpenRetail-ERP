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
            // Users
            'users.view', 'users.create', 'users.edit', 'users.delete',
            // Products / Inventory
            'products.view', 'products.create', 'products.edit', 'products.delete',
            'inventory.adjust',
            // Customers
            'customers.view', 'customers.create', 'customers.edit', 'customers.delete',
            // Invoices / POS
            'invoices.view', 'invoices.create', 'invoices.void',
            // Payments / Ledger
            'payments.view', 'payments.record',
            // Reports
            'reports.view', 'reports.export',
            // Settings
            'settings.view', 'settings.edit',
            // Audit log
            'audit.view',
            // Backup / Restore
            'backup.run', 'backup.restore',
            // Statements
            'statements.view', 'statements.send',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions(Permission::all());

        $salesman = Role::firstOrCreate(['name' => 'salesman']);
        $salesman->syncPermissions([
            'products.view',
            'customers.view', 'customers.create', 'customers.edit',
            'invoices.view', 'invoices.create',
            'payments.view', 'payments.record',
            'statements.view',
        ]);

        $backupOperator = Role::firstOrCreate(['name' => 'backup_operator']);
        $backupOperator->syncPermissions([
            'backup.run', 'backup.restore',
        ]);
    }
}
