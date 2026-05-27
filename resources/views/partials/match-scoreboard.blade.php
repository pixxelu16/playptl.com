@php
    $scoreRaw = trim((string) ($score ?? ''));
    $walkover = \App\Support\MatchScoreReader::isWalkover($scoreRaw);
    $breakdown = $walkover ? null : \App\Support\MatchScoreReader::breakdown($scoreRaw);
    $resolvedHomeWon = $homeSideWon ?? (isset($match) ? $match->homeSideWon() : null);
    $homeWon = $breakdown !== null
        ? $breakdown['homeWon']
        : ($resolvedHomeWon === true);
    $awayWon = $breakdown !== null
        ? ! $breakdown['homeWon']
        : ($resolvedHomeWon === false);
    $showFinal = $showFinal ?? true;
@endphp

@if ($breakdown)
    <div class="match-scoreboard" role="group" aria-label="Match score">
        <div class="match-scoreboard__layout">
            <div class="match-scoreboard__players">
                <div class="match-scoreboard__player-line {{ $homeWon ? 'is-winner' : 'is-loser' }}">{{ $homeName }}</div>
                <div class="match-scoreboard__player-line {{ $awayWon ? 'is-winner' : 'is-loser' }}">{{ $awayName }}</div>
            </div>
            <div class="match-scoreboard__totals" aria-hidden="true">
                <div class="match-scoreboard__total-line {{ $homeWon ? 'is-winner' : 'is-loser' }}">
                    @if ($homeWon)
                        <span class="match-scoreboard__caret"></span>
                    @endif
                    <span class="match-scoreboard__sets-num">{{ $breakdown['homeSets'] }}</span>
                </div>
                <div class="match-scoreboard__total-line {{ $awayWon ? 'is-winner' : 'is-loser' }}">
                    @if ($awayWon)
                        <span class="match-scoreboard__caret"></span>
                    @endif
                    <span class="match-scoreboard__sets-num">{{ $breakdown['awaySets'] }}</span>
                </div>
            </div>
            <div class="match-scoreboard__set-cols" aria-label="Games per set">
                @foreach ($breakdown['sets'] as $set)
                    @php
                        $homeWonSet = (int) $set['home'] > (int) $set['away'];
                    @endphp
                    <div class="match-scoreboard__set-col">
                        <span class="match-scoreboard__set-game {{ $homeWonSet ? 'is-winner' : 'is-loser' }}">{{ $set['home'] }}</span>
                        <span class="match-scoreboard__set-game {{ $homeWonSet ? 'is-loser' : 'is-winner' }}">{{ $set['away'] }}</span>
                    </div>
                @endforeach
            </div>
            @if ($showFinal)
                <div class="match-scoreboard__final" aria-label="Match complete">Final</div>
            @endif
        </div>
    </div>
@elseif ($walkover)
    <div class="match-scoreboard match-scoreboard--walkover">
        <p class="match-scoreboard__walkover-label">Walkover</p>
        <p class="match-scoreboard__walkover-score">{{ $scoreRaw }}</p>
    </div>
@else
    <div class="match-scoreboard match-scoreboard--raw">
        <span class="match-scoreboard__raw-label">{{ $scoreRaw !== '' ? $scoreRaw : 'Recorded' }}</span>
    </div>
@endif
