<?php

namespace App\Support;

use App\Enums\GroupMatchFormat;
use App\Models\Group;
use App\Models\GroupCard;
use App\Models\GroupMatch;
use App\Models\League;
use App\Models\LeagueRegistration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

/**
 * Auto-schedule round-robin group-stage matches: one match per participant per week.
 * Play dates are spread Mon–Sat. Reschedule freely until the league match close date on the Matches page.
 */
final class SubgroupRoundRobinScheduler
{
    /**
     * @return array{created: int, removed: int, weeks: int}
     */
    public static function sync(
        League $league,
        GroupCard $groupCard,
        Group $group,
        ?string $ageGroupKey = null,
    ): array {
        if (! Schema::hasTable('group_matches')) {
            return ['created' => 0, 'removed' => 0, 'weeks' => 0];
        }

        if ($league->start_date === null) {
            return ['created' => 0, 'removed' => 0, 'weeks' => 0];
        }

        if (DivisionPlayoffPhase::locksGroupMatchScheduling($league->id, $groupCard->id, $ageGroupKey)) {
            return ['created' => 0, 'removed' => 0, 'weeks' => 0];
        }

        $rosterRegs = self::rosterRegistrations($league, $groupCard, $group, $ageGroupKey);
        $format = $groupCard->forcedMatchFormat() ?? GroupMatchFormat::Singles;
        $participants = self::participantsForFormat($rosterRegs, $format);

        if ($participants->count() < 2) {
            return ['created' => 0, 'removed' => 0, 'weeks' => 0];
        }

        $removed = self::removePendingAutoMatches($league, $groupCard, $group);

        $rounds = self::roundRobinPairings($participants, $format);
        $roundCount = count($rounds);

        if ($roundCount === 0) {
            return ['created' => 0, 'removed' => $removed, 'weeks' => 0];
        }

        $calendar = LeagueWeekCalendar::calendar($league->start_date, $roundCount);
        $weekEndSundays = $calendar['playWeekSundays'];
        $leagueStart = $league->start_date->copy()->startOfDay();

        $seedByUserId = self::seedMap($rosterRegs);
        $created = 0;

        foreach ($rounds as $roundIndex => $pairings) {
            $roundNumber = $roundIndex + 1;
            $weekEndSunday = Carbon::parse($weekEndSundays[$roundIndex])->startOfDay();
            $matchDates = LeagueWeekCalendar::spreadMatchDatesAcrossWeek(
                $weekEndSunday,
                $leagueStart,
                count($pairings),
                $roundIndex === 0,
            );

            foreach ($pairings as $pairIndex => $pairing) {
                $matchDate = $matchDates[$pairIndex] ?? $weekEndSunday->copy()->subDays(2)->format('Y-m-d');
                $exists = self::matchAlreadyExists(
                    $league->id,
                    $groupCard->id,
                    $group->id,
                    $pairing['home_user_id'],
                    $pairing['away_user_id'],
                    $pairing['home_partner_user_id'],
                    $pairing['away_partner_user_id'],
                );

                if ($exists) {
                    continue;
                }

                GroupMatch::query()->create([
                    'league_id' => $league->id,
                    'group_card_id' => $groupCard->id,
                    'group_id' => $group->id,
                    'format' => $format,
                    'home_user_id' => $pairing['home_user_id'],
                    'away_user_id' => $pairing['away_user_id'],
                    'home_partner_user_id' => $pairing['home_partner_user_id'],
                    'away_partner_user_id' => $pairing['away_partner_user_id'],
                    'home_seed' => $seedByUserId[$pairing['home_user_id']] ?? null,
                    'away_seed' => $seedByUserId[$pairing['away_user_id']] ?? null,
                    'match_date' => $matchDate,
                    'start_time' => 'TBD',
                    'venue' => null,
                    'court' => null,
                    'score' => null,
                    'winner_side' => null,
                    'winner_user_id' => null,
                    'sort_order' => $roundNumber,
                    'round_number' => $roundNumber,
                    'auto_scheduled' => true,
                ]);
                $created++;
            }
        }

        return [
            'created' => $created,
            'removed' => $removed,
            'weeks' => $roundCount,
        ];
    }

