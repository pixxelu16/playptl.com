<?php

namespace App\Http\Controllers;

use App\Enums\GroupMatchFormat;
use App\Models\Group;
use App\Models\GroupCard;
use App\Models\GroupMatch;
use App\Models\League;
use App\Models\LeagueRegistration;
use App\Models\User;
use App\Support\DivisionPlayoffPhase;
use App\Support\GroupMatchScheduleNotifier;
use App\Support\LeagueSeasonPhase;
use App\Support\LeagueRegistrationRoster;
use App\Support\MatchResultInput;
use App\Support\MatchScoreReader;
use App\Support\MatchStartTime;
use App\Support\PlayerMatchDayConflict;
use App\Support\LeagueWeekCalendar;
use App\Support\SubgroupRoundRobinScheduler;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminGroupMatchController extends Controller
{
    public function index(Request $request, League $league, GroupCard $groupCard): View|RedirectResponse
    {
        abort_unless($league->groupCards()->whereKey($groupCard->id)->exists(), 404);

        if (! Schema::hasTable('group_matches')) {
            return redirect()
                ->route('admin.league-management.groups.index', [$league, $groupCard])
                ->with('status', 'Run migrations to enable matches (group_matches table).');
        }

        $ageGroupKey = (string) $request->query('age_group_key', '');
        $ageGroupKey = $ageGroupKey !== '' ? $ageGroupKey : null;

        $playerSchemaReady = Schema::hasTable('league_registrations')
            && Schema::hasColumn('league_registrations', 'group_id')
            && Schema::hasColumn('league_registrations', 'group_card_id');

        $activeGroupId = (int) $request->query('group', 0);

        $groupsQuery = Group::query()
            ->whereHas('groupCards', fn ($q) => $q->whereKey($groupCard->id))
            ->when(
                Schema::hasColumn('groups', 'age_group_key') && $ageGroupKey !== null,
                fn ($q) => $q->where(function ($qq) use ($ageGroupKey) {
                    $qq->whereNull('age_group_key')->orWhere('age_group_key', $ageGroupKey);
                })
            )
            ->orderBy('name');

        $groups = $groupsQuery->get();

        if ($activeGroupId === 0 && $groups->isNotEmpty()) {
            $activeGroupId = (int) $groups->first()->id;
        }

        $activeGroup = $groups->firstWhere('id', $activeGroupId);

        if (! $activeGroup instanceof Group) {
            return redirect()
                ->route('admin.league-management.groups.index', ['league' => $league, 'groupCard' => $groupCard] + ($ageGroupKey ? ['age_group_key' => $ageGroupKey] : []))
                ->with('status', 'Create a group first, then schedule matches.');
        }

        $rosterRegs = collect();
        $seedByUserId = [];
        if ($playerSchemaReady) {
            $rosterRegs = LeagueRegistration::query()
                ->where('league_id', $league->id)
                ->where('group_id', $activeGroup->id)
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

            foreach ($rosterRegs as $index => $reg) {
                if ($reg->user_id) {
                    $seedByUserId[(int) $reg->user_id] = $index + 1;
                }
            }
        }

        $matches = GroupMatch::query()
            ->where('league_id', $league->id)
            ->where('group_card_id', $groupCard->id)
            ->where('group_id', $activeGroup->id)
            ->with(['homeUser', 'awayUser', 'homePartnerUser', 'awayPartnerUser'])
            ->orderBy('match_date')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $rosterTeams = LeagueRegistrationRoster::teamOptionsForMatch($rosterRegs, $seedByUserId);
        $scheduleAsDoublesTeams = $groupCard->forcedMatchFormat() === GroupMatchFormat::Doubles;

        $participantCount = $scheduleAsDoublesTeams
            ? $rosterTeams->filter(fn (array $t) => $t['is_complete'])->count()
            : $rosterRegs->pluck('user_id')->filter()->unique()->count();

        $playWeekCount = max(
            (int) $matches->max('round_number'),
            LeagueWeekCalendar::roundRobinPlayWeekCount($participantCount),
        );

        $sortMatches = fn (Collection $weekMatches) => $weekMatches
            ->sortBy(fn (GroupMatch $m) => $m->match_date->format('Y-m-d')
                .'#'.str_pad((string) ((int) ($m->sort_order ?? 0)), 5, '0', STR_PAD_LEFT)
                .'#'.str_pad((string) $m->id, 10, '0', STR_PAD_LEFT))
            ->values();

        $matchesByWeek = $matches
            ->groupBy(fn (GroupMatch $m) => (int) ($m->round_number ?? 9999))
            ->sortKeys()
            ->map($sortMatches);

        $headToHeadSingles = $this->singlesHeadToHeadByPair($league, $groupCard);
        $playerNamesById = $this->rosterDisplayNamesByUserId($rosterRegs);

        $rosterPlayerIds = $rosterRegs
            ->pluck('user_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $groupSchedulingLocked = DivisionPlayoffPhase::locksGroupMatchScheduling(
            $league->id,
            $groupCard->id,
            $ageGroupKey
        );

        return view('admin.league-management.matches.index', [
            'league' => $league,
            'groupCard' => $groupCard,
            'ageGroupKey' => $ageGroupKey,
            'groups' => $groups,
            'activeGroup' => $activeGroup,
            'activeGroupId' => $activeGroupId,
            'rosterRegs' => $rosterRegs,
            'seedByUserId' => $seedByUserId,
            'matchesByWeek' => $matchesByWeek,
            'playWeekCount' => $playWeekCount,
            'allMatchesCount' => $matches->count(),
            'playerSchemaReady' => $playerSchemaReady,
            'headToHeadSingles' => $headToHeadSingles,
            'playerNamesById' => $playerNamesById,
            'rosterTeams' => $rosterTeams,
            'scheduleAsDoublesTeams' => $scheduleAsDoublesTeams,
            'playerScheduleByDay' => PlayerMatchDayConflict::scheduleIndexForPlayerIds($rosterPlayerIds),
            'groupSchedulingLocked' => $groupSchedulingLocked,
            'groupSchedulingLockMessage' => DivisionPlayoffPhase::lockMessage($league->id, $groupCard->id, $ageGroupKey),
            'canEditScheduleDates' => ! LeagueSeasonPhase::playoffsStarted($league) && ! $league->isFinished(),
            'leagueStartDateLocked' => LeagueSeasonPhase::hasGroupMatchesStarted($league),
            'leagueMatchCloseDate' => $league->end_date,
            'groupMatchesClosed' => LeagueSeasonPhase::groupMatchesClosed($league),
            'canAddManualMatch' => ! LeagueSeasonPhase::groupMatchesClosed($league)
                && ! LeagueSeasonPhase::playoffsStarted($league)
                && ! DivisionPlayoffPhase::divisionLocksGroupMatchScheduling($league->id, $groupCard->id, $ageGroupKey),
            'leagueHasScheduleDates' => $league->start_date !== null,
        ]);
    }

    public function saveScheduleDates(Request $request, League $league, GroupCard $groupCard): RedirectResponse
    {
        abort_unless($league->groupCards()->whereKey($groupCard->id)->exists(), 404);

        $ageGroupKey = (string) $request->query('age_group_key', '');
        $ageGroupKey = $ageGroupKey !== '' ? $ageGroupKey : null;

        if (LeagueSeasonPhase::playoffsStarted($league)) {
            return back()
                ->withErrors(['schedule' => 'Playoffs have started. League match dates cannot be changed.'])
                ->withInput();
        }

        $startDateLocked = LeagueSeasonPhase::hasGroupMatchesStarted($league);

        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ]);

        if ($startDateLocked) {
            $startDate = $league->start_date;
            $endDate = $validated['end_date'] ?? null;

            if ($endDate !== null && $startDate !== null && $endDate < $startDate->format('Y-m-d')) {
                return back()
                    ->withErrors(['end_date' => 'Close date must be on or after the league start date.'])
                    ->withInput();
            }

            $league->update(['end_date' => $endDate]);
            $league->refresh();

            $scheduleSummary = 'League match close date saved.';
        } else {
            $startDate = $validated['start_date'] ?? null;
            $endDate = $validated['end_date'] ?? null;

            if ($endDate !== null && $startDate === null) {
                return back()
                    ->withErrors(['end_date' => 'Set a league start date before setting a close date.'])
                    ->withInput();
            }

            if ($endDate !== null && $startDate !== null && $endDate < $startDate) {
                return back()
                    ->withErrors(['end_date' => 'Close date must be on or after the league start date.'])
                    ->withInput();
            }

            $league->update([
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);
            $league->refresh();

            if ($startDate === null) {
                $scheduleSummary = 'League start and close dates cleared.';
            } else {
                $scheduleSummary = 'League dates saved.';
            }

            if (
                $startDate !== null
                && ! LeagueSeasonPhase::groupMatchesClosed($league)
                && ! DivisionPlayoffPhase::divisionLocksGroupMatchScheduling($league->id, $groupCard->id, $ageGroupKey)
            ) {
                $totals = SubgroupRoundRobinScheduler::syncDivision($league, $groupCard, $ageGroupKey);
                $scheduleSummary = SubgroupRoundRobinScheduler::formatDivisionSyncSummary($totals, 'Dates saved.');
            } elseif ($startDate !== null && DivisionPlayoffPhase::divisionLocksGroupMatchScheduling($league->id, $groupCard->id, $ageGroupKey)) {
                $scheduleSummary .= ' Qualifier/playoffs are active — round-robin was not regenerated.';
            }
        }

        $redirect = back()->with('status', $scheduleSummary);
        $activeGroupId = (int) $request->query('group', 0);
        if ($activeGroupId > 0) {
            $redirect->withFragment('group-'.$activeGroupId);
        }

        return $redirect;
    }

    public function generateRoundRobin(Request $request, League $league, GroupCard $groupCard): RedirectResponse
    {
        abort_unless($league->groupCards()->whereKey($groupCard->id)->exists(), 404);

        $ageGroupKey = (string) $request->query('age_group_key', '');
        $ageGroupKey = $ageGroupKey !== '' ? $ageGroupKey : null;

        $groupId = (int) $request->input('group_id', $request->query('group', 0));
        $scope = (string) $request->input('scope', 'subgroup');

        if ($scope === 'division') {
            $totals = SubgroupRoundRobinScheduler::syncDivision($league, $groupCard, $ageGroupKey);

            return back()->with('status', SubgroupRoundRobinScheduler::formatDivisionSyncSummary($totals));
        }

        $group = Group::query()
            ->whereKey($groupId)
            ->whereHas('groupCards', fn ($q) => $q->whereKey($groupCard->id))
            ->firstOrFail();

        $result = SubgroupRoundRobinScheduler::sync($league, $groupCard, $group, $ageGroupKey);
        $status = $result['created'] > 0
            ? sprintf('%d match%s scheduled.', $result['created'], $result['created'] === 1 ? '' : 'es')
            : 'No matches scheduled for this subgroup.';

        return back()
            ->with('status', $status)
            ->withFragment('group-'.$group->id);
    }

    public function store(Request $request, League $league, GroupCard $groupCard): RedirectResponse
    {
        abort_unless($league->groupCards()->whereKey($groupCard->id)->exists(), 404);
        abort_unless(Schema::hasTable('group_matches'), 404);

        $ageGroupKey = (string) $request->query('age_group_key', '');
        $ageGroupKey = $ageGroupKey !== '' ? $ageGroupKey : null;

        if (LeagueSeasonPhase::groupMatchesClosed($league)) {
            return back()
                ->withErrors(['schedule' => 'League match close date has passed. Extend the close date to add matches.'])
                ->withInput();
        }

        if (DivisionPlayoffPhase::locksGroupMatchScheduling($league->id, $groupCard->id, $ageGroupKey)) {
            return back()
                ->withErrors(['schedule' => DivisionPlayoffPhase::lockMessage($league->id, $groupCard->id, $ageGroupKey)])
                ->withInput();
        }

        $allowedFormats = $groupCard->forcedMatchFormat() !== null
            ? [$groupCard->forcedMatchFormat()->value]
            : array_column(GroupMatchFormat::cases(), 'value');

        $validated = $request->validate([
            'group_id' => ['required', 'integer', Rule::exists('groups', 'id')],
            'format' => ['required', Rule::in($allowedFormats)],
            'home_user_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'away_user_id' => ['required', 'integer', Rule::exists('users', 'id'), 'different:home_user_id'],
            'home_partner_user_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'away_partner_user_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'match_date' => ['required', 'date'],
            'start_time' => ['required', 'string', 'max:32'],
            'venue' => ['nullable', 'string', 'max:255'],
            'court' => ['nullable', 'string', 'max:64'],
            'confirm_schedule_conflict' => ['nullable', 'boolean'],
        ]);

        $startTimeNorm = MatchStartTime::normalizeFromRequest($validated['start_time']);
        if ($startTimeNorm === '') {
            return back()->withErrors(['start_time' => 'Please choose a valid start time.'])->withInput();
        }

        $group = Group::query()
            ->whereKey($validated['group_id'])
            ->whereHas('groupCards', fn ($q) => $q->whereKey($groupCard->id))
            ->when(
                Schema::hasColumn('groups', 'age_group_key') && $ageGroupKey !== null,
                fn ($q) => $q->where(function ($qq) use ($ageGroupKey) {
                    $qq->whereNull('age_group_key')->orWhere('age_group_key', $ageGroupKey);
                })
            )
            ->firstOrFail();

        $rosterUserIds = $this->rosterUserIds($league, $groupCard, $group, $ageGroupKey);

        $format = GroupMatchFormat::from($validated['format']);
        $homePartner = $validated['home_partner_user_id'] ?? null;
        $awayPartner = $validated['away_partner_user_id'] ?? null;

        if ($format === GroupMatchFormat::Singles) {
            $homePartner = null;
            $awayPartner = null;
        } else {
            if (! $homePartner || ! $awayPartner) {
                return back()->withErrors(['format' => 'Doubles requires both partner players.'])->withInput();
            }
            $ids = [(int) $validated['home_user_id'], (int) $validated['away_user_id'], (int) $homePartner, (int) $awayPartner];
            if (count(array_unique($ids)) !== 4) {
                return back()->withErrors(['format' => 'Doubles needs four different players.'])->withInput();
            }
        }

        $participantIds = array_values(array_filter([
            (int) $validated['home_user_id'],
            (int) $validated['away_user_id'],
            $homePartner ? (int) $homePartner : null,
            $awayPartner ? (int) $awayPartner : null,
        ]));

        foreach ($participantIds as $uid) {
            if (! $rosterUserIds->contains($uid)) {
                return back()->withErrors(['home_user_id' => 'All selected players must be in this group roster.'])->withInput();
            }
        }

        $matchDateCarbon = Carbon::parse($validated['match_date'])->startOfDay();
        $dateRuleError = LeagueWeekCalendar::validateLeagueMatchDate($matchDateCarbon, $league);
        if ($dateRuleError !== null) {
            return back()->withErrors(['match_date' => $dateRuleError])->withInput();
        }

        $dateYmd = $matchDateCarbon->toDateString();
        $scheduleWarnings = PlayerMatchDayConflict::warningLinesForDate($dateYmd, $participantIds);
        if ($scheduleWarnings !== [] && ! $request->boolean('confirm_schedule_conflict')) {
            return back()->withErrors([
                'schedule' => implode(' ', $scheduleWarnings).' Do you want to schedule anyway? Confirm and submit again.',
            ])->withInput();
        }

        $seedByUserId = $this->seedMap($league, $groupCard, $group, $ageGroupKey);

        $groupMatch = GroupMatch::query()->create([
            'league_id' => $league->id,
            'group_card_id' => $groupCard->id,
            'group_id' => $group->id,
            'format' => $format,
            'home_user_id' => $validated['home_user_id'],
            'away_user_id' => $validated['away_user_id'],
            'home_partner_user_id' => $homePartner,
            'away_partner_user_id' => $awayPartner,
            'home_seed' => $seedByUserId[(int) $validated['home_user_id']] ?? null,
            'away_seed' => $seedByUserId[(int) $validated['away_user_id']] ?? null,
            'match_date' => $validated['match_date'],
            'start_time' => $startTimeNorm,
            'venue' => $validated['venue'] ?? null,
            'court' => $this->nullableTrimmedCourt($validated['court'] ?? null),
            'score' => null,
            'winner_side' => null,
            'winner_user_id' => null,
            'sort_order' => 0,
        ]);

        $groupMatch->load(['homeUser', 'awayUser', 'homePartnerUser', 'awayPartnerUser', 'group', 'league', 'groupCard']);
        $this->notifyParticipantsMatchSchedule($groupMatch);

        return redirect()
            ->route('admin.league-management.matches.index', [
                'league' => $league,
                'groupCard' => $groupCard,
                'group' => $group->id,
            ] + ($ageGroupKey ? ['age_group_key' => $ageGroupKey] : []))
            ->with('status', 'Match scheduled.');
    }

    public function update(Request $request, League $league, GroupCard $groupCard, GroupMatch $groupMatch): RedirectResponse
    {
        abort_unless($league->groupCards()->whereKey($groupCard->id)->exists(), 404);
        $this->assertMatchScope($league, $groupCard, $groupMatch);

        $isQuickResult = $request->boolean('quick_result');

        $ageGroupKey = (string) $request->query('age_group_key', '');
        $ageGroupKey = $ageGroupKey !== '' ? $ageGroupKey : null;

        $validated = $request->validate([
            'match_date' => ['required', 'date'],
            'start_time' => array_merge(
                $isQuickResult ? ['nullable'] : ['required'],
                ['string', 'max:32']
            ),
            'venue' => ['nullable', 'string', 'max:255'],
            'court' => ['nullable', 'string', 'max:64'],
            'confirm_schedule_conflict' => ['nullable', 'boolean'],
            'score' => ['nullable', 'string', 'max:64'],
            ...MatchResultInput::setFieldValidationRules(),
            'winner_side' => ['nullable', 'string', 'in:home,away'],
            'result_type' => ['nullable', 'string', 'in:normal,walkover'],
            'walked_off_side' => ['nullable', 'string', 'in:home,away'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);

        $startTime = trim((string) ($validated['start_time'] ?? ''));
        if ($startTime === '') {
            $startTime = (string) ($groupMatch->start_time ?? '');
        }
        if (! $isQuickResult) {
            $normalizedTime = MatchStartTime::normalizeFromRequest($startTime);
            if ($normalizedTime === '') {
                return back()->withErrors(['start_time' => 'Please choose a valid start time.'])->withInput();
            }
            $startTime = $normalizedTime;
        } else {
            $quickNorm = MatchStartTime::normalizeFromRequest($startTime);
            if ($quickNorm !== '') {
                $startTime = $quickNorm;
            }
        }

        $dateYmd = Carbon::parse($validated['match_date'])->toDateString();
        $newDateCarbon = Carbon::parse($dateYmd)->startOfDay();
        $playWeekCount = max(
            (int) GroupMatch::query()
                ->where('league_id', $league->id)
                ->where('group_card_id', $groupCard->id)
                ->where('group_id', $groupMatch->group_id)
                ->max('round_number'),
            (int) ($groupMatch->round_number ?? 0),
        );
        $dateRuleError = LeagueWeekCalendar::validatePendingMatchDate(
            $newDateCarbon,
            $league,
            $groupMatch,
            $playWeekCount,
        );
        if ($dateRuleError !== null) {
            return back()->withErrors(['match_date' => $dateRuleError])->withInput();
        }

        $participantIds = array_values(array_filter([
            (int) $groupMatch->home_user_id,
            (int) $groupMatch->away_user_id,
            $groupMatch->home_partner_user_id ? (int) $groupMatch->home_partner_user_id : null,
            $groupMatch->away_partner_user_id ? (int) $groupMatch->away_partner_user_id : null,
        ]));
        $scheduleWarnings = PlayerMatchDayConflict::warningLinesForDate($dateYmd, $participantIds, $groupMatch->id);
        if ($scheduleWarnings !== [] && ! $request->boolean('confirm_schedule_conflict')) {
            return back()->withErrors([
                'schedule' => implode(' ', $scheduleWarnings).' Do you want to save anyway? Confirm and submit again.',
            ])->withInput();
        }

        $oldDate = $groupMatch->match_date?->toDateString();
        $oldTime = MatchStartTime::toInputValue((string) ($groupMatch->start_time ?? ''));
        $oldVenue = trim((string) ($groupMatch->venue ?? ''));
        $oldCourt = trim((string) ($groupMatch->court ?? ''));

        $setPairError = MatchResultInput::validateSetPairs($validated);
        if ($setPairError !== null) {
            return back()->withErrors(['score' => $setPairError])->withInput();
        }

        $result = MatchResultInput::fromRequest(
            MatchResultInput::resolveScoreRaw($validated, $validated['result_type'] ?? null),
            $validated['result_type'] ?? null,
            $validated['walked_off_side'] ?? null,
        );
        $scoreTrimmed = $result['score'];
        $winnerSide = $result['winner_side'] ?? $this->resolvedWinnerSideForPersistence(
            $scoreTrimmed,
            $validated['winner_side'] ?? null,
        );
        if ($scoreTrimmed !== '' && MatchScoreReader::isWalkover($scoreTrimmed) && $winnerSide === null) {
            return back()->withErrors([
                'walked_off_side' => 'Choose which player walked off (forfeit).',
            ])->withInput();
        }
        $winnerUserId = $this->resolvedWinnerUserIdForPersistence($groupMatch, $winnerSide);

        $updatePayload = [
            'match_date' => $validated['match_date'],
            'start_time' => $startTime,
            'venue' => $validated['venue'] ?? null,
            'court' => $this->nullableTrimmedCourt($validated['court'] ?? null),
            'score' => $scoreTrimmed !== '' ? $scoreTrimmed : null,
            'winner_side' => $winnerSide,
            'winner_user_id' => $winnerUserId,
            'sort_order' => $validated['sort_order'] ?? $groupMatch->sort_order,
        ];

        $groupMatch->update($updatePayload);

        $newDate = Carbon::parse($validated['match_date'])->toDateString();
        $newVenue = trim((string) ($validated['venue'] ?? ''));
        $newCourt = trim((string) ($this->nullableTrimmedCourt($validated['court'] ?? null) ?? ''));
        $newTime = MatchStartTime::toInputValue($startTime);
        $scheduleChanged = $oldDate !== $newDate || $oldTime !== $newTime || $oldVenue !== $newVenue || $oldCourt !== $newCourt;
        if ($scheduleChanged && ! $isQuickResult && DivisionPlayoffPhase::locksGroupMatchScheduling($league->id, $groupCard->id, $ageGroupKey)) {
            return back()
                ->withErrors(['schedule' => 'Cannot reschedule group matches after qualifier/playoffs are set. You can still update the score.'])
                ->withInput();
        }
        if ($scheduleChanged && ! $isQuickResult) {
            $groupMatch->refresh();
            $groupMatch->load(['homeUser', 'awayUser', 'homePartnerUser', 'awayPartnerUser', 'group', 'league', 'groupCard']);
            $this->notifyParticipantsMatchSchedule($groupMatch);
        }

        return redirect()
            ->route('admin.league-management.matches.index', [
                'league' => $league,
                'groupCard' => $groupCard,
                'group' => $groupMatch->group_id,
            ] + ($ageGroupKey ? ['age_group_key' => $ageGroupKey] : []))
            ->with('status', 'Match updated.');
    }

    public function destroy(Request $request, League $league, GroupCard $groupCard, GroupMatch $groupMatch): RedirectResponse
    {
        abort_unless($league->groupCards()->whereKey($groupCard->id)->exists(), 404);
        $this->assertMatchScope($league, $groupCard, $groupMatch);

        $groupId = $groupMatch->group_id;
        $groupMatch->delete();

        DivisionPlayoffPhase::reconcileDivisionAfterGroupMatchesChanged($league->id, $groupCard->id);
        $league->refresh();
        LeagueSeasonPhase::resetLeaguePlayoffFlagsIfNoMatches($league);

        $ageGroupKey = (string) $request->query('age_group_key', '');
        $ageGroupKey = $ageGroupKey !== '' ? $ageGroupKey : null;

        return redirect()
            ->route('admin.league-management.matches.index', [
                'league' => $league,
                'groupCard' => $groupCard,
                'group' => $groupId,
            ] + ($ageGroupKey ? ['age_group_key' => $ageGroupKey] : []))
            ->with('status', 'Match removed.');
    }

    private function assertMatchScope(League $league, GroupCard $groupCard, GroupMatch $groupMatch): void
    {
        abort_unless(
            $groupMatch->league_id === $league->id
            && $groupMatch->group_card_id === $groupCard->id,
            404
        );
    }

    /**
     * @return Collection<int, int>
     */
    private function rosterUserIds(League $league, GroupCard $groupCard, Group $group, ?string $ageGroupKey): Collection
    {
        $q = LeagueRegistration::query()
            ->where('league_id', $league->id)
            ->where('group_id', $group->id)
            ->where(function ($qq) use ($groupCard) {
                $qq->whereNull('group_card_id')->orWhere('group_card_id', $groupCard->id);
            });

        if ($ageGroupKey !== null && Schema::hasColumn('league_registrations', 'age_group_key')) {
            $q->where('age_group_key', $ageGroupKey);
        }

        return $q->pluck('user_id')->filter()->map(fn ($id) => (int) $id)->unique()->values();
    }

    /**
     * @return array<int, int>
     */
    private function seedMap(League $league, GroupCard $groupCard, Group $group, ?string $ageGroupKey): array
    {
        $q = LeagueRegistration::query()
            ->where('league_id', $league->id)
            ->where('group_id', $group->id)
            ->where(function ($qq) use ($groupCard) {
                $qq->whereNull('group_card_id')->orWhere('group_card_id', $groupCard->id);
            })
            ->orderBy('id');

        if ($ageGroupKey !== null && Schema::hasColumn('league_registrations', 'age_group_key')) {
            $q->where('age_group_key', $ageGroupKey);
        }

        $map = [];
        foreach ($q->get() as $index => $reg) {
            if ($reg->user_id) {
                $map[(int) $reg->user_id] = $index + 1;
            }
        }

        return $map;
    }

    /**
     * Persisted winner: prefer a parseable score; otherwise use explicit home/away from the form.
     */
    private function resolvedWinnerSideForPersistence(string $scoreTrimmed, ?string $winnerSideInput): ?string
    {
        $winnerInput = in_array($winnerSideInput, ['home', 'away'], true) ? $winnerSideInput : null;

        if ($scoreTrimmed !== '' && MatchScoreReader::isWalkover($scoreTrimmed)) {
            return $winnerInput ?? MatchScoreReader::homeSideWonFromWalkover($scoreTrimmed, $winnerInput);
        }

        if ($scoreTrimmed !== '') {
            $parsed = MatchScoreReader::homeSideWon($scoreTrimmed);
            if ($parsed !== null) {
                return $parsed ? 'home' : 'away';
            }
        }

        return $winnerInput;
    }

    private function resolvedWinnerUserIdForPersistence(GroupMatch $groupMatch, ?string $winnerSide): ?int
    {
        if ($winnerSide === null || $groupMatch->format !== GroupMatchFormat::Singles) {
            return null;
        }

        return $winnerSide === 'home'
            ? (int) $groupMatch->home_user_id
            : (int) $groupMatch->away_user_id;
    }

    /**
     * Completed singles matches in this league + sub group, grouped by player pair (minUserId-maxUserId).
     *
     * @return array<string, array{meetings: list<array{match_id: int, match_date: string, score: string, winner_user_id: int, home_user_id: int, away_user_id: int}>}>
     */
    private function singlesHeadToHeadByPair(League $league, GroupCard $groupCard): array
    {
        $byPair = [];

        $rows = GroupMatch::query()
            ->where('league_id', $league->id)
            ->where('group_card_id', $groupCard->id)
            ->where('format', GroupMatchFormat::Singles)
            ->where(function ($q) {
                $q->whereIn('winner_side', ['home', 'away'])
                    ->orWhere(function ($qq) {
                        $qq->whereNotNull('score')->where('score', '!=', '');
                    });
            })
            ->orderBy('match_date')
            ->orderBy('id')
            ->get(['id', 'match_date', 'score', 'winner_side', 'winner_user_id', 'home_user_id', 'away_user_id', 'format']);

        foreach ($rows as $m) {
            $hw = $m->homeSideWon();
            if ($hw === null) {
                continue;
            }
            $winnerId = (int) ($m->winner_user_id ?? 0);
            if ($winnerId <= 0) {
                $winnerId = $hw ? (int) $m->home_user_id : (int) $m->away_user_id;
            }
            $a = min((int) $m->home_user_id, (int) $m->away_user_id);
            $b = max((int) $m->home_user_id, (int) $m->away_user_id);
            $key = $a.'-'.$b;
            if (! isset($byPair[$key])) {
                $byPair[$key] = ['meetings' => []];
            }
            $byPair[$key]['meetings'][] = [
                'match_id' => (int) $m->id,
                'match_date' => $m->match_date->toDateString(),
                'score' => (string) $m->score,
                'winner_user_id' => $winnerId,
                'home_user_id' => (int) $m->home_user_id,
                'away_user_id' => (int) $m->away_user_id,
            ];
        }

        return $byPair;
    }

    /**
     * @param  Collection<int, LeagueRegistration>  $rosterRegs
     * @return array<int, string>
     */
    private function rosterDisplayNamesByUserId(Collection $rosterRegs): array
    {
        return LeagueRegistrationRoster::displayNamesByUserId($rosterRegs);
    }

    /**
     * Email each participant when a match is scheduled or date/time/venue is updated.
     */
    private function notifyParticipantsMatchSchedule(GroupMatch $groupMatch): void
    {
        GroupMatchScheduleNotifier::notifyParticipants($groupMatch);
    }

    /**
     * @return list<User>
     */
    private function participantUsersForMatch(GroupMatch $groupMatch): array
    {
        $users = collect([
            $groupMatch->homeUser,
            $groupMatch->awayUser,
            $groupMatch->homePartnerUser,
            $groupMatch->awayPartnerUser,
        ])->filter()->unique('id')->values();

        return $users->all();
    }

    private function userHasDeliverableEmail(?User $user): bool
    {
        if (! $user instanceof User) {
            return false;
        }
        $email = trim((string) ($user->email ?? ''));

        return $email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    private function userDisplayNameForMail(?User $user): string
    {
        if (! $user instanceof User) {
            return 'Opponent';
        }
        $name = trim((string) ($user->name ?? ''));
        if ($name !== '') {
            return $name;
        }
        $first = trim((string) ($user->first_name ?? ''));
        $last = trim((string) ($user->last_name ?? ''));
        $full = trim($first.' '.$last);

        return $full !== '' ? $full : 'Player';
    }

    private function opponentSummaryForMatchRecipient(GroupMatch $match, int $recipientUserId): string
    {
        if ($match->format === GroupMatchFormat::Singles) {
            if ($recipientUserId === (int) $match->home_user_id) {
                return 'You vs '.$this->userDisplayNameForMail($match->awayUser);
            }

            return 'You vs '.$this->userDisplayNameForMail($match->homeUser);
        }

        $homeIds = [(int) $match->home_user_id, (int) $match->home_partner_user_id];
        $onHome = in_array($recipientUserId, $homeIds, true);
        if ($onHome) {
            $partner = $recipientUserId === (int) $match->home_user_id
                ? $match->homePartnerUser
                : $match->homeUser;
            $pName = $this->userDisplayNameForMail($partner);
            $o1 = $this->userDisplayNameForMail($match->awayUser);
            $o2 = $this->userDisplayNameForMail($match->awayPartnerUser);

            return 'You and '.$pName.' vs '.$o1.' and '.$o2;
        }

        $partner = $recipientUserId === (int) $match->away_user_id
            ? $match->awayPartnerUser
            : $match->awayUser;
        $pName = $this->userDisplayNameForMail($partner);
        $o1 = $this->userDisplayNameForMail($match->homeUser);
        $o2 = $this->userDisplayNameForMail($match->homePartnerUser);

        return 'You and '.$pName.' vs '.$o1.' and '.$o2;
    }

    protected function nullableTrimmedCourt(mixed $value): ?string
    {
        $court = trim((string) ($value ?? ''));

        return $court === '' ? null : $court;
    }
}
