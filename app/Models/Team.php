<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    protected $fillable = [
        'name',
        'league_id',
        'team_key',
    ];

    public function players(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_players')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function teamPlayers(): HasMany
    {
        return $this->hasMany(TeamPlayer::class);
    }

    public function league(): BelongsTo
    {
        return $this->belongsTo(League::class, 'league_id');
    }

    public function tournament(): BelongsTo
    {
        return $this->league();
    }

    public function addPlayer(User|int $user, string $role = 'member'): TeamPlayer
    {
        $userId = $user instanceof User ? $user->id : $user;
        return TeamPlayer::updateOrCreate(
            ['team_id' => $this->id, 'user_id' => $userId],
            ['role' => $role]
        );
    }
}
