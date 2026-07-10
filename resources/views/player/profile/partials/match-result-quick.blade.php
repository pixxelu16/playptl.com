@php
    $prefix = $prefix ?? 'pm';
    $scoreValue = $scoreValue ?? '';
    $walkedOffSide = $walkedOffSide ?? \App\Support\MatchResultInput::walkedOffSideFromStored($scoreValue, $winnerSide ?? null);
    $isWalkover = $walkedOffSide !== null || \App\Support\MatchScoreReader::isWalkover((string) $scoreValue);
    $resultType = old('result_type', $isWalkover ? 'walkover' : 'normal');
    $setsForForm = \App\Support\MatchScoreReader::setsForForm((string) $scoreValue);
@endphp
<div class="player-match-result" data-match-result-fields data-prefix="{{ $prefix }}">
    <div class="player-match-result__head">
        <span class="player-match-result__title">Your score</span>
        <fieldset class="player-match-result__type border-0 p-0 m-0">
            <label class="player-match-result__radio">
                <input type="radio" name="result_type" value="normal" data-result-type-radio class="h-3 w-3" @checked($resultType !== 'walkover')>
                <span>Played</span>
            </label>
            <label class="player-match-result__radio">
                <input type="radio" name="result_type" value="walkover" data-result-type-radio class="h-3 w-3" @checked($resultType === 'walkover')>
                <span>Walkover</span>
            </label>
        </fieldset>
    </div>
    <div data-walkover-panel @if ($resultType !== 'walkover') hidden @endif class="player-match-result__walkover">
        <select name="walked_off_side" data-walked-off-select class="player-match-result__select">
            <option value="">Who walked off?</option>
            <option value="home" @selected(old('walked_off_side', $walkedOffSide) === 'home')>Home walked off</option>
            <option value="away" @selected(old('walked_off_side', $walkedOffSide) === 'away')>Away walked off</option>
        </select>
    </div>
    <div data-score-panel @if ($resultType === 'walkover') hidden @endif>
        <div class="match-score-entry match-score-entry--player" data-set-boxes>
            <div class="match-score-entry__labels" aria-hidden="true">
                <span class="match-score-entry__who">Home</span>
                <span class="match-score-entry__who">Away</span>
            </div>
            <div class="match-score-entry__cols">
                @for ($n = 1; $n <= 3; $n++)
                    @php $set = $setsForForm[$n - 1] ?? ['home' => '', 'away' => '']; @endphp
                    <div class="match-score-entry__col">
                        <span class="match-score-entry__col-head">S{{ $n }}</span>
                        <input
                            type="text"
                            name="set_{{ $n }}_home"
                            inputmode="numeric"
                            maxlength="2"
                            value="{{ old('set_'.$n.'_home', $set['home']) }}"
                            data-set-home
                            class="match-score-entry__input"
                            aria-label="Set {{ $n }} home"
                        >
                        <input
                            type="text"
                            name="set_{{ $n }}_away"
                            inputmode="numeric"
                            maxlength="2"
                            value="{{ old('set_'.$n.'_away', $set['away']) }}"
                            data-set-away
                            class="match-score-entry__input"
                            aria-label="Set {{ $n }} away"
                        >
                    </div>
                @endfor
            </div>
        </div>
        <input type="hidden" name="score" value="{{ $isWalkover ? '' : $scoreValue }}" data-score-input>
    </div>
</div>
