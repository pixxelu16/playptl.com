@php
    /** @var \App\Models\PlayoffMatch $m */
    $slotLabel = $m->round === \App\Models\PlayoffMatch::ROUND_F
        ? 'Final'
        : $m->roundLabel().' '.$m->slot;
    $canEditNode = in_array($m->round, [\App\Models\PlayoffMatch::ROUND_PRE_PRE_Q, \App\Models\PlayoffMatch::ROUND_PRE_Q, \App\Models\PlayoffMatch::ROUND_QF], true)
        || ($m->round === \App\Models\PlayoffMatch::ROUND_SF && ($qfComplete ?? false))
        || ($m->round === \App\Models\PlayoffMatch::ROUND_F && ($qfComplete ?? false) && ($sfComplete ?? false));
    $isComplete = ! $m->isPending();
    $lockNote = $m->round === \App\Models\PlayoffMatch::ROUND_SF
        ? 'Complete all quarterfinals first.'
        : 'Complete both semifinals first.';
    $homePlaceholder = $m->sidePlaceholderLabel('home');
    $awayPlaceholder = $m->sidePlaceholderLabel('away');
    $bothPlayersSet = $m->home_user_id && $m->away_user_id;
    $playoffDateMin = $league->playoff_start_date?->format('Y-m-d');
    $playoffDateMax = $league->playoff_end_date?->format('Y-m-d');
    $playoffDateTitle = ($playoffDateMin && $playoffDateMax)
        ? 'Between '.$league->playoff_start_date->format('M j, Y').' and '.$league->playoff_end_date->format('M j, Y')
        : 'Set playoff start and end dates on the Playoffs page first';
@endphp
<article class="playoff-match-card {{ $isComplete ? 'is-complete' : '' }} {{ ! $canEditNode ? 'is-locked' : '' }}">
    <div class="playoff-match-card__top">
        <span class="playoff-match-card__label">{{ $slotLabel }}</span>
        @if ($isComplete)
            <span class="playoff-match-card__status">Done</span>
        @elseif (! $canEditNode)
            <span class="playoff-match-card__status playoff-match-card__status--locked">Locked</span>
        @else
            <span class="playoff-match-card__status playoff-match-card__status--pending">Pending</span>
        @endif
    </div>

    @if (! $canEditNode)
        <p class="playoff-match-card__lock">{{ $lockNote }}</p>
    @endif

    <fieldset class="playoff-match-card__fieldset" @disabled(! $canEditNode || ! $bothPlayersSet)>
        <form method="POST" action="{{ route('admin.league-management.playoffs.update', [$league, $groupCard, $m] + ($ageGroupKey ? ['age_group_key' => $ageGroupKey] : [])) }}">
            @csrf
            @method('PUT')

            <div class="playoff-match-card__versus">
                <div class="playoff-match-card__player">
                    <span class="playoff-match-card__side-label">Home</span>
                    @if ($m->homeUser)
                        <p class="playoff-match-card__name">
                            @include('admin.league-management.matches._player-name', ['user' => $m->homeUser])
                        </p>
                    @else
                        <p class="playoff-match-card__name playoff-match-card__name--tbd">{{ $homePlaceholder }}</p>
                    @endif
                </div>
                <span class="playoff-match-card__vs" aria-hidden="true">vs</span>
                <div class="playoff-match-card__player">
                    <span class="playoff-match-card__side-label">Away</span>
                    @if ($m->awayUser)
                        <p class="playoff-match-card__name">
                            @include('admin.league-management.matches._player-name', ['user' => $m->awayUser])
                        </p>
                    @else
                        <p class="playoff-match-card__name playoff-match-card__name--tbd">{{ $awayPlaceholder }}</p>
                    @endif
                </div>
            </div>

            @if (! $bothPlayersSet && $canEditNode)
                <p class="playoff-match-card__lock">Players are set from Qualifier seeding. Use <strong>Advance winners</strong> when earlier rounds finish.</p>
            @endif

            <div class="playoff-match-card__meta">
                <div>
                    <label for="pdt-{{ $m->id }}">Date</label>
                    <input
                        class="admin-input"
                        id="pdt-{{ $m->id }}"
                        type="date"
                        name="match_date"
                        value="{{ old('match_date', $m->match_date?->toDateString()) }}"
                        @if ($playoffDateMin) min="{{ $playoffDateMin }}" @endif
                        @if ($playoffDateMax) max="{{ $playoffDateMax }}" @endif
                        title="{{ $playoffDateTitle }}"
                    >
                </div>
                <div>
                    <label for="ptm-{{ $m->id }}">Time</label>
                    <input class="admin-input" id="ptm-{{ $m->id }}" type="text" name="start_time" value="{{ old('start_time', $m->start_time) }}" placeholder="10:00 AM">
                </div>
                <div>
                    <label for="pvn-{{ $m->id }}">Venue</label>
                    <input class="admin-input" id="pvn-{{ $m->id }}" type="text" name="venue" value="{{ old('venue', $m->venue) }}" placeholder="Club or city">
                </div>
                <div>
                    <label for="pct-{{ $m->id }}">Court</label>
                    <input class="admin-input" id="pct-{{ $m->id }}" type="text" name="court" value="{{ old('court', $m->court) }}" placeholder="Court 1" maxlength="64">
                </div>
            </div>
            @include('admin.league-management.matches._result-fields', [
                'prefix' => 'playoff-'.$m->id,
                'scoreValue' => $m->score,
                'winnerSide' => $m->winner_side,
            ])

            <button class="admin-button playoff-match-card__save" type="submit" @disabled(! $bothPlayersSet)>
                <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>
                <span>Save match</span>
            </button>
        </form>
    </fieldset>

    @if ($isComplete)
        @php $hw = $m->homeSideWon(); @endphp
        @if ($hw !== null)
            <p class="playoff-match-card__winner">
                Winner:
                <strong>
                    @if ($hw && $m->homeUser)
                        @include('admin.league-management.matches._player-name', ['user' => $m->homeUser])
                    @elseif (! $hw && $m->awayUser)
                        @include('admin.league-management.matches._player-name', ['user' => $m->awayUser])
                    @endif
                </strong>
            </p>
        @endif
    @endif
</article>