    /**
     * Sync every subgroup under a league division that has a roster.
     *
     * @return array{created: int, removed: int, subgroup_count: int}
     */
    public static function syncDivision(
        League $league,
        GroupCard $groupCard,
        ?string $ageGroupKey = null,
    ): array {
        $created = 0;
        $removed = 0;
        $subgroupCount = 0;

        $groups = Group::query()
            ->whereHas('groupCards', fn ($q) => $q->whereKey($groupCard->id))
            ->when(
                Schema::hasColumn('groups', 'age_group_key') && $ageGroupKey !== null,
                fn ($q) => $q->where(function ($qq) use ($ageGroupKey) {
                    $qq->whereNull('age_group_key')->orWhere('age_group_key', $ageGroupKey);
                })
            )
            ->orderBy('name')
            ->get();

        foreach ($groups as $group) {
            $result = self::sync($league, $groupCard, $group, $ageGroupKey);
            if ($result['created'] > 0 || $result['removed'] > 0) {
                $subgroupCount++;
            }
            $created += $result['created'];
            $removed += $result['removed'];
        }

        return [
            'created' => $created,
            'removed' => $removed,
            'subgroup_count' => $subgroupCount,
        ];
    }

    public static function formatDivisionSyncSummary(array $totals, string $prefix = ''): string
    {
        $lead = $prefix !== '' ? $prefix.' ' : '';

        if ($totals['created'] > 0) {
            $groups = (int) ($totals['subgroup_count'] ?? 0);

            return $lead.sprintf(
                '%d match%s scheduled%s.',
                $totals['created'],
                $totals['created'] === 1 ? '' : 'es',
                $groups > 0 ? ' across '.$groups.' subgroup'.($groups === 1 ? '' : 's') : '',
            );
        }

        return $lead.'No new matches scheduled (check rosters or close date).';
    }

    /**
     * @return Collection<int, LeagueRegistration>
     */
    private static function rosterRegistrations(
        League $league,
        GroupCard $groupCard,
        Group $group,
        ?string $ageGroupKey,
    ): Collection {
        return LeagueRegistration::query()
            ->where('league_id', $league->id)
            ->where('group_id', $group->id)
            ->where(function ($q) use ($groupCard) {
                $q->whereNull('group_card_id')->orWhere('group_card_id', $groupCard->id);
            })
            ->when(
                $ageGroupKey !== null && Schema::hasColumn('league_registrations', 'age_group_key'),
                fn ($q) => $q->where('age_group_key', $ageGroupKey)
            )
            ->with('user')
            ->orderBy('id')
            ->get();
    }

    /**
     * @param  Collection<int, LeagueRegistration>  $regs
     * @return Collection<int, array{
     *     key: string,
     *     home_user_id: int,
     *     away_user_id: int,
     *     home_partner_user_id: int|null,
     *     away_partner_user_id: int|null
     * }>
     */
    private static function participantsForFormat(Collection $regs, GroupMatchFormat $format): Collection
    {
        if ($format === GroupMatchFormat::Doubles) {
            return LeagueRegistrationRoster::teamOptionsForMatch($regs)
                ->filter(fn (array $team) => $team['is_complete'])
                ->map(fn (array $team) => [
                    'key' => $team['key'],
                    'user_id' => $team['primary_user_id'],
                    'partner_user_id' => $team['partner_user_id'],
                ])
                ->values();
        }

        return LeagueRegistrationRoster::collapseForDisplay($regs)
            ->map(fn (array $entry) => (int) ($entry['user']?->id ?? 0))
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values()
            ->map(fn (int $userId) => [
                'key' => (string) $userId,
                'user_id' => $userId,
                'partner_user_id' => null,
            ]);
    }

