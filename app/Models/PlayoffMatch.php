<?php

namespace App\Models;

use App\Support\MatchScoreReader;
use App\Support\PlayoffBracketBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayoffMatch extends Model
{
    public const ROUND_PRE_PRE_Q = 'ppq';

    public const ROUND_PRE_Q = 'pq';

    public const ROUND_QF = 'qf';

    public const ROUND_SF = 'sf';

    public const ROUND_F = 'f';

    protected $fillable = [
        'league_id',
        'group_card_id',
        'age_group_key',
        'round',
        'slot',
        'home_user_id',
        'away_user_id',
        'score',
        'winner_side',
        'winner_user_id',
        'match_date',
        'start_time',
        'venue',
        'court',
    ];

    protected function casts(): array
    {
        return [
            'match_date' => 'date',
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

    public function homeUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'home_user_id');
    }

    public function awayUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'away_user_id');
    }

    public function winnerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winner_user_id');
    }

    public function isPending(): bool
    {
        return $this->homeSideWon() === null;
    }

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

    public function bracketWinnerUserId(): ?int
    {
        if ($this->winner_user_id !== null) {
            return (int) $this->winner_user_id;
        }
        $won = $this->homeSideWon();
        if ($won === null) {
            return null;
        }

        return $won ? (int) $this->home_user_id : (int) $this->away_user_id;
    }

    public function roundLabel(): string
    {
        return match ($this->round) {
            self::ROUND_PRE_PRE_Q => 'Pre-Pre-Q',
            self::ROUND_PRE_Q => 'Pre-Q',
            self::ROUND_QF => 'Quarterfinal',
            self::ROUND_SF => 'Semifinal',
            default => 'Final',
        };
    }

    /**
     * Label when a bracket slot has no player yet (filled via Qualifier or Advance winners).
     */
    public function sidePlaceholderLabel(string $side): ?string
    {
        if ($side === 'home' && $this->home_user_id) {
            return null;
        }
        if ($side === 'away' && $this->away_user_id) {
            return null;
        }

        if ($this->round === self::ROUND_PRE_Q && $side === 'away') {
            $ppqSlots = PlayoffMatch::query()
                ->where('league_id', $this->league_id)
                ->where('group_card_id', $this->group_card_id)
                ->where('age_group_key', $this->age_group_key ?? '')
                ->where('round', self::ROUND_PRE_PRE_Q)
                ->count();
            if ($ppqSlots > 0) {
                $ppqSlot = PlayoffBracketBuilder::ppqFeedSlotForPqAway((int) $this->slot, $ppqSlots);

                return 'Winner of Pre-Pre-Q '.$ppqSlot;
            }

            return 'TBD';
        }

        if ($this->round === self::ROUND_QF && $side === 'away') {
            $preQSlot = [1 => 4, 2 => 1, 3 => 2, 4 => 3][$this->slot] ?? null;

            return $preQSlot ? 'Winner of Pre-Q '.$preQSlot : 'TBD';
        }

        if ($this->round === self::ROUND_SF) {
            return match ([$this->slot, $side]) {
                [1, 'home'] => 'Winner of QF 1',
                [1, 'away'] => 'Winner of QF 2',
                [2, 'home'] => 'Winner of QF 3',
                [2, 'away'] => 'Winner of QF 4',
                default => 'TBD',
            };
        }

        if ($this->round === self::ROUND_F) {
            return $side === 'home' ? 'Winner of Semifinal 1' : 'Winner of Semifinal 2';
        }

        return 'TBD';
    }
}
