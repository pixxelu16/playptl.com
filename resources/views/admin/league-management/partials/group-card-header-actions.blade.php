{{-- $active: groups | matches | points | qualifier | playoffs | players --}}
@php
    $ageQ = ! empty($ageGroupKey) ? ['age_group_key' => $ageGroupKey] : [];
    $groupQ = ! empty($activeGroupId) ? ['group' => $activeGroupId] : [];
    $navQ = $ageQ + $groupQ;
    $showQualifierPlayoffs = $showQualifierPlayoffs ?? \App\Support\LeagueSeasonPhase::showQualifierAndPlayoffs($league, $groupCard->id);
@endphp
<div class="admin-header-actions">
    <a class="admin-link" href="{{ route('admin.league-management.show', $league) }}">
        <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
        <span>Back</span>
    </a>
    @if ($active !== 'groups')
        <a class="admin-link" href="{{ route('admin.league-management.groups.index', ['league' => $league, 'groupCard' => $groupCard] + $navQ) }}">
            <i class="fa-solid fa-layer-group" aria-hidden="true"></i>
            <span>Subgroups &amp; players</span>
        </a>
    @endif
    @if (($playerSchemaReady ?? false) && $active !== 'matches')
        <a class="admin-link" href="{{ route('admin.league-management.matches.index', ['league' => $league, 'groupCard' => $groupCard] + $navQ) }}">
            <i class="fa-solid fa-calendar-days" aria-hidden="true"></i>
            <span>Matches</span>
        </a>
    @endif
    @if (($playerSchemaReady ?? false) && $active !== 'points')
        <a class="admin-link" href="{{ route('admin.league-management.points.index', ['league' => $league, 'groupCard' => $groupCard] + $ageQ) }}">
            <i class="fa-solid fa-ranking-star" aria-hidden="true"></i>
            <span>Points</span>
        </a>
    @endif
    @if (($playerSchemaReady ?? false) && $showQualifierPlayoffs && $active !== 'qualifier')
        <a class="admin-link" href="{{ route('admin.league-management.qualifier.index', ['league' => $league, 'groupCard' => $groupCard] + $ageQ) }}">
            <i class="fa-solid fa-filter" aria-hidden="true"></i>
            <span>Qualifier</span>
        </a>
    @endif
    @if (($playerSchemaReady ?? false) && $showQualifierPlayoffs && $active !== 'playoffs')
        <a class="admin-link" href="{{ route('admin.league-management.playoffs.index', ['league' => $league, 'groupCard' => $groupCard] + $navQ) }}">
            <i class="fa-solid fa-trophy" aria-hidden="true"></i>
            <span>Playoffs</span>
        </a>
    @endif
    @if (($playerSchemaReady ?? false) && $active !== 'players')
        <a class="admin-link" href="{{ route('admin.league-management.players.index', ['league' => $league, 'groupCard' => $groupCard] + $ageQ) }}">
            <i class="fa-solid fa-list" aria-hidden="true"></i>
            <span>All players</span>
        </a>
    @endif
</div>
