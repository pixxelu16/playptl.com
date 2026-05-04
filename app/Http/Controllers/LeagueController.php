<?php

namespace App\Http\Controllers;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LeagueController extends Controller
{
    public function index(): View
    {
        return view('league', [
            'breadcrumbCurrent' => 'PTL SPRING 2026',
            'heroTitleLight' => 'PTL SPRING',
            'heroTitleAccent' => '2026',
            'statDivisions' => 8,
            'statSeasonLabel' => 'Season:',
            'statSeasonRange' => 'May – Aug 2026',
            'statPlayers' => 105,
            'groupsHeadingDark' => 'PTL SPRING 2026',
            'groupsHeadingGreen' => 'GROUPS',
            'groupCards' => [
                ['slug' => 'voyagers-singles', 'tag' => 'SINGLES', 'title' => 'Voyagers Singles', 'meta' => '15 Players - 3 Groups'],
                ['slug' => 'voyagers-double', 'tag' => 'DOUBLES', 'title' => 'Voyagers Double', 'meta' => '15 Players - 3 Groups'],
                ['slug' => 'challengers-singles', 'tag' => 'SINGLES', 'title' => 'Challengers Singles', 'meta' => '15 Players - 3 Groups'],
                ['slug' => 'challengers-doubles', 'tag' => 'DOUBLES', 'title' => 'Challengers Doubles', 'meta' => '15 Players - 3 Groups'],
                ['slug' => 'warriors-singles', 'tag' => 'SINGLES', 'title' => 'Warriors Singles', 'meta' => '15 Players - 3 Groups'],
                ['slug' => 'warriors-doubles', 'tag' => 'DOUBLES', 'title' => 'Warriors Doubles', 'meta' => '15 Players - 3 Groups'],
                ['slug' => 'mixed-doubles', 'tag' => 'MIXED', 'title' => 'Mixed Doubles', 'meta' => '10 Players - 2 Groups'],
                ['slug' => 'youth-singles', 'tag' => 'YOUTH', 'title' => 'Youth Singles', 'meta' => '20 Players - 4 Groups'],
            ],
        ]);
    }

