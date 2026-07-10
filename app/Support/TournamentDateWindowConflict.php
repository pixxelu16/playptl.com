<?php

namespace App\Support;

use App\Models\GroupCard;
use App\Models\League;
use App\Models\PlayoffMatch;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

/**
 * When scheduled matches extend past the tournament end date, admins must expand the tournament window first.
 */
final class TournamentDateWindowConflict
{
    public static function groupMatchesExceedTournamentEnd(League $league, GroupCard $groupCard): ?string
    {
        if (! DivisionScheduleWindow::tournamentDatesConfigured($league)) {
            return null;
        }

        $latestMatch = DivisionScheduleWindow::latestScheduledMatchDate($league, $groupCard);
        $tournamentEnd = $league->end_date->copy()->startOfDay();

        if ($latestMatch === null || ! $latestMatch->gt($tournamentEnd)) {
            return null;
        }

        return self::message($league, $latestMatch, $tournamentEnd, 'group');
    }

    /**
     * Playoffs page: group stage runs past the tournament end, so playoffs cannot be scheduled yet.
     */
    public static function playoffsNeedTournamentExtension(League $league, GroupCard $groupCard): ?string
    {
        if (! DivisionScheduleWindow::tournamentDatesConfigured($league)) {
            return null;
        }

        $tournamentEnd = $league->end_date->copy()->startOfDay();
        $latestGroupMatch = DivisionScheduleWindow::latestScheduledMatchDate($league, $groupCard);
        $earliestPlayoffStart = DivisionScheduleWindow::earliestPlayoffStartDate($league, $groupCard);

        if ($latestGroupMatch === null) {
            return null;
        }

        $minimumTournamentEnd = $earliestPlayoffStart ?? $latestGroupMatch->copy()->addDay()->startOfDay();

        if ($minimumTournamentEnd->gt($tournamentEnd)) {
            return sprintf(
                'Group matches run through %s. Playoffs can start from %s onward, but this tournament ends %s. Extend the tournament end date on Edit Tournament (to at least %s) before scheduling playoffs.',
                $latestGroupMatch->format('M j, Y'),
                $minimumTournamentEnd->format('M j, Y'),
                $tournamentEnd->format('M j, Y'),
                $minimumTournamentEnd->format('M j, Y'),
            );
        }

        $savedPlayoffEnd = $league->playoff_end_date?->copy()->startOfDay();
        if ($savedPlayoffEnd !== null && $savedPlayoffEnd->gt($tournamentEnd)) {
            return self::playoffWindowNeedsExtensionMessage($savedPlayoffEnd, $tournamentEnd);
        }

        $savedPlayoffStart = $league->playoff_start_date?->copy()->startOfDay();
        if ($savedPlayoffStart !== null && $savedPlayoffStart->gt($tournamentEnd)) {
            return self::playoffWindowNeedsExtensionMessage($savedPlayoffStart, $tournamentEnd);
        }

        return null;
    }

    public static function playoffMatchesExceedTournamentEnd(
        League $league,
        GroupCard $groupCard,
        ?string $ageGroupKey = null,
    ): ?string {
        if (! DivisionScheduleWindow::tournamentDatesConfigured($league)) {
            return null;
        }

        $latestMatch = self::latestScheduledPlayoffMatchDate($league, $groupCard, $ageGroupKey);
        $tournamentEnd = $league->end_date->copy()->startOfDay();

        if ($latestMatch === null || ! $latestMatch->gt($tournamentEnd)) {
            return null;
        }

        return sprintf(
            'Scheduled playoff matches run through %s, but this tournament ends %s. Extend the tournament end date on Edit Tournament (to at least %s).',
            $latestMatch->format('M j, Y'),
            $tournamentEnd->format('M j, Y'),
            $latestMatch->format('M j, Y'),
        );
    }

    public static function playoffDatesNeedTournamentExtension(
        League $league,
        Carbon $playoffEnd,
    ): ?string {
        if (! DivisionScheduleWindow::tournamentDatesConfigured($league)) {
            return null;
        }

        $tournamentEnd = $league->end_date->copy()->startOfDay();
        $end = $playoffEnd->copy()->startOfDay();

        if (! $end->gt($tournamentEnd)) {
            return null;
        }

        return self::playoffWindowNeedsExtensionMessage($end, $tournamentEnd);
    }

    public static function latestScheduledPlayoffMatchDate(
        League $league,
        GroupCard $groupCard,
        ?string $ageGroupKey = null,
    ): ?Carbon {
        if (! Schema::hasTable('playoff_matches')) {
            return null;
        }

        $latest = PlayoffMatch::query()
            ->where('league_id', $league->id)
            ->where('group_card_id', $groupCard->id)
            ->where('age_group_key', $ageGroupKey ?? '')
            ->whereNotNull('match_date')
            ->max('match_date');

        return $latest !== null ? Carbon::parse($latest)->startOfDay() : null;
    }

    private static function message(
        League $league,
        Carbon $latestMatch,
        Carbon $tournamentEnd,
        string $context,
    ): string {
        if ($context === 'playoff') {
            return sprintf(
                'Scheduled playoff matches run through %s, but this tournament ends %s. Extend the tournament end date on Edit Tournament (to at least %s).',
                $latestMatch->format('M j, Y'),
                $tournamentEnd->format('M j, Y'),
                $latestMatch->format('M j, Y'),
            );
        }

        return sprintf(
            'Scheduled group matches run through %s, but this tournament ends %s. You cannot set a group end date until you extend the tournament end date on Edit Tournament (to at least %s).',
            $latestMatch->format('M j, Y'),
            $tournamentEnd->format('M j, Y'),
            $latestMatch->format('M j, Y'),
        );
    }

    private static function playoffWindowNeedsExtensionMessage(Carbon $neededEnd, Carbon $tournamentEnd): string
    {
        return sprintf(
            'Playoff dates extend through %s, but this tournament ends %s. Extend the tournament end date on Edit Tournament (to at least %s).',
            $neededEnd->format('M j, Y'),
            $tournamentEnd->format('M j, Y'),
            $neededEnd->format('M j, Y'),
        );
    }
}
