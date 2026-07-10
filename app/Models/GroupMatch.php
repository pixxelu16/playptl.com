<?php

namespace App\Models;

use App\Enums\GroupMatchFormat;
use App\Support\MatchScoreReader;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GroupMatch extends Model
{
    protected $fillable = [
        'league_id',
        'group_card_id',
        'group_id',
        'format',
        'home_user_id',
        'away_user_id',
        'home_partner_user_id',
        'away_partner_user_id',
        'home_seed',
        'away_seed',
        'match_date',
        'start_time',
        'venue',
        'court',
        'score',
        'winner_side',
        'winner_user_id',
        'sort_order',
        'round_number',
        'auto_scheduled',
    ];

    protected function casts(): array
    {
        return [
            'format' => GroupMatchFormat::class,
            'match_date' => 'date',
            'auto_scheduled' => 'boolean',
        ];
    }

    public function league(): BelongsTo
    {
        return $this->belongsTo(League::class);
    }

    public function groupCard(): BelongsTo
    {
        return $this->belongsTo(GroupCard::class, 'group_card_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function homeUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'home_user_id');
    }

    public function awayUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'away_user_id');
    }

    public function homePartnerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'home_partner_user_id');
    }

    public function awayPartnerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'away_partner_user_id');
    }

    public function winnerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winner_user_id');
    }

    public function playerUploads(): HasMany
    {
        return $this->hasMany(GroupMatchPlayerUpload::class, 'group_match_id')->orderByDesc('id');
    }

    public function isPending(): bool
    {
        return $this->homeSideWon() === null;
    }

    /**
     * True if home side won, false if away won, null if no result yet.
     * Uses stored {@see $winner_side} when set, otherwise parses {@see $score}.
     */
    public function homeSideWon(): ?bool
    {
        $side = $this->winner_side;
        if ($side === 'home') {
            return true;
        }
        if ($side === 'away') {
            return false;
        }

        return MatchScoreReader::homeSideWon(trim((string) ($this->score ?? '')));
    }

    /**
     * Winning user id for singles, or null for doubles / pending / no result.
     */
    public function singlesWinnerUserId(): ?int
    {
        if ($this->format !== GroupMatchFormat::Singles) {
            return null;
        }
        if ($this->winner_user_id !== null) {
            return (int) $this->winner_user_id;
        }
        $won = $this->homeSideWon();
        if ($won === null) {
            return null;
        }

        return $won ? (int) $this->home_user_id : (int) $this->away_user_id;
    }
}
