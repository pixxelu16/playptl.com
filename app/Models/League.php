<?php
namespace App\Models;

use Database\Factories\LeagueFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'logo_path', 'description', 'stats', 'start_date', 'end_date', 'playoffs_started_at', 'playoffs_closed_at', 'playoff_start_date', 'playoff_end_date', 'finished_at', 'type'])]
class League extends Model
{
    /** @use HasFactory<LeagueFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'playoffs_started_at' => 'datetime',
            'playoffs_closed_at' => 'datetime',
            'playoff_start_date' => 'date',
            'playoff_end_date' => 'date',
            'finished_at' => 'datetime',
        ];
    }

    public function isFinished(): bool
    {
        return $this->finished_at !== null;
    }

    public function groupCards(): BelongsToMany
    {
        return $this->belongsToMany(GroupCard::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function paymentHistories(): HasMany
    {
        return $this->hasMany(PaymentHistory::class);
    }

    public function leagueRegistrations(): HasMany
    {
        return $this->hasMany(LeagueRegistration::class);
    }
}
