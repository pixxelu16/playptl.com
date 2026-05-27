@extends('layouts.admin')

@section('title', 'Qualifier | '.$league->name.' | '.config('app.name', 'playptl'))
@section('meta_description', 'Playoff paths from group format and standings (read-only).')

@section('content')
    <section class="admin-card">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-card-title">Qualifier — {{ $league->name }}</h1>
                <p class="admin-card-text">
                    Group: <strong>{{ $groupCard->name }}</strong>
                    @if ($ageGroupKey)
                        · Age: <strong>{{ $ageGroupKey }}</strong>
                    @endif
                    · Format: <strong>{{ $playoffConfig->format->label() }}</strong>
                </p>
            </div>
            @include('admin.league-management.partials.group-card-header-actions', [
                'league' => $league,
                'groupCard' => $groupCard,
                'ageGroupKey' => $ageGroupKey,
                'activeGroupId' => $groups->first()?->id ?? 0,
                'playerSchemaReady' => $playerSchemaReady,
                'active' => 'qualifier',
            ])
        </div>

        @if (session('status'))
            <div class="admin-alert" style="margin-top:1rem;background:#f4faf4;border-color:#c5dfc6;color:#2f4a2d;">
                {{ session('status') }}
            </div>
        @endif

        @if ($playerSchemaReady)
            @include('admin.league-management.partials.group-card-standings-nav', [
                'league' => $league,
                'groupCard' => $groupCard,
                'ageGroupKey' => $ageGroupKey,
                'activeGroupId' => $groups->first()?->id ?? 0,
                'standingsSection' => 'qualifier',
            ])
        @endif

        @if (! $playerSchemaReady)
            <div class="admin-alert admin-alert-error" style="margin-top: 1rem;">
                Player roster is not ready. Run migrations and assign players first.
            </div>
        @elseif (! ($showQualifierPlayoffs ?? false))
            <div class="admin-empty-state" style="margin-top: 1.5rem;">
                <i class="fa-solid fa-hourglass-half" aria-hidden="true"></i>
                <p>{{ $qualifierUnavailableMessage ?? 'Qualifier is not available yet.' }}</p>
                <p style="margin-top:0.75rem;">
                    <a class="admin-link" href="{{ route('admin.league-management.matches.index', [$league, $groupCard] + ($ageGroupKey ? ['age_group_key' => $ageGroupKey] : [])) }}">
                        <i class="fa-solid fa-calendar-days" aria-hidden="true"></i>
                        <span>Go to Matches</span>
                    </a>
                </p>
            </div>
        @elseif ($qualifierRows === [])
            <div class="admin-empty-state" style="margin-top: 1.5rem;">
                <i class="fa-solid fa-filter" aria-hidden="true"></i>
                <p>No players in this group yet.</p>
            </div>
        @else
            <div class="admin-alert" style="margin:1.25rem 0 0;background:#f4faf4;border-color:#c5dfc6;color:#2f4a2d;">
                <strong>{{ $playoffConfig->format->label() }}</strong>
                <p style="margin:0.5rem 0 0;line-height:1.5;">
                    {!! $playoffConfig->descriptionHtml() !!}
                </p>
                <p style="margin:0.5rem 0 0;line-height:1.5;font-size:0.9rem;">
                    Paths are set automatically from standings and the playoff format on
                    <a class="admin-link" href="{{ route('admin.group-cards.edit', $groupCard) }}">Edit group</a>.
                    You can add optional notes below.
                </p>
            </div>

            @if ($syncStatus)
                <p class="admin-muted" style="margin:0.75rem 0 0;font-size:0.85rem;">{{ $syncStatus }}</p>
            @endif

            <div style="display:flex;flex-wrap:wrap;gap:0.75rem;margin:1.25rem 0 1.25rem;">
                @if ($hasSavedPaths)
                    <form
                        method="POST"
                        action="{{ route('admin.league-management.qualifier.clear', [$league, $groupCard] + ($ageGroupKey ? ['age_group_key' => $ageGroupKey] : [])) }}"
                        data-admin-confirm
                        data-admin-confirm-title="Clear playoff data?"
                        data-admin-confirm-message="All playoff paths and bracket matches for {{ $groupCard->name }} will be removed. Re-open this page to re-apply from group format."
                        data-admin-confirm-button="Clear all"
                    >
                        @csrf
                        <button type="submit" class="admin-button admin-button-secondary">
                            <i class="fa-solid fa-eraser" aria-hidden="true"></i>
                            <span>Clear playoff data</span>
                        </button>
                    </form>
                @endif
                <a class="admin-link" href="{{ route('admin.league-management.playoffs.index', [$league, $groupCard] + ($ageGroupKey ? ['age_group_key' => $ageGroupKey] : [])) }}">
                    <i class="fa-solid fa-sitemap" aria-hidden="true"></i>
                    <span>Open playoffs</span>
                </a>
            </div>

            <form method="POST" action="{{ route('admin.league-management.qualifier.update', [$league, $groupCard] + ($ageGroupKey ? ['age_group_key' => $ageGroupKey] : [])) }}">
                @csrf
                @method('PUT')
                <div class="admin-table-wrap">
                    <table class="admin-table qualifier-table">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Player</th>
                                <th style="text-align:center;">PF</th>
                                <th style="text-align:center;">PA</th>
                                <th style="text-align:right;">Game%</th>
                                <th>Playoff path</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($qualifierRows as $i => $row)
                                <tr>
                                    <td>{{ str_pad((string) $row['rank'], 2, '0', STR_PAD_LEFT) }}</td>
                                    <td>
                                        <input type="hidden" name="players[{{ $i }}][user_id]" value="{{ $row['userId'] }}">
                                        <strong>{{ $row['name'] }}</strong>
                                        <span class="admin-muted" style="display:block;font-size:0.8rem;">{{ $row['groupName'] ?? '—' }}</span>
                                    </td>
                                    <td style="text-align:center;font-weight:700;">{{ $row['points'] ?? 0 }}</td>
                                    <td style="text-align:center;">{{ $row['pointsAgainst'] ?? 0 }}</td>
                                    <td style="text-align:right;color:#2f7a2a;">{{ $row['gamePct'] ?? 0 }}%</td>
                                    <td>
                                        <input
                                            type="text"
                                            class="admin-input"
                                            value="{{ $row['pathLabel'] }}"
                                            placeholder=""
                                            disabled
                                            readonly
                                            style="min-width:11rem;background:{{ $row['pathLabel'] !== '' ? '#f3f4f6' : '#fff' }};color:#374151;cursor:not-allowed;"
                                        >
                                    </td>
                                    <td>
                                        <input type="text" name="players[{{ $i }}][notes]" class="admin-input" value="{{ $row['notes'] }}" placeholder="Optional" maxlength="255">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div style="margin-top:1.25rem;">
                    <button type="submit" class="admin-button">
                        <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>
                        <span>Save notes</span>
                    </button>
                </div>
            </form>
        @endif
    </section>
@endsection

@push('styles')
    <style>
        .admin-group-tabs--section { margin-top: 1rem; margin-bottom: 0.25rem; }
        .qualifier-table input.admin-input { font-size: 0.85rem; padding: 0.35rem 0.5rem; }
    </style>
@endpush
