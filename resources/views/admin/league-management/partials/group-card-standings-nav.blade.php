{{-- Sub-nav: Standings vs Qualifier (points area). $standingsSection: 'points' | 'qualifier' --}}
@php
    $ageQ = ! empty($ageGroupKey) ? ['age_group_key' => $ageGroupKey] : [];
    $section = $standingsSection ?? 'points';
    $standingsOverallActive = $section === 'points' && ($pointsView ?? 'overall') === 'overall';
    $showQualifierPlayoffs = $showQualifierPlayoffs ?? \App\Support\LeagueSeasonPhase::showQualifierAndPlayoffs($league, $groupCard->id);
@endphp
<div class="admin-group-tabs admin-group-tabs--section" aria-label="Standings section">
    <a class="admin-group-tab {{ $standingsOverallActive ? 'is-active' : '' }}"
       href="{{ route('admin.league-management.points.index', ['league' => $league, 'groupCard' => $groupCard] + $ageQ) }}">
        <span>Standings</span>
    </a>
    @if ($showQualifierPlayoffs)
        <a class="admin-group-tab {{ $section === 'qualifier' ? 'is-active' : '' }}"
           href="{{ route('admin.league-management.qualifier.index', ['league' => $league, 'groupCard' => $groupCard] + $ageQ) }}">
            <span>Qualifier</span>
        </a>
    @endif
</div>
