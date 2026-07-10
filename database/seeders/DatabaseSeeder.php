<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin User',
                'email' => 'adminuser@playptl.com',
                'role' => UserRole::Admin,
            ],
            [
                'name' => 'Organiser User',
                'email' => 'organiser@playptl.com',
                'role' => UserRole::Organiser,
            ],
            [
                'name' => 'Player User',
                'email' => 'player@playptl.com',
                'role' => UserRole::Player,
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'role' => $user['role'],
                    'password' => Hash::make('s4E0t0WkL@#$23'),
                ]
            );
        }
    }
}
