<?php

namespace App\Support;

use App\Enums\PlayoffQualifierPath;
use App\Models\GroupCard;
use App\Models\League;
use App\Models\PlayoffMatch;
use App\Models\PlayoffQualifier;
use Illuminate\Support\Facades\Schema;

final class PlayoffBracketBuilder
{
    /** QF slot => Pre-Q match slot whose winner plays QF away (1 vs 8 cross, 4 Pre-Q matches). */
    private const QF_AWAY_FROM_PREQ_FOUR = [
        1 => 4,
        2 => 1,
        3 => 2,
        4 => 3,
    ];

    /** QF slot => Pre-Q home/away when eight Round of 16 matches feed four quarterfinals. */
    private const QF_HOME_FROM_PREQ_EIGHT = [
        1 => 1,
        2 => 4,
        3 => 3,
        4 => 2,
    ];

    private const QF_AWAY_FROM_PREQ_EIGHT = [
        1 => 8,
        2 => 5,
        3 => 6,
        4 => 7,
    ];

    /** QF slot => index in rank-sorted quarter list for QF home (R1, R4, R3, R2). */
    private const QF_HOME_QUARTER_INDEX = [
        1 => 0,
        2 => 3,
        3 => 2,
        4 => 1,
    ];

    public static function hasPlayoffAssignments(int $leagueId, int $groupCardId, string $ageKeyDb): bool
    {
        if (! Schema::hasTable('playoff_qualifiers')) {
            return false;
        }

        return PlayoffQualifier::query()
            ->where('league_id', $leagueId)
            ->where('group_card_id', $groupCardId)
            ->where('age_group_key', $ageKeyDb)
            ->whereIn('path', [
                PlayoffQualifierPath::PrePreQ->value,
                PlayoffQualifierPath::RoundOf16->value,
                PlayoffQualifierPath::Quarter->value,
                PlayoffQualifierPath::LEGACY_DIRECT_QF,
                PlayoffQualifierPath::LEGACY_PRE_Q,
            ])
            ->exists();
    }

    /**
     * @return array{ppq: list<int>, r16: list<int>, quarter: list<int>}
     */
    public static function qualifierPlayerIdsByPath(int $leagueId, int $groupCardId, string $ageKeyDb): array
    {
        $byPath = [
            PlayoffQualifierPath::PrePreQ->value => [],
            PlayoffQualifierPath::RoundOf16->value => [],
            PlayoffQualifierPath::Quarter->value => [],
        ];

        if (! Schema::hasTable('playoff_qualifiers')) {
            return ['ppq' => [], 'r16' => [], 'quarter' => []];
        }

        foreach (PlayoffQualifier::query()
            ->where('league_id', $leagueId)
            ->where('group_card_id', $groupCardId)
            ->where('age_group_key', $ageKeyDb)
            ->get() as $q) {
            $path = PlayoffQualifierPath::normalizeStored($q->path);
            if ($path === PlayoffQualifierPath::Eliminated->value || $path === '') {
                continue;
            }
            if (isset($byPath[$path])) {
                $byPath[$path][] = (int) $q->user_id;
            }
        }

        return [
            'ppq' => $byPath[PlayoffQualifierPath::PrePreQ->value],
            'r16' => $byPath[PlayoffQualifierPath::RoundOf16->value],
            'quarter' => $byPath[PlayoffQualifierPath::Quarter->value],
        ];
    }

