<?php

namespace App\Support;

use App\Models\League;
use Illuminate\Support\Carbon;

class LeagueSeasonWindow
{
    /**
     * @return array{is_current: bool, label: string}
     */
    public static function status(League $league, ?Carbon $today = null): array
    {
        $today ??= now()->startOfDay();

        if ($league->isFinished() || ($league->stats ?? '') !== 'active') {
            return ['is_current' => false, 'label' => 'Inactive'];
        }

        if ($league->start_date !== null && $today->lt($league->start_date->copy()->startOfDay())) {
            return ['is_current' => false, 'label' => 'Upcoming'];
        }

        if ($league->end_date !== null && $today->gt($league->end_date->copy()->startOfDay())) {
            return ['is_current' => false, 'label' => 'Season ended'];
        }

        return ['is_current' => true, 'label' => 'Current'];
    }

    public static function isCurrent(League $league, ?Carbon $today = null): bool
    {
        return self::status($league, $today)['is_current'];
    }

    public static function label(League $league): string
    {
        if ($league->start_date !== null && $league->end_date !== null) {
            return $league->start_date->format('M j, Y').' – '.$league->end_date->format('M j, Y');
        }

        if ($league->start_date !== null) {
            return 'Starts '.$league->start_date->format('M j, Y');
        }

        if ($league->end_date !== null) {
            return 'Ends '.$league->end_date->format('M j, Y');
        }

        return 'Dates not announced';
    }

    /**
     * Tournaments shown on admin player list (active season + upcoming).
     */
    public static function isListedForAdminPlayers(League $league): bool
    {
        if ($league->isFinished()) {
            return false;
        }

        return in_array((string) ($league->stats ?? ''), ['active', 'upcoming'], true);
    }

    public static function adminPlayerListStatusLabel(League $league): string
    {
        if (($league->stats ?? '') === 'upcoming') {
            return 'Upcoming';
        }

        $season = self::status($league);

        if ($season['label'] === 'Upcoming') {
            return 'Upcoming';
        }

        if ($season['is_current']) {
            return 'Active';
        }

        return $season['label'];
    }
}
