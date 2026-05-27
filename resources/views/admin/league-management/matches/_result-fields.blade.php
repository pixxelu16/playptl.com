@php
    $prefix = $prefix ?? 'match';
    $scoreValue = $scoreValue ?? '';
    $walkedOffSide = $walkedOffSide ?? \App\Support\MatchResultInput::walkedOffSideFromStored($scoreValue, $winnerSide ?? null);
    $isWalkover = $walkedOffSide !== null || \App\Support\MatchScoreReader::isWalkover((string) $scoreValue);
    $resultType = old('result_type', $isWalkover ? 'walkover' : 'normal');
    $setsForForm = \App\Support\MatchScoreReader::setsForForm((string) $scoreValue);
@endphp
<div class="match-result-fields" data-match-result-fields data-prefix="{{ $prefix }}">
    <fieldset class="match-result-fields__type">
        <legend class="match-result-fields__legend">Result</legend>
        <label class="match-result-fields__radio">
            <input type="radio" name="result_type" value="normal" data-result-type-radio @checked($resultType !== 'walkover')>
            <span>Played (score)</span>
        </label>
        <label class="match-result-fields__radio">
            <input type="radio" name="result_type" value="walkover" data-result-type-radio @checked($resultType === 'walkover')>
            <span>Walkover</span>
        </label>
    </fieldset>
    <div class="match-result-fields__walkover" data-walkover-panel @if ($resultType !== 'walkover') hidden @endif>
        <label for="{{ $prefix }}-walked-off">Who walked off?</label>
        <select class="admin-input" id="{{ $prefix }}-walked-off" name="walked_off_side" data-walked-off-select>
            <option value="">— Select —</option>
            <option value="home" @selected(old('walked_off_side', $walkedOffSide) === 'home')>Home player walked off</option>
            <option value="away" @selected(old('walked_off_side', $walkedOffSide) === 'away')>Away player walked off</option>
        </select>
        <p class="match-result-fields__note">Winner <strong>10</strong> pts · walk-off <strong>0</strong>.</p>
    </div>
    <div class="match-result-fields__score-wrap" data-score-panel @if ($resultType === 'walkover') hidden @endif>
        <span class="match-result-fields__score-label">{{ $scoreLabel ?? 'Score' }}</span>
        <div class="match-score-entry" data-set-boxes>
            <div class="match-score-entry__labels" aria-hidden="true">
                <span class="match-score-entry__who">Home</span>
                <span class="match-score-entry__who">Away</span>
            </div>
            <div class="match-score-entry__cols">
                @for ($n = 1; $n <= 3; $n++)
                    @php $set = $setsForForm[$n - 1] ?? ['home' => '', 'away' => '']; @endphp
                    <div class="match-score-entry__col">
                        <span class="match-score-entry__col-head">Set {{ $n }}</span>
                        <input
                            class="admin-input match-score-entry__input"
                            type="text"
                            id="{{ $prefix }}-set-{{ $n }}-home"
                            name="set_{{ $n }}_home"
                            inputmode="numeric"
                            pattern="[0-9]*"
                            maxlength="2"
                            autocomplete="off"
                            value="{{ old('set_'.$n.'_home', $set['home']) }}"
                            data-set-home
                            aria-label="Set {{ $n }} home games"
                        >
                        <input
                            class="admin-input match-score-entry__input"
                            type="text"
                            id="{{ $prefix }}-set-{{ $n }}-away"
                            name="set_{{ $n }}_away"
                            inputmode="numeric"
                            pattern="[0-9]*"
                            maxlength="2"
                            autocomplete="off"
                            value="{{ old('set_'.$n.'_away', $set['away']) }}"
                            data-set-away
                            aria-label="Set {{ $n }} away games"
                        >
                    </div>
                @endfor
            </div>
        </div>
        <input type="hidden" name="score" value="{{ $isWalkover ? '' : $scoreValue }}" data-score-input>
    </div>
</div>
