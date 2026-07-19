<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use App\Enums\UserRole;

class SuperAdminPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Ensure all default permissions exist
        $permissions = [
            'view admin panel',
            'manage leagues',
            'manage groups',
            'manage group cards',
            'manage announcements',
            'manage official partners',
            'manage players',
            'manage settings',
            'manage donations',
            'manage skills',
            'manage bookings',
            'manage gallery',
            'manage roles',
            'manage users',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // Find or create Super Admin role
        $superAdmin = Role::findOrCreate('Super Admin');

        // Assign all permissions to Super Admin role
        $superAdmin->syncPermissions(Permission::all());

        // Assign 'Super Admin' role to the main admin user
        $adminUser = User::where('email', 'adminuser@playptl.com')->first();
        if ($adminUser) {
            $adminUser->assignRole('Super Admin');
            // Also update their user role enum if necessary
            $adminUser->role = UserRole::Admin;
            $adminUser->save();
        }
    }
}
