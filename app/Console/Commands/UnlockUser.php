<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class UnlockUser extends Command
{
    protected $signature = 'user:unlock {email : The email address of the user to unlock}';
    protected $description = 'Unlock a locked user account and reset failed login attempts';

    public function handle(): int
    {
        $email = $this->argument('email');
        $user = User::where('email', strtolower($email))->first();

        if (!$user) {
            $this->error("User with email '{$email}' not found.");
            return Command::FAILURE;
        }

        $user->forceFill([
            'is_locked' => false,
            'locked_at' => null,
            'failed_login_attempts' => 0,
        ])->save();

        $this->info("Successfully unlocked account for {$user->name} ({$user->email}).");
        return Command::SUCCESS;
    }
}
