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
    /** @var list<GroupMatch> */
    private static array $deferredMatchNotifications = [];

    private static bool $deferMatchNotifications = false;

    /**
     * @return \Illuminate\Support\Collection<int, Group>
     */
    public static function divisionGroups(League $league, GroupCard $groupCard, ?string $ageGroupKey = null): Collection
    {
        return Group::query()
            ->where(function ($q) use ($groupCard) {
                $q->whereHas('groupCards', fn ($qq) => $qq->whereKey($groupCard->id));
                if (Schema::hasColumn('groups', 'group_card_id')) {
                    $q->orWhere('group_card_id', $groupCard->id);
                }
            })
            ->when(
                Schema::hasColumn('groups', 'age_group_key') && $ageGroupKey !== null,
                fn ($q) => $q->where(function ($qq) use ($ageGroupKey) {
                    $qq->whereNull('age_group_key')->orWhere('age_group_key', $ageGroupKey);
                })
            )
            ->orderBy('name')
            ->get()
            ->unique('id')
            ->values();
    }

    /**
     * @return array{created: int, removed: int, weeks: int}
     */
    public static function sync(
        League $league,
        GroupCard $groupCard,
        Group $group,
        ?string $ageGroupKey = null,
        bool $reschedulePending = false,
        ?Carbon $divisionStartOverride = null,
    ): array {
        if (! Schema::hasTable('group_matches')) {
            return ['created' => 0, 'removed' => 0, 'weeks' => 0];
        }

        $divisionStart = $divisionStartOverride ?? DivisionScheduleWindow::startDate($league, $groupCard);
        if ($divisionStart === null) {
            return ['created' => 0, 'removed' => 0, 'weeks' => 0];
        }

        if (DivisionPlayoffPhase::locksGroupMatchScheduling($league->id, $groupCard->id, $ageGroupKey)) {
            return ['created' => 0, 'removed' => 0, 'weeks' => 0];
        }

        $rosterRegs = self::rosterRegistrations($league, $groupCard, $group, $ageGroupKey);
        $format = $groupCard->forcedMatchFormat() ?? GroupMatchFormat::Singles;
        $participants = self::participantsForFormat($rosterRegs, $format);

        if ($participants->count() < 2) {
            return ['created' => 0, 'removed' => 0, 'weeks' => 0, 'updated' => 0];
        }

        if ($reschedulePending && self::subgroupHasAutoScheduledMatches($league, $groupCard, $group)) {
            return self::reschedulePendingAutoMatches($league, $groupCard, $group, $participants, $format, $divisionStart);
        }

        $removed = self::removePendingAutoMatches($league, $groupCard, $group);

        $rounds = self::roundRobinPairings($participants, $format);
        $roundCount = count($rounds);

        if ($roundCount === 0) {
            return ['created' => 0, 'removed' => $removed, 'weeks' => 0, 'updated' => 0];
        }

        $calendar = LeagueWeekCalendar::calendar($divisionStart, $roundCount);
        $weekEndSundays = $calendar['playWeekSundays'];
        $leagueStart = $divisionStart->copy()->startOfDay();

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

                $groupMatch = GroupMatch::query()->create([
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
                $groupMatch->load(['homeUser', 'awayUser', 'homePartnerUser', 'awayPartnerUser', 'group', 'league', 'groupCard']);
                self::queueMatchNotification($groupMatch);
                $created++;
            }
        }

        return [
            'created' => $created,
            'removed' => $removed,
            'weeks' => $roundCount,
            'updated' => 0,
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
        bool $reschedulePending = false,
        ?Carbon $divisionStartOverride = null,
    ): array {
        MatchScheduleMailQueue::beginBulkScheduling();
        self::$deferMatchNotifications = true;
        self::$deferredMatchNotifications = [];

        $created = 0;
        $removed = 0;
        $updated = 0;
        $subgroupCount = 0;
        $subgroupDetails = [];

        $groups = self::divisionGroups($league, $groupCard, $ageGroupKey);

        foreach ($groups as $group) {
            $result = self::sync($league, $groupCard, $group, $ageGroupKey, $reschedulePending, $divisionStartOverride);
            $groupCreated = (int) $result['created'];
            $groupUpdated = (int) ($result['updated'] ?? 0);

            if ($groupCreated > 0 || $result['removed'] > 0 || $groupUpdated > 0) {
                $subgroupCount++;
            }
            $created += $groupCreated;
            $removed += $result['removed'];
            $updated += $groupUpdated;

            $detail = [
                'name' => (string) $group->name,
                'created' => $groupCreated,
                'updated' => $groupUpdated,
            ];
            if ($groupCreated === 0 && $groupUpdated === 0) {
                $note = self::syncSkipReason($league, $groupCard, $group, $ageGroupKey, $divisionStartOverride);
                if ($note !== null) {
                    $detail['note'] = $note;
                }
            }
            $subgroupDetails[] = $detail;
        }

        foreach (self::$deferredMatchNotifications as $match) {
            GroupMatchScheduleNotifier::notifyParticipants($match);
        }
        self::$deferMatchNotifications = false;
        self::$deferredMatchNotifications = [];

        return [
            'created' => $created,
            'removed' => $removed,
            'updated' => $updated,
            'subgroup_count' => $subgroupCount,
            'subgroups' => $subgroupDetails,
        ];
    }

    public static function formatDivisionSyncSummary(array $totals, string $prefix = ''): string
    {
        $lead = $prefix !== '' ? $prefix.' ' : '';

        if (($totals['updated'] ?? 0) > 0) {
            $groups = (int) ($totals['subgroup_count'] ?? 0);

            return $lead.sprintf(
                '%d match date%s updated%s. Notification emails are queued.',
                $totals['updated'],
                $totals['updated'] === 1 ? '' : 's',
                $groups > 0 ? ' across '.$groups.' subgroup'.($groups === 1 ? '' : 's') : '',
            ).self::formatSubgroupDetailSuffix($totals);
        }

        if ($totals['created'] > 0) {
            $groups = (int) ($totals['subgroup_count'] ?? 0);

            return $lead.sprintf(
                '%d match%s scheduled%s. Notification emails are queued.',
                $totals['created'],
                $totals['created'] === 1 ? '' : 'es',
                $groups > 0 ? ' across '.$groups.' subgroup'.($groups === 1 ? '' : 's') : '',
            ).self::formatSubgroupDetailSuffix($totals);
        }

        $suffix = self::formatSubgroupDetailSuffix($totals);

        return $lead.'No new matches scheduled (check rosters or close date).'.$suffix;
    }

    /**
     * @param  array<string, mixed>  $totals
     */
    private static function formatSubgroupDetailSuffix(array $totals): string
    {
        $subgroups = $totals['subgroups'] ?? [];
        if (! is_array($subgroups) || $subgroups === []) {
            return '';
        }

        $lines = [];
        foreach ($subgroups as $row) {
            if (! is_array($row)) {
                continue;
            }
            $name = trim((string) ($row['name'] ?? 'Subgroup'));
            $groupCreated = (int) ($row['created'] ?? 0);
            $groupUpdated = (int) ($row['updated'] ?? 0);
            $note = trim((string) ($row['note'] ?? ''));

            if ($groupCreated > 0) {
                $lines[] = $name.': '.$groupCreated.' match'.($groupCreated === 1 ? '' : 'es').' scheduled';
            } elseif ($groupUpdated > 0) {
                $lines[] = $name.': '.$groupUpdated.' date'.($groupUpdated === 1 ? '' : 's').' updated';
            } elseif ($note !== '') {
                $lines[] = $name.': '.$note;
            }
        }

        if ($lines === []) {
            return '';
        }

        return ' '.implode('; ', $lines).'.';
    }

    private static function queueMatchNotification(GroupMatch $groupMatch): void
    {
        if (self::$deferMatchNotifications) {
            self::$deferredMatchNotifications[] = $groupMatch;

            return;
        }

        GroupMatchScheduleNotifier::notifyParticipants($groupMatch);
    }

    private static function subgroupHasAutoScheduledMatches(League $league, GroupCard $groupCard, Group $group): bool
    {
        if (! Schema::hasTable('group_matches')) {
            return false;
        }

        $query = GroupMatch::query()
            ->where('league_id', $league->id)
            ->where('group_card_id', $groupCard->id)
            ->where('group_id', $group->id);

        if (Schema::hasColumn('group_matches', 'auto_scheduled')) {
            $query->where('auto_scheduled', true);
        }

        return $query->exists();
    }

    private static function syncSkipReason(
        League $league,
        GroupCard $groupCard,
        Group $group,
        ?string $ageGroupKey,
        ?Carbon $divisionStartOverride = null,
    ): ?string {
        if (($divisionStartOverride ?? DivisionScheduleWindow::startDate($league, $groupCard)) === null) {
            return 'division start date not set';
        }

        if (DivisionPlayoffPhase::locksGroupMatchScheduling($league->id, $groupCard->id, $ageGroupKey)) {
            return 'scheduling locked (playoffs active)';
        }

        $rosterRegs = self::rosterRegistrations($league, $groupCard, $group, $ageGroupKey);
        $format = $groupCard->forcedMatchFormat() ?? GroupMatchFormat::Singles;
        $participants = self::participantsForFormat($rosterRegs, $format);

        if ($participants->count() < 2) {
            return 'need at least 2 players in roster';
        }

        return 'round-robin already up to date';
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

    /**
     * @param  Collection<int, array{user_id: int, partner_user_id: int|null}>  $participants
     * @return array{created: int, removed: int, weeks: int, updated: int}
     */
    private static function reschedulePendingAutoMatches(
        League $league,
        GroupCard $groupCard,
        Group $group,
        Collection $participants,
        GroupMatchFormat $format,
        Carbon $divisionStart,
    ): array {
        $rounds = self::roundRobinPairings($participants, $format);
        $roundCount = count($rounds);

        if ($roundCount === 0) {
            return ['created' => 0, 'removed' => 0, 'weeks' => 0, 'updated' => 0];
        }

        $calendar = LeagueWeekCalendar::calendar($divisionStart, $roundCount);
        $weekEndSundays = $calendar['playWeekSundays'];
        $leagueStart = $divisionStart->copy()->startOfDay();
        $updated = 0;

        foreach ($rounds as $roundIndex => $pairings) {
            $weekEndSunday = Carbon::parse($weekEndSundays[$roundIndex])->startOfDay();
            $matchDates = LeagueWeekCalendar::spreadMatchDatesAcrossWeek(
                $weekEndSunday,
                $leagueStart,
                count($pairings),
                $roundIndex === 0,
            );

            foreach ($pairings as $pairIndex => $pairing) {
                $matchDate = $matchDates[$pairIndex] ?? $weekEndSunday->copy()->subDays(2)->format('Y-m-d');
                $match = self::findPendingAutoMatch(
                    $league->id,
                    $groupCard->id,
                    $group->id,
                    (int) $pairing['home_user_id'],
                    (int) $pairing['away_user_id'],
                    $pairing['home_partner_user_id'] ? (int) $pairing['home_partner_user_id'] : null,
                    $pairing['away_partner_user_id'] ? (int) $pairing['away_partner_user_id'] : null,
                );

                if (! $match instanceof GroupMatch) {
                    continue;
                }

                $oldDate = $match->match_date?->format('Y-m-d');
                if ($oldDate === $matchDate) {
                    continue;
                }

                $match->update(['match_date' => $matchDate]);
                $updated++;
                $match->load(['homeUser', 'awayUser', 'homePartnerUser', 'awayPartnerUser', 'group', 'league', 'groupCard']);
                self::queueMatchNotification($match);
            }
        }

        return [
            'created' => 0,
            'removed' => 0,
            'weeks' => $roundCount,
            'updated' => $updated,
        ];
    }

    private static function findPendingAutoMatch(
        int $leagueId,
        int $groupCardId,
        int $groupId,
        int $homeUserId,
        int $awayUserId,
        ?int $homePartnerId,
        ?int $awayPartnerId,
    ): ?GroupMatch {
        $query = GroupMatch::query()
            ->where('league_id', $leagueId)
            ->where('group_card_id', $groupCardId)
            ->where('group_id', $groupId)
            ->where(fn ($q) => $q->whereNull('score')->orWhere('score', ''))
            ->where(fn ($q) => $q->whereNull('winner_side')->orWhere('winner_side', ''))
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

        if (Schema::hasColumn('group_matches', 'auto_scheduled')) {
            $query->where('auto_scheduled', true);
        }

        return $query->first();
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