    /**
     * Circle-method round-robin: each participant plays once per round.
     *
     * @param  Collection<int, array<string, mixed>>  $participants
     * @return list<list<array{
     *     home_user_id: int,
     *     away_user_id: int,
     *     home_partner_user_id: int|null,
     *     away_partner_user_id: int|null
     * }>>
     */
    private static function roundRobinPairings(Collection $participants, GroupMatchFormat $format): array
    {
        $slots = $participants->values()->all();
        $count = count($slots);

        if ($count < 2) {
            return [];
        }

        $bye = null;
        if ($count % 2 === 1) {
            $slots[] = ['key' => 'bye', 'user_id' => 0, 'partner_user_id' => null];
            $count++;
        }

        $rounds = $count - 1;
        $half = (int) ($count / 2);
        $result = [];

        for ($round = 0; $round < $rounds; $round++) {
            $roundPairings = [];

            for ($i = 0; $i < $half; $i++) {
                $a = $slots[$i];
                $b = $slots[$count - 1 - $i];

                if (($a['user_id'] ?? 0) === 0 || ($b['user_id'] ?? 0) === 0) {
                    continue;
                }

                if ($format === GroupMatchFormat::Doubles) {
                    $roundPairings[] = [
                        'home_user_id' => (int) $a['user_id'],
                        'away_user_id' => (int) $b['user_id'],
                        'home_partner_user_id' => $a['partner_user_id'] ?? null,
                        'away_partner_user_id' => $b['partner_user_id'] ?? null,
                    ];
                } else {
                    $roundPairings[] = [
                        'home_user_id' => (int) $a['user_id'],
                        'away_user_id' => (int) $b['user_id'],
                        'home_partner_user_id' => null,
                        'away_partner_user_id' => null,
                    ];
                }
            }

            $result[] = $roundPairings;

            if ($count > 2) {
                $rotated = [$slots[0]];
                $tail = array_slice($slots, 1);
                $last = array_pop($tail);
                array_unshift($tail, $last);
                $slots = array_merge($rotated, $tail);
            }
        }

        return $result;
    }

    /**
     * @param  Collection<int, LeagueRegistration>  $regs
     * @return array<int, int>
     */
    private static function seedMap(Collection $regs): array
    {
        $map = [];
        foreach ($regs as $index => $reg) {
            if ($reg->user_id) {
                $map[(int) $reg->user_id] = $index + 1;
            }
        }

        return $map;
    }

    private static function removePendingAutoMatches(League $league, GroupCard $groupCard, Group $group): int
    {
        $query = GroupMatch::query()
            ->where('league_id', $league->id)
            ->where('group_card_id', $groupCard->id)
            ->where('group_id', $group->id)
            ->where(fn ($q) => $q->whereNull('score')->orWhere('score', ''))
            ->where(fn ($q) => $q->whereNull('winner_side')->orWhere('winner_side', ''));

        if (Schema::hasColumn('group_matches', 'auto_scheduled')) {
            $query->where('auto_scheduled', true);
        }

        $count = (clone $query)->count();
        $query->delete();

        return $count;
    }

    private static function matchAlreadyExists(
        int $leagueId,
        int $groupCardId,
        int $groupId,
        int $homeUserId,
        int $awayUserId,
        ?int $homePartnerId,
        ?int $awayPartnerId,
    ): bool {
        $query = GroupMatch::query()
            ->where('league_id', $leagueId)
            ->where('group_card_id', $groupCardId)
            ->where('group_id', $groupId)
            ->where(function ($q) use ($homeUserId, $awayUserId, $homePartnerId, $awayPartnerId) {
                $q->where(function ($qq) use ($homeUserId, $awayUserId, $homePartnerId, $awayPartnerId) {
                    $qq->where('home_user_id', $homeUserId)
                        ->where('away_user_id', $awayUserId)
                        ->when($homePartnerId, fn ($q) => $q->where('home_partner_user_id', $homePartnerId))
                        ->when($awayPartnerId, fn ($q) => $q->where('away_partner_user_id', $awayPartnerId));
                })->orWhere(function ($qq) use ($homeUserId, $awayUserId, $homePartnerId, $awayPartnerId) {
                    $qq->where('home_user_id', $awayUserId)
                        ->where('away_user_id', $homeUserId)
                        ->when($awayPartnerId, fn ($q) => $q->where('home_partner_user_id', $awayPartnerId))
                        ->when($homePartnerId, fn ($q) => $q->where('away_partner_user_id', $homePartnerId));
                });
            });

        return $query->exists();
    }
}
