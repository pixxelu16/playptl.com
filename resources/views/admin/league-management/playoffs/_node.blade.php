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
    $rosterUsers = ($playoffRosterUsers ?? collect())->keyBy('id');
    if ($m->homeUser && ! $rosterUsers->has((int) $m->home_user_id)) {
        $rosterUsers = $rosterUsers->put((int) $m->home_user_id, $m->homeUser);
    }
    if ($m->awayUser && ! $rosterUsers->has((int) $m->away_user_id)) {
        $rosterUsers = $rosterUsers->put((int) $m->away_user_id, $m->awayUser);
    }
    $rosterUsers = $rosterUsers->sortBy(fn ($user) => strtolower((string) $user->name))->values();
    $homeSelectLabel = $homePlaceholder ?: '— Select home —';
    $awaySelectLabel = $awayPlaceholder ?: '— Select away —';
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

    <fieldset class="playoff-match-card__fieldset" @disabled(! $canEditNode)>
        <form method="POST" action="{{ route('admin.league-management.playoffs.update', [$league, $groupCard, $m] + ($ageGroupKey ? ['age_group_key' => $ageGroupKey] : [])) }}">
            @csrf
            @method('PUT')

            <div class="playoff-match-card__versus">
                <div class="playoff-match-card__player">
                    <span class="playoff-match-card__side-label">Home</span>
                    @if ($canEditNode)
                        <select class="admin-input playoff-match-card__player-select" name="home_user_id" aria-label="Home player">
                            <option value="">{{ $homeSelectLabel }}</option>
                            @foreach ($rosterUsers as $user)
                                <option value="{{ $user->id }}" @selected((int) old('home_user_id', $m->home_user_id) === (int) $user->id)>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    @elseif ($m->homeUser)
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
                    @if ($canEditNode)
                        <select class="admin-input playoff-match-card__player-select" name="away_user_id" aria-label="Away player">
                            <option value="">{{ $awaySelectLabel }}</option>
                            @foreach ($rosterUsers as $user)
                                <option value="{{ $user->id }}" @selected((int) old('away_user_id', $m->away_user_id) === (int) $user->id)>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    @elseif ($m->awayUser)
                        <p class="playoff-match-card__name">
                            @include('admin.league-management.matches._player-name', ['user' => $m->awayUser])
                        </p>
                    @else
                        <p class="playoff-match-card__name playoff-match-card__name--tbd">{{ $awayPlaceholder }}</p>
                    @endif
                </div>
            </div>

            @if ($canEditNode)
                <p class="playoff-match-card__lock" style="background:#fff8e1;border-color:#ffe082;color:#5d4037;">
                    Players are filled automatically from Qualifier / <strong>Advance winners</strong>. Use the dropdowns to change home or away if needed — both players will be emailed when you save.
                </p>
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
            <div @if (! $bothPlayersSet) hidden @endif>
                @include('admin.league-management.matches._result-fields', [
                    'prefix' => 'playoff-'.$m->id,
                    'scoreValue' => $m->score,
                    'winnerSide' => $m->winner_side,
                ])
            </div>

            <button class="admin-button playoff-match-card__save" type="submit">
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
