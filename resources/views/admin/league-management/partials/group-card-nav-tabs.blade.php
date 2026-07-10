{{-- $active: 'matches' | 'points' --}}
@php
    $ageQ = ! empty($ageGroupKey) ? ['age_group_key' => $ageGroupKey] : [];
    $tabRoute = ($active ?? 'matches') === 'points'
        ? 'admin.league-management.points.index'
        : 'admin.league-management.matches.index';
    $isPoints = ($active ?? 'matches') === 'points';
    $subgroupTabActive = $isPoints && ($pointsView ?? '') === 'subgroup';
@endphp
<div class="admin-group-tabs" aria-label="Subgroup navigation">
    @foreach ($groups as $g)
        @php
            $tabActive = $isPoints
                ? ($subgroupTabActive && (int) $g->id === (int) $activeGroupId)
                : ((int) $g->id === (int) $activeGroupId);
        @endphp
        <a class="admin-group-tab {{ $tabActive ? 'is-active' : '' }}"
           href="{{ route($tabRoute, ['league' => $league, 'groupCard' => $groupCard, 'group' => $g->id] + $ageQ) }}">
            <span>{{ $g->name }}</span>
        </a>
    @endforeach
</div>
