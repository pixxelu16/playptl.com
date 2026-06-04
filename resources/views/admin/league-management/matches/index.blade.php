@extends('layouts.admin')

@section('title', 'Matches | '.$league->name.' | '.config('app.name', 'playptl'))
@section('meta_description', 'Schedule singles and doubles matches by date for a group.')

@push('styles')
    @include('partials.match-scoreboard-styles')
    <style>
        .match-date-heading {
            font-family: "Bebas Neue", "Inter", sans-serif;
            font-size: 1.65rem;
            letter-spacing: 0.06em;
            color: #1a1a1a;
            margin: 2rem 0 1rem;
        }
        .match-date-heading:first-of-type {
            margin-top: 0.5rem;
        }
        .match-card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.25rem;
        }
        .match-card {
            background: var(--match-card-bg);
            border-radius: 14px;
            border: 1px solid #d7ead9;
            box-shadow: 0 8px 24px rgba(85, 166, 78, 0.08);
            padding: 1.1rem 1.15rem 0.85rem;
            position: relative;
        }
        .match-card-top {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            gap: 0.5rem;
            align-items: start;
        }
        .match-card-top--scored {
            display: block;
        }
        .match-side {
            text-align: center;
        }
        .match-tag {
            display: inline-block;
            font-size: 0.65rem;
            font-weight: 800;
            letter-spacing: 0.1em;
            color: var(--match-green);
            margin-bottom: 0.35rem;
        }
        .match-player-name {
            font-weight: 800;
            font-size: 1rem;
            line-height: 1.25;
        }
        .match-meta {
            font-size: 0.78rem;
            color: #555;
            margin-top: 0.25rem;
        }
        .match-vs {
            text-align: center;
            padding-top: 1.25rem;
        }
        .match-vs-label {
            font-weight: 900;
            font-size: 0.85rem;
            color: #888;
        }
        .match-status-pill {
            display: inline-flex;
            margin-top: 0.4rem;
            padding: 0.35rem 0.65rem;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 800;
        }
        .match-status-pill--pending {
            background: var(--match-green-soft);
            color: #2f7a2a;
        }
        .match-status-pill--done {
            background: #eef6ff;
            color: #1a5fb4;
        }
        .match-card-foot {
            margin-top: 1rem;
            padding-top: 0.85rem;
            border-top: 1px solid #edf4ee;
            font-size: 0.82rem;
            color: #444;
            display: grid;
            gap: 0.35rem;
        }
        .match-card-foot-row {
            display: flex;
            align-items: center;
            gap: 0.45rem;
        }
        .match-card-foot-row i {
            width: 1rem;
            color: var(--match-green);
            text-align: center;
        }
        .match-card-menu {
            position: absolute;
            top: 10px;
            right: 10px;
        }
        .match-card-menu summary {
            list-style: none;
            cursor: pointer;
            color: #888;
            padding: 4px 8px;
            border-radius: 8px;
        }
        .match-card-menu summary::-webkit-details-marker { display: none; }
        .match-card-menu[open] summary {
            background: #f3f3f3;
        }
        .match-card-menu-panel {
            position: absolute;
            right: 0;
            top: 100%;
            min-width: 17.5rem;
            max-width: min(22rem, calc(100vw - 1.5rem));
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 10px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
            padding: 0.55rem 0.6rem;
            z-index: 3;
        }
        .match-edit-form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.35rem 0.5rem;
        }
        .match-edit-field {
            min-width: 0;
        }
        .match-edit-field label {
            display: block;
            margin: 0 0 0.2rem;
            font-size: 0.68rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #555;
            line-height: 1.2;
        }
        .match-edit-field .admin-input {
            width: 100%;
            margin: 0;
            padding: 0.4rem 0.45rem;
            font-size: 0.82rem;
        }
        .match-edit-form-actions {
            grid-column: 1 / -1;
            margin-top: 0.15rem;
        }
        .match-edit-form-actions .admin-button {
            width: 100%;
            margin: 0;
            padding: 0.45rem 0.65rem;
            font-size: 0.82rem;
        }
        .match-edit-panel {
            margin-top: 0.75rem;
            padding: 0.75rem;
            background: #fafafa;
            border-radius: 10px;
            border: 1px dashed #ccc;
        }
        .match-edit-panel .admin-input,
        .match-edit-panel .admin-button {
            width: 100%;
            margin-bottom: 0.5rem;
        }
        .match-h2h-box {
            margin-top: 0.75rem;
            padding: 0.65rem 0.75rem;
            border-radius: 10px;
            background: #f6faf6;
            border: 1px solid #d7ead9;
            font-size: 0.78rem;
            line-height: 1.45;
            color: #333;
        }
        .match-h2h-box strong {
            display: block;
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #2f7a2a;
            margin-bottom: 0.35rem;
        }
        .match-h2h-meetings {
            margin: 0.4rem 0 0;
            padding-left: 1rem;
            max-height: 6.5rem;
            overflow-y: auto;
        }
        .match-h2h-meetings li {
            margin: 0.15rem 0;
        }
        .match-quick-result {
            margin-top: 0.5rem;
            padding-top: 0.5rem;
            border-top: 1px dashed #cfe8d2;
        }
        .match-quick-result-inner {
            display: flex;
            flex-wrap: wrap;
            gap: 0.4rem;
            align-items: flex-end;
        }
        .match-quick-result .admin-button {
            padding: 0.45rem 0.85rem;
            font-size: 0.82rem;
        }
        .match-quick-result .match-result-fields {
            flex: 1 1 100%;
            margin-top: 0;
            padding: 0.4rem 0.5rem;
            border-radius: 8px;
        }
        .match-quick-result .match-result-fields__type {
            margin-bottom: 0.35rem;
            gap: 0.4rem 0.75rem;
        }
        .match-quick-result .match-result-fields__legend {
            font-size: 0.62rem;
            margin-right: 0.35rem;
        }
        .match-quick-result .match-result-fields__radio {
            font-size: 0.72rem;
            gap: 0.25rem;
        }
        .match-quick-result .match-result-fields__radio input {
            width: 0.85rem;
            height: 0.85rem;
        }
        .match-quick-result .match-result-fields__score-label {
            font-size: 0.62rem;
            margin-bottom: 0.15rem;
        }
        .match-quick-result .match-result-fields__note {
            font-size: 0.7rem;
            margin-top: 0.25rem;
        }
        .match-quick-result .match-result-fields__walkover .admin-input {
            padding: 0.35rem 0.45rem;
            font-size: 0.8rem;
            min-height: 0;
        }
        #add-match-h2h-preview {
            margin-top: 0.75rem;
        }
        input[type="time"].admin-input {
            min-height: 48px;
        }
        .match-result-fields {
            flex: 1 1 100%;
            margin-top: 0.35rem;
            padding: 0.5rem 0.55rem;
            border: 1px solid #d7ead9;
            border-radius: 8px;
            background: #f9fcf9;
        }
        .match-result-fields__type {
            border: 0;
            margin: 0 0 0.4rem;
            padding: 0;
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem 0.85rem;
            align-items: center;
        }
        .match-result-fields__legend {
            font-size: 0.65rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #2f7a2a;
            padding: 0;
            margin: 0 0.35rem 0 0;
        }
        .match-result-fields__radio {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            font-size: 0.78rem;
            font-weight: 600;
            cursor: pointer;
        }
        .match-result-fields__walkover label,
        .match-result-fields__score-wrap label {
            display: block;
            font-size: 0.65rem;
            font-weight: 700;
            color: #2f7a2a;
            margin-bottom: 0.15rem;
        }
        .match-result-fields__note {
            margin: 0.25rem 0 0;
            font-size: 0.7rem;
            color: #555;
            line-height: 1.3;
        }
        .match-result-fields__score-label {
            display: block;
            font-size: 0.65rem;
            font-weight: 700;
            color: #2f7a2a;
            margin-bottom: 0.15rem;
        }
        .playoff-schedule-lock {
            margin: 1rem 0;
        }
        .match-add-toggle-wrap {
            margin-bottom: 1.25rem;
        }
        .match-add-panel {
            margin-top: 1rem;
            padding: 1rem 1.25rem;
            border: 1px solid rgba(0, 0, 0, 0.08);
            border-radius: 12px;
            background: #fafcfa;
        }
        .match-add-panel[hidden] {
            display: none;
        }
    </style>
    <script src="{{ asset('admin/js/match-result-fields.js') }}" defer></script>
