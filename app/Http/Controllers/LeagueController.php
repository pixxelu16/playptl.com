<?php

namespace App\Http\Controllers;

use Illuminate\Support\Arr;
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
}