    /**
     * True when saved Qualifier paths expect a different bracket than what is in the database.
     */
    public static function bracketStructureIsStale(int $leagueId, int $groupCardId, string $ageKeyDb): bool
    {
        if (! Schema::hasTable('playoff_matches')) {
            return false;
        }

        $paths = self::qualifierPlayerIdsByPath($leagueId, $groupCardId, $ageKeyDb);
        $ppqPlayers = count($paths['ppq']);
        $r16Players = count($paths['r16']);

        if ($ppqPlayers < 2 || $r16Players < 1) {
            return false;
        }

        $expectedPpqMatches = count(self::seedPairings($ppqPlayers));
        $expectedPqMatches = $r16Players;

        $ppqMatchCount = PlayoffMatch::query()
            ->where('league_id', $leagueId)
            ->where('group_card_id', $groupCardId)
            ->where('age_group_key', $ageKeyDb)
            ->where('round', PlayoffMatch::ROUND_PRE_PRE_Q)
            ->count();

        $pqMatches = PlayoffMatch::query()
            ->where('league_id', $leagueId)
            ->where('group_card_id', $groupCardId)
            ->where('age_group_key', $ageKeyDb)
            ->where('round', PlayoffMatch::ROUND_PRE_Q)
            ->get();

        if ($ppqMatchCount !== $expectedPpqMatches) {
            return true;
        }

        if ($pqMatches->count() !== $expectedPqMatches) {
            return true;
        }

        $r16Set = array_flip($paths['r16']);
        foreach ($pqMatches as $pq) {
            if ($pq->away_user_id && isset($r16Set[(int) $pq->away_user_id])) {
                return true;
            }
        }

        return false;
    }

    public static function clearBracket(int $leagueId, int $groupCardId, string $ageKeyDb): void
    {
        if (! Schema::hasTable('playoff_matches')) {
            return;
        }

        PlayoffMatch::query()
            ->where('league_id', $leagueId)
            ->where('group_card_id', $groupCardId)
            ->where('age_group_key', $ageKeyDb)
            ->delete();
    }

    public static function clearDivisionPlayoffData(int $leagueId, int $groupCardId, string $ageKeyDb): void
    {
        if (Schema::hasTable('playoff_qualifiers')) {
            PlayoffQualifier::query()
                ->where('league_id', $leagueId)
                ->where('group_card_id', $groupCardId)
                ->where('age_group_key', $ageKeyDb)
                ->delete();
        }

        self::clearBracket($leagueId, $groupCardId, $ageKeyDb);
    }

    /** Remove all qualifier paths and playoff matches for a group card (every age band). */
    public static function clearAllDivisionPlayoffData(int $leagueId, int $groupCardId): void
    {
        if (Schema::hasTable('playoff_qualifiers')) {
            PlayoffQualifier::query()
                ->where('league_id', $leagueId)
                ->where('group_card_id', $groupCardId)
                ->delete();
        }

        if (Schema::hasTable('playoff_matches')) {
            PlayoffMatch::query()
                ->where('league_id', $leagueId)
                ->where('group_card_id', $groupCardId)
                ->delete();
        }
    }

