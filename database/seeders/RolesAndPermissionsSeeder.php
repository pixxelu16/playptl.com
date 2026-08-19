<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use App\Enums\UserRole;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Permissions
        $permissions = [
            'view admin panel',
            'manage leagues',
            'manage groups',
            'manage group cards',
            'manage announcements',
            'manage official partners',
            'manage players',
            'manage settings',
            'manage categories',
            'manage donations',
            'manage charity causes',
            'manage charity donations',
            'manage skills',
            'manage bookings',
            'manage gallery',
            'manage roles',
            'manage users',
            'manage payment history',
            'manage rules',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // Create default roles
        $superAdmin = Role::findOrCreate('Super Admin');
        $admin = Role::findOrCreate('Admin');
        $organiser = Role::findOrCreate('Organiser');
        $player = Role::findOrCreate('Player');
        $coach = Role::findOrCreate('Coach');
        $mentor = Role::findOrCreate('Mentor');
        $student = Role::findOrCreate('Student');

        // Assign permissions to Admin role
        $admin->givePermissionTo(Permission::all());

        // Assign permissions to Super Admin role
        $superAdmin->givePermissionTo(Permission::all());

        // Map existing users' enum roles to Spatie roles
        $users = User::all();
        foreach ($users as $user) {
            if ($user->role === UserRole::Admin) {
                // If it is our main admin, give Super Admin
                if ($user->email === 'adminuser@playptl.com') {
                    $user->assignRole('Super Admin');
                } else {
                    $user->assignRole('Admin');
                }
            } elseif ($user->role === UserRole::Organiser) {
                $user->assignRole('Organiser');
            } elseif ($user->role === UserRole::Player) {
                $user->assignRole('Player');
            } elseif ($user->role === UserRole::Coach) {
                $user->assignRole('Coach');
            } elseif ($user->role === UserRole::Mentor) {
                $user->assignRole('Mentor');
            } elseif ($user->role === UserRole::Student) {
                $user->assignRole('Student');
            }
        }
    }
}
