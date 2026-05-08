<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\GroupCard;
use App\Models\Group;
use App\Models\League;
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
        $detail['standingsRows'] = $this->sampleStandingsTable($playerGroups);

        $detail['playerProfiles'] = $this->buildPlayerProfiles(
            $detail['breadcrumbGroup'],
            (int) $detail['statPlayers'],
            (int) $detail['statGroups'],
            $playerGroups,
        );
        $detail['myProfile'] = $this->playerMyProfile($league, $groupCard, $detail['breadcrumbGroup']);

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
            'myProfile' => $this->sampleMyProfile($breadcrumbGroup),
        ];
    }

    /**
     * Demo “My Profile” form payload — replace with authenticated user later.
     *
     * @return array<string, mixed>
     */
    protected function sampleMyProfile(string $breadcrumbGroup): array
    {
        $divisionLabel = Str::title(Str::lower($breadcrumbGroup));

        return [
            'name' => 'Arjun Kumar',
            'roleLine' => 'Player · Group A',
            'avatarUrl' => 'https://ui-avatars.com/api/?name='.rawurlencode('Arjun Kumar').'&size=256&background=E8F5E9&color=2E7D32&bold=true',
            'firstName' => 'Arjun',
            'lastName' => 'Kumar',
            'dob' => '1995-08-14',
            'ntrp' => '4.0',
            'email' => 'arjun.kumar@gmail.com',
            'phone' => '+91 98765 43210',
            'city' => 'Chandigarh',
            'division' => $divisionLabel,
            'group' => 'Group A',
            'homeCourt' => 'Highland Country Club · Court 3',
            'dominantHand' => 'Right',
            /** Demo “Players Schedule” / Add Location — replace with persisted data later. */
            'scheduleMatchOptions' => [
                'Arjun Kumar Vs Rahul Singh',
                'Arjun Kumar Vs Vikram Mehta',
                'Rahul Singh Vs Karan Joshi',
            ],
            'scheduleMatch' => 'Arjun Kumar Vs Rahul Singh',
            'scheduleDate' => '2026-05-10',
            'scheduleTime' => '10:00',
            'scheduleVenue' => 'Highland Country Club · Court 3',
            /**
             * Upload Match Images grid (3×4): same 6 thumbnails as the design, Row3–4 repeat Row1–2.
             * Filenames with spaces are URL-encoded so images load reliably on all hosts.
             *
             * @see public/frontend/images/
             */
            'uploadMatchGallery' => $this->uploadMatchGalleryUrls(),
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function playerMyProfile(League $league, GroupCard $groupCard, string $breadcrumbGroup): array
    {
        $user = auth()->user();
        if (! $user || $user->role !== UserRole::Player) {
            return $this->sampleMyProfile($breadcrumbGroup);
        }

        $registration = $user->leagueRegistrations()
            ->where('league_id', $league->id)
            ->where(function ($q) use ($groupCard) {
                $q->whereNull('group_card_id')->orWhere('group_card_id', $groupCard->id);
            })
            ->with('group')
            ->latest('id')
            ->first();

        $firstName = trim((string) ($user->first_name ?? ''));
        $lastName = trim((string) ($user->last_name ?? ''));
        if ($firstName === '' && $lastName === '') {
            $parts = preg_split('/\s+/', trim((string) $user->name), 2) ?: [];
            $firstName = $parts[0] ?? '';
            $lastName = $parts[1] ?? '';
        }

        $groupName = (string) ($registration?->group?->name ?? '—');
        $divisionLabel = Str::title(Str::lower($breadcrumbGroup));

        return [
            'name' => trim((string) $user->name) !== '' ? (string) $user->name : trim($firstName.' '.$lastName),
            'roleLine' => 'Player - '.$groupName,
            'avatarUrl' => asset($user->avatar_path ?: 'upload/user-avatar/default-user-pic.png'),
            'firstName' => $firstName,
            'lastName' => $lastName,
            'dob' => $user->date_of_birth?->format('Y-m-d') ?? '',
            'ntrp' => (string) ($registration?->skill_level ?? ''),
            'email' => (string) $user->email,
            'phone' => (string) ($user->phone ?? ''),
            'city' => (string) ($user->city ?? ''),
            'division' => $divisionLabel,
            'group' => $groupName,
            'homeCourt' => (string) ($user->home_court ?? ''),
            'dominantHand' => (string) ($user->dominant_hand ?? 'Right'),
        ];
    }

    /**
     * Demo playoff bracket — replace with DB later.
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
     * @return list<array{rank: int, name: string, matches: int, wins: int, losses: int, points: int, gamePct: int}>
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
                $points = 36 + ($h % 45);
                $gamePct = 52 + ($h % 45);
                $rows[] = [
                    'name' => $name,
                    'avatarUrl' => $player['avatarUrl'] ?? 'https://ui-avatars.com/api/?name='.rawurlencode($name).'&size=64&background=E8F5E9&color=2E7D32&bold=true',
                    'matches' => $matches,
                    'wins' => $wins,
                    'losses' => $losses,
                    'points' => $points,
                    'gamePct' => $gamePct,
                ];
            }
        }

        usort($rows, function (array $a, array $b) {
            if ($a['points'] !== $b['points']) {
                return $b['points'] <=> $a['points'];
            }
            if ($a['gamePct'] !== $b['gamePct']) {
                return $b['gamePct'] <=> $a['gamePct'];
            }

            return strcmp($a['name'], $b['name']);
        });

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
            $players = $group->leagueRegistrations
                ->filter(fn ($registration) => $registration->user !== null)
                ->values()
                ->map(function ($registration, int $index) use ($group, $groupCard): array {
                    $user = $registration->user;
                    $name = trim((string) ($user->name ?? ''));
                    $name = $name !== '' ? $name : 'Player';
                    $city = trim((string) ($user->city ?? ''));
                    $state = trim((string) ($user->state ?? ''));
                    $location = trim(collect([$city, $state])->filter()->join(', '));

                    return [
                        'index' => (string) ($index + 1),
                        'name' => $name,
                        'key' => 'registration-'.$registration->id,
                        'avatarUrl' => asset($user->avatar_path ?: 'upload/user-avatar/default-user-pic.png'),
                        'email' => (string) ($user->email ?? ''),
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
     * Full profile payload for client-side player dashboard (same page, no URL change).
     *
     * @return array<string, array<string, mixed>>
     */
    protected function buildPlayerProfiles(string $breadcrumbGroup, int $statPlayers, int $statGroups, ?array $playerGroups = null): array
    {
        $division = Str::title(Str::lower($breadcrumbGroup));
        $metaContext = $statPlayers.' Players - '.$statGroups.' Groups';
        $groups = $playerGroups ?? $this->samplePlayerGroups();
        $profiles = [];
        $idCounter = 1;

        foreach ($groups as $group) {
            $names = array_map(fn (array $p) => $p['name'], $group['players']);
            foreach ($group['players'] as $pi => $player) {
                $key = $player['key'] ?? Str::slug($player['name']);
                $h = crc32($key);
                $matches = 5;
                $wins = 3;
                $losses = 2;
                $points = 73;

                $opponents = [];
                foreach ($names as $n) {
                    if ($n !== $player['name']) {
                        $opponents[] = $n;
                    }
                }
                $recent = [];
                $staticResults = [
                    ['score' => '6-3, 7-5', 'result' => 'Win'],
                    ['score' => '7-6, 6-4', 'result' => 'Win'],
                    ['score' => '6-2, 6-3', 'result' => 'Loss'],
                ];
                for ($r = 0; $r < 3; $r++) {
                    $opp = $opponents[$r % max(1, count($opponents))] ?? 'Opponent';
                    $recent[] = [
                        'opponent' => $opp,
                        'context' => $metaContext,
                        'score' => $staticResults[$r]['score'],
                        'result' => $staticResults[$r]['result'],
                    ];
                }

                $first = Str::before($player['name'], ' ');
                $email = Str::lower(preg_replace('/[^a-z]/i', '', $first) ?: 'player').'@example.com';

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
                    'winRate' => 82,
                    'gamePct' => 67,
                    'setPct' => 60,
                    'recentMatches' => $recent,
                    'fullName' => $player['name'],
                    'phone' => $player['phone'] ?? '+91 98765 '.str_pad((string) (43210 + ($h % 900)), 5, '0', STR_PAD_LEFT),
                    'email' => $player['email'] ?? $email,
                    'location' => $player['location'] ?? 'Chandigarh, India',
                    'dob' => $player['dob'] ?? '—',
                    'ntrp' => $player['skillLevel'] ?? '—',
                ];
            }
        }

        return $profiles;
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
