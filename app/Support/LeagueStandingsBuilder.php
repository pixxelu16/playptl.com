<?php

namespace App\Support;

use App\Enums\GroupMatchFormat;
use App\Models\Group;
use App\Models\GroupCard;
use App\Models\GroupMatch;
use App\Models\League;
use App\Models\LeagueRegistration;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

final class LeagueStandingsBuilder
{
    /**
     * Standings for a league sub group; optionally limited to one roster group.
     *
     * @return list<array{rank: int, userId: int, name: string, avatarUrl: string, groupName: string, matches: int, wins: int, losses: int, points: int, pointsAgainst: int, gamePct: int, gamesWon: int}>
     */
    public static function forSubGroup(
        League $league,
        GroupCard $groupCard,
        ?string $ageGroupKey = null,
        ?int $groupId = null,
    ): array {
        if (! Schema::hasTable('group_matches') || ! Schema::hasTable('league_registrations')) {
            return [];
        }

        $regsQuery = LeagueRegistration::query()
            ->where('league_id', $league->id)
            ->where(function ($q) use ($groupCard) {
                $q->whereNull('group_card_id')->orWhere('group_card_id', $groupCard->id);
            })
            ->when($groupId !== null, fn ($q) => $q->where('group_id', $groupId))
            ->when(
                $ageGroupKey !== null && Schema::hasColumn('league_registrations', 'age_group_key'),
                fn ($q) => $q->where('age_group_key', $ageGroupKey)
            )
            ->with(['user', 'group']);

        $byUser = [];

        foreach (LeagueRegistrationRoster::collapseForDisplay($regsQuery->get()) as $entry) {
            $user = $entry['user'];
            if (! $user) {
                continue;
            }
            $uid = (int) $user->id;
            $slot = self::emptySlot(
                $uid,
                (string) $entry['display_name'],
                self::avatarUrl($user)
            );
            $slot['groupName'] = (string) ($entry['registration']->group?->name ?? 'Unassigned');
            $byUser[$uid] = $slot;
        }

        $matchesQuery = GroupMatch::query()
            ->where('league_id', $league->id)
            ->where(function ($q) use ($groupCard) {
                $q->whereNull('group_card_id')->orWhere('group_card_id', $groupCard->id);
            })
            ->when($groupId !== null, fn ($q) => $q->where('group_id', $groupId))
            ->with(['homeUser', 'awayUser', 'homePartnerUser', 'awayPartnerUser'])
            ->orderBy('match_date')
            ->orderBy('id');

        foreach ($matchesQuery->get() as $match) {
            self::applyMatch($match, $byUser);
        }

        if ($byUser === []) {
            return [];
        }

        $rows = [];
        foreach ($byUser as $uid => $s) {
            $gw = $s['gamesWon'];
            $gl = $s['gamesLost'];
            $playedGames = $gw + $gl;

            $rows[] = [
                'userId' => (int) $uid,
                'name' => $s['name'],
                'avatarUrl' => $s['avatarUrl'],
                'groupName' => (string) ($s['groupName'] ?? '—'),
                'matches' => $s['matches'],
                'wins' => $s['wins'],
                'losses' => $s['losses'],
                'pointsFor' => $s['pointsFor'],
                'pointsAgainst' => $s['pointsAgainst'],
                'points' => $s['pointsFor'],
                'gamePct' => $playedGames > 0 ? (int) round(100 * $gw / $playedGames) : 0,
                'gamesWon' => $gw,
            ];
        }

        LeaguePointSystem::sortStandingsRows($rows);

        $out = [];
        foreach ($rows as $i => $row) {
            $out[] = array_merge($row, ['rank' => $i + 1]);
        }

        return $out;
    }

    /**
     * @return array{name: string, avatarUrl: string, matches: int, wins: int, losses: int, pointsFor: int, pointsAgainst: int, gamesWon: int, gamesLost: int}
     */
    private static function emptySlot(int $userId, string $name, string $avatarUrl): array
    {
        return [
            'name' => $name !== '' ? $name : 'Player',
            'avatarUrl' => $avatarUrl,
            'matches' => 0,
            'wins' => 0,
            'losses' => 0,
            'pointsFor' => 0,
            'pointsAgainst' => 0,
            'gamesWon' => 0,
            'gamesLost' => 0,
        ];
    }

    private static function avatarUrl(User $user): string
    {
        $path = $user->avatar_path ?? null;

        return $path ? asset($path) : asset('upload/user-avatar/default-user-pic.png');
    }

    /**
     * @param  array<int, array{name: string, avatarUrl: string, matches: int, wins: int, losses: int, pointsFor: int, pointsAgainst: int, gamesWon: int, gamesLost: int}>  $byUser
     */
    private static function applyMatch(GroupMatch $match, array &$byUser): void
    {
        $sidePoints = LeaguePointSystem::resolveMatchPoints($match);
        if ($sidePoints === null) {
            return;
        }

        $homeWonMatch = $match->homeSideWon();
        if ($homeWonMatch === null) {
            return;
        }

        $score = trim((string) ($match->score ?? ''));
        $totals = $score !== '' ? MatchScoreReader::totals($score) : null;
        if ($totals !== null && $totals['homeSets'] !== $totals['awaySets']) {
            $homeGw = $totals['homeGames'];
            $awayGw = $totals['awayGames'];
        } else {
            $homeGw = 0;
            $awayGw = 0;
        }

        $homeIds = [(int) $match->home_user_id];
        $awayIds = [(int) $match->away_user_id];
        if ($match->format === GroupMatchFormat::Doubles) {
            if ($match->home_partner_user_id) {
                $homeIds[] = (int) $match->home_partner_user_id;
            }
            if ($match->away_partner_user_id) {
                $awayIds[] = (int) $match->away_partner_user_id;
            }
        }

        foreach ($homeIds as $hid) {
            if ($hid <= 0 || ! isset($byUser[$hid])) {
                continue;
            }
            $byUser[$hid]['matches']++;
            $homeWonMatch ? $byUser[$hid]['wins']++ : $byUser[$hid]['losses']++;
            $byUser[$hid]['pointsFor'] += $sidePoints['home'];
            $byUser[$hid]['pointsAgainst'] += $sidePoints['away'];
            $byUser[$hid]['gamesWon'] += $homeGw;
            $byUser[$hid]['gamesLost'] += $awayGw;
        }

        foreach ($awayIds as $aid) {
            if ($aid <= 0 || ! isset($byUser[$aid])) {
                continue;
            }
            $byUser[$aid]['matches']++;
            $homeWonMatch ? $byUser[$aid]['losses']++ : $byUser[$aid]['wins']++;
            $byUser[$aid]['pointsFor'] += $sidePoints['away'];
            $byUser[$aid]['pointsAgainst'] += $sidePoints['home'];
            $byUser[$aid]['gamesWon'] += $awayGw;
            $byUser[$aid]['gamesLost'] += $homeGw;
        }
    }
}
