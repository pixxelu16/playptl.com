<?php

namespace App\Support;

use App\Enums\GroupMatchFormat;
use App\Models\GroupCard;
use App\Models\GroupMatch;
use App\Models\League;
use App\Models\LeagueRegistration;
use App\Models\PlayoffMatch;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

final class MatchSchedulePresenter
{
    /**
     * @param  Collection<int, GroupMatch>  $matches
     * @return list<array{dateLabel: string, matches: list<array<string, mixed>>}>
     */
    public static function groupIntoDays(
        Collection $matches,
        ?League $league = null,
        ?GroupCard $groupCard = null,
        bool $showSeedsInMeta = false,
        bool $showLeagueInMeta = false,
    ): array {
        if ($matches->isEmpty()) {
            return [];
        }

        $seedMapsByGroup = ($showSeedsInMeta && $league && $groupCard)
            ? self::seedMapsByGroup($league, $groupCard, $matches->pluck('group_id')->unique()->filter()->all())
            : [];

        $sorted = $matches->sortBy(fn (GroupMatch $m) => $m->match_date->format('Y-m-d')
            .'#'.str_pad((string) ((int) ($m->sort_order ?? 0)), 5, '0', STR_PAD_LEFT)
            .'#'.str_pad((string) $m->id, 10, '0', STR_PAD_LEFT));

        $playWeekCount = (int) $matches->max('round_number');
        $leagueStart = $league?->start_date?->copy()->startOfDay();

        $days = [];
        foreach ($sorted->groupBy(function (GroupMatch $m) {
            return $m->round_number !== null
                ? 'week-'.(int) $m->round_number
                : 'date-'.$m->match_date->format('Y-m-d');
        }) as $groupKey => $groupMatches) {
            $first = $groupMatches->first();
            if (! $first instanceof GroupMatch) {
                continue;
            }

            if ($first->round_number !== null) {
                $dateLabel = LeagueWeekCalendar::weekHeading((int) $first->round_number);
            } else {
                $dateLabel = strtoupper($first->match_date->format('M j, Y'));
            }

            $days[] = [
                'dateLabel' => $dateLabel,
                'matches' => $groupMatches
                    ->map(fn (GroupMatch $match) => self::matchRow(
                        $match,
                        $seedMapsByGroup,
                        $showSeedsInMeta,
                        $league,
                        $playWeekCount,
                        $showLeagueInMeta,
                    ))
                    ->values()
                    ->all(),
            ];
        }

        return $days;
    }

    /**
     * @param  Collection<int, PlayoffMatch>  $matches
     * @return list<array{dateLabel: string, matches: list<array<string, mixed>>}>
     */
    public static function playoffGroupIntoDays(Collection $matches): array
    {
        if ($matches->isEmpty()) {
            return [];
        }

        $roundTitles = [
            PlayoffMatch::ROUND_PRE_PRE_Q => 'Pre-Pre-Quarterfinals',
            PlayoffMatch::ROUND_PRE_Q => 'Round of 16',
            PlayoffMatch::ROUND_QF => 'Quarterfinals',
            PlayoffMatch::ROUND_SF => 'Semifinals',
            PlayoffMatch::ROUND_F => 'Final',
        ];

        $roundOrder = [
            PlayoffMatch::ROUND_PRE_PRE_Q => 1,
            PlayoffMatch::ROUND_PRE_Q => 2,
            PlayoffMatch::ROUND_QF => 3,
            PlayoffMatch::ROUND_SF => 4,
            PlayoffMatch::ROUND_F => 5,
        ];

        $sorted = $matches->sortBy(fn (PlayoffMatch $m) => sprintf(
            '%02d-%03d-%s',
            $roundOrder[$m->round] ?? 9,
            (int) $m->slot,
            $m->match_date?->format('Y-m-d') ?? '9999-12-31',
        ));

        $days = [];
        foreach ($sorted->groupBy('round') as $round => $roundMatches) {
            $title = $roundTitles[$round] ?? 'Playoff';
            $days[] = [
                'dateLabel' => strtoupper($title),
                'matches' => $roundMatches
                    ->map(fn (PlayoffMatch $match) => self::playoffMatchRow($match))
                    ->values()
                    ->all(),
            ];
        }

        return $days;
    }

