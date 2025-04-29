<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admins = Admin::all();
        if($admins->isEmpty()){
            Admin::query()->create([
                'name' => 'Super Admin',
                'email' => 'admin@mail.com',
                'password' => bcrypt('password1324'),
            ]);
        }
        $admin = Admin::query()->first();
        $roles = Role::all();

        if( $roles->isEmpty() ){

            $permissions = Permission::all();

            $role = Role::create(['name' => 'Super Admin', 'guard_name' => 'admin']);
            foreach ($permissions as $row) {
                $role->givePermissionTo($row['name']);
            }
            $admin->assignRole($role->id);
        } else {
            $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'admin']);
            $admin->assignRole($superAdminRole->id);
        }
    }
}