    public static function rebuild(League $league, GroupCard $groupCard, ?string $ageGroupKey): string
    {
        if (! Schema::hasTable('playoff_matches') || ! Schema::hasTable('playoff_qualifiers')) {
            return '';
        }

        $ageKeyDb = $ageGroupKey ?? '';

        if (! self::hasPlayoffAssignments($league->id, $groupCard->id, $ageKeyDb)) {
            self::clearBracket($league->id, $groupCard->id, $ageKeyDb);

            return 'Playoff bracket is empty until you assign paths on Qualifier and save.';
        }

        $standings = LeagueStandingsBuilder::forSubGroup($league, $groupCard, $ageGroupKey, null);
        $rankByUser = [];
        foreach ($standings as $row) {
            $rankByUser[(int) $row['userId']] = (int) $row['rank'];
        }

        $qualifiers = PlayoffQualifier::query()
            ->where('league_id', $league->id)
            ->where('group_card_id', $groupCard->id)
            ->where('age_group_key', $ageKeyDb)
            ->get();

        $byPath = [
            PlayoffQualifierPath::PrePreQ->value => [],
            PlayoffQualifierPath::RoundOf16->value => [],
            PlayoffQualifierPath::Quarter->value => [],
        ];

        foreach ($qualifiers as $q) {
            $path = PlayoffQualifierPath::normalizeStored($q->path);
            if ($path === PlayoffQualifierPath::Eliminated->value || $path === '') {
                continue;
            }
            if (isset($byPath[$path])) {
                $byPath[$path][] = (int) $q->user_id;
            }
        }

        $sortByRank = function (int $a, int $b) use ($rankByUser): int {
            $ra = $rankByUser[$a] ?? 9999;
            $rb = $rankByUser[$b] ?? 9999;

            return $ra <=> $rb;
        };

        foreach ($byPath as &$ids) {
            usort($ids, $sortByRank);
        }
        unset($ids);

        self::clearBracket($league->id, $groupCard->id, $ageKeyDb);

        $ppqCount = 0;
        $pqCount = 0;
        $qfCount = 0;

        $ppqIds = $byPath[PlayoffQualifierPath::PrePreQ->value];
        $pqIds = $byPath[PlayoffQualifierPath::RoundOf16->value];
        $quarterIds = $byPath[PlayoffQualifierPath::Quarter->value];
        $hasPpqFeeder = count($ppqIds) >= 2;
        $hasR16Direct = count($pqIds) >= 1;

        if ($hasPpqFeeder) {
            $slot = 1;
            foreach (self::seedPairings(count($ppqIds)) as [$hi, $lo]) {
                self::createMatch($league, $groupCard, $ageKeyDb, PlayoffMatch::ROUND_PRE_PRE_Q, $slot, $ppqIds[$hi] ?? null, $ppqIds[$lo] ?? null);
                $slot++;
                $ppqCount++;
            }
        }

        if ($hasR16Direct && $hasPpqFeeder) {
            // Top seeds wait in Round of 16; Pre-Pre-Q winners fill the away slot (1 vs 8 style).
            foreach ($pqIds as $index => $homeId) {
                self::createMatch($league, $groupCard, $ageKeyDb, PlayoffMatch::ROUND_PRE_Q, $index + 1, $homeId, null);
                $pqCount++;
            }
        } elseif (count($pqIds) >= 2) {
            $slot = 1;
            foreach (self::seedPairings(count($pqIds)) as [$hi, $lo]) {
                self::createMatch($league, $groupCard, $ageKeyDb, PlayoffMatch::ROUND_PRE_Q, $slot, $pqIds[$hi] ?? null, $pqIds[$lo] ?? null);
                $slot++;
                $pqCount++;
            }
        }

        $needsQfShell = $quarterIds !== [] || $pqCount > 0 || $ppqCount > 0;
        if ($needsQfShell) {
            $qfCount = self::createQuarterfinalShell($league, $groupCard, $ageKeyDb, $quarterIds);
        }

        if ($ppqCount === 0 && $pqCount === 0 && $qfCount === 0) {
            self::clearBracket($league->id, $groupCard->id, $ageKeyDb);

            return 'No playoff rounds to build — assign Quarter, Pre-Q, or Pre-Pre-Q on Qualifier first.';
        }

        $filledPpqIntoPq = 0;
        if ($ppqCount > 0 && $pqCount > 0) {
            $filledPpqIntoPq = self::feedPpqWinnersIntoPreQuarter($league->id, $groupCard->id, $ageKeyDb);
        }

        $parts = [];
        if ($ppqCount > 0) {
            $parts[] = "{$ppqCount} Pre-Pre-Q match(es)";
        }
        if ($pqCount > 0) {
            if ($hasPpqFeeder && $hasR16Direct) {
                $parts[] = "{$pqCount} Round of 16 (".count($pqIds).' direct home + Pre-Pre-Q winners on away)';
            } else {
                $parts[] = "{$pqCount} Round of 16 (Pre-Q)";
            }
        }
        if ($qfCount > 0) {
            $parts[] = "{$qfCount} quarterfinal slot(s)";
        }
        if ($filledPpqIntoPq > 0) {
            $parts[] = "{$filledPpqIntoPq} Pre-Pre-Q winner(s) already placed in Round of 16";
        }

        return 'Playoff bracket built: '.implode('; ', $parts).'.';
    }

