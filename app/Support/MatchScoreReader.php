<?php

namespace App\Support;

final class MatchScoreReader
{
    /**
     * Parse typical tennis set strings (e.g. "6-3, 7-5" or "6-3 7-5") into set wins and total games per side.
     *
     * @return array{homeSets: int, awaySets: int, homeGames: int, awayGames: int}|null
     */
    public static function totals(string $raw): ?array
    {
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }

        if (self::isWalkover($raw)) {
            return null;
        }

        if (! preg_match_all('/\d+\s*[-–\/]\s*\d+/', $raw, $m)) {
            return null;
        }

        $homeSets = 0;
        $awaySets = 0;
        $homeGames = 0;
        $awayGames = 0;

        foreach ($m[0] as $segment) {
            if (! preg_match('/^(\d+)\s*[-–\/]\s*(\d+)$/', trim($segment), $p)) {
                continue;
            }
            $a = (int) $p[1];
            $b = (int) $p[2];
            $homeGames += $a;
            $awayGames += $b;
            if ($a > $b) {
                $homeSets++;
            } elseif ($b > $a) {
                $awaySets++;
            }
        }

        if ($homeSets === 0 && $awaySets === 0) {
            return null;
        }

        return [
            'homeSets' => $homeSets,
            'awaySets' => $awaySets,
            'homeGames' => $homeGames,
            'awayGames' => $awayGames,
        ];
    }

    public static function isWalkover(string $raw): bool
    {
        return preg_match('/\b(?:wo|walkover|walk\s*off|ret\.?|default|def\.?)\b/i', trim($raw)) === 1;
    }

    /**
     * Winner side when score is a walkover and {@see $winnerSide} is set (home/away = winner, not who walked off).
     */
    public static function homeSideWonFromWalkover(string $raw, ?string $winnerSide): ?bool
    {
        if (! self::isWalkover($raw)) {
            return null;
        }
        if ($winnerSide === 'home') {
            return true;
        }
        if ($winnerSide === 'away') {
            return false;
        }

        return null;
    }

    /**
     * Parsed set-by-set display data for scoreboard UI.
     *
     * @return array{
     *     homeSets: int,
     *     awaySets: int,
     *     sets: list<array{home: int, away: int}>,
     *     homeWon: bool
     * }|null
     */
    /**
     * Up to three set rows for score entry forms (empty strings when unset).
     *
     * @return list<array{home: string, away: string}>
     */
    public static function setsForForm(string $raw, int $maxSets = 3): array
    {
        $out = [];
        for ($i = 0; $i < $maxSets; $i++) {
            $out[] = ['home' => '', 'away' => ''];
        }

        $raw = trim($raw);
        if ($raw === '' || self::isWalkover($raw)) {
            return $out;
        }

        $breakdown = self::breakdown($raw);
        if ($breakdown === null) {
            return $out;
        }

        foreach ($breakdown['sets'] as $i => $set) {
            if ($i >= $maxSets) {
                break;
            }
            $out[$i] = [
                'home' => (string) $set['home'],
                'away' => (string) $set['away'],
            ];
        }

        return $out;
    }

    public static function breakdown(string $raw): ?array
    {
        $t = self::totals($raw);
        if ($t === null) {
            return null;
        }

        if (! preg_match_all('/\d+\s*[-–\/]\s*\d+/', trim($raw), $m)) {
            return null;
        }

        $sets = [];
        foreach ($m[0] as $segment) {
            if (! preg_match('/^(\d+)\s*[-–\/]\s*(\d+)$/', trim($segment), $p)) {
                continue;
            }
            $sets[] = [
                'home' => (int) $p[1],
                'away' => (int) $p[2],
            ];
        }

        if ($sets === []) {
            return null;
        }

        return [
            'homeSets' => $t['homeSets'],
            'awaySets' => $t['awaySets'],
            'sets' => $sets,
            'homeWon' => $t['homeSets'] > $t['awaySets'],
        ];
    }

    /**
     * True if the home side won the match, false if away won, null if pending or unparseable.
     */
    public static function homeSideWon(string $raw): ?bool
    {
        $t = self::totals($raw);
        if ($t === null) {
            return null;
        }
        if ($t['homeSets'] === $t['awaySets']) {
            return null;
        }

        return $t['homeSets'] > $t['awaySets'];
    }
}
