<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupCard;
use App\Models\League;
use App\Models\LeagueRegistration;
use App\Models\PlayoffMatch;
use App\Models\User;
use App\Support\DivisionScheduleWindow;
use App\Support\LeaguePlayoffCalendar;
use App\Support\LeagueSeasonPhase;
use App\Support\PlayoffMatchScheduleNotifier;
use App\Support\PlayoffMatchScheduler;
use App\Support\MatchResultInput;
use App\Support\MatchScoreReader;
use App\Support\MatchStartTime;
use App\Support\PlayoffBracketBuilder;
use App\Support\PlayoffPathAssigner;
use App\Support\PlayerMatchDayConflict;
use App\Support\TournamentDateWindowConflict;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminPlayoffMatchController extends Controller
{
    public function index(Request $request, League $league, GroupCard $groupCard): View|RedirectResponse
    {
        abort_unless($league->groupCards()->whereKey($groupCard->id)->exists(), 404);

        if (! Schema::hasTable('playoff_matches')) {
            return redirect()
                ->route('admin.league-management.matches.index', [$league, $groupCard])
                ->with('status', 'Run migrations to enable playoffs (playoff_matches table).');
        }

        $ageGroupKey = $this->ageGroupKeyFromRequest($request);
        $ageKeyDb = $ageGroupKey ?? '';

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

        if ($groups->isEmpty()) {
            return redirect()
                ->route('admin.league-management.groups.index', ['league' => $league, 'groupCard' => $groupCard] + ($ageGroupKey ? ['age_group_key' => $ageGroupKey] : []))
                ->with('status', 'Create a group before playoffs.');
        }

        $activeGroupId = (int) $request->query('group', $groups->first()?->id ?? 0);

        $playerSchemaReady = Schema::hasTable('league_registrations')
            && Schema::hasColumn('league_registrations', 'group_id')
            && Schema::hasColumn('league_registrations', 'group_card_id');

        $rosterRegs = collect();
        if ($playerSchemaReady) {
            $rosterRegs = LeagueRegistration::query()
                ->where('league_id', $league->id)
                ->where('group_card_id', $groupCard->id)
                ->when(
                    $ageGroupKey !== null && Schema::hasColumn('league_registrations', 'age_group_key'),
                    fn ($q) => $q->where('age_group_key', $ageGroupKey)
                )
                ->with('user')
                ->orderBy('id')
                ->get();
        }

        $showQualifierPlayoffs = LeagueSeasonPhase::showQualifierAndPlayoffs($league, $groupCard->id);

        if (! $showQualifierPlayoffs) {
            PlayoffBracketBuilder::clearDivisionPlayoffData($league->id, $groupCard->id, $ageKeyDb);
        }

        $qualifierReady = $showQualifierPlayoffs
            && Schema::hasTable('playoff_qualifiers')
            && PlayoffBracketBuilder::hasPlayoffAssignments($league->id, $groupCard->id, $ageKeyDb);

        if ($showQualifierPlayoffs && ! $qualifierReady) {
            PlayoffBracketBuilder::clearBracket($league->id, $groupCard->id, $ageKeyDb);
        } elseif ($showQualifierPlayoffs && PlayoffBracketBuilder::bracketStructureIsStale($league->id, $groupCard->id, $ageKeyDb)) {
            $rebuildNote = PlayoffBracketBuilder::rebuild($league, $groupCard, $ageGroupKey);

            return redirect()
                ->route('admin.league-management.playoffs.index', [$league, $groupCard] + ($ageGroupKey ? ['age_group_key' => $ageGroupKey] : []))
                ->with('status', 'Bracket rebuilt from Qualifier paths. '.$rebuildNote);
        }

        $playoffMatches = $qualifierReady
            ? PlayoffMatch::query()
                ->where('league_id', $league->id)
                ->where('group_card_id', $groupCard->id)
                ->where('age_group_key', $ageKeyDb)
                ->with(['homeUser', 'awayUser'])
                ->orderByRaw("FIELD(round, 'ppq', 'pq', 'qf', 'sf', 'f')")
                ->orderBy('slot')
                ->get()
            : collect();

        $byRound = $playoffMatches->groupBy('round');
        [$qfComplete, $sfComplete] = $this->roundCompletionFromGrouped($byRound);
        $pqComplete = $this->roundIsComplete($byRound->get(PlayoffMatch::ROUND_PRE_Q, collect()));
        $activeGroup = $groups->firstWhere('id', $activeGroupId) ?? $groups->first();

        return view('admin.league-management.playoffs.index', [
            'league' => $league,
            'groupCard' => $groupCard,
            'ageGroupKey' => $ageGroupKey,
            'groups' => $groups,
            'activeGroupId' => $activeGroupId,
            'activeGroup' => $activeGroup,
            'playerSchemaReady' => $playerSchemaReady,
            'rosterRegs' => $rosterRegs,
            'playoffRosterUsers' => $this->playoffRosterUsers($rosterRegs),
            'playoffMatches' => $playoffMatches,
            'ppqMatches' => $byRound->get(PlayoffMatch::ROUND_PRE_PRE_Q, collect()),
            'pqMatches' => $byRound->get(PlayoffMatch::ROUND_PRE_Q, collect()),
            'qfMatches' => $byRound->get(PlayoffMatch::ROUND_QF, collect()),
            'sfMatches' => $byRound->get(PlayoffMatch::ROUND_SF, collect()),
            'finalMatch' => $byRound->get(PlayoffMatch::ROUND_F, collect())->first(),
            'qualifierReady' => $qualifierReady,
            'bracketExists' => $qualifierReady && $playoffMatches->isNotEmpty(),
            'pqComplete' => $pqComplete,
            'qfComplete' => $qfComplete,
            'sfComplete' => $sfComplete,
            'canClosePlayoffs' => LeagueSeasonPhase::canClosePlayoffs($league),
            'playoffsStarted' => LeagueSeasonPhase::playoffsStarted($league),
            'playoffsClosed' => LeagueSeasonPhase::playoffsClosed($league),
            'playoffsPhaseMessage' => LeagueSeasonPhase::playoffsLockMessage($league, $groupCard),
            'groupMatchesCloseDate' => DivisionScheduleWindow::endDate($league, $groupCard),
            'latestGroupMatchDate' => DivisionScheduleWindow::latestScheduledMatchDate($league, $groupCard),
            'earliestPlayoffStartDate' => DivisionScheduleWindow::earliestPlayoffStartDate($league, $groupCard),
            'tournamentStartDate' => $league->start_date,
            'tournamentEndDate' => $league->end_date,
            'groupMatchesStarted' => LeagueSeasonPhase::hasGroupMatchesStarted($league),
            'showQualifierPlayoffs' => $showQualifierPlayoffs,
            'qualifierUnavailableMessage' => LeagueSeasonPhase::qualifierPlayoffsUnavailableMessage($league),
            'playoffsNeedTournamentExtensionMessage' => TournamentDateWindowConflict::playoffsNeedTournamentExtension(
                $league,
                $groupCard,
            ),
            'playoffExceedsTournamentMessage' => TournamentDateWindowConflict::playoffMatchesExceedTournamentEnd(
                $league,
                $groupCard,
                $ageGroupKey,
            ),
        ]);
    }

    public function savePlayoffDates(Request $request, League $league, GroupCard $groupCard): RedirectResponse
    {
        abort_unless($league->groupCards()->whereKey($groupCard->id)->exists(), 404);

        if (LeagueSeasonPhase::playoffsClosed($league)) {
            return back()->withErrors(['playoff_dates' => 'Playoffs are closed. Dates cannot be changed.']);
        }

        $ageGroupKey = $this->ageGroupKeyFromRequest($request);
        $playoffsAlreadyStarted = LeagueSeasonPhase::playoffsStarted($league);

        $extensionBlock = TournamentDateWindowConflict::playoffsNeedTournamentExtension($league, $groupCard);
        if ($extensionBlock !== null) {
            return back()->withErrors(['playoff_dates' => $extensionBlock])->withInput();
        }

        $validated = $request->validate([
            'playoff_start_date' => ['required', 'date'],
            'playoff_end_date' => ['required', 'date'],
        ]);
        $playoffStart = Carbon::parse($validated['playoff_start_date'])->startOfDay();
        $playoffEnd = Carbon::parse($validated['playoff_end_date'])->startOfDay();

        $datesExtension = TournamentDateWindowConflict::playoffDatesNeedTournamentExtension($league, $playoffEnd);
        if ($datesExtension !== null) {
            return back()->withErrors(['playoff_end_date' => $datesExtension])->withInput();
        }

        $startError = LeaguePlayoffCalendar::validatePlayoffStartDate($playoffStart, $league, $groupCard);
        if ($startError !== null) {
            return back()->withErrors(['playoff_start_date' => $startError])->withInput();
        }

        $endError = LeaguePlayoffCalendar::validatePlayoffEndDate($playoffStart, $playoffEnd, $league, $groupCard);
        if ($endError !== null) {
            return back()->withErrors(['playoff_end_date' => $endError])->withInput();
        }

        $matchStartError = PlayoffMatchScheduler::validateStartDateAgainstCompletedMatches(
            $league,
            $groupCard,
            $playoffStart->toDateString(),
            $ageGroupKey,
        );
        if ($matchStartError !== null) {
            return back()->withErrors(['playoff_start_date' => $matchStartError])->withInput();
        }

        $matchEndError = PlayoffMatchScheduler::validateEndDateAgainstScheduledMatches(
            $league,
            $groupCard,
            $playoffEnd->toDateString(),
            $ageGroupKey,
        );
        if ($matchEndError !== null) {
            return back()->withErrors(['playoff_end_date' => $matchEndError])->withInput();
        }

        if ($playoffsAlreadyStarted) {
            $league->update([
                'playoff_start_date' => $playoffStart->toDateString(),
                'playoff_end_date' => $playoffEnd->toDateString(),
            ]);
            $league->refresh();

            $totals = PlayoffMatchScheduler::syncDivision($league, $groupCard, $ageGroupKey, reschedulePending: true);
            $status = PlayoffMatchScheduler::formatSyncSummary(
                $totals,
                'Playoff dates updated. The final is scheduled on '.$playoffEnd->format('M j, Y').'.',
            );

            return redirect()
                ->route('admin.league-management.playoffs.index', [$league, $groupCard] + ($ageGroupKey ? ['age_group_key' => $ageGroupKey] : []))
                ->with('status', $status);
        }

        if (! LeagueSeasonPhase::hasGroupMatchesStarted($league)) {
            return back()->withErrors([
                'playoff_dates' => 'Schedule group matches on the Matches page before starting playoffs.',
            ])->withInput();
        }

        $league->update([
            'playoff_start_date' => $playoffStart->toDateString(),
            'playoff_end_date' => $playoffEnd->toDateString(),
        ]);

        $bracketNote = $this->activatePlayoffSeason($league, $groupCard, $ageGroupKey);
        $league->refresh();

        $totals = PlayoffMatchScheduler::syncDivision($league, $groupCard, $ageGroupKey);
        $windowLabel = $playoffStart->format('M j, Y').' – '.$playoffEnd->format('M j, Y');
        $status = PlayoffMatchScheduler::formatSyncSummary(
            $totals,
            'Playoffs started ('.$windowLabel.'). Group-stage scheduling is closed. Final on '.$playoffEnd->format('M j, Y').'.',
        );

        if ($bracketNote !== '') {
            $status .= ' '.$bracketNote;
        }

        return redirect()
            ->route('admin.league-management.playoffs.index', [$league, $groupCard] + ($ageGroupKey ? ['age_group_key' => $ageGroupKey] : []))
            ->with('status', $status);
    }

    public function startPlayoffs(Request $request, League $league, GroupCard $groupCard): RedirectResponse
    {
        abort_unless($league->groupCards()->whereKey($groupCard->id)->exists(), 404);

        if (! LeagueSeasonPhase::canStartPlayoffs($league, $groupCard)) {
            $message = ! LeaguePlayoffCalendar::playoffDatesAreValid($league, $groupCard)
                ? 'Set playoff start and end dates, then click Schedule matches.'
                : 'Playoffs can start only after group matches have begun (at least one group match scheduled).';

            return back()->withErrors(['playoffs' => $message]);
        }

        $ageGroupKey = $this->ageGroupKeyFromRequest($request);
        $bracketNote = $this->activatePlayoffSeason($league, $groupCard, $ageGroupKey);

        return redirect()
            ->route('admin.league-management.playoffs.index', [$league, $groupCard] + ($ageGroupKey ? ['age_group_key' => $ageGroupKey] : []))
            ->with('status', 'Playoffs started. Group-stage scheduling is now closed. '.$bracketNote);
    }

    private function activatePlayoffSeason(League $league, GroupCard $groupCard, ?string $ageGroupKey): string
    {
        if (Schema::hasTable('playoff_qualifiers')) {
            PlayoffPathAssigner::syncDivision($league, $groupCard, $ageGroupKey);
        }

        $bracketNote = '';
        if (Schema::hasTable('playoff_matches')) {
            $bracketNote = PlayoffBracketBuilder::rebuild($league, $groupCard, $ageGroupKey);
        }

        if (! LeagueSeasonPhase::playoffsStarted($league)) {
            $league->update(['playoffs_started_at' => now()]);
        }

        return $bracketNote;
    }

    public function closePlayoffs(Request $request, League $league, GroupCard $groupCard): RedirectResponse
    {
        abort_unless($league->groupCards()->whereKey($groupCard->id)->exists(), 404);

        if (! LeagueSeasonPhase::canClosePlayoffs($league)) {
            return back()->withErrors([
                'playoffs' => 'Playoffs must be started before they can be closed.',
            ]);
        }

        $ageGroupKey = $this->ageGroupKeyFromRequest($request);
        $league->update(['playoffs_closed_at' => now()]);

        return redirect()
            ->route('admin.league-management.playoffs.index', [$league, $groupCard] + ($ageGroupKey ? ['age_group_key' => $ageGroupKey] : []))
            ->with('status', 'Playoffs closed for this league.');
    }

    public function rebuildFromQualifier(Request $request, League $league, GroupCard $groupCard): RedirectResponse
    {
        abort_unless($league->groupCards()->whereKey($groupCard->id)->exists(), 404);

        if (LeagueSeasonPhase::playoffsClosed($league)) {
            return back()->withErrors(['bracket' => 'Playoffs are closed for this league.']);
        }

        $ageGroupKey = $this->ageGroupKeyFromRequest($request);
        $ageKeyDb = $ageGroupKey ?? '';

        if (! PlayoffBracketBuilder::hasPlayoffAssignments($league->id, $groupCard->id, $ageKeyDb)) {
            return redirect()
                ->route('admin.league-management.qualifier.index', [$league, $groupCard] + ($ageGroupKey ? ['age_group_key' => $ageGroupKey] : []))
                ->withErrors(['bracket' => 'Assign playoff paths on Qualifier and save first.']);
        }

        $message = PlayoffBracketBuilder::rebuild($league, $groupCard, $ageGroupKey);

        return redirect()
            ->route('admin.league-management.playoffs.index', [$league, $groupCard] + ($ageGroupKey ? ['age_group_key' => $ageGroupKey] : []))
            ->with('status', $message);
    }

    public function storeBracket(Request $request, League $league, GroupCard $groupCard): RedirectResponse
    {
        abort_unless($league->groupCards()->whereKey($groupCard->id)->exists(), 404);
        abort_unless(Schema::hasTable('playoff_matches'), 404);

        $ageGroupKey = $this->ageGroupKeyFromRequest($request);
        $ageKeyDb = $ageGroupKey ?? '';

        $exists = PlayoffMatch::query()
            ->where('league_id', $league->id)
            ->where('group_card_id', $groupCard->id)
            ->where('age_group_key', $ageKeyDb)
            ->exists();

        if ($exists) {
            return back()->with('status', 'Playoff bracket already exists for this division.');
        }

        $rows = [
            ['round' => PlayoffMatch::ROUND_QF, 'slot' => 1],
            ['round' => PlayoffMatch::ROUND_QF, 'slot' => 2],
            ['round' => PlayoffMatch::ROUND_QF, 'slot' => 3],
            ['round' => PlayoffMatch::ROUND_QF, 'slot' => 4],
            ['round' => PlayoffMatch::ROUND_SF, 'slot' => 1],
            ['round' => PlayoffMatch::ROUND_SF, 'slot' => 2],
            ['round' => PlayoffMatch::ROUND_F, 'slot' => 1],
        ];

        foreach ($rows as $row) {
            PlayoffMatch::query()->create([
                'league_id' => $league->id,
                'group_card_id' => $groupCard->id,
                'age_group_key' => $ageKeyDb,
                'round' => $row['round'],
                'slot' => $row['slot'],
                'home_user_id' => null,
                'away_user_id' => null,
                'score' => null,
                'winner_side' => null,
                'winner_user_id' => null,
                'match_date' => null,
                'start_time' => null,
            ]);
        }

        return redirect()
            ->route('admin.league-management.playoffs.index', [$league, $groupCard] + ($ageGroupKey ? ['age_group_key' => $ageGroupKey] : []))
            ->with('status', '8-player playoff bracket created. Add players to quarterfinals, then save results.');
    }

    public function update(Request $request, League $league, GroupCard $groupCard, PlayoffMatch $playoffMatch): RedirectResponse
    {
        abort_unless($league->groupCards()->whereKey($groupCard->id)->exists(), 404);
        $this->assertPlayoffScope($league, $groupCard, $playoffMatch);

        if (LeagueSeasonPhase::playoffsClosed($league)) {
            return back()->withErrors([
                'bracket' => 'Playoffs are closed for this league. Results cannot be changed.',
            ])->withInput();
        }

        if (! LeagueSeasonPhase::playoffsStarted($league)) {
            return back()->withErrors([
                'bracket' => 'Click Playoff start before editing playoff matches.',
            ])->withInput();
        }

        [$qfComplete, $sfComplete] = $this->roundCompletionForBracket(
            $league->id,
            $groupCard->id,
            (string) ($playoffMatch->age_group_key ?? '')
        );
        if (! $this->canEditPlayoffMatch($playoffMatch, $qfComplete, $sfComplete)) {
            if ($playoffMatch->round === PlayoffMatch::ROUND_SF) {
                $msg = 'Finish all four quarterfinals (with a winner for each) before editing semifinals.';
            } elseif (! $qfComplete) {
                $msg = 'Finish all four quarterfinals before editing the final.';
            } else {
                $msg = 'Finish both semifinals (with a winner for each) before editing the final.';
            }

            return back()->withErrors(['bracket' => $msg])->withInput();
        }

        $rosterUserIds = $this->rosterUserIdsForDivision($league, $groupCard, $request);

        $validated = $request->validate([
            'home_user_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'away_user_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'match_date' => ['nullable', 'date'],
            'start_time' => ['nullable', 'string', 'max:32'],
            'venue' => ['nullable', 'string', 'max:255'],
            'court' => ['nullable', 'string', 'max:64'],
            'score' => ['nullable', 'string', 'max:64'],
            ...MatchResultInput::setFieldValidationRules(),
            'result_type' => ['nullable', 'string', 'in:normal,walkover'],
            'walked_off_side' => ['nullable', 'string', 'in:home,away'],
        ]);

        $setPairError = MatchResultInput::validateSetPairs($validated);
        if ($setPairError !== null) {
            return back()->withErrors(['score' => $setPairError])->withInput();
        }

        $oldHomeId = $playoffMatch->home_user_id ? (int) $playoffMatch->home_user_id : null;
        $oldAwayId = $playoffMatch->away_user_id ? (int) $playoffMatch->away_user_id : null;

        $homeId = array_key_exists('home_user_id', $validated)
            ? ($validated['home_user_id'] !== null && $validated['home_user_id'] !== '' ? (int) $validated['home_user_id'] : null)
            : $oldHomeId;
        $awayId = array_key_exists('away_user_id', $validated)
            ? ($validated['away_user_id'] !== null && $validated['away_user_id'] !== '' ? (int) $validated['away_user_id'] : null)
            : $oldAwayId;

        if ($homeId !== null && ! in_array($homeId, $rosterUserIds, true)) {
            return back()->withErrors(['home_user_id' => 'Home player must be registered in this division.'])->withInput();
        }
        if ($awayId !== null && ! in_array($awayId, $rosterUserIds, true)) {
            return back()->withErrors(['away_user_id' => 'Away player must be registered in this division.'])->withInput();
        }
        if ($homeId !== null && $awayId !== null && $homeId === $awayId) {
            return back()->withErrors(['away_user_id' => 'Home and away must be different players.'])->withInput();
        }

        $playersChanged = $oldHomeId !== $homeId || $oldAwayId !== $awayId;
        if ($playersChanged) {
            $playoffMatch->home_user_id = $homeId;
            $playoffMatch->away_user_id = $awayId;
            $playoffMatch->score = null;
            $playoffMatch->winner_side = null;
            $playoffMatch->winner_user_id = null;
        }

        $result = MatchResultInput::fromRequest(
            MatchResultInput::resolveScoreRaw($validated, $validated['result_type'] ?? null),
            $validated['result_type'] ?? null,
            $validated['walked_off_side'] ?? null,
        );
        $scoreTrimmed = $result['score'];
        if ($scoreTrimmed !== '' && (! $homeId || ! $awayId)) {
            return back()->withErrors([
                'score' => 'Both players must be assigned before saving a result. Use Advance winners when earlier rounds are complete.',
            ])->withInput();
        }
        $winnerSide = $result['winner_side'] ?? $this->resolvedWinnerSideForPersistence($scoreTrimmed, null);
        if ($scoreTrimmed !== '' && MatchScoreReader::isWalkover($scoreTrimmed) && $winnerSide === null) {
            return back()->withErrors([
                'walked_off_side' => 'Choose which player walked off (forfeit).',
            ])->withInput();
        }

        $matchDateCarbon = ! empty($validated['match_date'])
            ? Carbon::parse($validated['match_date'])->startOfDay()
            : null;

        $playoffDateError = LeaguePlayoffCalendar::validatePlayoffMatchDate($matchDateCarbon, $league);
        if ($playoffDateError !== null) {
            return back()->withErrors(['match_date' => $playoffDateError])->withInput();
        }

        if ($matchDateCarbon !== null) {
            $dateYmd = $matchDateCarbon->toDateString();
            $participantIds = array_values(array_filter([$homeId, $awayId]));
            $conflicts = PlayerMatchDayConflict::conflictingPlayerIds(
                $dateYmd,
                $participantIds,
                null,
                $playoffMatch->id
            );
            if ($conflicts !== []) {
                return back()->withErrors([
                    'match_date' => PlayerMatchDayConflict::messageFor($dateYmd, $conflicts),
                ])->withInput();
            }
        }

        $oldDate = $playoffMatch->match_date?->toDateString();
        $oldTime = MatchStartTime::toInputValue((string) ($playoffMatch->start_time ?? ''));
        $oldVenue = trim((string) ($playoffMatch->venue ?? ''));
        $oldCourt = trim((string) ($playoffMatch->court ?? ''));

        $newStartTime = trim((string) ($validated['start_time'] ?? ''));
        $newVenue = trim((string) ($validated['venue'] ?? ''));
        $newCourt = trim((string) ($validated['court'] ?? ''));
        $newDate = $matchDateCarbon?->toDateString();

        $playoffMatch->match_date = $validated['match_date'] ?? null;
        $playoffMatch->start_time = $newStartTime !== '' ? $newStartTime : null;
        $playoffMatch->venue = $newVenue !== '' ? $newVenue : null;
        $playoffMatch->court = $newCourt !== '' ? $newCourt : null;
        $playoffMatch->score = $scoreTrimmed !== '' ? $scoreTrimmed : null;
        $playoffMatch->winner_side = $winnerSide;
        $playoffMatch->winner_user_id = $this->resolvedWinnerUserIdForPlayoff($playoffMatch, $winnerSide);
        $playoffMatch->save();

        $scheduleChanged = $oldDate !== $newDate
            || $oldTime !== MatchStartTime::toInputValue($newStartTime)
            || $oldVenue !== $newVenue
            || $oldCourt !== $newCourt;

        if (($scheduleChanged || $playersChanged) && ($homeId || $awayId || $oldHomeId || $oldAwayId)) {
            $playoffMatch->refresh();
            $playoffMatch->load(['homeUser', 'awayUser', 'league', 'groupCard']);
            PlayoffMatchScheduleNotifier::notifyPlayoffAssignmentChange(
                $playoffMatch,
                $oldHomeId,
                $oldAwayId,
                $playersChanged,
                $scheduleChanged,
            );
        }

        $ageGroupKey = $this->ageGroupKeyFromRequest($request);

        $emailed = ($scheduleChanged || $playersChanged) && ($homeId || $awayId || $oldHomeId || $oldAwayId);

        return redirect()
            ->route('admin.league-management.playoffs.index', [$league, $groupCard] + ($ageGroupKey ? ['age_group_key' => $ageGroupKey] : []))
            ->with('status', $emailed
                ? 'Playoff match updated. Players have been emailed.'
                : 'Playoff match updated.');
    }

    /**
     * Copy QF winners into SF slots, then SF winners into the final (when prior matches are complete).
     */
    public function pullWinners(Request $request, League $league, GroupCard $groupCard): RedirectResponse
    {
        abort_unless($league->groupCards()->whereKey($groupCard->id)->exists(), 404);
        abort_unless(Schema::hasTable('playoff_matches'), 404);

        if (LeagueSeasonPhase::playoffsClosed($league)) {
            return back()->withErrors(['bracket' => 'Playoffs are closed for this league.']);
        }

        if (! LeagueSeasonPhase::playoffsStarted($league)) {
            return back()->withErrors(['bracket' => 'Click Playoff start before advancing winners.']);
        }

        $ageGroupKey = $this->ageGroupKeyFromRequest($request);
        $ageKeyDb = $ageGroupKey ?? '';

        $messages = [];

        $filledPpq = PlayoffBracketBuilder::feedPpqWinnersIntoPreQuarter($league->id, $groupCard->id, $ageKeyDb);
        if ($filledPpq > 0) {
            $messages[] = "{$filledPpq} Pre-Pre-Q winner(s) placed into Round of 16 away slots.";
        }

        $filledPq = PlayoffBracketBuilder::feedPreQWinnersIntoQuarterfinals($league->id, $groupCard->id, $ageKeyDb);
        if ($filledPq > 0) {
            $messages[] = "{$filledPq} Round of 16 winner(s) placed into quarterfinal slots.";
        }

        [$qfComplete, $sfComplete] = $this->roundCompletionForBracket($league->id, $groupCard->id, $ageKeyDb);
        if (! $qfComplete) {
            $hint = 'Finish Pre-Pre-Q and Round of 16 matches first, then all quarterfinals, before semifinals unlock.';
            if ($messages !== []) {
                return redirect()
                    ->route('admin.league-management.playoffs.index', [$league, $groupCard] + ($ageGroupKey ? ['age_group_key' => $ageGroupKey] : []))
                    ->with('status', implode(' ', $messages).' '.$hint);
            }

            return redirect()
                ->route('admin.league-management.playoffs.index', [$league, $groupCard] + ($ageGroupKey ? ['age_group_key' => $ageGroupKey] : []))
                ->withErrors(['bracket' => $hint]);
        }

        $all = PlayoffMatch::query()
            ->where('league_id', $league->id)
            ->where('group_card_id', $groupCard->id)
            ->where('age_group_key', $ageKeyDb)
            ->get()
            ->keyBy(fn (PlayoffMatch $m) => $m->round.'-'.$m->slot);

        $w = function (string $round, int $slot) use ($all): ?int {
            /** @var PlayoffMatch|null $m */
            $m = $all->get($round.'-'.$slot);

            return $m?->bracketWinnerUserId();
        };

        $qf1 = $w(PlayoffMatch::ROUND_QF, 1);
        $qf2 = $w(PlayoffMatch::ROUND_QF, 2);
        $qf3 = $w(PlayoffMatch::ROUND_QF, 3);
        $qf4 = $w(PlayoffMatch::ROUND_QF, 4);

        if ($qf1 && $qf2 && $qf3 && $qf4) {
            $this->assignIfExists($all, PlayoffMatch::ROUND_SF, 1, 'home_user_id', $qf1);
            $this->assignIfExists($all, PlayoffMatch::ROUND_SF, 1, 'away_user_id', $qf2);
            $this->assignIfExists($all, PlayoffMatch::ROUND_SF, 2, 'home_user_id', $qf3);
            $this->assignIfExists($all, PlayoffMatch::ROUND_SF, 2, 'away_user_id', $qf4);
        }

        $all = PlayoffMatch::query()
            ->where('league_id', $league->id)
            ->where('group_card_id', $groupCard->id)
            ->where('age_group_key', $ageKeyDb)
            ->get()
            ->keyBy(fn (PlayoffMatch $m) => $m->round.'-'.$m->slot);

        $sf1 = $all->get(PlayoffMatch::ROUND_SF.'-1')?->bracketWinnerUserId();
        $sf2 = $all->get(PlayoffMatch::ROUND_SF.'-2')?->bracketWinnerUserId();
        if ($sf1 && $sf2) {
            $this->assignIfExists($all, PlayoffMatch::ROUND_F, 1, 'home_user_id', $sf1);
            $this->assignIfExists($all, PlayoffMatch::ROUND_F, 1, 'away_user_id', $sf2);
        }

        $messages[] = 'Semifinals and final updated from quarterfinal / semifinal winners.';

        return redirect()
            ->route('admin.league-management.playoffs.index', [$league, $groupCard] + ($ageGroupKey ? ['age_group_key' => $ageGroupKey] : []))
            ->with('status', implode(' ', $messages));
    }

    /**
     * @param  Collection<string, Collection<int, PlayoffMatch>>  $byRound
     * @return array{0: bool, 1: bool}
     */
    private function roundCompletionFromGrouped(Collection $byRound): array
    {
        $qf = $byRound->get(PlayoffMatch::ROUND_QF, collect());
        $sf = $byRound->get(PlayoffMatch::ROUND_SF, collect());
        $qfComplete = $qf->count() === 4 && $qf->every(fn (PlayoffMatch $m) => ! $m->isPending());
        $sfComplete = $sf->count() === 2 && $sf->every(fn (PlayoffMatch $m) => ! $m->isPending());

        return [$qfComplete, $sfComplete];
    }

    /**
     * @return array{0: bool, 1: bool}
     */
    private function roundCompletionForBracket(int $leagueId, int $groupCardId, string $ageGroupKeyDb): array
    {
        $byRound = PlayoffMatch::query()
            ->where('league_id', $leagueId)
            ->where('group_card_id', $groupCardId)
            ->where('age_group_key', $ageGroupKeyDb)
            ->get()
            ->groupBy('round');

        return $this->roundCompletionFromGrouped($byRound);
    }

    private function canEditPlayoffMatch(PlayoffMatch $playoffMatch, bool $qfComplete, bool $sfComplete): bool
    {
        return match ($playoffMatch->round) {
            PlayoffMatch::ROUND_PRE_PRE_Q, PlayoffMatch::ROUND_PRE_Q, PlayoffMatch::ROUND_QF => true,
            PlayoffMatch::ROUND_SF => $qfComplete,
            PlayoffMatch::ROUND_F => $qfComplete && $sfComplete,
            default => false,
        };
    }

    /**
     * @param  Collection<int, PlayoffMatch>  $matches
     */
    private function roundIsComplete(Collection $matches): bool
    {
        return $matches->isNotEmpty() && $matches->every(fn (PlayoffMatch $m) => ! $m->isPending());
    }

    private function assignIfExists(Collection $all, string $round, int $slot, string $field, int $userId): void
    {
        /** @var PlayoffMatch|null $m */
        $m = $all->get($round.'-'.$slot);
        if (! $m instanceof PlayoffMatch) {
            return;
        }
        if ($field === 'home_user_id' && $m->home_user_id) {
            return;
        }
        if ($field === 'away_user_id' && $m->away_user_id) {
            return;
        }
        $m->{$field} = $userId;
        $m->score = null;
        $m->winner_side = null;
        $m->winner_user_id = null;
        $m->save();
    }

    private function assertPlayoffScope(League $league, GroupCard $groupCard, PlayoffMatch $playoffMatch): void
    {
        abort_unless(
            $playoffMatch->league_id === $league->id
            && $playoffMatch->group_card_id === $groupCard->id,
            404
        );
    }

    /**
     * @param  Collection<int, LeagueRegistration>  $rosterRegs
     * @return Collection<int, User>
     */
    private function playoffRosterUsers(Collection $rosterRegs): Collection
    {
        return $rosterRegs
            ->pluck('user')
            ->filter()
            ->unique('id')
            ->sortBy(fn (?User $user) => strtolower((string) ($user?->name ?? '')))
            ->values();
    }

    /**
     * @return list<int>
     */
    private function rosterUserIdsForDivision(League $league, GroupCard $groupCard, Request $request): array
    {
        $ageGroupKey = $this->ageGroupKeyFromRequest($request);

        $query = LeagueRegistration::query()
            ->where('league_id', $league->id)
            ->where('group_card_id', $groupCard->id);

        if ($ageGroupKey !== null && Schema::hasColumn('league_registrations', 'age_group_key')) {
            $query->where('age_group_key', $ageGroupKey);
        }

        return $query
            ->pluck('user_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    private function ageGroupKeyFromRequest(Request $request): ?string
    {
        $ageGroupKey = (string) $request->query('age_group_key', '');

        return $ageGroupKey !== '' ? $ageGroupKey : null;
    }

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

    private function resolvedWinnerUserIdForPlayoff(PlayoffMatch $match, ?string $winnerSide): ?int
    {
        if ($winnerSide === null) {
            return null;
        }

        return $winnerSide === 'home'
            ? (int) $match->home_user_id
            : (int) $match->away_user_id;
    }

    /**
     * @return Collection<int, int>
     */
    private function divisionRosterUserIds(League $league, GroupCard $groupCard, ?string $ageGroupKey): Collection
    {
        if (! Schema::hasTable('league_registrations')) {
            return collect();
        }

        $q = LeagueRegistration::query()
            ->where('league_id', $league->id)
            ->where('group_card_id', $groupCard->id);

        if ($ageGroupKey !== null && Schema::hasColumn('league_registrations', 'age_group_key')) {
            $q->where('age_group_key', $ageGroupKey);
        }

        return $q->pluck('user_id')->filter()->map(fn ($id) => (int) $id)->unique()->values();
    }
}
