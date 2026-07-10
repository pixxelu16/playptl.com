<?php

namespace App\Support;

use App\Models\GroupMatch;

/**
 * PTL league point rules (PF / PA) from the official standings sheet.
 */
final class LeaguePointSystem
{
    public const WINNER_STRAIGHT_SETS = 14;

    public const WINNER_THREE_SETS = 12;

    public const LOSER_MAX = 8;

    public const WALKOVER_WINNER = 10;

    public const WALKOVER_LOSER = 0;

    /**
     * Match points per side, or null when the match is pending / not scorable.
     *
     * @return array{home: int, away: int}|null
     */
    public static function resolveMatchPoints(GroupMatch $match): ?array
    {
        if ($match->isPending()) {
            return null;
        }

        $homeWon = $match->homeSideWon();
        if ($homeWon === null) {
            return null;
        }

        $score = trim((string) ($match->score ?? ''));

        if (MatchScoreReader::isWalkover($score)) {
            return $homeWon
                ? ['home' => self::WALKOVER_WINNER, 'away' => self::WALKOVER_LOSER]
                : ['home' => self::WALKOVER_LOSER, 'away' => self::WALKOVER_WINNER];
        }

        $totals = $score !== '' ? MatchScoreReader::totals($score) : null;
        if ($totals === null) {
            if (trim((string) ($match->winner_side ?? '')) !== '') {
                return $homeWon
                    ? ['home' => self::WALKOVER_WINNER, 'away' => self::WALKOVER_LOSER]
                    : ['home' => self::WALKOVER_LOSER, 'away' => self::WALKOVER_WINNER];
            }

            return null;
        }

        $setsPlayed = $totals['homeSets'] + $totals['awaySets'];
        $winnerPoints = $setsPlayed === 2 ? self::WINNER_STRAIGHT_SETS : self::WINNER_THREE_SETS;

        if ($homeWon) {
            return [
                'home' => $winnerPoints,
                'away' => min($totals['awayGames'], self::LOSER_MAX),
            ];
        }

        return [
            'home' => min($totals['homeGames'], self::LOSER_MAX),
            'away' => $winnerPoints,
        ];
    }

    /**
     * @param  list<array{pointsFor: int, pointsAgainst: int, gamesWon: int, gamesLost: int, matches: int, wins: int, losses: int, name: string}>  $rows
     */
    public static function sortStandingsRows(array &$rows): void
    {
        usort($rows, function (array $a, array $b): int {
            if ($a['pointsFor'] !== $b['pointsFor']) {
                return $b['pointsFor'] <=> $a['pointsFor'];
            }
            if ($a['pointsAgainst'] !== $b['pointsAgainst']) {
                return $a['pointsAgainst'] <=> $b['pointsAgainst'];
            }
            $ratioA = self::gameWinRatio($a);
            $ratioB = self::gameWinRatio($b);
            if ($ratioA !== $ratioB) {
                return $ratioB <=> $ratioA;
            }
            if (($a['wins'] ?? 0) !== ($b['wins'] ?? 0)) {
                return ($b['wins'] ?? 0) <=> ($a['wins'] ?? 0);
            }

            return strcmp($a['name'], $b['name']);
        });
    }

    /**
     * @param  array{gamesWon?: int, gamesLost?: int}  $row
     */
    private static function gameWinRatio(array $row): float
    {
        $won = (int) ($row['gamesWon'] ?? 0);
        $lost = (int) ($row['gamesLost'] ?? 0);
        $played = $won + $lost;

        return $played > 0 ? $won / $played : 0.0;
    }
}
