@php
    /** @var \App\Models\PlayoffMatch $m */
    $slotLabel = $m->round === \App\Models\PlayoffMatch::ROUND_F
        ? 'Final'
        : $m->roundLabel().' '.$m->slot;
    $isComplete = ! $m->isPending();
    $homePlaceholder = $m->sidePlaceholderLabel('home');
    $awayPlaceholder = $m->sidePlaceholderLabel('away');
    $scoreDisplay = trim((string) ($m->score ?? ''));
    $dateDisplay = $m->match_date?->format('D, M j, Y') ?? '';
    $timeDisplay = \App\Support\MatchStartTime::formatDisplay((string) ($m->start_time ?? '')) ?: (trim((string) ($m->start_time ?? '')) ?: '');
@endphp
<article class="playoff-match-card {{ $isComplete ? 'is-complete' : '' }}">
    <div class="playoff-match-card__top">
        <span class="playoff-match-card__label">{{ $slotLabel }}</span>
        @if ($isComplete)
            <span class="playoff-match-card__status">Done</span>
        @else
            <span class="playoff-match-card__status playoff-match-card__status--pending">Pending</span>
        @endif
    </div>

    <div class="playoff-match-card__versus">
        <div class="playoff-match-card__player">
            <span class="playoff-match-card__side-label">Home</span>
            @if ($m->homeUser)
                <p class="playoff-match-card__name">{{ \App\Support\MatchSchedulePresenter::playerDisplayName($m->homeUser) }}</p>
            @else
                <p class="playoff-match-card__name playoff-match-card__name--tbd">{{ $homePlaceholder }}</p>
            @endif
        </div>
        <span class="playoff-match-card__vs" aria-hidden="true">vs</span>
        <div class="playoff-match-card__player">
            <span class="playoff-match-card__side-label">Away</span>
            @if ($m->awayUser)
                <p class="playoff-match-card__name">{{ \App\Support\MatchSchedulePresenter::playerDisplayName($m->awayUser) }}</p>
            @else
                <p class="playoff-match-card__name playoff-match-card__name--tbd">{{ $awayPlaceholder }}</p>
            @endif
        </div>
    </div>

    @if ($dateDisplay !== '' || $timeDisplay !== '' || $scoreDisplay !== '')
        <div class="playoff-match-card__details">
            @if ($dateDisplay !== '')
                <span>{{ $dateDisplay }}</span>
            @endif
            @if ($timeDisplay !== '')
                <span>{{ $timeDisplay }}</span>
            @endif
            @if ($scoreDisplay !== '')
                <span class="playoff-match-card__score-line"><strong>Score:</strong> {{ $scoreDisplay }}</span>
            @endif
        </div>
    @endif

    @if ($isComplete)
        @php $hw = $m->homeSideWon(); @endphp
        @if ($hw !== null)
            <p class="playoff-match-card__winner">
                Winner:
                <strong>
                    @if ($hw && $m->homeUser)
                        {{ \App\Support\MatchSchedulePresenter::playerDisplayName($m->homeUser) }}
                    @elseif (! $hw && $m->awayUser)
                        {{ \App\Support\MatchSchedulePresenter::playerDisplayName($m->awayUser) }}
                    @endif
                </strong>
            </p>
        @endif
    @endif
</article>
