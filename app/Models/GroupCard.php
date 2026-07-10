<?php

namespace App\Models;

use App\Enums\GroupMatchFormat;
use Database\Factories\GroupCardFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'skill_level_match', 'playoff_format', 'playoff_quarter_spots', 'playoff_r16_spots', 'playoff_ppq_spots', 'tag', 'players_count', 'groups_count', 'status', 'display_order'])]
class GroupCard extends Model
{
    /** @use HasFactory<GroupCardFactory> */
    use HasFactory;

    public function leagues(): BelongsToMany
    {
        return $this->belongsToMany(League::class)
            ->withPivot(['start_date', 'end_date']);
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

    /**
     * When the sub group tag is strictly singles or doubles, match format is fixed for scheduling.
     * Mixed / youth tags allow choosing per match.
     */
    public function forcedMatchFormat(): ?GroupMatchFormat
    {
        $tag = strtolower(trim((string) $this->tag));

        return match ($tag) {
            'single', 'singles' => GroupMatchFormat::Singles,
            'double', 'doubles' => GroupMatchFormat::Doubles,
            default => null,
        };
    }
}