    /**
     * @return array<string, mixed>
     */
    public static function playoffMatchRow(PlayoffMatch $match): array
    {
        $score = trim((string) ($match->score ?? ''));
        $finished = ! $match->isPending();
        $scoreDisplay = $score !== '' ? $score : ($finished ? 'Recorded' : '');
        $won = $finished ? $match->homeSideWon() : null;

        $winnerLabel = null;
        if ($won !== null) {
            $winnerLabel = $won
                ? self::playoffSideName($match, 'home')
                : self::playoffSideName($match, 'away');
        }

        $timeRaw = trim((string) ($match->start_time ?? ''));
        $timeDisplay = $timeRaw !== ''
            ? (MatchStartTime::formatDisplay($timeRaw) ?: $timeRaw)
            : 'TBA';

        $roundMeta = $match->round === PlayoffMatch::ROUND_F
            ? 'Grand Final'
            : $match->roundLabel().((int) $match->slot > 1 ? ' #'.(int) $match->slot : '');

        $leagueName = trim((string) ($match->league?->name ?? ''));
        $divisionName = trim((string) ($match->groupCard?->name ?? ''));
        $metaBase = $leagueName !== ''
            ? ($divisionName !== '' ? $leagueName.' · '.$divisionName : $leagueName)
            : $divisionName;

        $participantIds = array_values(array_filter([
            $match->home_user_id ? (int) $match->home_user_id : null,
            $match->away_user_id ? (int) $match->away_user_id : null,
        ]));

        $matchDate = $match->match_date;
        $venueOnly = trim((string) ($match->venue ?? ''));
        $courtOnly = trim((string) ($match->court ?? ''));
        $venueParts = array_filter([$venueOnly, $courtOnly]);
        $league = $match->league;
        $dateMin = $league?->playoff_start_date?->format('Y-m-d') ?? '';
        $dateMax = $league?->playoff_end_date?->format('Y-m-d') ?? '';
        $dateWindowHint = ($dateMin !== '' && $dateMax !== '')
            ? $league->playoff_start_date->format('M j, Y').' – '.$league->playoff_end_date->format('M j, Y')
            : '';

        return [
            'matchKind' => 'playoff',
            'playoffMatchId' => (int) $match->id,
            'groupMatchId' => 0,
            'leftName' => self::playoffSideName($match, 'home'),
            'leftMeta' => $metaBase !== '' ? $metaBase.' · '.$roundMeta : $roundMeta,
            'rightName' => self::playoffSideName($match, 'away'),
            'rightMeta' => $metaBase !== '' ? $metaBase.' · Playoff' : 'Playoff',
            'participantUserIds' => $participantIds,
            'homeParticipantIds' => $match->home_user_id ? [(int) $match->home_user_id] : [],
            'awayParticipantIds' => $match->away_user_id ? [(int) $match->away_user_id] : [],
            'finished' => $finished,
            'score' => $finished ? $scoreDisplay : null,
            'scoreRaw' => $score,
            'winnerSide' => $match->winner_side,
            'homeSideWon' => $won,
            'winnerLabel' => $winnerLabel,
            'dateShort' => $matchDate ? $matchDate->format('D, M j') : 'Date TBD',
            'dateValue' => $matchDate?->format('Y-m-d') ?? '',
            'time' => $timeDisplay,
            'timeInput' => MatchStartTime::toInputValue($timeRaw),
            'venue' => $venueParts !== [] ? implode(' · ', $venueParts) : 'TBA',
            'venueOnly' => $venueOnly,
            'courtOnly' => $courtOnly,
            'venueInput' => $venueOnly,
            'courtInput' => $courtOnly,
            'dateMin' => $dateMin,
            'dateMax' => $dateMax,
            'dateWindowHint' => $dateWindowHint,
        ];
    }

    public static function playoffSideName(PlayoffMatch $match, string $side): string
    {
        $user = $side === 'home' ? $match->homeUser : $match->awayUser;
        if ($user) {
            return self::playerDisplayName($user);
        }

        return $match->sidePlaceholderLabel($side) ?? 'TBD';
    }

