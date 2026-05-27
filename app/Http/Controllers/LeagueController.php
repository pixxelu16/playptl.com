<?php

namespace App\Http\Controllers;

use App\Enums\GroupMatchFormat;
use App\Models\Group;
use App\Models\GroupCard;
use App\Models\GroupMatch;
use App\Models\League;
use App\Models\LeagueRegistration;
use App\Models\PlayoffMatch;
use App\Models\User;
use App\Support\LeaguePointSystem;
use App\Support\LeagueRegistrationRoster;
use App\Support\MatchSchedulePresenter;
use App\Support\MatchScoreReader;
use App\Support\MatchStartTime;
use App\Support\PlayoffBracketPresenter;
use App\Support\PlayerMatchWorkflow;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LeagueController extends Controller
{
    public function index(): View
    {
        return view('league', $this->leagueOverviewPayload(null));
    }

    public function overview(string $slug): View
    {
        $league = League::query()
            ->with(['groupCards' => fn ($q) => $q->orderBy('display_order')->orderBy('name')])
            ->where('slug', $slug)
            ->where('stats', 'active')
            ->firstOrFail();

        return view('league', $this->leagueOverviewPayload($league));
    }

    public function show(string $leagueSlug, string $groupCardSlug): View
    {
        $league = League::query()
            ->with(['groupCards' => fn ($q) => $q->where('status', 'active')])
            ->where('slug', $leagueSlug)
            ->where('stats', 'active')
            ->firstOrFail();

        $groupCard = $league->groupCards->firstWhere('slug', $groupCardSlug);
        if (! $groupCard instanceof GroupCard) {
            abort(404);
        }

        $groupNameUpper = Str::upper($groupCard->name);
        $parts = preg_split('/\s+/', $groupNameUpper) ?: [];
        $heroAccent = array_pop($parts) ?? '';
        $heroLight = implode(' ', $parts);
        if ($heroLight === '') {
            $heroLight = $groupNameUpper;
            $heroAccent = '';
        }

        $seasonRange = 'May – Aug 2026';
        if ($league->start_date || $league->end_date) {
            $start = $league->start_date?->format('M Y');
            $end = $league->end_date?->format('M Y');
            $seasonRange = trim(collect([$start, $end])->filter()->join(' – '));
            $seasonRange = $seasonRange !== '' ? $seasonRange : 'TBA';
        }

        $playerGroups = $this->assignedPlayerGroups($league, $groupCard);
        $assignedPlayerCount = collect($playerGroups)->sum('playerCount');
        $assignedGroupCount = count($playerGroups);

        $detail = $this->detailPayload(
            slug: $groupCardSlug,
            pageTitle: $groupCard->name.' | '.$league->name,
            breadcrumbGroup: $groupNameUpper,
            heroTitleLight: $heroLight,
            heroTitleAccent: $heroAccent,
            statPlayers: (int) $assignedPlayerCount,
            statGroups: (int) $assignedGroupCount,
        );
        $detail['leagueSlug'] = $leagueSlug;
        $detail['leagueId'] = $league->id;
        $detail['groupCardId'] = $groupCard->id;
        $detail['breadcrumbLeagueLabel'] = Str::upper($league->name);
        $detail['statSeasonRange'] = $seasonRange;
        $detail['playerGroups'] = $playerGroups;
        $detail['standingsRows'] = $this->standingsRowsForGroupCard($league, $groupCard, $playerGroups);

        $detail['playerProfiles'] = $this->buildPlayerProfiles(
            $detail['breadcrumbGroup'],
            (int) $detail['statPlayers'],
            (int) $detail['statGroups'],
            $playerGroups,
            $detail['standingsRows'],
            $league,
            $groupCard,
        );

        $detail['scheduleDays'] = $this->scheduleDaysForGroupCard($league, $groupCard);
        $detail = array_merge($detail, PlayoffBracketPresenter::publicViewData($league, $groupCard));

        return view('league-detail', $detail);
    }

    /**
     * @return array<string, mixed>
     */
    protected function detailPayload(
        string $slug,
        string $pageTitle,
        string $breadcrumbGroup,
        string $heroTitleLight,
        string $heroTitleAccent,
        int $statPlayers,
        int $statGroups,
    ): array {
        return [
            'slug' => $slug,
            'pageTitle' => $pageTitle,
            'breadcrumbLeagueLabel' => 'PTL SPRING 2026',
            'breadcrumbGroup' => $breadcrumbGroup,
            'metaDescription' => $breadcrumbGroup.' — PTL Spring 2026 group on Premier Tennis League.',
            'heroTitleLight' => $heroTitleLight,
            'heroTitleAccent' => $heroTitleAccent,
            'statPlayers' => $statPlayers,
            'statGroups' => $statGroups,
            'statSeasonLabel' => 'Season:',
            'statSeasonRange' => 'May – Aug 2026',
            'playerGroups' => $this->samplePlayerGroups(),
            'scheduleDays' => $this->sampleScheduleDays(),
            'standingsRows' => $this->sampleStandingsTable(),
            'playoffColumns' => $this->samplePlayoffBracket(),
        ];
    }

    /**
     * Public playoff bracket from admin (empty until bracket exists in DB).
     *
     * @return list<array{title: string, matches: list<array<string, mixed>>, champion?: bool}>
     */
    protected function playoffColumnsForGroupCard(League $league, GroupCard $groupCard, ?string $ageGroupKey = null): array
    {
        if (! Schema::hasTable('playoff_matches')) {
            return [];
        }

        $ageKeyDb = $ageGroupKey ?? '';

        $matches = PlayoffMatch::query()
            ->where('league_id', $league->id)
            ->where('group_card_id', $groupCard->id)
            ->where('age_group_key', $ageKeyDb)
            ->with(['homeUser', 'awayUser'])
            ->orderBy('round')
            ->orderBy('slot')
            ->get();

        if ($matches->isEmpty()) {
            return [];
        }

        $byRound = $matches->groupBy('round');

        $roundConfig = [
            PlayoffMatch::ROUND_PRE_PRE_Q => ['title' => 'PRE-PRE-QUARTERFINALS', 'champion' => false],
            PlayoffMatch::ROUND_PRE_Q => ['title' => 'ROUND OF 16', 'champion' => false],
            PlayoffMatch::ROUND_QF => ['title' => 'QUARTERFINALS', 'champion' => false],
            PlayoffMatch::ROUND_SF => ['title' => 'SEMIFINALS', 'champion' => false],
            PlayoffMatch::ROUND_F => ['title' => 'FINAL', 'champion' => true],
        ];

        $columns = [];
        foreach ($roundConfig as $round => $cfg) {
            $roundMatches = $byRound->get($round, collect());
            if ($roundMatches->isEmpty()) {
                continue;
            }

            $columns[] = [
                'title' => $cfg['title'],
                'matches' => $roundMatches
                    ->map(fn (PlayoffMatch $m) => $this->playoffMatchToPublicCard($m))
                    ->values()
                    ->all(),
                'champion' => $cfg['champion'],
            ];
        }

        return $columns;
    }

    /**
     * @return array{label: string, status: string, p1: array{code: string, name: string}, p2: array{code: string, name: string}}
     */
    protected function playoffMatchToPublicCard(PlayoffMatch $match): array
    {
        $label = $match->round === PlayoffMatch::ROUND_F
            ? 'Grand Final'
            : $match->roundLabel().' '.$match->slot;

        return [
            'label' => $label,
            'status' => $this->playoffMatchStatusLabel($match),
            'p1' => $this->playoffPlayerSide($match, 'home'),
            'p2' => $this->playoffPlayerSide($match, 'away'),
        ];
    }

    /**
     * @return array{code: string, name: string}
     */
    protected function playoffPlayerSide(PlayoffMatch $match, string $side): array
    {
        $user = $side === 'home' ? $match->homeUser : $match->awayUser;
        if ($user) {
            return [
                'code' => str_pad((string) $match->slot, 2, '0', STR_PAD_LEFT).($side === 'home' ? 'H' : 'A'),
                'name' => MatchSchedulePresenter::playerDisplayName($user),
            ];
        }

        $placeholder = $match->sidePlaceholderLabel($side) ?? 'TBD';

        return [
            'code' => '—',
            'name' => $placeholder,
        ];
    }

    protected function playoffMatchStatusLabel(PlayoffMatch $match): string
    {
        if (! $match->isPending()) {
            $score = trim((string) ($match->score ?? ''));

            return $score !== '' ? $score : 'Complete';
        }

        if ($match->home_user_id && $match->away_user_id) {
            return 'Scheduled';
        }

        return 'TBD';
    }

    /**
     * Demo playoff bracket — fallback when DB not wired.
     *
     * @return list<array<string, mixed>>
     */
    protected function samplePlayoffBracket(): array
    {
        return [
            [
                'title' => 'QUARTERFINALS',
                'matches' => [
                    [
                        'label' => 'QF 1',
                        'status' => 'Scheduled',
                        'p1' => ['code' => '01', 'name' => 'Arjun Kumar'],
                        'p2' => ['code' => '02', 'name' => 'Rahul Singh'],
                    ],
                    [
                        'label' => 'QF 2',
                        'status' => 'Scheduled',
                        'p1' => ['code' => '03', 'name' => 'Vikram Mehta'],
                        'p2' => ['code' => '04', 'name' => 'Karan Joshi'],
                    ],
                    [
                        'label' => 'QF 3',
                        'status' => 'Scheduled',
                        'p1' => ['code' => '01', 'name' => 'Aniket Rao'],
                        'p2' => ['code' => '02', 'name' => 'Suresh Nair'],
                    ],
                    [
                        'label' => 'QF 4',
                        'status' => 'Scheduled',
                        'p1' => ['code' => '03', 'name' => 'Manish Kapoor'],
                        'p2' => ['code' => '04', 'name' => 'Rohit Verma'],
                    ],
                ],
            ],
            [
                'title' => 'SEMIFINALS',
                'matches' => [
                    [
                        'label' => 'SF 1',
                        'status' => 'TBD',
                        'p1' => ['code' => 'W1', 'name' => 'Winner QF 1'],
                        'p2' => ['code' => 'W2', 'name' => 'Winner QF 2'],
                    ],
                    [
                        'label' => 'SF 2',
                        'status' => 'TBD',
                        'p1' => ['code' => 'W3', 'name' => 'Winner QF 3'],
                        'p2' => ['code' => 'W4', 'name' => 'Winner QF 4'],
                    ],
                ],
            ],
            [
                'title' => 'FINAL',
                'matches' => [
                    [
                        'label' => 'Grand Final',
                        'status' => 'TBD',
                        'p1' => ['code' => 'W1', 'name' => 'Winner SF 1'],
                        'p2' => ['code' => 'W2', 'name' => 'Winner SF 2'],
                    ],
                ],
                'champion' => true,
            ],
        ];
    }

    /**
     * Demo standings (all roster players) — replace with DB later.
     *
     * @return list<array{rank: int, userId: int, name: string, matches: int, wins: int, losses: int, points: int, pointsAgainst: int, gamePct: int}>
     */
    protected function sampleStandingsTable(?array $playerGroups = null): array
    {
        $rows = [];
        foreach (($playerGroups ?? $this->samplePlayerGroups()) as $group) {
            foreach ($group['players'] as $player) {
                $name = $player['name'];
                $h = crc32(Str::slug($name));
                $matches = 4 + ($h % 4);
                $wins = max(0, min($matches, (int) floor($matches * (0.45 + ($h % 10) * 0.04))));
                $losses = max(0, $matches - $wins);
                $pointsFor = 28 + ($h % 40);
                $pointsAgainst = 18 + (($h >> 4) % 25);
                $gamePct = 52 + ($h % 45);
                $rows[] = [
                    'userId' => (int) ($player['userId'] ?? 0),
                    'name' => $name,
                    'avatarUrl' => $player['avatarUrl'] ?? 'https://ui-avatars.com/api/?name='.rawurlencode($name).'&size=64&background=E8F5E9&color=2E7D32&bold=true',
                    'matches' => $matches,
                    'wins' => $wins,
                    'losses' => $losses,
                    'pointsFor' => $pointsFor,
                    'pointsAgainst' => $pointsAgainst,
                    'points' => $pointsFor,
                    'gamePct' => $gamePct,
                    'gamesWon' => 40 + ($h % 30),
                ];
            }
        }

        LeaguePointSystem::sortStandingsRows($rows);

        $out = [];
        foreach ($rows as $i => $r) {
            $out[] = array_merge($r, [
                'rank' => $i + 1,
            ]);
        }

        return $out;
    }

    /**
     * Demo schedule rows for league detail Schedules tab.
     *
     * @return list<array{dateLabel: string, matches: list<array<string, mixed>>}>
     */
    protected function sampleScheduleDays(): array
    {
        return [
            [
                'dateLabel' => 'MAY 10, 2026',
                'matches' => [
                    [
                        'leftName' => 'Arjun Kumar',
                        'leftMeta' => 'Group A • Seed #1',
                        'rightName' => 'Rahul Singh',
                        'rightMeta' => 'Group A • Seed #2',
                        'finished' => true,
                        'score' => '6-3, 7-5',
                        'dateShort' => 'Sat, May 10',
                        'time' => '10:00 AM',
                        'venue' => 'Highland Country Club · Court 3',
                    ],
                    [
                        'leftName' => 'Vikram Mehta',
                        'leftMeta' => 'Group A • Seed #3',
                        'rightName' => 'Karan Joshi',
                        'rightMeta' => 'Group A • Seed #4',
                        'finished' => false,
                        'score' => null,
                        'dateShort' => 'Sat, May 10',
                        'time' => '11:30 AM',
                        'venue' => 'Highland Country Club · Court 1',
                    ],
                    [
                        'leftName' => 'Aniket Rao',
                        'leftMeta' => 'Group B • Seed #1',
                        'rightName' => 'Suresh Nair',
                        'rightMeta' => 'Group B • Seed #2',
                        'finished' => true,
                        'score' => '7-6, 6-2',
                        'dateShort' => 'Sat, May 10',
                        'time' => '2:00 PM',
                        'venue' => 'Highland Country Club · Court 4',
                    ],
                    [
                        'leftName' => 'Neeraj Gill',
                        'leftMeta' => 'Group C • Seed #2',
                        'rightName' => 'Puneet Arora',
                        'rightMeta' => 'Group C • Seed #3',
                        'finished' => false,
                        'score' => null,
                        'dateShort' => 'Sat, May 10',
                        'time' => '4:00 PM',
                        'venue' => 'Highland Country Club · Court 2',
                    ],
                ],
            ],
            [
                'dateLabel' => 'MAY 17, 2026',
                'matches' => [
                    [
                        'leftName' => 'Dev Patel',
                        'leftMeta' => 'Group A • Seed #5',
                        'rightName' => 'Manish Kapoor',
                        'rightMeta' => 'Group B • Seed #3',
                        'finished' => true,
                        'score' => '6-4, 6-4',
                        'dateShort' => 'Sat, May 17',
                        'time' => '9:00 AM',
                        'venue' => 'Highland Country Club · Court 3',
                    ],
                    [
                        'leftName' => 'Rohit Verma',
                        'leftMeta' => 'Group B • Seed #4',
                        'rightName' => 'Varun Saxena',
                        'rightMeta' => 'Group C • Seed #4',
                        'finished' => false,
                        'score' => null,
                        'dateShort' => 'Sat, May 17',
                        'time' => '12:00 PM',
                        'venue' => 'Highland Country Club · Court 2',
                    ],
                ],
            ],
        ];
    }

    /**
     * Demo roster for league detail — replace with DB later.
     *
     * @return list<array{label: string, playerCount: int, players: list<array{index: string, name: string}>}>
     */
    protected function samplePlayerGroups(): array
    {
        $groupA = ['Arjun Kumar', 'Rahul Singh', 'Vikram Mehta', 'Karan Joshi', 'Dev Patel'];
        $groupB = ['Aniket Rao', 'Suresh Nair', 'Manish Kapoor', 'Rohit Verma', 'Amit Shah'];
        $groupC = ['Neeraj Gill', 'Puneet Arora', 'Varun Saxena', 'Harsh Malhotra', 'Sid Gupta'];

        $rows = function (array $names): array {
            $out = [];
            foreach ($names as $i => $name) {
                $out[] = [
                    'index' => (string) ($i + 1),
                    'name' => $name,
                    'key' => Str::slug($name),
                ];
            }

            return $out;
        };

        return [
            ['label' => 'Group A', 'playerCount' => count($groupA), 'players' => $rows($groupA)],
            ['label' => 'Group B', 'playerCount' => count($groupB), 'players' => $rows($groupB)],
            ['label' => 'Group C', 'playerCount' => count($groupC), 'players' => $rows($groupC)],
        ];
    }

    /**
     * Build website roster cards from admin-assigned players for this league and group card.
     *
     * @return list<array{label: string, playerCount: int, players: list<array<string, mixed>>}>
     */
    protected function assignedPlayerGroups(League $league, GroupCard $groupCard): array
    {
        if (! Schema::hasTable('groups')
            || ! Schema::hasTable('league_registrations')
            || ! Schema::hasColumn('league_registrations', 'group_id')
            || ! Schema::hasColumn('league_registrations', 'group_card_id')
        ) {
            return [];
        }

        $groups = Group::query()
            ->whereHas('groupCards', fn ($q) => $q->whereKey($groupCard->id))
            ->when(
                Schema::hasColumn('groups', 'status'),
                fn ($q) => $q->where('status', 'active')
            )
            ->with([
                'leagueRegistrations' => fn ($q) => $q
                    ->where('league_id', $league->id)
                    ->where(function ($qq) use ($groupCard) {
                        $qq->whereNull('group_card_id')->orWhere('group_card_id', $groupCard->id);
                    })
                    ->with('user')
                    ->oldest('id'),
            ])
            ->orderBy('name')
            ->get();

        return $groups->map(function (Group $group) use ($groupCard): array {
            $rosterEntries = LeagueRegistrationRoster::collapseForDisplay(
                $group->leagueRegistrations->filter(fn ($registration) => $registration->user !== null)
            );

            $players = $rosterEntries
                ->values()
                ->map(function (array $entry, int $index) use ($group, $groupCard): array {
                    $registration = $entry['registration'];
                    $user = $entry['user'];
                    $name = $entry['display_name'] !== '—' ? $entry['display_name'] : 'Player';
                    $city = trim((string) ($user->city ?? ''));
                    $state = trim((string) ($user->state ?? ''));
                    $location = trim(collect([$city, $state])->filter()->join(', '));

                    return [
                        'index' => (string) ($index + 1),
                        'name' => $name,
                        'key' => 'registration-'.$registration->id,
                        'userId' => (int) $user->id,
                        'avatarUrl' => asset($user->avatar_path ?: 'upload/user-avatar/default-user-pic.png'),
                        'email' => $entry['display_subtitle'] !== '' ? $entry['display_subtitle'] : (string) ($user->email ?? ''),
                        'phone' => (string) ($user->phone ?? ''),
                        'location' => $location !== '' ? $location : '—',
                        'skillLevel' => (string) ($registration->skill_level ?? '—'),
                        'group' => $group->name,
                        'division' => $groupCard->name,
                        'playerId' => '#PTL-'.str_pad((string) $user->id, 3, '0', STR_PAD_LEFT),
                        'joined' => optional($registration->created_at)->format('M Y') ?: '—',
                        'status' => ucfirst((string) ($user->status ?? 'Active')),
                    ];
                })
                ->all();

            return [
                'label' => $group->name,
                'playerCount' => count($players),
                'players' => $players,
            ];
        })->values()->all();
    }

    /**
     * Standings for the public league detail tab — roster for this group card + stats from completed matches.
     *
     * @param  list<array{label: string, playerCount: int, players: list<array<string, mixed>>}>  $playerGroups
     * @return list<array{rank: int, userId: int, name: string, avatarUrl: string, matches: int, wins: int, losses: int, points: int, pointsAgainst: int, gamePct: int}>
     */
    protected function standingsRowsForGroupCard(League $league, GroupCard $groupCard, array $playerGroups): array
    {
        if (! Schema::hasTable('group_matches')) {
            return $this->sampleStandingsTable($playerGroups !== [] ? $playerGroups : null);
        }

        /** @var array<int, array{name: string, avatarUrl: string, matches: int, wins: int, losses: int, pointsFor: int, pointsAgainst: int, gamesWon: int, gamesLost: int}> $byUser */
        $byUser = [];

        foreach ($playerGroups as $group) {
            foreach ($group['players'] as $player) {
                $uid = (int) ($player['userId'] ?? 0);
                if ($uid <= 0) {
                    continue;
                }
                $byUser[$uid] = [
                    'name' => (string) ($player['name'] ?? 'Player'),
                    'avatarUrl' => (string) ($player['avatarUrl'] ?? ''),
                    'matches' => 0,
                    'wins' => 0,
                    'losses' => 0,
                    'pointsFor' => 0,
                    'pointsAgainst' => 0,
                    'gamesWon' => 0,
                    'gamesLost' => 0,
                ];
            }
        }

        $matches = GroupMatch::query()
            ->where('league_id', $league->id)
            ->where('group_card_id', $groupCard->id)
            ->with(['homeUser', 'awayUser', 'homePartnerUser', 'awayPartnerUser'])
            ->orderBy('match_date')
            ->orderBy('id')
            ->get();

        foreach ($matches as $match) {
            $this->applyMatchToStandingsAggregate($match, $byUser);
        }

        if ($byUser === []) {
            return $this->sampleStandingsTable($playerGroups !== [] ? $playerGroups : null);
        }

        $rows = [];
        foreach ($byUser as $uid => $s) {
            $gw = $s['gamesWon'];
            $gl = $s['gamesLost'];
            $playedGames = $gw + $gl;
            $gamePct = $playedGames > 0 ? (int) round(100 * $gw / $playedGames) : 0;

            $rows[] = [
                'userId' => (int) $uid,
                'name' => $s['name'],
                'avatarUrl' => $s['avatarUrl'] !== '' ? $s['avatarUrl'] : 'https://ui-avatars.com/api/?name='.rawurlencode($s['name']).'&size=64&background=E8F5E9&color=2E7D32&bold=true',
                'matches' => $s['matches'],
                'wins' => $s['wins'],
                'losses' => $s['losses'],
                'pointsFor' => $s['pointsFor'],
                'pointsAgainst' => $s['pointsAgainst'],
                'points' => $s['pointsFor'],
                'gamePct' => $gamePct,
                'gamesWon' => $gw,
            ];
        }

        LeaguePointSystem::sortStandingsRows($rows);

        $out = [];
        foreach ($rows as $i => $r) {
            $out[] = array_merge($r, ['rank' => $i + 1]);
        }

        return $out;
    }

    /**
     * @param  array<int, array{name: string, avatarUrl: string, matches: int, wins: int, losses: int, pointsFor: int, pointsAgainst: int, gamesWon: int, gamesLost: int}>  $byUser
     */
    protected function applyMatchToStandingsAggregate(GroupMatch $match, array &$byUser): void
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

        $homeIds = array_values(array_filter([(int) $match->home_user_id, (int) ($match->home_partner_user_id ?? 0)]));
        $awayIds = array_values(array_filter([(int) $match->away_user_id, (int) ($match->away_partner_user_id ?? 0)]));

        if ($match->format !== GroupMatchFormat::Doubles) {
            $homeIds = [(int) $match->home_user_id];
            $awayIds = [(int) $match->away_user_id];
        }

        foreach ($homeIds as $hid) {
            if ($hid <= 0) {
                continue;
            }
            $homePlayer = $hid === (int) $match->home_user_id ? $match->homeUser : $match->homePartnerUser;
            $this->ensureStandingsPlayerSlot($hid, $homePlayer, $byUser);
            $byUser[$hid]['matches']++;
            if ($homeWonMatch) {
                $byUser[$hid]['wins']++;
            } else {
                $byUser[$hid]['losses']++;
            }
            $byUser[$hid]['pointsFor'] += $sidePoints['home'];
            $byUser[$hid]['pointsAgainst'] += $sidePoints['away'];
            $byUser[$hid]['gamesWon'] += $homeGw;
            $byUser[$hid]['gamesLost'] += $awayGw;
        }

        foreach ($awayIds as $aid) {
            if ($aid <= 0) {
                continue;
            }
            $awayPlayer = $aid === (int) $match->away_user_id ? $match->awayUser : $match->awayPartnerUser;
            $this->ensureStandingsPlayerSlot($aid, $awayPlayer, $byUser);
            $byUser[$aid]['matches']++;
            if (! $homeWonMatch) {
                $byUser[$aid]['wins']++;
            } else {
                $byUser[$aid]['losses']++;
            }
            $byUser[$aid]['pointsFor'] += $sidePoints['away'];
            $byUser[$aid]['pointsAgainst'] += $sidePoints['home'];
            $byUser[$aid]['gamesWon'] += $awayGw;
            $byUser[$aid]['gamesLost'] += $homeGw;
        }
    }

    /**
     * @param  array<int, array{name: string, avatarUrl: string, matches: int, wins: int, losses: int, pointsFor: int, pointsAgainst: int, gamesWon: int, gamesLost: int}>  $byUser
     */
    protected function ensureStandingsPlayerSlot(int $userId, ?User $user, array &$byUser): void
    {
        if (isset($byUser[$userId])) {
            return;
        }

        $name = $this->matchPlayerDisplayName($user);
        $avatar = '';
        if ($user !== null) {
            $path = $user->avatar_path ?? null;
            $avatar = $path ? asset($path) : '';
        }

        $byUser[$userId] = [
            'name' => $name,
            'avatarUrl' => $avatar,
            'matches' => 0,
            'wins' => 0,
            'losses' => 0,
            'pointsFor' => 0,
            'pointsAgainst' => 0,
            'gamesWon' => 0,
            'gamesLost' => 0,
        ];
    }

    /**
     * Schedule rows for the public league detail "Schedules" tab — all matches for this division.
     *
     * @return list<array{dateLabel: string, matches: list<array<string, mixed>>}>
     */
    protected function scheduleDaysForGroupCard(League $league, GroupCard $groupCard): array
    {
        if (! Schema::hasTable('group_matches')) {
            return [];
        }

        $matches = GroupMatch::query()
            ->where('league_id', $league->id)
            ->where('group_card_id', $groupCard->id)
            ->with(['group', 'homeUser', 'awayUser', 'homePartnerUser', 'awayPartnerUser'])
            ->orderBy('match_date')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $days = MatchSchedulePresenter::groupIntoDays($matches, $league, $groupCard, showSeedsInMeta: false);

        return PlayerMatchWorkflow::maskScheduleScoresUntilMatchUpload($days);
    }

    /**
     * @param  list<int|string>  $groupIds
     * @return array<int, array<int, int>> group_id => [ user_id => 1-based seed ]
     */
    protected function rosterSeedMapsByGroup(League $league, GroupCard $groupCard, array $groupIds): array
    {
        $groupIds = array_values(array_unique(array_map('intval', $groupIds)));
        if ($groupIds === []) {
            return [];
        }

        $regs = LeagueRegistration::query()
            ->where('league_id', $league->id)
            ->whereIn('group_id', $groupIds)
            ->where(function ($q) use ($groupCard) {
                $q->whereNull('group_card_id')->orWhere('group_card_id', $groupCard->id);
            })
            ->whereNotNull('user_id')
            ->orderBy('group_id')
            ->orderBy('id')
            ->get(['id', 'group_id', 'user_id']);

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

    protected function matchPlayerDisplayName(?User $user): string
    {
        $rawName = trim((string) ($user?->name ?? ''));
        $displayName = trim(preg_split('/\s*&\s*/', $rawName)[0] ?? $rawName);

        return $displayName !== '' ? $displayName : '—';
    }

    /**
     * User IDs on home/away for schedule "participant actions" visibility (singles + doubles partners).
     *
     * @return list<int>
     */
    protected function scheduleParticipantUserIds(GroupMatch $match): array
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

    protected function formatScheduleSideNames(GroupMatch $match, string $side): string
    {
        $isHome = $side === 'home';
        if ($match->format === GroupMatchFormat::Doubles) {
            if ($isHome && $match->homePartnerUser) {
                return $this->matchPlayerDisplayName($match->homeUser).' & '.$this->matchPlayerDisplayName($match->homePartnerUser);
            }
            if (! $isHome && $match->awayPartnerUser) {
                return $this->matchPlayerDisplayName($match->awayUser).' & '.$this->matchPlayerDisplayName($match->awayPartnerUser);
            }
        }

        $u = $isHome ? $match->homeUser : $match->awayUser;

        return $this->matchPlayerDisplayName($u);
    }

    /**
     * @param  array<int, array<int, int>>  $seedMapsByGroup
     */
    protected function formatScheduleSideMeta(GroupMatch $match, string $side, array $seedMapsByGroup): string
    {
        $groupName = $match->group?->name ?? 'Group';
        $gid = (int) $match->group_id;
        $map = $seedMapsByGroup[$gid] ?? [];

        if ($match->format === GroupMatchFormat::Doubles) {
            if ($side === 'home') {
                $s1 = $map[(int) $match->home_user_id] ?? $match->home_seed;
                $s2 = $match->home_partner_user_id ? ($map[(int) $match->home_partner_user_id] ?? null) : null;
                if ($s1 && $s2) {
                    return $groupName.' • Seeds #'.$s1.' & #'.$s2;
                }
                if ($s1) {
                    return $groupName.' • Seed #'.$s1;
                }

                return $groupName;
            }

            $s1 = $map[(int) $match->away_user_id] ?? $match->away_seed;
            $s2 = $match->away_partner_user_id ? ($map[(int) $match->away_partner_user_id] ?? null) : null;
            if ($s1 && $s2) {
                return $groupName.' • Seeds #'.$s1.' & #'.$s2;
            }
            if ($s1) {
                return $groupName.' • Seed #'.$s1;
            }

            return $groupName;
        }

        if ($side === 'home') {
            $s = $map[(int) $match->home_user_id] ?? $match->home_seed;
        } else {
            $s = $map[(int) $match->away_user_id] ?? $match->away_seed;
        }

        if ($s) {
            return $groupName.' • Seed #'.$s;
        }

        return $groupName;
    }

    /**
     * Full profile payload for client-side player dashboard (same page, no URL change).
     *
     * @param  list<array{label: string, playerCount: int, players: list<array<string, mixed>>}>|null  $playerGroups
     * @param  list<array<string, mixed>>  $standingsRows
     * @return array<string, array<string, mixed>>
     */
    protected function buildPlayerProfiles(
        string $breadcrumbGroup,
        int $statPlayers,
        int $statGroups,
        ?array $playerGroups = null,
        array $standingsRows = [],
        ?League $league = null,
        ?GroupCard $groupCard = null,
    ): array {
        $division = Str::title(Str::lower($breadcrumbGroup));
        $metaContext = $statPlayers.' Players - '.$statGroups.' Subgroups';
        $groups = $playerGroups ?? $this->samplePlayerGroups();
        $statsByUserId = [];
        foreach ($standingsRows as $row) {
            $uid = (int) ($row['userId'] ?? 0);
            if ($uid > 0) {
                $statsByUserId[$uid] = $row;
            }
        }

        $completedMatches = ($league && $groupCard)
            ? $this->completedGroupMatches($league, $groupCard)
            : collect();

        $profiles = [];
        $idCounter = 1;

        foreach ($groups as $group) {
            foreach ($group['players'] as $player) {
                $key = $player['key'] ?? Str::slug($player['name']);
                $uid = (int) ($player['userId'] ?? 0);
                $standing = $uid > 0 ? ($statsByUserId[$uid] ?? null) : null;
                $matches = (int) ($standing['matches'] ?? 0);
                $wins = (int) ($standing['wins'] ?? 0);
                $losses = (int) ($standing['losses'] ?? 0);
                $points = (int) ($standing['points'] ?? 0);
                $gamePct = (int) ($standing['gamePct'] ?? 0);
                $setPct = $uid > 0
                    ? $this->setWinPercentForUser($uid, $completedMatches)
                    : 0;
                $recent = $uid > 0
                    ? $this->recentMatchesForUser($uid, $completedMatches, $metaContext)
                    : [];

                $first = Str::before($player['name'], ' ');
                $fallbackEmail = Str::lower(preg_replace('/[^a-z]/i', '', $first) ?: 'player').'@example.com';

                $profiles[$key] = [
                    'key' => $key,
                    'name' => $player['name'],
                    'subtitle' => 'Player - '.$group['label'],
                    'avatarUrl' => $player['avatarUrl'] ?? 'https://ui-avatars.com/api/?name='.rawurlencode($player['name']).'&size=128&background=e1f0e1&color=2d4a2d&bold=true',
                    'playerId' => $player['playerId'] ?? '#PTL-'.str_pad((string) $idCounter++, 3, '0', STR_PAD_LEFT),
                    'division' => $player['division'] ?? $division,
                    'group' => $group['label'],
                    'seed' => $player['index'],
                    'status' => $player['status'] ?? 'Active',
                    'joined' => $player['joined'] ?? 'Mar 2026',
                    'matches' => $matches,
                    'wins' => $wins,
                    'losses' => $losses,
                    'points' => $points,
                    'pointsAgainst' => (int) ($standing['pointsAgainst'] ?? 0),
                    'winRate' => $matches > 0 ? (int) round(100 * $wins / $matches) : 0,
                    'gamePct' => $gamePct,
                    'setPct' => $setPct,
                    'recentMatches' => $recent,
                    'fullName' => $player['name'],
                    'phone' => $player['phone'] ?? '—',
                    'email' => $player['email'] ?? $fallbackEmail,
                    'location' => $player['location'] ?? '—',
                    'dob' => $player['dob'] ?? '—',
                    'ntrp' => $player['skillLevel'] ?? '—',
                ];
            }
        }

        return $profiles;
    }

    /**
     * @return \Illuminate\Support\Collection<int, GroupMatch>
     */
    protected function completedGroupMatches(League $league, GroupCard $groupCard): \Illuminate\Support\Collection
    {
        if (! Schema::hasTable('group_matches')) {
            return collect();
        }

        return GroupMatch::query()
            ->where('league_id', $league->id)
            ->where('group_card_id', $groupCard->id)
            ->with(['homeUser', 'awayUser', 'homePartnerUser', 'awayPartnerUser', 'group'])
            ->orderByDesc('match_date')
            ->orderByDesc('id')
            ->get()
            ->filter(fn (GroupMatch $match) => ! $match->isPending())
            ->values();
    }

    /**
     * @param  \Illuminate\Support\Collection<int, GroupMatch>  $matches
     * @return list<array{opponent: string, context: string, score: string, result: string}>
     */
    protected function recentMatchesForUser(int $userId, \Illuminate\Support\Collection $matches, string $metaContext): array
    {
        $recent = [];

        foreach ($matches as $match) {
            if (! $this->userParticipatesInMatch($match, $userId)) {
                continue;
            }

            $homeWon = $match->homeSideWon();
            if ($homeWon === null) {
                continue;
            }

            $onHome = $this->userOnHomeSide($match, $userId);
            $userWon = $onHome ? $homeWon : ! $homeWon;
            $opponent = $this->opponentNameForUser($match, $userId);
            $score = trim((string) ($match->score ?? ''));

            $recent[] = [
                'opponent' => $opponent,
                'context' => $metaContext,
                'score' => $score !== '' ? $score : 'Recorded',
                'result' => $userWon ? 'Win' : 'Loss',
            ];

            if (count($recent) >= 5) {
                break;
            }
        }

        return $recent;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, GroupMatch>  $matches
     */
    protected function setWinPercentForUser(int $userId, \Illuminate\Support\Collection $matches): int
    {
        $setsWon = 0;
        $setsLost = 0;

        foreach ($matches as $match) {
            if (! $this->userParticipatesInMatch($match, $userId)) {
                continue;
            }

            $score = trim((string) ($match->score ?? ''));
            $totals = $score !== '' ? MatchScoreReader::totals($score) : null;
            if ($totals === null || $totals['homeSets'] === $totals['awaySets']) {
                continue;
            }

            if ($this->userOnHomeSide($match, $userId)) {
                $setsWon += $totals['homeSets'];
                $setsLost += $totals['awaySets'];
            } else {
                $setsWon += $totals['awaySets'];
                $setsLost += $totals['homeSets'];
            }
        }

        $played = $setsWon + $setsLost;

        return $played > 0 ? (int) round(100 * $setsWon / $played) : 0;
    }

    protected function userParticipatesInMatch(GroupMatch $match, int $userId): bool
    {
        if ($userId <= 0) {
            return false;
        }

        $ids = [(int) $match->home_user_id, (int) $match->away_user_id];
        if ($match->format === GroupMatchFormat::Doubles) {
            if ($match->home_partner_user_id) {
                $ids[] = (int) $match->home_partner_user_id;
            }
            if ($match->away_partner_user_id) {
                $ids[] = (int) $match->away_partner_user_id;
            }
        }

        return in_array($userId, $ids, true);
    }

    protected function userOnHomeSide(GroupMatch $match, int $userId): bool
    {
        if ((int) $match->home_user_id === $userId) {
            return true;
        }

        return $match->format === GroupMatchFormat::Doubles
            && (int) ($match->home_partner_user_id ?? 0) === $userId;
    }

    protected function opponentNameForUser(GroupMatch $match, int $userId): string
    {
        if ($this->userOnHomeSide($match, $userId)) {
            return MatchSchedulePresenter::formatSideNames($match, 'away');
        }

        return MatchSchedulePresenter::formatSideNames($match, 'home');
    }

    /**
     * @return array<string, mixed>
     */
    protected function leagueOverviewPayload(?League $league): array
    {
        $leagueName = $league?->name ?? 'PTL SPRING 2026';
        $leagueTitle = Str::upper(trim($leagueName));
        $segments = preg_split('/\s+/', $leagueTitle) ?: [];
        $heroAccent = array_pop($segments) ?? '';
        $heroLight = implode(' ', $segments);

        if ($heroLight === '') {
            $heroLight = $leagueTitle;
            $heroAccent = '';
        }

        $activeGroupCards = $league?->groupCards->where('status', 'active')->values() ?? collect();
        $statDivisions = $activeGroupCards->count();
        $statPlayers = (int) $activeGroupCards->sum('players_count');
        $seasonRange = 'May – Aug 2026';
        if ($league?->start_date || $league?->end_date) {
            $start = $league?->start_date?->format('M Y');
            $end = $league?->end_date?->format('M Y');
            $seasonRange = trim(collect([$start, $end])->filter()->join(' – '));
            $seasonRange = $seasonRange !== '' ? $seasonRange : 'TBA';
        }

        return [
            'currentLeagueSlug' => $league?->slug,
            'pageTitle' => $leagueName.' | Premier Tennis League',
            'pageMetaDescription' => ($league?->description && trim($league->description) !== '')
                ? trim($league->description)
                : $leagueName.' season overview on Premier Tennis League.',
            'breadcrumbCurrent' => $leagueTitle,
            'heroTitleLight' => $heroLight,
            'heroTitleAccent' => $heroAccent,
            'statDivisions' => $statDivisions,
            'statSeasonLabel' => 'Season:',
            'statSeasonRange' => $seasonRange,
            'statPlayers' => $statPlayers,
            'groupsHeadingGreen' => 'GROUPS',
            'groupCards' => $activeGroupCards->map(fn (GroupCard $card): array => [
                'slug' => $card->slug ?: Str::slug($card->name),
                'tag' => strtoupper($card->tag),
                'title' => $card->name,
                'meta' => $card->players_count.' Players - '.$card->groups_count.' Groups',
            ])->values()->all(),
        ];
    }
}
