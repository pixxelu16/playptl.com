@php
    $scheduleDays = $scheduleDays ?? [];
    $highlightUserId = isset($highlightUserId) ? (int) $highlightUserId : null;
    $locationEditBaseUrl = $locationEditBaseUrl ?? null;
    $useVenueModal = (bool) ($useVenueModal ?? false);
    $compactCards = (bool) ($compactCards ?? false);
    $weeksInColumns = (bool) ($weeksInColumns ?? false);
    $showPlayerMatchActions = (bool) ($showPlayerMatchActions ?? false);
@endphp

@if ($scheduleDays === [])
    <p class="rounded-[10px] bg-[#F9FAFB] px-4 py-8 text-center text-[14px] font-medium text-[#757575] ring-1 ring-[#E8E8E8] sm:text-[15px]">
        {{ $emptyMessage ?? 'No matches are scheduled for you in this league yet.' }}
    </p>
@else
    @once
        @push('styles')
            @include('partials.match-scoreboard-styles')
        @endpush
    @endonce
    @if ($weeksInColumns)
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
    @endif
    @foreach ($scheduleDays as $day)
        @php
            $weekMatchCount = count($day['matches'] ?? []);
            $weekMatchesGrid = $weekMatchCount > 1 ? 'grid-cols-2' : 'grid-cols-1';
        @endphp
        @if ($weeksInColumns)
            <div class="flex min-w-0 flex-col">
                <h4 class="mb-2 text-[11px] font-bold uppercase tracking-[0.06em] text-[#424242] sm:text-[12px]">{{ $day['dateLabel'] }}</h4>
                <div class="grid {{ $weekMatchesGrid }} gap-3">
        @else
            <h4 class="mb-2 mt-5 text-[12px] font-bold uppercase tracking-[0.06em] text-[#424242] first:mt-0 sm:text-[13px]">{{ $day['dateLabel'] }}</h4>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-3">
        @endif
            @foreach ($day['matches'] as $match)
                @php
                    $isMine = $highlightUserId !== null
                        && in_array($highlightUserId, array_map('intval', $match['participantUserIds'] ?? []), true);
                    $onHome = $highlightUserId !== null
                        && in_array($highlightUserId, array_map('intval', $match['homeParticipantIds'] ?? []), true);
                    $viewerWon = null;
                    $hideScoreForPlayer = $showPlayerMatchActions && $isMine && ! ($match['hasPlayerUpload'] ?? false);
                    if ($isMine && ($match['finished'] ?? false) && ($match['homeSideWon'] ?? null) !== null && ! $hideScoreForPlayer) {
                        $viewerWon = $onHome ? (bool) $match['homeSideWon'] : ! (bool) $match['homeSideWon'];
                    }
                    $scoreDisplay = $hideScoreForPlayer ? null : ($match['score'] ?? null);
                    $cardPad = $compactCards ? 'p-3' : 'p-4';
                    $nameCls = $compactCards ? 'text-[13px]' : 'text-[15px]';
                    $metaCls = $compactCards ? 'text-[11px]' : 'text-[12px]';
                    $matchKind = ($match['matchKind'] ?? '') === 'playoff' ? 'playoff' : 'group';
                    $playerMatchId = $matchKind === 'playoff'
                        ? (int) ($match['playoffMatchId'] ?? 0)
                        : (int) ($match['groupMatchId'] ?? 0);
                    $isPlayoff = $matchKind === 'playoff';
                @endphp
                <article class="overflow-hidden rounded-[8px] bg-white shadow-[0_1px_3px_rgba(0,0,0,0.06)] ring-1 {{ $isMine ? 'ring-[#66A157] ring-2' : 'ring-black/[0.04]' }} {{ $cardPad }}">
                    @if ($isPlayoff)
                        <span class="mb-1.5 inline-flex rounded bg-[#FFF8E1] px-1.5 py-px text-[9px] font-bold uppercase tracking-wide text-[#E65100]">Playoff</span>
                    @endif
                    @if (($match['finished'] ?? false) === true && ! $compactCards)
                        @include('partials.match-scoreboard', [
                            'score' => $match['score'] ?? '',
                            'homeName' => $match['leftName'],
                            'awayName' => $match['rightName'],
                            'homeMeta' => $match['leftMeta'],
                            'awayMeta' => $match['rightMeta'],
                            'homeSideWon' => $match['homeSideWon'] ?? null,
                        ])
                    @elseif (($match['finished'] ?? false) === true)
                        <div class="grid grid-cols-[1fr_auto_1fr] items-start gap-1.5">
                            <div class="min-w-0 text-left">
                                <span class="mb-0.5 inline-block rounded bg-[#E8F5E9] px-1.5 py-px text-[9px] font-semibold uppercase text-[#2E7D32]">Home</span>
                                <p class="{{ $nameCls }} font-bold leading-snug {{ ($match['homeSideWon'] ?? false) ? 'text-[#1B5E20]' : 'text-[#212121]' }}">{{ $match['leftName'] }}</p>
                            </div>
                            <div class="flex flex-col items-center gap-0.5 px-1 pt-3">
                                <span class="text-[10px] font-bold uppercase text-[#212121]">VS</span>
                                <span class="text-[11px] font-bold text-[#424242]">{{ $scoreDisplay ?? ($hideScoreForPlayer ? '—' : ($match['score'] ?? '—')) }}</span>
                            </div>
                            <div class="min-w-0 text-right">
                                <span class="mb-0.5 inline-block rounded bg-[#E8F5E9] px-1.5 py-px text-[9px] font-semibold uppercase text-[#2E7D32]">Away</span>
                                <p class="{{ $nameCls }} font-bold leading-snug {{ ($match['homeSideWon'] ?? true) === false ? 'text-[#1B5E20]' : 'text-[#212121]' }}">{{ $match['rightName'] }}</p>
                            </div>
                        </div>
                        @if (! empty($match['winnerLabel']))
                            <p class="mt-2 text-center text-[11px] font-semibold text-[#2E7D32]">
                                Winner: {{ $match['winnerLabel'] }}
                            </p>
                        @endif
                        @if ($viewerWon !== null)
                            <p class="mt-1 text-center">
                                <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold uppercase {{ $viewerWon ? 'bg-[#E8F5E9] text-[#1B5E20]' : 'bg-[#FEE2E2] text-[#991B1B]' }}">
                                    {{ $viewerWon ? 'You won' : 'You lost' }}
                                </span>
                            </p>
                        @endif
                    @else
                        <div class="grid grid-cols-[1fr_auto_1fr] items-start gap-1.5">
                            <div class="min-w-0 text-left">
                                <span class="mb-0.5 inline-block rounded bg-[#E8F5E9] px-1.5 py-px text-[9px] font-semibold uppercase text-[#2E7D32]">Home</span>
                                <p class="{{ $nameCls }} font-bold leading-snug text-[#212121]">{{ $match['leftName'] }}</p>
                                <p class="mt-0.5 {{ $metaCls }} text-[#757575]">{{ $match['leftMeta'] }}</p>
                            </div>
                            <div class="flex flex-col items-center gap-0.5 py-0.5">
                                <span class="text-[10px] font-bold uppercase text-[#212121]">VS</span>
                                <span class="rounded-full bg-[#E8F5E9] px-2 py-0.5 text-[10px] font-semibold text-[#2E7D32]">Pending</span>
                            </div>
                            <div class="min-w-0 text-right">
                                <span class="mb-0.5 inline-block rounded bg-[#E8F5E9] px-1.5 py-px text-[9px] font-semibold uppercase text-[#2E7D32]">Away</span>
                                <p class="{{ $nameCls }} font-bold leading-snug text-[#212121]">{{ $match['rightName'] }}</p>
                                <p class="mt-0.5 {{ $metaCls }} text-[#757575]">{{ $match['rightMeta'] }}</p>
                            </div>
                        </div>
                    @endif
                    <div class="mt-2 flex flex-wrap items-center gap-x-2 gap-y-0.5 border-t border-[#EEEEEE] pt-2 text-[11px] text-[#757575]">
                        <span>{{ $match['dateShort'] }}</span>
                        <span>{{ $match['time'] }}</span>
                        @if (! empty($match['venueOnly']))
                            <span class="min-w-0 truncate">{{ $match['venueOnly'] }}</span>
                        @endif
                        @if (! empty($match['courtOnly']))
                            <span class="min-w-0 truncate">Court {{ $match['courtOnly'] }}</span>
                        @endif
                        @if (empty($match['venueOnly']) && empty($match['courtOnly']))
                            <span>TBA</span>
                        @endif
                    </div>
                    @if ($isMine && $playerMatchId > 0)
                        <div class="mt-2 border-t border-[#EEEEEE] pt-2">
                            @if ($showPlayerMatchActions)
                                @if (! ($match['hasPlayerUpload'] ?? false))
                                    <a
                                        href="{{ $match['uploadUrl'] ?? route('player.profile.upload', ['match' => $playerMatchId, 'kind' => $matchKind]) }}"
                                        class="inline-flex items-center gap-1 text-[12px] font-semibold text-[#66A157] hover:underline"
                                    >
                                        <i class="fa-solid fa-image text-[11px]" aria-hidden="true"></i>
                                        Upload image
                                    </a>
                                    <p class="mt-1 text-[10px] leading-snug text-[#757575]">
                                        Upload a match photo to view or enter the score.
                                    </p>
                                @elseif (! ($match['finished'] ?? false))
                                    <form
                                        method="post"
                                        action="{{ route('player.profile.match.result') }}"
                                        class="mb-2"
                                    >
                                        @csrf
                                        <input type="hidden" name="match" value="{{ $playerMatchId }}">
                                        <input type="hidden" name="match_kind" value="{{ $matchKind }}">
                                        @include('player.profile.partials.match-result-quick', [
                                            'prefix' => ($isPlayoff ? 'po' : 'pm').'-'.$playerMatchId,
                                            'scoreValue' => $match['scoreRaw'] ?? '',
                                            'winnerSide' => $match['winnerSide'] ?? null,
                                        ])
                                        <button
                                            type="submit"
                                            class="player-match-save-score bg-[#66A157] text-white hover:bg-[#5a9048]"
                                        >
                                            Save score
                                        </button>
                                    </form>
                                @endif
                                @if ($useVenueModal && ! ($match['finished'] ?? false))
                                    <button
                                        type="button"
                                        class="js-open-venue-modal text-[12px] font-semibold text-[#66A157] hover:underline"
                                        data-match-id="{{ $playerMatchId }}"
                                        data-match-kind="{{ $matchKind }}"
                                        data-match-label="{{ $match['leftName'] }} vs {{ $match['rightName'] }}"
                                        data-date="{{ $match['dateValue'] ?? '' }}"
                                        data-time="{{ $match['timeInput'] ?? '' }}"
                                        data-venue="{{ $match['venueInput'] ?? '' }}"
                                        data-court="{{ $match['courtInput'] ?? '' }}"
                                        data-date-min="{{ $match['dateMin'] ?? '' }}"
                                        data-date-max="{{ $match['dateMax'] ?? '' }}"
                                        data-date-hint="{{ $match['dateWindowHint'] ?? '' }}"
                                    >
                                        Update venue &amp; time
                                    </button>
                                    @if (! empty($match['dateWindowHint']))
                                        <p class="mt-1 text-[10px] leading-snug text-[#757575]">
                                            Date must be {{ $match['dateWindowHint'] }}.
                                        </p>
                                    @endif
                                @elseif ($locationEditBaseUrl && ! ($match['finished'] ?? false))
                                    <a href="{{ $locationEditBaseUrl.'?match='.$playerMatchId.'&kind='.$matchKind }}" class="text-[12px] font-semibold text-[#66A157] hover:underline">
                                        Update venue &amp; time →
                                    </a>
                                @endif
                                @if ($match['hasPlayerUpload'] ?? false)
                                    <p class="mt-2">
                                        <a
                                            href="{{ $match['uploadUrl'] ?? route('player.profile.upload', ['match' => $playerMatchId, 'kind' => $matchKind]) }}"
                                            class="text-[11px] font-semibold text-[#757575] hover:text-[#66A157] hover:underline"
                                        >
                                            Add more photos
                                        </a>
                                    </p>
                                @endif
                            @elseif ($useVenueModal)
                                <button
                                    type="button"
                                    class="js-open-venue-modal text-[12px] font-semibold text-[#66A157] hover:underline"
                                    data-match-id="{{ $playerMatchId }}"
                                    data-match-kind="{{ $matchKind }}"
                                    data-match-label="{{ $match['leftName'] }} vs {{ $match['rightName'] }}"
                                    data-date="{{ $match['dateValue'] ?? '' }}"
                                    data-time="{{ $match['timeInput'] ?? '' }}"
                                    data-venue="{{ $match['venueInput'] ?? '' }}"
                                    data-court="{{ $match['courtInput'] ?? '' }}"
                                    data-date-min="{{ $match['dateMin'] ?? '' }}"
                                    data-date-max="{{ $match['dateMax'] ?? '' }}"
                                    data-date-hint="{{ $match['dateWindowHint'] ?? '' }}"
                                >
                                    Update venue &amp; time
                                </button>
                            @elseif ($locationEditBaseUrl)
                                <a href="{{ $locationEditBaseUrl.'?match='.$playerMatchId.'&kind='.$matchKind }}" class="text-[12px] font-semibold text-[#66A157] hover:underline">
                                    Update venue &amp; time →
                                </a>
                            @endif
                        </div>
                    @endif
                </article>
            @endforeach
        @if ($weeksInColumns)
                </div>
            </div>
        @else
        </div>
        @endif
    @endforeach
    @if ($weeksInColumns)
        </div>
    @endif
@endif
