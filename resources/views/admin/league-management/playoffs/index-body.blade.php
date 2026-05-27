{{-- Playoff bracket and forms (included below the same header + tabs shell as Matches). --}}
<div class="playoffs-subpage">
    @if (($showQualifierPlayoffs ?? false) && ($groupMatchesStarted ?? false))
        <div class="playoff-phase-bar admin-card" style="margin-bottom:1.25rem;padding:1rem 1.25rem;border:1px solid rgba(0,0,0,0.08);box-shadow:none;">
            <h2 class="admin-card-title" style="font-size:1.05rem;margin-bottom:0.5rem;">Playoff season</h2>
            @if ($playoffsPhaseMessage ?? null)
                <p class="admin-card-text" style="margin-bottom:0.75rem;">{{ $playoffsPhaseMessage }}</p>
            @endif
            @php
                $leagueCloseLabel = $league->end_date?->format('M j, Y') ?? 'not set';
                $playoffStartValue = old('playoff_start_date', $league->playoff_start_date?->format('Y-m-d') ?? '');
                $playoffEndValue = old('playoff_end_date', $league->playoff_end_date?->format('Y-m-d') ?? '');
                $playoffStartMin = $league->end_date?->copy()->addDay()->format('Y-m-d') ?? '';
                $playoffEndMin = $playoffStartMin;
                if ($playoffStartValue !== '' && ($playoffEndMin === '' || $playoffStartValue > $playoffEndMin)) {
                    $playoffEndMin = $playoffStartValue;
                }
                $playoffDatesLocked = ($playoffsClosed ?? false);
            @endphp
            @if (! $playoffDatesLocked)
                <form
                    method="POST"
                    action="{{ route('admin.league-management.playoffs.dates', [$league, $groupCard] + ($ageGroupKey ? ['age_group_key' => $ageGroupKey] : [])) }}"
                    class="playoff-dates-form"
                    style="margin-bottom:0.85rem;"
                >
                    @csrf
                    <p class="admin-card-text" style="font-size:0.85rem;margin:0 0 0.65rem;">
                        League match close: <strong>{{ $leagueCloseLabel }}</strong>.
                        Playoff start and end must both be <strong>after</strong> that date. End cannot be before start. Match dates must fall between playoff start and end.
                    </p>
                    <div style="display:flex;flex-wrap:wrap;gap:0.75rem 1.25rem;align-items:flex-end;">
                        <div>
                            <label for="playoff_start_date" style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.85rem;">Playoff start date</label>
                            @if ($playoffsStarted ?? false)
                                <input class="admin-input" id="playoff_start_date" type="date" value="{{ $playoffStartValue }}" disabled readonly style="background:#f3f4f6;cursor:not-allowed;">
                            @else
                                <input class="admin-input" id="playoff_start_date" type="date" name="playoff_start_date" value="{{ $playoffStartValue }}" @if ($playoffStartMin) min="{{ $playoffStartMin }}" @endif required>
                            @endif
                        </div>
                        <div>
                            <label for="playoff_end_date" style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.85rem;">Playoff end date</label>
                            <input class="admin-input" id="playoff_end_date" type="date" name="playoff_end_date" value="{{ $playoffEndValue }}" @if ($playoffEndMin) min="{{ $playoffEndMin }}" @endif required>
                        </div>
                        <button class="admin-button admin-button-secondary" type="submit">
                            <i class="fa-solid fa-calendar-check" aria-hidden="true"></i>
                            <span>Save dates</span>
                        </button>
                    </div>
                    @error('playoff_start_date')
                        <p class="admin-field-error" style="margin-top:0.5rem;">{{ $message }}</p>
                    @enderror
                    @error('playoff_end_date')
                        <p class="admin-field-error" style="margin-top:0.5rem;">{{ $message }}</p>
                    @enderror
                    @error('playoff_dates')
                        <p class="admin-field-error" style="margin-top:0.5rem;">{{ $message }}</p>
                    @enderror
                </form>
            @elseif ($league->playoff_start_date && $league->playoff_end_date)
                <p class="admin-card-text" style="font-size:0.85rem;margin:0 0 0.65rem;">
                    Playoff window: <strong>{{ $league->playoff_start_date->format('M j, Y') }}</strong>
                    – <strong>{{ $league->playoff_end_date->format('M j, Y') }}</strong>
                </p>
            @endif
            <div style="display:flex;flex-wrap:wrap;gap:0.5rem;align-items:center;">
                @if ($canStartPlayoffs ?? false)
                    <form
                        method="POST"
                        action="{{ route('admin.league-management.playoffs.start', [$league, $groupCard] + ($ageGroupKey ? ['age_group_key' => $ageGroupKey] : [])) }}"
                        style="margin:0;"
                        data-admin-confirm
                        data-admin-confirm-title="Start playoffs?"
                        data-admin-confirm-message="Group-stage match scheduling will close for this league. Playoff matches will use the dates you set above."
                        data-admin-confirm-button="Start playoffs"
                    >
                        @csrf
                        <button class="admin-button" type="submit">
                            <i class="fa-solid fa-play" aria-hidden="true"></i>
                            <span>Playoff start</span>
                        </button>
                    </form>
                @elseif ($playoffsStarted ?? false)
                    <span class="match-status-pill match-status-pill--done" style="margin:0;">
                        Playoffs started{{ $league->playoffs_started_at ? ' · '.$league->playoffs_started_at->format('M j, Y g:i A') : '' }}
                    </span>
                @endif
                @if ($canClosePlayoffs ?? false)
                    <form
                        method="POST"
                        action="{{ route('admin.league-management.playoffs.close', [$league, $groupCard] + ($ageGroupKey ? ['age_group_key' => $ageGroupKey] : [])) }}"
                        style="margin:0;"
                        data-admin-confirm
                        data-admin-confirm-title="Close playoffs?"
                        data-admin-confirm-message="Playoff results will be locked. Players will not be able to change scores after this."
                        data-admin-confirm-button="Close playoffs"
                    >
                        @csrf
                        <button class="admin-button admin-button-secondary" type="submit">
                            <i class="fa-solid fa-stop" aria-hidden="true"></i>
                            <span>Playoff close</span>
                        </button>
                    </form>
                @elseif ($playoffsClosed ?? false)
                    <span class="match-status-pill match-status-pill--pending" style="margin:0;">
                        Playoffs closed{{ $league->playoffs_closed_at ? ' · '.$league->playoffs_closed_at->format('M j, Y g:i A') : '' }}
                    </span>
                @endif
            </div>
            @if (! ($playoffsStarted ?? false))
                <p class="admin-card-text" style="font-size:0.85rem;margin:0.75rem 0 0;opacity:0.9;">
                    Set Qualifier paths, save playoff dates above, then click <strong>Playoff start</strong>.
                </p>
            @endif
        </div>
    @endif

    @if (! ($showQualifierPlayoffs ?? false))
        <div class="admin-empty-state playoffs-empty" style="margin-top:1rem;">
            <i class="fa-solid fa-hourglass-half" aria-hidden="true"></i>
            <p>{{ $qualifierUnavailableMessage ?? 'Playoffs are not available yet.' }}</p>
            <p style="margin-top:0.75rem;">
                <a class="admin-link" href="{{ route('admin.league-management.matches.index', [$league, $groupCard] + ($ageGroupKey ? ['age_group_key' => $ageGroupKey] : [])) }}">
                    <i class="fa-solid fa-calendar-days" aria-hidden="true"></i>
                    <span>Go to Matches</span>
                </a>
            </p>
        </div>
    @elseif (! $playerSchemaReady)
        <div class="admin-alert admin-alert-error">Player roster is not ready for this division.</div>
    @elseif ($rosterRegs->isEmpty())
        <div class="admin-alert admin-alert-error">Add players to this division first, then set paths on the Qualifier page.</div>
    @else
        @if (! $bracketExists)
            <div class="admin-empty-state playoffs-empty">
                <i class="fa-solid fa-sitemap" aria-hidden="true"></i>
                <p>
                    @if (! ($qualifierReady ?? false))
                        Playoff bracket is empty. Go to
                        <a href="{{ route('admin.league-management.qualifier.index', [$league, $groupCard] + ($ageGroupKey ? ['age_group_key' => $ageGroupKey] : [])) }}">Qualifier</a>,
                        assign players to <strong>Quarter</strong>, <strong>Pre-Q / Round of 16</strong>, or <strong>Pre-Pre-Q</strong>, then save — the bracket will appear here automatically.
                    @else
                        Qualifier paths are saved but the bracket has not been built yet. Save again on the
                        <a href="{{ route('admin.league-management.qualifier.index', [$league, $groupCard] + ($ageGroupKey ? ['age_group_key' => $ageGroupKey] : [])) }}">Qualifier</a> page to generate it.
                    @endif
                </p>
            </div>
        @else
            @php
                $playoffRounds = [];

                if ($ppqMatches->isNotEmpty()) {
                    $playoffRounds[] = [
                        'id' => 'ppq',
                        'label' => 'Pre-Pre-Q',
                        'title' => 'Pre-Pre-Quarterfinals',
                        'hint' => '16 players play down to 8 winners — those winners join Round of 16 (away slot) after you click Advance winners.',
                        'matches' => $ppqMatches,
                        'done' => $ppqMatches->every(fn ($m) => ! $m->isPending()),
                    ];
                }

                if ($pqMatches->isNotEmpty()) {
                    $playoffRounds[] = [
                        'id' => 'pq',
                        'label' => 'Pre-Q',
                        'title' => 'Pre-Quarterfinals (Round of 16)',
                        'hint' => '8 direct seeds on Home — away slots fill with Pre-Pre-Q winners (1 vs 8 cross). Then Advance winners sends Round of 16 winners to quarterfinals.',
                        'matches' => $pqMatches,
                        'done' => $pqMatches->every(fn ($m) => ! $m->isPending()),
                    ];
                }

                if ($qfMatches->isNotEmpty()) {
                    $playoffRounds[] = [
                        'id' => 'qf',
                        'label' => 'Quarterfinals',
                        'title' => 'Quarterfinals',
                        'hint' => 'Direct quarter seeds on home when set; away slots fill from Round of 16 winners.',
                        'matches' => $qfMatches,
                        'done' => $qfComplete ?? false,
                    ];
                }

                $playoffRounds[] = [
                    'id' => 'sf',
                    'label' => 'Semifinals',
                    'title' => 'Semifinals',
                    'hint' => 'Unlocks when all four quarterfinals have a winner.',
                    'matches' => $sfMatches,
                    'done' => $sfComplete ?? false,
                ];

                if ($finalMatch) {
                    $playoffRounds[] = [
                        'id' => 'f',
                        'label' => 'Final',
                        'title' => 'Final',
                        'hint' => 'Unlocks when both semifinals have a winner.',
                        'matches' => collect([$finalMatch]),
                        'done' => ! $finalMatch->isPending(),
                    ];
                }
            @endphp

            <div class="playoff-toolbar">
                <form method="POST" action="{{ route('admin.league-management.playoffs.pull-winners', [$league, $groupCard] + ($ageGroupKey ? ['age_group_key' => $ageGroupKey] : [])) }}" class="playoff-toolbar__form">
                    @csrf
                    <button class="admin-button admin-button-secondary" type="submit" title="Move Pre-Q winners into quarterfinals, then advance QF → SF → Final">
                        <i class="fa-solid fa-arrow-down-short-wide" aria-hidden="true"></i>
                        <span>Advance winners</span>
                    </button>
                </form>
                <p class="playoff-toolbar__hint">
                    Save each match with a score, then use <strong>Advance winners</strong>: Pre-Pre-Q → Round of 16, then Round of 16 → quarterfinals.
                    @if (! ($qfComplete ?? false))
                        <span class="playoff-toolbar__warn">Finish Pre-Pre-Q, Round of 16, and all quarterfinals before semifinals unlock.</span>
                    @endif
                </p>
            </div>

            <nav class="playoff-flow" aria-label="Bracket rounds">
                @foreach ($playoffRounds as $round)
                    <a class="playoff-flow__item {{ $round['done'] ? 'is-done' : '' }}" href="#playoff-round-{{ $round['id'] }}">
                        <span class="playoff-flow__step">{{ $loop->iteration }}</span>
                        <span class="playoff-flow__label">{{ $round['label'] }}</span>
                        <span class="playoff-flow__meta">{{ $round['matches']->count() }} · {{ $round['done'] ? 'Done' : 'Open' }}</span>
                    </a>
                    @if (! $loop->last)
                        <span class="playoff-flow__arrow" aria-hidden="true"><i class="fa-solid fa-chevron-right"></i></span>
                    @endif
                @endforeach
            </nav>

            <div class="playoff-stack">
                @foreach ($playoffRounds as $round)
                    @include('admin.league-management.playoffs._round', [
                        'roundId' => $round['id'],
                        'step' => $loop->iteration,
                        'title' => $round['title'],
                        'hint' => $round['hint'],
                        'matches' => $round['matches'],
                        'rosterRegs' => $rosterRegs,
                        'league' => $league,
                        'groupCard' => $groupCard,
                        'ageGroupKey' => $ageGroupKey,
                        'qfComplete' => $qfComplete,
                        'sfComplete' => $sfComplete,
                    ])
                @endforeach
            </div>
        @endif
    @endif
</div>
