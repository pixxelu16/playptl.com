@php
    /** @var \Illuminate\Support\Collection<int, \App\Models\PlayoffMatch> $matches */
    $matchCount = $matches->count();
@endphp
<section class="playoff-round" id="playoff-round-{{ $roundId }}" aria-labelledby="playoff-round-title-{{ $roundId }}">
    <header class="playoff-round__header">
        <div class="playoff-round__heading">
            <span class="playoff-round__step">{{ $step }}</span>
            <h2 class="playoff-round__title" id="playoff-round-title-{{ $roundId }}">{{ $title }}</h2>
            <span class="playoff-round__count">{{ $matchCount }} {{ $matchCount === 1 ? 'match' : 'matches' }}</span>
        </div>
        @if (! empty($hint))
            <p class="playoff-round__hint">{{ $hint }}</p>
        @endif
    </header>
    <div class="playoff-round__grid">
        @foreach ($matches as $pm)
            @include('admin.league-management.playoffs._node', [
                'm' => $pm,
                'league' => $league,
                'groupCard' => $groupCard,
                'ageGroupKey' => $ageGroupKey,
                'qfComplete' => $qfComplete ?? false,
                'sfComplete' => $sfComplete ?? false,
            ])
        @endforeach
    </div>
</section>
