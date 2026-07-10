<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use App\Notifications\ResetPasswordNotification;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name',
    'first_name',
    'last_name',
    'email',
    'phone',
    'password',
    'role',
    'status',
    'avatar_path',
    'date_of_birth',
    'city',
    'state',
    'home_court',
    'preferred_play_date',
    'preferred_play_time',
    'dominant_hand',
    'sex',
    'skill_level',
    'registration_type',
    'transaction_id',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function paymentHistories(): HasMany
    {
        return $this->hasMany(PaymentHistory::class);
    }

    public function leagueRegistrations(): HasMany
    {
        return $this->hasMany(LeagueRegistration::class);
    }

    public function dashboardRouteName(): string
    {
        return $this->role->dashboardRouteName();
    }

    public function playerProfileUrl(): string
    {
        return route('player.my-profile');
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'date_of_birth' => 'date',
            'preferred_play_date' => 'date',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }
}