    /**
     * @param  array<int, array<int, int>>  $seedMapsByGroup
     * @return array<string, mixed>
     */
    public static function matchRow(
        GroupMatch $match,
        array $seedMapsByGroup = [],
        bool $showSeedsInMeta = false,
        ?League $league = null,
        int $playWeekCount = 0,
        bool $showLeagueInMeta = false,
    ): array
    {
        $score = trim((string) ($match->score ?? ''));
        $finished = ! $match->isPending();
        $scoreDisplay = $score !== '' ? $score : ($finished ? 'Recorded' : '');
        $venueOnly = trim((string) ($match->venue ?? ''));
        $courtOnly = trim((string) ($match->court ?? ''));
        $venueParts = array_filter([$venueOnly, $courtOnly]);

        $won = $finished ? $match->homeSideWon() : null;
        $winnerLabel = null;
        if ($won !== null) {
            $winnerLabel = $match->format === GroupMatchFormat::Doubles
                ? ($won ? 'Home team' : 'Away team')
                : self::formatSideNames($match, $won ? 'home' : 'away');
        }

        $timeRaw = trim((string) ($match->start_time ?? ''));
        $timeDisplay = $timeRaw !== ''
            ? (MatchStartTime::formatDisplay($timeRaw) ?: $timeRaw)
            : 'TBA';

        $homeParticipantIds = array_values(array_filter([
            (int) $match->home_user_id,
            $match->format === GroupMatchFormat::Doubles && $match->home_partner_user_id
                ? (int) $match->home_partner_user_id
                : null,
        ]));
        $awayParticipantIds = array_values(array_filter([
            (int) $match->away_user_id,
            $match->format === GroupMatchFormat::Doubles && $match->away_partner_user_id
                ? (int) $match->away_partner_user_id
                : null,
        ]));

        return [
            'groupMatchId' => (int) $match->id,
            'leftName' => self::formatSideNames($match, 'home'),
            'leftMeta' => self::formatSideMeta($match, 'home', $seedMapsByGroup, $showSeedsInMeta, $showLeagueInMeta),
            'rightName' => self::formatSideNames($match, 'away'),
            'rightMeta' => self::formatSideMeta($match, 'away', $seedMapsByGroup, $showSeedsInMeta, $showLeagueInMeta),
            'participantUserIds' => self::participantUserIds($match),
            'homeParticipantIds' => $homeParticipantIds,
            'awayParticipantIds' => $awayParticipantIds,
            'finished' => $finished,
            'score' => $finished ? $scoreDisplay : null,
            'scoreRaw' => $score,
            'winnerSide' => $match->winner_side,
            'homeSideWon' => $won,
            'winnerLabel' => $winnerLabel,
            'dateShort' => $match->match_date->format('D, M j'),
            'dateValue' => $match->match_date->format('Y-m-d'),
            'time' => $timeDisplay,
            'timeInput' => MatchStartTime::toInputValue($timeRaw),
            'venue' => $venueParts !== [] ? implode(' · ', $venueParts) : 'TBA',
            'venueOnly' => $venueOnly,
            'courtOnly' => $courtOnly,
            'venueInput' => $venueOnly,
            'courtInput' => $courtOnly,
            'matchKind' => 'group',
            'playoffMatchId' => 0,
            'dateMin' => $league?->start_date?->format('Y-m-d') ?? '',
            'dateMax' => $league?->end_date?->format('Y-m-d') ?? '',
            'dateWindowHint' => ($league?->start_date && $league?->end_date)
                ? $league->start_date->format('M j, Y').' – '.$league->end_date->format('M j, Y')
                : '',
        ];
    }

