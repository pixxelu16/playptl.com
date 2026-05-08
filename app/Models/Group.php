<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['group_card_id', 'age_group_key', 'name', 'description', 'players_count', 'status'])]
class Group extends Model
{
    /** @use HasFactory<\Database\Factories\GroupFactory> */
    use HasFactory;

    public function groupCard(): BelongsTo
    {
        return $this->belongsTo(GroupCard::class);
    }

    public function groupCards(): BelongsToMany
    {
        return $this->belongsToMany(GroupCard::class);
    }

    public function leagueRegistrations(): HasMany
    {
        return $this->hasMany(LeagueRegistration::class, 'group_id');
    }

    // League scope is derived via GroupCard -> leagues (no direct Group<->League pivot).
}
