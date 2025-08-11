<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        // Create super admin user
        $user = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
        ]);

        // Create super admin role
        $role = Role::create(['name' => 'super_admin', 'guard_name' => 'web']);

        // Get all permissions
        $permissions = Permission::all();

        // Assign all permissions to the role
        $role->syncPermissions($permissions);

        // Assign role to user
        $user->assignRole($role);
    }
}