    public function show(string $slug): View
    {
        $detail = Arr::get($this->groupDetailMap(), $slug);

        if ($detail === null) {
            abort(404);
        }

        $detail['playerProfiles'] = $this->buildPlayerProfiles(
            $detail['breadcrumbGroup'],
            (int) $detail['statPlayers'],
            (int) $detail['statGroups'],
        );

        return view('league-detail', $detail);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    protected function groupDetailMap(): array
    {
        return [
            'voyagers-singles' => $this->detailPayload(
                slug: 'voyagers-singles',
                pageTitle: 'Voyagers Singles | PTL Spring 2026',
                breadcrumbGroup: 'VOYAGERS SINGLES',
                heroTitleLight: 'VOYAGERS',
                heroTitleAccent: 'SINGLES',
                statPlayers: 15,
                statGroups: 3,
            ),
            'voyagers-double' => $this->detailPayload(
                slug: 'voyagers-double',
                pageTitle: 'Voyagers Double | PTL Spring 2026',
                breadcrumbGroup: 'VOYAGERS DOUBLE',
                heroTitleLight: 'VOYAGERS',
                heroTitleAccent: 'DOUBLE',
                statPlayers: 15,
                statGroups: 3,
            ),
            'challengers-singles' => $this->detailPayload(
                slug: 'challengers-singles',
                pageTitle: 'Challengers Singles | PTL Spring 2026',
                breadcrumbGroup: 'CHALLENGERS SINGLES',
                heroTitleLight: 'CHALLENGERS',
                heroTitleAccent: 'SINGLES',
                statPlayers: 15,
                statGroups: 3,
            ),
            'challengers-doubles' => $this->detailPayload(
                slug: 'challengers-doubles',
                pageTitle: 'Challengers Doubles | PTL Spring 2026',
                breadcrumbGroup: 'CHALLENGERS DOUBLES',
                heroTitleLight: 'CHALLENGERS',
                heroTitleAccent: 'DOUBLES',
                statPlayers: 15,
                statGroups: 3,
            ),
            'warriors-singles' => $this->detailPayload(
                slug: 'warriors-singles',
                pageTitle: 'Warriors Singles | PTL Spring 2026',
                breadcrumbGroup: 'WARRIORS SINGLES',
                heroTitleLight: 'WARRIORS',
                heroTitleAccent: 'SINGLES',
                statPlayers: 15,
                statGroups: 3,
            ),
            'warriors-doubles' => $this->detailPayload(
                slug: 'warriors-doubles',
                pageTitle: 'Warriors Doubles | PTL Spring 2026',
                breadcrumbGroup: 'WARRIORS DOUBLES',
                heroTitleLight: 'WARRIORS',
                heroTitleAccent: 'DOUBLES',
                statPlayers: 15,
                statGroups: 3,
            ),
            'mixed-doubles' => $this->detailPayload(
                slug: 'mixed-doubles',
                pageTitle: 'Mixed Doubles | PTL Spring 2026',
                breadcrumbGroup: 'MIXED DOUBLES',
                heroTitleLight: 'MIXED',
                heroTitleAccent: 'DOUBLES',
                statPlayers: 10,
                statGroups: 2,
            ),
            'youth-singles' => $this->detailPayload(
                slug: 'youth-singles',
                pageTitle: 'Youth Singles | PTL Spring 2026',
                breadcrumbGroup: 'YOUTH SINGLES',
                heroTitleLight: 'YOUTH',
                heroTitleAccent: 'SINGLES',
                statPlayers: 20,
                statGroups: 4,
            ),
        ];
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
     * @return array<string, string>
     */
    protected function sampleMyProfile(string $breadcrumbGroup): array
    {
        $divisionLabel = Str::title(Str::lower($breadcrumbGroup));

        return [
            'name' => 'Arjun Kumar',
            'roleLine' => 'Player - Group A',
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
    protected function sampleStandingsTable(): array
    {
        $rows = [];
        foreach ($this->samplePlayerGroups() as $group) {
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
                    'index' => str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT),
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
     * Full profile payload for client-side player dashboard (same page, no URL change).
     *
     * @return array<string, array<string, mixed>>
     */
    protected function buildPlayerProfiles(string $breadcrumbGroup, int $statPlayers, int $statGroups): array
    {
        $division = Str::title(Str::lower($breadcrumbGroup));
        $metaContext = $statPlayers.' Players - '.$statGroups.' Groups';
        $groups = $this->samplePlayerGroups();
        $profiles = [];
        $idCounter = 1;

        foreach ($groups as $group) {
            $names = array_map(fn (array $p) => $p['name'], $group['players']);
            foreach ($group['players'] as $pi => $player) {
                $key = Str::slug($player['name']);
                $h = crc32($key);
                $matches = 3 + ($h % 3);
                $wins = max(0, min($matches, (int) floor($matches * (0.55 + (($h % 9) * 0.04)))));
                $losses = max(0, $matches - $wins);
                $points = 48 + ($h % 40);

                $opponents = [];
                foreach ($names as $n) {
                    if ($n !== $player['name']) {
                        $opponents[] = $n;
                    }
                }
                $recent = [];
                for ($r = 0; $r < 3; $r++) {
                    $opp = $opponents[$r % max(1, count($opponents))] ?? 'Opponent';
                    $recent[] = [
                        'opponent' => $opp,
                        'context' => $metaContext,
                        'score' => ['6-3, 7-5', '7-6, 6-4', '6-2, 6-3'][$r % 3],
                        'result' => ['Win', 'Win', 'Loss'][$r % 3],
                    ];
                }

                $first = Str::before($player['name'], ' ');
                $email = Str::lower(preg_replace('/[^a-z]/i', '', $first) ?: 'player').'@example.com';

                $profiles[$key] = [
                    'key' => $key,
                    'name' => $player['name'],
                    'subtitle' => 'Player - '.$group['label'],
                    'avatarUrl' => 'https://ui-avatars.com/api/?name='.rawurlencode($player['name']).'&size=128&background=e1f0e1&color=2d4a2d&bold=true',
                    'playerId' => '#PTL-'.str_pad((string) $idCounter++, 3, '0', STR_PAD_LEFT),
                    'division' => $division,
                    'group' => $group['label'],
                    'seed' => $player['index'],
                    'status' => 'Active',
                    'joined' => 'Mar 2026',
                    'matches' => $matches,
                    'wins' => $wins,
                    'losses' => $losses,
                    'points' => $points,
                    'winRate' => min(95, 52 + ($h % 35)),
                    'gamePct' => min(92, 58 + ($h % 28)),
                    'setPct' => min(90, 55 + ($h % 30)),
                    'recentMatches' => $recent,
                    'fullName' => $player['name'],
                    'phone' => '+91 98765 '.str_pad((string) (43210 + ($h % 900)), 5, '0', STR_PAD_LEFT),
                    'email' => $email,
                    'location' => 'Chandigarh, India',
                    'dob' => ['14 Aug 1995', '22 Nov 1992', '03 Jun 1998'][($h >> 3) % 3],
                    'ntrp' => number_format(4.0 + ($h % 15) / 10, 1),
                ];
            }
        }

        return $profiles;
    }
}
