<?php

namespace App\Support;

use App\Models\GroupCard;
use App\Models\League;
use App\Models\PlayoffMatch;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

/**
 * Read-only playoff bracket data for the public league detail page (same rules as admin).
 */
final class PlayoffBracketPresenter
{
    /**
     * @return array<string, mixed>
     */
    public static function publicViewData(League $league, GroupCard $groupCard, ?string $ageGroupKey = null): array
    {
        $ageKeyDb = $ageGroupKey ?? '';
        $showQualifierPlayoffs = LeagueSeasonPhase::showQualifierAndPlayoffs($league, $groupCard->id);

        if (! $showQualifierPlayoffs) {
            return [
                'showPlayoffsSection' => false,
                'bracketExists' => false,
                'playoffRounds' => [],
                'qualifierUnavailableMessage' => LeagueSeasonPhase::qualifierPlayoffsUnavailableMessage($league),
                'playoffEmptyMessage' => null,
            ];
        }

        $qualifierReady = Schema::hasTable('playoff_qualifiers')
            && PlayoffBracketBuilder::hasPlayoffAssignments($league->id, $groupCard->id, $ageKeyDb);

        if ($qualifierReady && PlayoffBracketBuilder::bracketStructureIsStale($league->id, $groupCard->id, $ageKeyDb)) {
            PlayoffBracketBuilder::rebuild($league, $groupCard, $ageGroupKey);
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
        [$qfComplete, $sfComplete] = self::roundCompletionFromGrouped($byRound);

        $playoffEmptyMessage = null;
        if (! $qualifierReady) {
            $playoffEmptyMessage = 'Playoff paths are not set for this division yet. The bracket will appear here after the league admin assigns Qualifier paths.';
        } elseif ($playoffMatches->isEmpty()) {
            $playoffEmptyMessage = 'Qualifier paths are saved. The knockout bracket will appear here once it is generated.';
        }

        return [
            'showPlayoffsSection' => true,
            'bracketExists' => $qualifierReady && $playoffMatches->isNotEmpty(),
            'playoffRounds' => self::buildRounds($byRound, $qfComplete, $sfComplete),
            'qfComplete' => $qfComplete,
            'sfComplete' => $sfComplete,
            'qualifierUnavailableMessage' => LeagueSeasonPhase::qualifierPlayoffsUnavailableMessage($league),
            'playoffEmptyMessage' => $playoffEmptyMessage,
        ];
    }

    /**
     * @param  Collection<string, Collection<int, PlayoffMatch>>  $byRound
     * @return list<array{id: string, label: string, title: string, hint: string, matches: Collection<int, PlayoffMatch>, done: bool}>
     */
    private static function buildRounds(Collection $byRound, bool $qfComplete, bool $sfComplete): array
    {
        $rounds = [];

        $ppq = $byRound->get(PlayoffMatch::ROUND_PRE_PRE_Q, collect());
        if ($ppq->isNotEmpty()) {
            $rounds[] = [
                'id' => 'ppq',
                'label' => 'Pre-Pre-Q',
                'title' => 'Pre-Pre-Quarterfinals',
                'hint' => 'Sixteen players play down to eight winners who advance to the Round of 16.',
                'matches' => $ppq,
                'done' => $ppq->every(fn (PlayoffMatch $m) => ! $m->isPending()),
            ];
        }

        $pq = $byRound->get(PlayoffMatch::ROUND_PRE_Q, collect());
        if ($pq->isNotEmpty()) {
            $rounds[] = [
                'id' => 'pq',
                'label' => 'Pre-Q',
                'title' => 'Pre-Quarterfinals (Round of 16)',
                'hint' => 'Eight direct seeds on home; away slots fill from Pre-Pre-Q winners.',
                'matches' => $pq,
                'done' => $pq->every(fn (PlayoffMatch $m) => ! $m->isPending()),
            ];
        }

        $qf = $byRound->get(PlayoffMatch::ROUND_QF, collect());
        if ($qf->isNotEmpty()) {
            $rounds[] = [
                'id' => 'qf',
                'label' => 'Quarterfinals',
                'title' => 'Quarterfinals',
                'hint' => 'Quarter seeds on home; away slots fill from Round of 16 winners.',
                'matches' => $qf,
                'done' => $qfComplete,
            ];
        }

        $sf = $byRound->get(PlayoffMatch::ROUND_SF, collect());
        if ($sf->isNotEmpty()) {
            $rounds[] = [
                'id' => 'sf',
                'label' => 'Semifinals',
                'title' => 'Semifinals',
                'hint' => 'Opens when all four quarterfinals have a winner.',
                'matches' => $sf,
                'done' => $sfComplete,
            ];
        }

        $final = $byRound->get(PlayoffMatch::ROUND_F, collect())->first();
        if ($final instanceof PlayoffMatch) {
            $rounds[] = [
                'id' => 'f',
                'label' => 'Final',
                'title' => 'Final',
                'hint' => 'Opens when both semifinals have a winner.',
                'matches' => collect([$final]),
                'done' => ! $final->isPending(),
            ];
        }

        return $rounds;
    }

    /**
     * @param  Collection<string, Collection<int, PlayoffMatch>>  $byRound
     * @return array{0: bool, 1: bool}
     */
    private static function roundCompletionFromGrouped(Collection $byRound): array
    {
        $qf = $byRound->get(PlayoffMatch::ROUND_QF, collect());
        $sf = $byRound->get(PlayoffMatch::ROUND_SF, collect());
        $qfComplete = $qf->count() === 4 && $qf->every(fn (PlayoffMatch $m) => ! $m->isPending());
        $sfComplete = $sf->count() === 2 && $sf->every(fn (PlayoffMatch $m) => ! $m->isPending());

        return [$qfComplete, $sfComplete];
    }
}