    /**
     * After Pre-Pre-Q completes, place winners into Round of 16 away slots (direct seeds on home).
     */
    public static function feedPpqWinnersIntoPreQuarter(int $leagueId, int $groupCardId, string $ageKeyDb): int
    {
        $all = self::matchesKeyed($leagueId, $groupCardId, $ageKeyDb);
        $ppqSlots = $all->filter(fn (PlayoffMatch $m) => $m->round === PlayoffMatch::ROUND_PRE_PRE_Q)->count();
        if ($ppqSlots < 1) {
            return 0;
        }

        $winner = fn (string $round, int $slot) => $all->get($round.'-'.$slot)?->bracketWinnerUserId();

        $filled = 0;
        foreach ($all as $pq) {
            if ($pq->round !== PlayoffMatch::ROUND_PRE_Q || $pq->away_user_id) {
                continue;
            }
            $ppqSlot = self::ppqFeedSlotForPqAway((int) $pq->slot, $ppqSlots);
            $w = $winner(PlayoffMatch::ROUND_PRE_PRE_Q, $ppqSlot);
            if (! $w) {
                continue;
            }
            $pq->away_user_id = $w;
            $pq->save();
            $filled++;
        }

        return $filled;
    }

    /**
     * After Pre-Q completes, copy winners into quarterfinal slots.
     */
    public static function feedPreQWinnersIntoQuarterfinals(int $leagueId, int $groupCardId, string $ageKeyDb): int
    {
        $all = self::matchesKeyed($leagueId, $groupCardId, $ageKeyDb);
        $pqMatches = $all->filter(fn (PlayoffMatch $m) => $m->round === PlayoffMatch::ROUND_PRE_Q);
        $pqCount = $pqMatches->count();

        if ($pqCount === 0) {
            return 0;
        }

        $winner = fn (string $round, int $slot) => $all->get($round.'-'.$slot)?->bracketWinnerUserId();

        $filled = 0;

        if ($pqCount >= 8) {
            foreach (self::QF_HOME_FROM_PREQ_EIGHT as $qfSlot => $pqSlot) {
                $w = $winner(PlayoffMatch::ROUND_PRE_Q, $pqSlot);
                if (! $w) {
                    continue;
                }
                /** @var PlayoffMatch|null $qf */
                $qf = $all->get(PlayoffMatch::ROUND_QF.'-'.$qfSlot);
                if (! $qf || $qf->home_user_id) {
                    continue;
                }
                $qf->home_user_id = $w;
                $qf->save();
                $filled++;
            }
            foreach (self::QF_AWAY_FROM_PREQ_EIGHT as $qfSlot => $pqSlot) {
                $w = $winner(PlayoffMatch::ROUND_PRE_Q, $pqSlot);
                if (! $w) {
                    continue;
                }
                /** @var PlayoffMatch|null $qf */
                $qf = $all->get(PlayoffMatch::ROUND_QF.'-'.$qfSlot);
                if (! $qf || $qf->away_user_id) {
                    continue;
                }
                $qf->away_user_id = $w;
                $qf->save();
                $filled++;
            }

            return $filled;
        }

        foreach (self::QF_AWAY_FROM_PREQ_FOUR as $qfSlot => $pqSlot) {
            $w = $winner(PlayoffMatch::ROUND_PRE_Q, $pqSlot);
            if (! $w) {
                continue;
            }
            /** @var PlayoffMatch|null $qf */
            $qf = $all->get(PlayoffMatch::ROUND_QF.'-'.$qfSlot);
            if (! $qf || $qf->away_user_id) {
                continue;
            }
            $qf->away_user_id = $w;
            $qf->save();
            $filled++;
        }

        return $filled;
    }