@endpush

@section('content')
    <section class="admin-card match-schedule-page">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-card-title">Matches — {{ $league->name }}</h1>
                <p class="admin-card-text">
                    Group: <strong>{{ $groupCard->name }}</strong>
                    @if ($ageGroupKey)
                        · Age: <strong>{{ $ageGroupKey }}</strong>
                    @endif
                    @if ($activeGroup)
                        · Subgroup: <strong>{{ $activeGroup->name }}</strong>
                    @endif
                </p>
            </div>
            <div class="admin-header-actions" style="flex-wrap:wrap;gap:0.5rem;">
                @include('admin.league-management.partials.group-card-header-actions', [
                    'league' => $league,
                    'groupCard' => $groupCard,
                    'ageGroupKey' => $ageGroupKey,
                    'activeGroupId' => $activeGroupId,
                    'playerSchemaReady' => $playerSchemaReady,
                    'active' => 'matches',
                ])
            </div>
        </div>

        @include('admin.league-management.partials.group-card-nav-tabs', [
            'league' => $league,
            'groupCard' => $groupCard,
            'ageGroupKey' => $ageGroupKey,
            'groups' => $groups,
            'activeGroupId' => $activeGroupId,
            'active' => 'matches',
        ])

        @if (session('status'))
            <div class="admin-alert admin-alert-success">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="admin-alert admin-alert-error">
                <ul style="margin:0;padding-left:1.2rem;">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (! $playerSchemaReady)
            <div class="admin-alert admin-alert-error">
                Player roster is not ready. Run migrations and assign players to subgroups first.
            </div>
        @elseif ($rosterRegs->isEmpty())
            <div class="admin-alert admin-alert-error">
                No players in <strong>{{ $activeGroup->name }}</strong> yet. Add players from Subgroups &amp; players, then schedule matches.
            </div>
        @else
            @if ($groupSchedulingLocked ?? false)
                <div class="admin-alert admin-alert-error playoff-schedule-lock" role="status">
                    {{ $groupSchedulingLockMessage ?? 'Group match scheduling is closed while qualifier or playoffs are active.' }}
                    <a href="{{ route('admin.league-management.playoffs.index', [$league, $groupCard] + ($ageGroupKey ? ['age_group_key' => $ageGroupKey] : [])) }}" style="margin-left:0.35rem;font-weight:700;">Open playoffs</a>
                </div>
            @endif
            @php
                $lockedFormat = $groupCard->forcedMatchFormat();
            @endphp
            @if ($canEditScheduleDates ?? false)
            <div class="admin-card" style="margin-bottom:1.25rem;padding:1rem 1.25rem;border:1px solid rgba(0,0,0,0.08);box-shadow:none;">
                @php
                    $scheduleStart = old('start_date', ($divisionScheduleStart ?? null)?->format('Y-m-d') ?? '');
                    $scheduleEnd = old('end_date', ($divisionScheduleEnd ?? null)?->format('Y-m-d') ?? '');
                    $tournamentDateMin = $league->start_date?->format('Y-m-d') ?? '';
                    $tournamentDateMax = $league->end_date?->format('Y-m-d') ?? '';
                @endphp
                <h2 class="admin-card-title" style="font-size:1.05rem;margin-bottom:0.5rem;">Auto round-robin schedule — {{ $groupCard->name }}</h2>
                <p class="admin-card-text" style="margin:0 0 0.35rem;font-size:0.85rem;">
                    Applies to all subgroups ({{ $groups->pluck('name')->join(', ') }}) in this group.
                </p>
                @if ($tournamentDatesConfigured ?? false)
                    <p class="admin-card-text" style="margin:0 0 1rem;font-size:0.85rem;">
                        Group dates must be between the tournament window:
                        <strong>{{ $league->start_date?->format('M j, Y') }}</strong>
                        –
                        <strong>{{ $league->end_date?->format('M j, Y') }}</strong>.
                    </p>
                @else
                    <p class="admin-card-text" style="margin:0 0 1rem;font-size:0.85rem;color:#b45309;">
                        Set tournament start and end dates on
                        <a href="{{ route('admin.leagues.edit', $league) }}" class="admin-link" style="font-weight:700;">Edit Tournament</a>
                        before scheduling this group.
                    </p>
                @endif
                <form method="POST" action="{{ route('admin.league-management.matches.save-schedule-dates', [$league, $groupCard] + ($ageGroupKey ? ['age_group_key' => $ageGroupKey] : []) + ['group' => $activeGroup->id]) }}">
                    @csrf
                    @php
                        $playoffsStartedLock = $playoffsStarted ?? false;
                        $matchesAlreadyScheduled = $groupMatchesScheduled ?? false;
                        $groupEndDateMin = $scheduleStart !== '' ? $scheduleStart : $tournamentDateMin;
                        if (($divisionLatestMatchDate ?? null) instanceof \Illuminate\Support\Carbon) {
                            $latestMatchYmd = $divisionLatestMatchDate->format('Y-m-d');
                            if ($groupEndDateMin === '' || $latestMatchYmd > $groupEndDateMin) {
                                $groupEndDateMin = $latestMatchYmd;
                            }
                        }
                    @endphp
                    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:1rem;margin-bottom:1rem;">
                        <div>
                            <label for="group_start_date" style="display:block;font-weight:600;margin-bottom:0.35rem;">Group start date</label>
                            @if ($playoffsStartedLock)
                                <input class="admin-input" id="group_start_date" type="date" value="{{ $scheduleStart }}" disabled readonly style="background:#f3f4f6;cursor:not-allowed;">
                            @else
                                <input class="admin-input @error('start_date') border-red-500 @enderror" id="group_start_date" type="date" name="start_date" value="{{ $scheduleStart }}" required
                                    @if ($tournamentDateMin) min="{{ $tournamentDateMin }}" @endif
                                    @if ($tournamentDateMax) max="{{ $tournamentDateMax }}" @endif
                                    @if (! ($tournamentDatesConfigured ?? false)) disabled @endif>
                                @error('start_date')
                                    <p class="mt-1 text-[12px] font-semibold text-red-600">{{ $message }}</p>
                                @enderror
                            @endif
                        </div>
                        @if ($matchesAlreadyScheduled)
                            <div>
                                <label for="group_end_date" style="display:block;font-weight:600;margin-bottom:0.35rem;">Group end date</label>
                                <input class="admin-input @error('end_date') border-red-500 @enderror" id="group_end_date" type="date" name="end_date" value="{{ $scheduleEnd }}"
                                    @if ($groupEndDateMin) min="{{ $groupEndDateMin }}" @endif
                                    @if ($tournamentDateMax) max="{{ $tournamentDateMax }}" @endif
                                    @if (! ($tournamentDatesConfigured ?? false)) disabled @endif>
                                @error('end_date')
                                    <p class="mt-1 text-[12px] font-semibold text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif
                    </div>
                    <button class="admin-button" type="submit" @if (! ($tournamentDatesConfigured ?? false)) disabled @endif>
                        <i class="fa-solid fa-calendar-check" aria-hidden="true"></i>
                        <span>{{ $matchesAlreadyScheduled ? 'Reschedule matches' : 'Schedule matches' }}</span>
                    </button>
                </form>
                @if (! ($matchesAlreadyScheduled ?? false) && ($tournamentDatesConfigured ?? false))
                    <p class="admin-card-text" style="margin:0.75rem 0 0;font-size:0.85rem;">
                        Pick a start date and click <strong>Schedule matches</strong>. The group end date field appears after matches are generated.
                    </p>
                @elseif ($matchesAlreadyScheduled && ! ($playoffsStartedLock ?? false))
                    <p class="admin-card-text" style="margin:0.75rem 0 0;font-size:0.85rem;">
                        You can change the group start or end date until playoffs start, then click <strong>Reschedule matches</strong>.
                    </p>
                @endif
                @if ($groupMatchesClosed ?? false)
                    <p class="admin-card-text" style="margin:0.75rem 0 0;font-size:0.85rem;">
                        Group matches closed on <strong>{{ $groupMatchCloseDate?->format('M j, Y') }}</strong>. Extend the end date above to reopen scheduling.
                    </p>
                @endif
            </div>
            @endif
            @if ($canAddManualMatch ?? false)
            @php
                $addMatchPanelOpen = $errors->hasAny(['home_user_id', 'away_user_id', 'match_date', 'start_time', 'format', 'group_id'])
                    || old('home_user_id') || old('away_user_id');
                $divisionMatchDateMin = ($divisionScheduleStart ?? null)?->format('Y-m-d') ?? '';
                $divisionMatchDateMax = ($divisionScheduleEnd ?? null)?->format('Y-m-d') ?? '';
                $divisionMatchDateTitle = ($divisionMatchDateMin !== '' && $divisionMatchDateMax !== '')
                    ? 'Between '.($divisionScheduleStart ?? null)->format('M j, Y').' and '.($divisionScheduleEnd ?? null)->format('M j, Y')
                    : (($divisionMatchDateMin !== '') ? 'On or after '.($divisionScheduleStart ?? null)->format('M j, Y') : 'Set group start and end dates first');
            @endphp
            <div class="match-add-toggle-wrap">
                <button
                    type="button"
                    class="admin-button admin-button-secondary"
                    id="match-add-toggle-btn"
                    aria-expanded="{{ $addMatchPanelOpen ? 'true' : 'false' }}"
                    aria-controls="match-add-panel"
                >
                    <i class="fa-solid fa-calendar-plus" aria-hidden="true"></i>
                    <span>Add match</span>
                </button>
            </div>
            <div
                id="match-add-panel"
                class="match-add-panel match-add-form"
                @if (! $addMatchPanelOpen) hidden @endif
            >
                <h2 class="admin-card-title" style="font-size:1.05rem;margin-bottom:1rem;">Add match (manual)</h2>
                <form id="add-match-form" class="match-schedule-form" method="POST" action="{{ route('admin.league-management.matches.store', [$league, $groupCard] + ($ageGroupKey ? ['age_group_key' => $ageGroupKey] : [])) }}">
                    @csrf
                    <input type="hidden" name="group_id" value="{{ $activeGroup->id }}">
                    <input type="hidden" name="confirm_schedule_conflict" value="{{ old('confirm_schedule_conflict', '0') }}">

                    <div class="match-add-grid">
                        <div>
                            <label for="format" style="display:block;">Format</label>
                            @if ($lockedFormat)
                                <input type="hidden" name="format" id="format" value="{{ $lockedFormat->value }}">
                                <p class="admin-card-text" style="margin:0;padding:10px 12px;border:1px solid #d7ead9;border-radius:10px;background:#fff;">
                                    <strong>{{ $lockedFormat === \App\Enums\GroupMatchFormat::Singles ? 'Singles (1 vs 1)' : 'Doubles (2 vs 2)' }}</strong>
                                </p>
                            @else
                                <select class="admin-input" id="format" name="format" required>
                                    <option value="singles" @selected(old('format', 'singles') === 'singles')>Singles (1 vs 1)</option>
                                    <option value="doubles" @selected(old('format') === 'doubles')>Doubles (2 vs 2)</option>
                                </select>
                                <p class="admin-card-text" style="font-size:0.8rem;margin:0.35rem 0 0;opacity:0.85;">Choose singles or doubles per match.</p>
                            @endif
                        </div>
                        <div>
                            <label for="match_date">Match date <span style="color:#c62828;">*</span></label>
                            <input
                                class="admin-input"
                                id="match_date"
                                type="date"
                                name="match_date"
                                value="{{ old('match_date', now()->toDateString()) }}"
                                @if ($divisionMatchDateMin) min="{{ $divisionMatchDateMin }}" @endif
                                @if ($divisionMatchDateMax) max="{{ $divisionMatchDateMax }}" @endif
                                title="{{ $divisionMatchDateTitle }}"
                                required
                            >
                        </div>
                        <div>
                            <label for="start_time">Start time <span style="color:#c62828;">*</span></label>
                            <input class="admin-input" id="start_time" type="time" name="start_time" step="300" value="{{ old('start_time', \App\Support\MatchStartTime::toInputValue('10:00')) }}" required>
                        </div>
                        <div>
                            <label for="venue">Venue</label>
                            <input class="admin-input" id="venue" type="text" name="venue" value="{{ old('venue') }}" placeholder="Highland Country Club">
                        </div>
                        <div>
                            <label for="court">Court</label>
                            <input class="admin-input" id="court" type="text" name="court" value="{{ old('court') }}" placeholder="Court 1" maxlength="64">
                        </div>
                    </div>

                    @php
                        $oldHomeKey = old('home_user_id') && old('home_partner_user_id')
                            ? (int) old('home_user_id').':'.(int) old('home_partner_user_id')
                            : (old('home_user_id') ? (string) (int) old('home_user_id') : '');
                        $oldAwayKey = old('away_user_id') && old('away_partner_user_id')
                            ? (int) old('away_user_id').':'.(int) old('away_partner_user_id')
                            : (old('away_user_id') ? (string) (int) old('away_user_id') : '');
                    @endphp

                    <div id="singles-player-picks" class="match-add-grid" style="margin-top:1rem;{{ $scheduleAsDoublesTeams ? ' display:none;' : '' }}">
                        <div>
                            <label for="home_user_id">Home player</label>
                            <select class="admin-input" id="home_user_id" name="home_user_id" @if (! $scheduleAsDoublesTeams) required @endif>
                                <option value="">— Select —</option>
                                @foreach ($rosterRegs as $reg)
                                    @if ($reg->user)
                                        <option value="{{ $reg->user_id }}" @selected((int) old('home_user_id') === (int) $reg->user_id)>
                                            @include('admin.league-management.matches._player-name', ['user' => $reg->user])
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="away_user_id">Away player</label>
                            <select class="admin-input" id="away_user_id" name="away_user_id" @if (! $scheduleAsDoublesTeams) required @endif>
                                <option value="">— Select —</option>
                                @foreach ($rosterRegs as $reg)
                                    @if ($reg->user)
                                        <option value="{{ $reg->user_id }}" @selected((int) old('away_user_id') === (int) $reg->user_id)>
                                            @include('admin.league-management.matches._player-name', ['user' => $reg->user])
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div id="doubles-team-picks" class="match-add-grid" style="margin-top:1rem;{{ $scheduleAsDoublesTeams ? '' : ' display:none;' }}">
                        <div>
                            <label for="home_team_select">Home team</label>
                            <select class="admin-input" id="home_team_select" @if ($scheduleAsDoublesTeams) required @endif>
                                <option value="">— Select team —</option>
                                @foreach ($rosterTeams as $team)
                                    <option value="{{ $team['key'] }}"
                                            data-primary="{{ $team['primary_user_id'] }}"
                                            data-partner="{{ $team['partner_user_id'] ?? '' }}"
                                            @selected($oldHomeKey === $team['key'])
                                            @disabled(! $team['is_complete'])>
                                        {{ $team['display_name'] }}
                                        @if (! $team['is_complete'])
                                            — partner missing
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            <input type="hidden" name="home_user_id" id="home_user_id_doubles" value="{{ old('home_user_id') }}">
                            <input type="hidden" name="home_partner_user_id" id="home_partner_user_id_doubles" value="{{ old('home_partner_user_id') }}">
                        </div>
                        <div>
                            <label for="away_team_select">Away team</label>
                            <select class="admin-input" id="away_team_select" @if ($scheduleAsDoublesTeams) required @endif>
                                <option value="">— Select team —</option>
                                @foreach ($rosterTeams as $team)
                                    <option value="{{ $team['key'] }}"
                                            data-primary="{{ $team['primary_user_id'] }}"
                                            data-partner="{{ $team['partner_user_id'] ?? '' }}"
                                            @selected($oldAwayKey === $team['key'])
                                            @disabled(! $team['is_complete'])>
                                        {{ $team['display_name'] }}
                                        @if (! $team['is_complete'])
                                            — partner missing
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            <input type="hidden" name="away_user_id" id="away_user_id_doubles" value="{{ old('away_user_id') }}">
                            <input type="hidden" name="away_partner_user_id" id="away_partner_user_id_doubles" value="{{ old('away_partner_user_id') }}">
                        </div>
                    </div>

                    <div id="add-match-h2h-preview" class="match-h2h-box" style="display:none;" aria-live="polite"></div>

                    <div style="margin-top:1rem;">
                        <button class="admin-button" type="submit">
                            <i class="fa-solid fa-calendar-plus" aria-hidden="true"></i>
                            <span>Schedule match</span>
                        </button>
                    </div>
                </form>
            </div>
            <script>
                (function () {
                    var btn = document.getElementById('match-add-toggle-btn');
                    var panel = document.getElementById('match-add-panel');
                    if (! btn || ! panel) {
                        return;
                    }
                    btn.addEventListener('click', function () {
                        var open = panel.hidden;
                        panel.hidden = ! open;
                        btn.setAttribute('aria-expanded', open ? 'true' : 'false');
                    });
                })();
            </script>
            @endif

            <div id="match-schedule-conflict-modal" class="admin-modal" hidden aria-hidden="true">
                <button type="button" class="admin-modal-backdrop" data-match-schedule-modal-cancel aria-label="Close"></button>
                <div class="admin-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="match-schedule-conflict-title">
                    <h2 id="match-schedule-conflict-title" class="admin-modal-title">Schedule conflict</h2>
                    <div id="match-schedule-conflict-list" class="admin-modal-options" role="list"></div>
                    <p class="admin-modal-footer-note">Do you still want to schedule this match?</p>
                    <div class="admin-modal-actions">
                        <button type="button" class="admin-modal-btn-cancel" data-match-schedule-modal-cancel>Cancel</button>
                        <button type="button" class="admin-modal-btn-primary" id="match-schedule-conflict-confirm">Schedule anyway</button>
                    </div>
                </div>
            </div>

            <script>
                (function () {
                    var scheduleAsTeams = @json($scheduleAsDoublesTeams);
                    var formatEl = document.getElementById('format');
                    var singlesPicks = document.getElementById('singles-player-picks');
                    var doublesPicks = document.getElementById('doubles-team-picks');
                    var homeSingles = document.getElementById('home_user_id');
                    var awaySingles = document.getElementById('away_user_id');
                    var homeTeam = document.getElementById('home_team_select');
                    var awayTeam = document.getElementById('away_team_select');
                    var homeUserDoubles = document.getElementById('home_user_id_doubles');
                    var homePartnerDoubles = document.getElementById('home_partner_user_id_doubles');
                    var awayUserDoubles = document.getElementById('away_user_id_doubles');
                    var awayPartnerDoubles = document.getElementById('away_partner_user_id_doubles');

                    function currentFormat() {
                        if (scheduleAsTeams) {
                            return 'doubles';
                        }
                        if (! formatEl) {
                            return 'singles';
                        }
                        return formatEl.value || 'singles';
                    }

                    function applyTeamSelect(teamSelect, userInput, partnerInput) {
                        if (! teamSelect || ! userInput || ! partnerInput) {
                            return;
                        }
                        var opt = teamSelect.options[teamSelect.selectedIndex];
                        if (! opt || ! opt.value) {
                            userInput.value = '';
                            partnerInput.value = '';
                            return;
                        }
                        userInput.value = opt.getAttribute('data-primary') || '';
                        partnerInput.value = opt.getAttribute('data-partner') || '';
                    }

                    function syncPickers() {
                        var doubles = currentFormat() === 'doubles';
                        if (singlesPicks) {
                            singlesPicks.style.display = doubles ? 'none' : '';
                        }
                        if (doublesPicks) {
                            doublesPicks.style.display = doubles ? '' : 'none';
                        }
                        if (homeSingles) {
                            homeSingles.disabled = doubles;
                            homeSingles.required = ! doubles && ! scheduleAsTeams;
                        }
                        if (awaySingles) {
                            awaySingles.disabled = doubles;
                            awaySingles.required = ! doubles && ! scheduleAsTeams;
                        }
                        if (homeTeam) {
                            homeTeam.disabled = ! doubles;
                            homeTeam.required = doubles;
                        }
                        if (awayTeam) {
                            awayTeam.disabled = ! doubles;
                            awayTeam.required = doubles;
                        }
                        if (homeUserDoubles) {
                            homeUserDoubles.disabled = ! doubles;
                        }
                        if (homePartnerDoubles) {
                            homePartnerDoubles.disabled = ! doubles;
                        }
                        if (awayUserDoubles) {
                            awayUserDoubles.disabled = ! doubles;
                        }
                        if (awayPartnerDoubles) {
                            awayPartnerDoubles.disabled = ! doubles;
                        }
                        if (doubles) {
                            applyTeamSelect(homeTeam, homeUserDoubles, homePartnerDoubles);
                            applyTeamSelect(awayTeam, awayUserDoubles, awayPartnerDoubles);
                        }
                    }

                    if (homeTeam) {
                        homeTeam.addEventListener('change', function () {
                            applyTeamSelect(homeTeam, homeUserDoubles, homePartnerDoubles);
                        });
                    }
                    if (awayTeam) {
                        awayTeam.addEventListener('change', function () {
                            applyTeamSelect(awayTeam, awayUserDoubles, awayPartnerDoubles);
                        });
                    }
                    if (formatEl && formatEl.tagName === 'SELECT') {
                        formatEl.addEventListener('change', syncPickers);
                    }
                    syncPickers();
                })();
            </script>

            <script>
                window.PLAYER_SCHEDULE_BY_DAY = @json($playerScheduleByDay ?? []);
                window.MATCH_PLAYER_NAMES = @json($playerNamesById);
            </script>
            <script>
                (function () {
                    function nameOf(id) {
                        var n = window.MATCH_PLAYER_NAMES && window.MATCH_PLAYER_NAMES[String(id)];
                        return n || ('Player #' + id);
                    }

                    function collectParticipantIds(form) {
                        var ids = [];
                        var fromData = form.getAttribute('data-participant-ids');
                        if (fromData) {
                            fromData.split(',').forEach(function (part) {
                                var n = parseInt(part, 10);
                                if (n) {
                                    ids.push(n);
                                }
                            });
                            return ids;
                        }

                        var doubles = false;
                        var formatEl = document.getElementById('format');
                        if (formatEl && formatEl.tagName === 'SELECT') {
                            doubles = (formatEl.value || '') === 'doubles';
                        } else if (document.getElementById('doubles-team-picks')) {
                            var dp = document.getElementById('doubles-team-picks');
                            doubles = dp && dp.style.display !== 'none';
                        }

                        if (doubles) {
                            ['home_user_id', 'home_partner_user_id', 'away_user_id', 'away_partner_user_id'].forEach(function (field) {
                                var el = form.querySelector('[name="' + field + '"]');
                                if (el && el.value) {
                                    ids.push(parseInt(el.value, 10));
                                }
                            });
                        } else {
                            ['home_user_id', 'away_user_id'].forEach(function (field) {
                                var el = form.querySelector('[name="' + field + '"]:not([disabled])') || form.querySelector('[name="' + field + '"]');
                                if (el && el.value && !el.disabled) {
                                    ids.push(parseInt(el.value, 10));
                                }
                            });
                        }

                        return ids.filter(function (id, i, arr) {
                            return id && arr.indexOf(id) === i;
                        });
                    }

                    function warningLines(dateYmd, playerIds, ignoreGroupMatchId) {
                        var lines = [];
                        var index = window.PLAYER_SCHEDULE_BY_DAY || {};
                        var ignoreId = ignoreGroupMatchId ? parseInt(ignoreGroupMatchId, 10) : 0;

                        playerIds.forEach(function (userId) {
                            var daySlots = (index[userId] && index[userId][dateYmd]) ? index[userId][dateYmd] : [];
                            var slots = daySlots.filter(function (slot) {
                                return ! ignoreId || parseInt(slot.group_match_id, 10) !== ignoreId;
                            });
                            if (! slots.length) {
                                return;
                            }
                            var parts = slots.map(function (slot) {
                                return (slot.time_label || 'Time TBA') + ' (' + (slot.league || 'Tournament') + ')';
                            });
                            lines.push(nameOf(userId) + ' already has a match on this date at ' + parts.join(', '));
                        });

                        return lines;
                    }

                    var conflictModal = document.getElementById('match-schedule-conflict-modal');
                    var conflictList = document.getElementById('match-schedule-conflict-list');
                    var conflictConfirmBtn = document.getElementById('match-schedule-conflict-confirm');
                    var pendingScheduleForm = null;

                    function openScheduleConflictModal(lines, form) {
                        if (! conflictModal || ! conflictList) {
                            return;
                        }
                        pendingScheduleForm = form;
                        conflictList.innerHTML = '';
                        lines.forEach(function (line) {
                            var row = document.createElement('div');
                            row.className = 'admin-modal-option';
                            row.setAttribute('role', 'listitem');

                            var icon = document.createElement('span');
                            icon.className = 'admin-modal-option-icon';
                            icon.setAttribute('aria-hidden', 'true');
                            icon.innerHTML = '<i class="fa-solid fa-exclamation"></i>';

                            var text = document.createElement('span');
                            text.className = 'admin-modal-option-text';
                            text.textContent = line;

                            row.appendChild(icon);
                            row.appendChild(text);
                            conflictList.appendChild(row);
                        });
                        conflictModal.hidden = false;
                        conflictModal.classList.add('is-open');
                        conflictModal.setAttribute('aria-hidden', 'false');
                        document.body.classList.add('admin-modal-open');
                    }

                    function closeScheduleConflictModal() {
                        if (! conflictModal) {
                            return;
                        }
                        conflictModal.hidden = true;
                        conflictModal.classList.remove('is-open');
                        conflictModal.setAttribute('aria-hidden', 'true');
                        document.body.classList.remove('admin-modal-open');
                        pendingScheduleForm = null;
                    }

                    if (conflictConfirmBtn) {
                        conflictConfirmBtn.addEventListener('click', function () {
                            if (! pendingScheduleForm) {
                                return;
                            }
                            var confirmEl = pendingScheduleForm.querySelector('[name="confirm_schedule_conflict"]');
                            if (confirmEl) {
                                confirmEl.value = '1';
                            }
                            var formToSubmit = pendingScheduleForm;
                            if (window.AdminFormSubmitLock) {
                                window.AdminFormSubmitLock.lockButton(conflictConfirmBtn);
                                window.AdminFormSubmitLock.lockForm(formToSubmit);
                            }
                            closeScheduleConflictModal();
                            if (formToSubmit.requestSubmit) {
                                formToSubmit.requestSubmit();
                            } else {
                                formToSubmit.submit();
                            }
                        });
                    }

                    document.querySelectorAll('[data-match-schedule-modal-cancel]').forEach(function (btn) {
                        btn.addEventListener('click', closeScheduleConflictModal);
                    });

                    document.addEventListener('keydown', function (e) {
                        if (e.key === 'Escape' && conflictModal && conflictModal.classList.contains('is-open')) {
                            closeScheduleConflictModal();
                        }
                    });

                    document.querySelectorAll('.match-schedule-form').forEach(function (form) {
                        form.addEventListener('submit', function (e) {
                            var confirmEl = form.querySelector('[name="confirm_schedule_conflict"]');
                            if (confirmEl && confirmEl.value === '1') {
                                return;
                            }

                            var dateEl = form.querySelector('[name="match_date"]');
                            if (! dateEl || ! dateEl.value) {
                                return;
                            }

                            var playerIds = collectParticipantIds(form);
                            if (! playerIds.length) {
                                return;
                            }

                            var lines = warningLines(
                                dateEl.value,
                                playerIds,
                                form.getAttribute('data-ignore-group-match-id')
                            );

                            if (! lines.length) {
                                return;
                            }

                            e.preventDefault();
                            openScheduleConflictModal(lines, form);
                        });
                    });
                })();
            </script>
            <script>
                window.MATCH_ADMIN = @json(['h2h' => $headToHeadSingles, 'names' => $playerNamesById]);
                (function () {
                    var h = document.getElementById('home_user_id');
                    var a = document.getElementById('away_user_id');
                    var out = document.getElementById('add-match-h2h-preview');
                    if (! h || ! a || ! out || ! window.MATCH_ADMIN) {
                        return;
                    }
                    function nameOf(id) {
                        var n = window.MATCH_ADMIN.names[String(id)];
                        return n || ('Player #' + id);
                    }
                    function syncH2h() {
                        var ha = parseInt(h.value, 10) || 0;
                        var aa = parseInt(a.value, 10) || 0;
                        if (! ha || ! aa || ha === aa) {
                            out.style.display = 'none';
                            out.innerHTML = '';
                            return;
                        }
                        var lo = Math.min(ha, aa);
                        var hi = Math.max(ha, aa);
                        var key = lo + '-' + hi;
                        var pack = window.MATCH_ADMIN.h2h[key];
                        var meetings = (pack && pack.meetings) ? pack.meetings : [];
                        var wHa = 0;
                        var wAa = 0;
                        for (var i = 0; i < meetings.length; i++) {
                            if (meetings[i].winner_user_id === ha) {
                                wHa++;
                            }
                            if (meetings[i].winner_user_id === aa) {
                                wAa++;
                            }
                        }
                        var nh = nameOf(ha);
                        var na = nameOf(aa);
                        var total = wHa + wAa;
                        var html = '<strong>Head-to-head (this group)</strong>';
                        if (total === 0) {
                            html += '<p style="margin:0.35rem 0 0;">No completed matches between these two in this division yet.</p>';
                        } else {
                            html += '<p style="margin:0.35rem 0 0;font-weight:700;">' + nh + ' ' + wHa + '—' + wAa + ' ' + na + ' <span style="font-weight:600;opacity:0.85;">(' + total + ' completed)</span></p>';
                            html += '<ul class="match-h2h-meetings">';
                            var start = Math.max(0, meetings.length - 5);
                            for (var j = meetings.length - 1; j >= start; j--) {
                                var m = meetings[j];
                                var wn = nameOf(m.winner_user_id);
                                html += '<li>' + m.match_date + ' · <span style="font-weight:700;">' + m.score + '</span> — won by ' + wn + '</li>';
                            }
                            html += '</ul>';
                        }
                        out.innerHTML = html;
                        out.style.display = '';
                    }
                    h.addEventListener('change', syncH2h);
                    a.addEventListener('change', syncH2h);
                    syncH2h();
                })();
            </script>

            @if (($allMatchesCount ?? 0) > 0)
                <p class="admin-card-text" style="margin:1rem 0 0.75rem;">
                    <strong>{{ $allMatchesCount }}</strong> scheduled match(es) for <strong>{{ $activeGroup->name }}</strong> (sorted by week deadline).
                </p>
            @endif
            @forelse ($matchesByWeek as $weekKey => $weekMatches)
                @php
                    $roundNum = (int) $weekKey;
                    $isWeekGroup = $roundNum > 0 && $roundNum < 9999;
                @endphp
                <h2 class="match-date-heading">
                    @if ($isWeekGroup)
                        Week {{ $roundNum }}
                    @else
                        Other matches
                    @endif
                </h2>
                <div class="match-card-grid">
                    @foreach ($weekMatches as $match)
                        <article class="match-card">
                            <details class="match-card-menu">
                                <summary aria-label="Match actions"><i class="fa-solid fa-ellipsis-vertical"></i></summary>
                                <div class="match-card-menu-panel">
                                    <p style="margin:0 0 8px;font-size:0.8rem;font-weight:800;color:#555;">Edit match</p>
                                    <form class="match-edit-form match-schedule-form" method="POST" action="{{ route('admin.league-management.matches.update', [$league, $groupCard, $match] + ($ageGroupKey ? ['age_group_key' => $ageGroupKey] : [])) }}"
                                          data-ignore-group-match-id="{{ $match->id }}"
                                          data-participant-ids="{{ implode(',', array_filter([(int) $match->home_user_id, (int) $match->away_user_id, $match->home_partner_user_id ? (int) $match->home_partner_user_id : null, $match->away_partner_user_id ? (int) $match->away_partner_user_id : null])) }}">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="confirm_schedule_conflict" value="0">
                                        <div class="match-edit-field">
                                            <label for="md-{{ $match->id }}">Date <span style="color:#c62828;">*</span></label>
                                            <input
                                                class="admin-input"
                                                id="md-{{ $match->id }}"
                                                type="date"
                                                name="match_date"
                                                value="{{ $match->match_date->toDateString() }}"
                                                @if ($divisionScheduleStart ?? null) min="{{ $divisionScheduleStart->format('Y-m-d') }}" @endif
                                                @if ($divisionScheduleEnd ?? null) max="{{ $divisionScheduleEnd->format('Y-m-d') }}" @endif
                                                title="{{ (($divisionScheduleStart ?? null) && ($divisionScheduleEnd ?? null)) ? 'Between '.$divisionScheduleStart->format('M j, Y').' and '.$divisionScheduleEnd->format('M j, Y') : '' }}"
                                                required
                                            >
                                        </div>
                                        <div class="match-edit-field">
                                            <label for="mt-{{ $match->id }}">Time <span style="color:#c62828;">*</span></label>
                                            <input class="admin-input" id="mt-{{ $match->id }}" type="time" name="start_time" step="300" value="{{ \App\Support\MatchStartTime::toInputValue(old('start_time', $match->start_time)) }}" required>
                                        </div>
                                        <div class="match-edit-field">
                                            <label for="mv-{{ $match->id }}">Venue</label>
                                            <input class="admin-input" id="mv-{{ $match->id }}" type="text" name="venue" value="{{ $match->venue }}">
                                        </div>
                                        <div class="match-edit-field">
                                            <label for="mc-{{ $match->id }}">Court</label>
                                            <input class="admin-input" id="mc-{{ $match->id }}" type="text" name="court" value="{{ $match->court }}" placeholder="Court 1" maxlength="64">
                                        </div>
                                        <div class="match-edit-field" style="grid-column:1/-1;">
                                            @include('admin.league-management.matches._result-fields', [
                                                'prefix' => 'edit-'.$match->id,
                                                'scoreValue' => $match->score,
                                                'winnerSide' => $match->winner_side,
                                                'scoreLabel' => 'Score',
                                                'scorePlaceholder' => 'Pending',
                                            ])
                                        </div>
                                        <div class="match-edit-field">
                                            <label for="mo-{{ $match->id }}">Sort</label>
                                            <input class="admin-input" id="mo-{{ $match->id }}" type="number" name="sort_order" value="{{ $match->sort_order }}" min="0">
                                        </div>
                                        <div class="match-edit-form-actions">
                                            <button class="admin-button" type="submit">Save</button>
                                        </div>
                                    </form>
                                    <form method="POST" action="{{ route('admin.league-management.matches.destroy', [$league, $groupCard, $match] + ($ageGroupKey ? ['age_group_key' => $ageGroupKey] : [])) }}" onsubmit="return confirm('Delete this match?');" style="margin-top:10px;">
                                        @csrf
                                        @method('DELETE')
                                        <button class="admin-button admin-button-secondary" type="submit" style="width:100%;">Delete match</button>
                                    </form>
                                </div>
                            </details>

                            @php
                                $homeDisplayName = ($match->format === \App\Enums\GroupMatchFormat::Doubles && $match->homePartnerUser)
                                    ? ($playerNamesById[(int) $match->home_user_id] ?? $match->homeUser?->name)
                                    : ($match->homeUser?->name ?? '—');
                                $awayDisplayName = ($match->format === \App\Enums\GroupMatchFormat::Doubles && $match->awayPartnerUser)
                                    ? ($playerNamesById[(int) $match->away_user_id] ?? $match->awayUser?->name)
                                    : ($match->awayUser?->name ?? '—');
                                $homeMeta = $activeGroup->name;
                                $awayMeta = $activeGroup->name;
                            @endphp
                            @if ($match->isPending())
                                <div class="match-card-top">
                                    <div class="match-side">
                                        <div class="match-tag">HOME</div>
                                        <div class="match-player-name">{{ $homeDisplayName }}</div>
                                        <div class="match-meta">{{ $homeMeta }}</div>
                                    </div>
                                    <div class="match-vs">
                                        <div class="match-vs-label">VS</div>
                                        <span class="match-status-pill match-status-pill--pending">Pending</span>
                                    </div>
                                    <div class="match-side">
                                        <div class="match-tag">AWAY</div>
                                        <div class="match-player-name">{{ $awayDisplayName }}</div>
                                        <div class="match-meta">{{ $awayMeta }}</div>
                                    </div>
                                </div>
                            @else
                                <div class="match-card-top match-card-top--scored">
                                    @include('admin.league-management.matches._scoreboard', [
                                        'match' => $match,
                                        'score' => $match->score,
                                        'homeName' => $homeDisplayName,
                                        'awayName' => $awayDisplayName,
                                        'homeMeta' => $homeMeta,
                                        'awayMeta' => $awayMeta,
                                    ])
                                </div>
                            @endif
                            <div class="match-card-foot">
                                <div class="match-card-foot-row">
                                    <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                                    <span>{{ $match->match_date->format('D, M j') }}</span>
                                </div>
                                @if ($match->start_time)
                                    <div class="match-card-foot-row">
                                        <i class="fa-regular fa-clock" aria-hidden="true"></i>
                                        <span>{{ \App\Support\MatchStartTime::formatDisplay($match->start_time) ?: $match->start_time }}</span>
                                    </div>
                                @endif
                                @if ($match->venue || $match->court)
                                    <div class="match-card-foot-row">
                                        <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                                        <span>
                                            {{ trim(implode(' — ', array_filter([$match->venue, $match->court]))) ?: '—' }}
                                        </span>
                                    </div>
                                @endif
                            </div>

                            @php
                                $matchParticipantIds = array_values(array_filter([
                                    (int) $match->home_user_id,
                                    (int) $match->away_user_id,
                                    $match->home_partner_user_id ? (int) $match->home_partner_user_id : null,
                                    $match->away_partner_user_id ? (int) $match->away_partner_user_id : null,
                                ]));
                                $matchScheduleConflictLines = \App\Support\PlayerMatchDayConflict::cardNoticeLinesFromIndex(
                                    $playerScheduleByDay ?? [],
                                    $match->match_date->format('Y-m-d'),
                                    $matchParticipantIds,
                                    (int) $match->id,
                                    null,
                                    $playerNamesById ?? [],
                                );
                            @endphp
                            @if ($matchScheduleConflictLines !== [])
                                <div class="admin-match-schedule-conflict">
                                    @include('partials.match-schedule-conflict-notice', ['lines' => $matchScheduleConflictLines])
                                </div>
                            @endif

                            @php
                                $fmtSingles = $match->format === \App\Enums\GroupMatchFormat::Singles;
                                $wid = $fmtSingles ? $match->singlesWinnerUserId() : null;
                                $teamWon = $match->homeSideWon();
                            @endphp
                            @if ($wid !== null && $fmtSingles)
                                <p class="match-winner-line">
                                    Winner:
                                    <strong>@include('admin.league-management.matches._player-name', ['user' => $wid === (int) $match->home_user_id ? $match->homeUser : $match->awayUser])</strong>
                                </p>
                            @elseif (! $match->isPending() && $match->format === \App\Enums\GroupMatchFormat::Doubles && $teamWon !== null)
                                <p class="match-winner-line">
                                    Winner: <strong>{{ $teamWon ? 'Home team' : 'Away team' }}</strong>
                                </p>
                            @endif

                            @if ($fmtSingles)
                                @php
                                    $uidH = (int) $match->home_user_id;
                                    $uidA = (int) $match->away_user_id;
                                    $pairKey = min($uidH, $uidA).'-'.max($uidH, $uidA);
                                    $meetings = collect($headToHeadSingles[$pairKey]['meetings'] ?? []);
                                    if ($match->isPending()) {
                                        $meetings = $meetings->where('match_id', '!=', (int) $match->id)->values();
                                    }
                                    $wHome = $meetings->where('winner_user_id', $uidH)->count();
                                    $wAway = $meetings->where('winner_user_id', $uidA)->count();
                                    $playedH2h = $wHome + $wAway;
                                @endphp
                                <div class="match-h2h-box">
                                    <strong>Head-to-head (this group)</strong>
                                    @if ($playedH2h === 0)
                                        <p style="margin:0.35rem 0 0;">@if ($match->isPending()) First meeting in this division.@else No prior completed H2H rows stored.@endif</p>
                                    @else
                                        <p style="margin:0.35rem 0 0;font-weight:700;">
                                            @include('admin.league-management.matches._player-name', ['user' => $match->homeUser])
                                            {{ $wHome }}—{{ $wAway }}
                                            @include('admin.league-management.matches._player-name', ['user' => $match->awayUser])
                                            <span style="font-weight:600;opacity:0.85;">({{ $playedH2h }} completed @if ($match->isPending()) before this match @endif)</span>
                                        </p>
                                        @if ($meetings->isNotEmpty())
                                            <ul class="match-h2h-meetings">
                                                @foreach ($meetings->sortByDesc('match_date')->take(5) as $row)
                                                    <li>
                                                        {{ $row['match_date'] }}
                                                        · <span style="font-weight:700;">{{ $row['score'] }}</span>
                                                        — won by
                                                        @php $wuid = (int) $row['winner_user_id']; @endphp
                                                        @if ($wuid === $uidH)
                                                            @include('admin.league-management.matches._player-name', ['user' => $match->homeUser])
                                                        @else
                                                            @include('admin.league-management.matches._player-name', ['user' => $match->awayUser])
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    @endif
                                </div>
                            @endif

                            <div class="match-quick-result">
                                <form method="POST" action="{{ route('admin.league-management.matches.update', [$league, $groupCard, $match] + ($ageGroupKey ? ['age_group_key' => $ageGroupKey] : [])) }}" class="match-quick-result-inner">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="quick_result" value="1">
                                    <input type="hidden" name="match_date" value="{{ $match->match_date->toDateString() }}">
                                    <input type="hidden" name="start_time" value="{{ \App\Support\MatchStartTime::toInputValue($match->start_time) }}">
                                    <input type="hidden" name="venue" value="{{ $match->venue }}">
                                    <input type="hidden" name="court" value="{{ $match->court }}">
                                    <input type="hidden" name="sort_order" value="{{ $match->sort_order }}">
                                    @include('admin.league-management.matches._result-fields', [
                                        'prefix' => 'quick-'.$match->id,
                                        'scoreValue' => $match->score,
                                        'winnerSide' => $match->winner_side,
                                    ])
                                    <button class="admin-button" type="submit" style="margin-bottom:2px;">Save result</button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>
            @empty
                <div class="admin-empty-state" style="margin-top:1rem;">
                    <i class="fa-solid fa-calendar-xmark" aria-hidden="true"></i>
                    <p>No matches scheduled for <strong>{{ $activeGroup->name }}</strong> yet. Set the group start date and click <strong>Schedule matches</strong>, or use <strong>Add match</strong> for a manual game.</p>
                </div>
            @endforelse
        @endif
    </section>
@endsection