    /**
     * @param  list<int|string>  $groupIds
     * @return array<int, array<int, int>>
     */
    public static function seedMapsByGroup(League $league, GroupCard $groupCard, array $groupIds): array
    {
        $groupIds = array_values(array_unique(array_map('intval', $groupIds)));
        if ($groupIds === [] || ! Schema::hasTable('league_registrations')) {
            return [];
        }

        $regs = LeagueRegistration::query()
            ->where('league_id', $league->id)
            ->whereIn('group_id', $groupIds)
            ->where(function ($q) use ($groupCard) {
                $q->whereNull('group_card_id')->orWhere('group_card_id', $groupCard->id);
            })
            ->orderBy('id')
            ->get(['group_id', 'user_id']);

        $maps = [];
        foreach ($regs as $reg) {
            $gid = (int) $reg->group_id;
            $uid = (int) $reg->user_id;
            if (! isset($maps[$gid])) {
                $maps[$gid] = [];
            }
            if (! isset($maps[$gid][$uid])) {
                $maps[$gid][$uid] = count($maps[$gid]) + 1;
            }
        }

        return $maps;
    }

    /**
     * @return list<int>
     */
    public static function participantUserIds(GroupMatch $match): array
    {
        $ids = [];
        foreach ([$match->home_user_id, $match->away_user_id] as $id) {
            if ($id !== null && (int) $id > 0) {
                $ids[] = (int) $id;
            }
        }
        if ($match->format === GroupMatchFormat::Doubles) {
            foreach ([$match->home_partner_user_id, $match->away_partner_user_id] as $id) {
                if ($id !== null && (int) $id > 0) {
                    $ids[] = (int) $id;
                }
            }
        }

        return array_values(array_unique($ids));
    }

    public static function formatSideNames(GroupMatch $match, string $side): string
    {
        $isHome = $side === 'home';
        if ($match->format === GroupMatchFormat::Doubles) {
            if ($isHome && $match->homePartnerUser) {
                return self::playerDisplayName($match->homeUser).' & '.self::playerDisplayName($match->homePartnerUser);
            }
            if (! $isHome && $match->awayPartnerUser) {
                return self::playerDisplayName($match->awayUser).' & '.self::playerDisplayName($match->awayPartnerUser);
            }
        }

        $u = $isHome ? $match->homeUser : $match->awayUser;

        return self::playerDisplayName($u);
    }

    /**
     * @param  array<int, array<int, int>>  $seedMapsByGroup
     */
    public static function formatSideMeta(GroupMatch $match, string $side, array $seedMapsByGroup, bool $showSeeds, bool $showLeagueInMeta = false): string
    {
        $groupName = $match->group?->name ?? 'Subgroup';

        if (! $showSeeds) {
            if ($showLeagueInMeta) {
                $leagueName = trim((string) ($match->league?->name ?? ''));

                return $leagueName !== '' ? $leagueName.' · '.$groupName : $groupName;
            }

            return $groupName;
        }

        $gid = (int) $match->group_id;
        $map = $seedMapsByGroup[$gid] ?? [];

        if ($match->format === GroupMatchFormat::Doubles) {
            if ($side === 'home') {
                $s1 = $map[(int) $match->home_user_id] ?? $match->home_seed;
                $s2 = $match->home_partner_user_id ? ($map[(int) $match->home_partner_user_id] ?? null) : null;
                if ($s1 && $s2) {
                    return $groupName.' · Seeds #'.$s1.' & #'.$s2;
                }
                if ($s1) {
                    return $groupName.' · Seed #'.$s1;
                }

                return $groupName;
            }

            $s1 = $map[(int) $match->away_user_id] ?? $match->away_seed;
            $s2 = $match->away_partner_user_id ? ($map[(int) $match->away_partner_user_id] ?? null) : null;
            if ($s1 && $s2) {
                return $groupName.' · Seeds #'.$s1.' & #'.$s2;
            }
            if ($s1) {
                return $groupName.' · Seed #'.$s1;
            }

            return $groupName;
        }

        $s = $side === 'home'
            ? ($map[(int) $match->home_user_id] ?? $match->home_seed)
            : ($map[(int) $match->away_user_id] ?? $match->away_seed);

        return $s ? $groupName.' · Seed #'.$s : $groupName;
    }

    public static function playerDisplayName(?User $user): string
    {
        $rawName = trim((string) ($user?->name ?? ''));
        $displayName = trim(preg_split('/\s*&\s*/', $rawName)[0] ?? $rawName);

        return $displayName !== '' ? $displayName : '—';
    }
}
