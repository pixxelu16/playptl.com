<?php

namespace App\Models;

use Database\Factories\GroupCardFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['name', 'slug', 'tag', 'players_count', 'groups_count', 'status', 'display_order'])]
class GroupCard extends Model
{
    /** @use HasFactory<GroupCardFactory> */
    use HasFactory;

    public function leagues(): BelongsToMany
    {
        return $this->belongsToMany(League::class);
    }
}
