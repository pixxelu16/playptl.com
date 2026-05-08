<?php

namespace App\Models;

use Database\Factories\GroupCardFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'skill_level_match', 'tag', 'players_count', 'groups_count', 'status', 'display_order'])]
class GroupCard extends Model
{
    /** @use HasFactory<GroupCardFactory> */
    use HasFactory;

    public function leagues(): BelongsToMany
    {
        return $this->belongsToMany(League::class);
    }

    /**
     * Admin-assigned groups for this card.
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class);
    }

    public function leagueRegistrations(): HasMany
    {
        return $this->hasMany(LeagueRegistration::class);
    }
}
