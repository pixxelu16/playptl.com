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

use Spatie\Permission\Traits\HasRoles;


#[Fillable([
    'name',
    'first_name',
    'last_name',
    'email',
    'username',
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
    'profile_title_ad',
    'profile_lessons',
    'profile_bio',
    'profile_locations',
    'profile_rate',
    'profile_rate_details',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * Generate a unique username based on the email address.
     */
    public static function generateUniqueUsername(string $email): string
    {
        $prefix = explode('@', $email)[0];
        // Clean prefix: lowercase, replace non-alphanumeric with a dot, strip trailing/leading dots
        $baseUsername = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '.', $prefix));
        $baseUsername = trim($baseUsername, '.');
        if (empty($baseUsername)) {
            $baseUsername = 'user';
        }

        $username = $baseUsername;
        $counter = 1;

        while (static::where('username', $username)->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        return $username;
    }

    public function paymentHistories(): HasMany
    {
        return $this->hasMany(PaymentHistory::class);
    }

    public function leagueRegistrations(): HasMany
    {
        return $this->hasMany(LeagueRegistration::class);
    }

    public function bookingsAsStudent(): HasMany
    {
        return $this->hasMany(Booking::class, 'student_id');
    }

    public function bookingsAsProvider(): HasMany
    {
        return $this->hasMany(Booking::class, 'provider_id');
    }

    public function dashboardRouteName(): string
    {
        return $this->role->dashboardRouteName();
    }

    public function playerProfileUrl(): string
    {
        return route('player.my-profile');
    }

    public function mentorProfileUrl(): string
    {
        return route('mentor.profile');
    }

    public function coachProfileUrl(): string
    {
        return route('coach.profile');
    }

    public function studentProfileUrl(): string
    {
        return route('student.profile');
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
            'profile_locations' => 'array',
        ];
    }
}
