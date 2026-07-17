<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UpdateExistingUsernamesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::whereNull('username')->orWhere('username', '')->get();

        foreach ($users as $user) {
            $user->update([
                'username' => User::generateUniqueUsername($user->email)
            ]);
        }
    }
}
