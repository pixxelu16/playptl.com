@extends('layouts.admin')

@section('title', 'Points | '.$league->name.' | '.config('app.name', 'playptl'))
@section('meta_description', 'Standings and points for a tournament group.')

@section('content')
    <section class="admin-card">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-card-title">Points — {{ $league->name }}</h1>
                <p class="admin-card-text">
                    Group: <strong>{{ $groupCard->name }}</strong>
                    @if ($ageGroupKey)
                        · Age: <strong>{{ $ageGroupKey }}</strong>
                    @endif
                    @if (($pointsView ?? 'overall') === 'subgroup' && $activeGroup)
                        · Subgroup: <strong>{{ $activeGroup->name }}</strong>
                    @elseif (($pointsView ?? 'overall') === 'overall')
                        · <strong>Overall standings</strong> (all subgroups)
                    @endif
                </p>
            </div>
            @include('admin.league-management.partials.group-card-header-actions', [
                'league' => $league,
                'groupCard' => $groupCard,
                'ageGroupKey' => $ageGroupKey,
                'activeGroupId' => $activeGroupId,
                'playerSchemaReady' => $playerSchemaReady,
                'active' => 'points',
            ])
        </div>

        @if ($playerSchemaReady)
            @include('admin.league-management.partials.group-card-standings-nav', [
                'league' => $league,
                'groupCard' => $groupCard,
                'ageGroupKey' => $ageGroupKey,
                'pointsView' => $pointsView,
                'standingsSection' => 'points',
            ])
        @endif

        @if ($groups->isNotEmpty())
            @include('admin.league-management.partials.group-card-nav-tabs', [
                'league' => $league,
                'groupCard' => $groupCard,
                'ageGroupKey' => $ageGroupKey,
                'groups' => $groups,
                'activeGroupId' => $activeGroupId,
                'pointsView' => $pointsView,
                'active' => 'points',
            ])
        @endif

        @if (! $playerSchemaReady)
            <div class="admin-alert admin-alert-error" style="margin-top: 1rem;">
                Player roster is not ready. Run migrations and assign players to subgroups first.
            </div>
        @elseif ($standingsRows === [])
            <div class="admin-empty-state" style="margin-top: 1.5rem;">
                <i class="fa-solid fa-ranking-star" aria-hidden="true"></i>
                <p>No players or completed matches yet for this filter.</p>
            </div>
        @else
            <p class="admin-card-text" style="margin: 1.25rem 0 0.75rem;">
                @if (($pointsView ?? 'overall') === 'overall')
                    <strong>Overall standings</strong> — all subgroups combined.
                @else
                    <strong>{{ $activeGroup?->name ?? 'Subgroup' }}</strong> standings only.
                @endif
                Sorted by PF, then PA (lower is better), then game win %.
            </p>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            @if (($pointsView ?? 'overall') === 'overall')
                                <th>Subgroup</th>
                            @endif
                            <th>Player</th>
                            <th style="text-align:center;">Matches</th>
                            <th style="text-align:center;">W</th>
                            <th style="text-align:center;">L</th>
                            <th style="text-align:center;" title="Points For">PF</th>
                            <th style="text-align:center;" title="Points Against">PA</th>
                            <th style="text-align:right;">Game%</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($standingsRows as $row)
                            <tr>
                                <td>{{ str_pad((string) $row['rank'], 2, '0', STR_PAD_LEFT) }}</td>
                                @if (($pointsView ?? 'overall') === 'overall')
                                    <td>{{ $row['groupName'] ?? '—' }}</td>
                                @endif
                                <td>
                                    <div style="display:flex;align-items:center;gap:10px;">
                                        <img src="{{ $row['avatarUrl'] }}" alt="" width="40" height="40" style="width:40px;height:40px;border-radius:999px;object-fit:cover;border:1px solid #d7ead9;">
                                        <strong>{{ $row['name'] }}</strong>
                                    </div>
                                </td>
                                <td style="text-align:center;">{{ $row['matches'] }}</td>
                                <td style="text-align:center;">{{ $row['wins'] }}</td>
                                <td style="text-align:center;">{{ $row['losses'] }}</td>
                                <td style="text-align:center;font-weight:700;">{{ $row['points'] }}</td>
                                <td style="text-align:center;">{{ $row['pointsAgainst'] }}</td>
                                <td style="text-align:right;font-weight:700;color:#2f7a2a;">{{ $row['gamePct'] }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
