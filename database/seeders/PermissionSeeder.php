<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'view-admins',
            'create-admins',
            'update-admins',
            'delete-admins',
            'view-colors',
            'create-colors',
            'update-colors',
            'delete-colors',
            'view-sizes',
            'create-sizes',
            'update-sizes',
            'delete-sizes',
            'view-countries',
            'create-countries',
            'update-countries',
            'delete-countries',
            'view-states',
            'create-states',
            'update-states',
            'delete-states',
            'view-users',
            'create-users',
            'update-users',
            'delete-users',
            'view-billing-addresses',
            'create-billing-addresses',
            'update-billing-addresses',
            'delete-billing-addresses',
            'view-product-brands',
            'create-product-brands',
            'update-product-brands',
            'delete-product-brands',
            'view-product-categories',
            'create-product-categories',
            'update-product-categories',
            'delete-product-categories',
            'view-products',
            'create-products',
            'update-products',
            'delete-products',
            'view-product-variants',
            'create-product-variants',
            'update-product-variants',
            'delete-product-variants',
            'view-orders',
            'create-orders',
            'update-orders',
            'delete-orders',
            'view-order-lines',
            'create-order-lines',
            'update-order-lines',
            'delete-order-lines',
            'view-payments',
            'create-payments',
            'update-payments',
            'delete-payments',
            'view-payment-methods',
            'create-payment-methods',
            'update-payment-methods',
            'delete-payment-methods',
            'view-promo-codes',
            'create-promo-codes',
            'update-promo-codes',
            'delete-promo-codes',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'admin']);
        }

        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'admin']);
        $superAdminRole->syncPermissions($permissions);

        $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'admin']);
        $adminRole->syncPermissions($permissions);

        $this->command->info('Permissions and roles seeded successfully.');
    }
}
