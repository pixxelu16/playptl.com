@if (! ($showPlayoffsSection ?? false))
    <div class="rounded-[10px] bg-white px-6 py-14 text-center shadow-[0_2px_10px_rgba(0,0,0,0.08)] ring-1 ring-black/[0.04]">
        <p class="text-[16px] font-semibold text-[#374151]">Playoffs are not available yet.</p>
        <p class="mx-auto mt-2 max-w-md text-[14px] text-[#6B7280]">{{ $qualifierUnavailableMessage ?? 'League matches have not started yet.' }}</p>
    </div>
@elseif (! ($bracketExists ?? false))
    <div class="rounded-[10px] bg-white px-6 py-14 text-center shadow-[0_2px_10px_rgba(0,0,0,0.08)] ring-1 ring-black/[0.04]">
        <p class="text-[16px] font-semibold text-[#374151]">Playoff bracket</p>
        <p class="mx-auto mt-2 max-w-md text-[14px] text-[#6B7280]">{{ $playoffEmptyMessage ?? 'The knockout bracket will appear here once it is ready.' }}</p>
    </div>
@else
    <div class="playoffs-public">
        <nav class="playoff-flow" aria-label="Bracket rounds">
            @foreach ($playoffRounds as $round)
                <a class="playoff-flow__item {{ $round['done'] ? 'is-done' : '' }}" href="#playoff-round-{{ $round['id'] }}">
                    <span class="playoff-flow__step">{{ $loop->iteration }}</span>
                    <span class="playoff-flow__label">{{ $round['label'] }}</span>
                    <span class="playoff-flow__meta">{{ $round['matches']->count() }} · {{ $round['done'] ? 'Done' : 'Open' }}</span>
                </a>
                @if (! $loop->last)
                    <span class="playoff-flow__arrow" aria-hidden="true">›</span>
                @endif
            @endforeach
        </nav>

        <div class="playoff-stack">
            @foreach ($playoffRounds as $round)
                @php
                    $matchCount = $round['matches']->count();
                @endphp
                <section class="playoff-round" id="playoff-round-{{ $round['id'] }}" aria-labelledby="playoff-round-title-{{ $round['id'] }}">
                    <header class="playoff-round__header">
                        <div class="playoff-round__heading">
                            <span class="playoff-round__step">{{ $loop->iteration }}</span>
                            <h2 class="playoff-round__title" id="playoff-round-title-{{ $round['id'] }}">{{ $round['title'] }}</h2>
                            <span class="playoff-round__count">{{ $matchCount }} {{ $matchCount === 1 ? 'match' : 'matches' }}</span>
                        </div>
                        @if (! empty($round['hint']))
                            <p class="playoff-round__hint">{{ $round['hint'] }}</p>
                        @endif
                    </header>
                    <div class="playoff-round__grid">
                        @foreach ($round['matches'] as $pm)
                            @include('partials.playoffs-node-public', ['m' => $pm])
                        @endforeach
                    </div>
                    @if ($round['id'] === 'f' && ($round['done'] ?? false))
                        <div class="relative mt-4 overflow-hidden rounded-[10px]">
                            <img
                                src="{{ asset('frontend/images/champion.png') }}"
                                alt="Champion"
                                class="block h-auto w-full max-w-full"
                                loading="lazy"
                                decoding="async"
                            />
                        </div>
                    @endif
                </section>
            @endforeach
        </div>
    </div>
@endif