    /**
     * Pre-Q away slot => Pre-Pre-Q match slot whose winner feeds in (1 vs 8 cross).
     */
    public static function ppqFeedSlotForPqAway(int $pqSlot, int $ppqMatchCount): int
    {
        if ($ppqMatchCount < 1) {
            return 1;
        }

        return max(1, min($ppqMatchCount, $ppqMatchCount + 1 - $pqSlot));
    }

    /**
     * @return list<array{0: int, 1: int}>} 0-based indexes into the rank-sorted player list
     */
    public static function seedPairings(int $count): array
    {
        if ($count < 2) {
            return [];
        }

        if ($count === 2) {
            return [[0, 1]];
        }

        if (($count & ($count - 1)) === 0) {
            return self::powerOfTwoPairings($count);
        }

        $pairs = [];
        for ($i = 0; $i < (int) floor($count / 2); $i++) {
            $pairs[] = [$i, $count - 1 - $i];
        }

        return $pairs;
    }

    /**
     * @return list<array{0: int, 1: int}>
     */
    private static function powerOfTwoPairings(int $count): array
    {
        $rounds = (int) log($count, 2);
        $seeds = [1];
        for ($i = 0; $i < $rounds; $i++) {
            $next = [];
            $sum = (2 ** ($i + 1)) + 1;
            foreach ($seeds as $s) {
                $next[] = $s;
                $next[] = $sum - $s;
            }
            $seeds = $next;
        }

        $pairs = [];
        for ($i = 0; $i < $count; $i += 2) {
            $pairs[] = [$seeds[$i] - 1, $seeds[$i + 1] - 1];
        }

        return $pairs;
    }

    /**
     * @param  list<int>  $quarterIds
     */
    private static function createQuarterfinalShell(League $league, GroupCard $groupCard, string $ageKeyDb, array $quarterIds): int
    {
        $qfCount = 0;

        for ($qfSlot = 1; $qfSlot <= 4; $qfSlot++) {
            $homeId = null;
            if ($quarterIds !== []) {
                $qIndex = self::QF_HOME_QUARTER_INDEX[$qfSlot];
                $homeId = $quarterIds[$qIndex] ?? null;
            }
            self::createMatch($league, $groupCard, $ageKeyDb, PlayoffMatch::ROUND_QF, $qfSlot, $homeId, null);
            $qfCount++;
        }

        self::createMatch($league, $groupCard, $ageKeyDb, PlayoffMatch::ROUND_SF, 1, null, null);
        self::createMatch($league, $groupCard, $ageKeyDb, PlayoffMatch::ROUND_SF, 2, null, null);
        self::createMatch($league, $groupCard, $ageKeyDb, PlayoffMatch::ROUND_F, 1, null, null);

        return $qfCount;
    }

    /**
     * @return \Illuminate\Support\Collection<string, PlayoffMatch>
     */
    private static function matchesKeyed(int $leagueId, int $groupCardId, string $ageKeyDb)
    {
        return PlayoffMatch::query()
            ->where('league_id', $leagueId)
            ->where('group_card_id', $groupCardId)
            ->where('age_group_key', $ageKeyDb)
            ->get()
            ->keyBy(fn (PlayoffMatch $m) => $m->round.'-'.$m->slot);
    }

    private static function createMatch(
        League $league,
        GroupCard $groupCard,
        string $ageKeyDb,
        string $round,
        int $slot,
        ?int $homeUserId,
        ?int $awayUserId,
    ): void {
        PlayoffMatch::query()->create([
            'league_id' => $league->id,
            'group_card_id' => $groupCard->id,
            'age_group_key' => $ageKeyDb,
            'round' => $round,
            'slot' => $slot,
            'home_user_id' => $homeUserId,
            'away_user_id' => $awayUserId,
            'score' => null,
            'winner_side' => null,
            'winner_user_id' => null,
            'match_date' => null,
            'start_time' => null,
        ]);
    }
}
