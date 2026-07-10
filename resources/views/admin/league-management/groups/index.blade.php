@extends('layouts.admin')

@section('title', 'Subgroups & players | '.$league->name.' | '.config('app.name', 'playptl'))
@section('meta_description', 'Manage subgroups and player assignments for a tournament group.')

@section('content')
    <section class="admin-card">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-card-title">Subgroups &amp; players — {{ $league->name }}</h1>
                <p class="admin-card-text">
                    Group: <strong>{{ $groupCard->name }}</strong>
                    @if ($ageGroupKey)
                        · Age: <strong>{{ $ageGroupKey }}</strong>
                    @endif
                </p>
            </div>
            @include('admin.league-management.partials.group-card-header-actions', [
                'league' => $league,
                'groupCard' => $groupCard,
                'ageGroupKey' => $ageGroupKey,
                'activeGroupId' => $activeGroupId,
                'playerSchemaReady' => $playerSchemaReady,
                'active' => 'groups',
            ])
        </div>

        <style>
            .admin-assign {
                display: flex;
                align-items: center;
                justify-content: flex-start;
                gap: 10px;
                flex-wrap: nowrap;
            }
            .admin-assign .admin-input {
                min-width: 0;
                width: 220px;
                max-width: 220px;
                padding: 10px 12px;
            }
            .admin-assign .admin-button {
                padding: 10px 14px;
                border-radius: 10px;
                min-width: 92px;
                justify-content: center;
            }
            @media (max-width: 860px) {
                .admin-assign {
                    justify-content: flex-start;
                }
                .admin-assign .admin-input {
                    width: 200px;
                    max-width: 200px;
                }
            }
        </style>

        <div style="display:flex; flex-wrap:wrap; gap:12px; align-items:center; justify-content:space-between; margin: 12px 0 0;">
            <form method="GET" action="{{ route('admin.league-management.groups.index', ['league' => $league, 'groupCard' => $groupCard]) }}" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                @if ($ageGroupKey)
                    <input type="hidden" name="age_group_key" value="{{ $ageGroupKey }}">
                @endif
                @if ($activeGroupId)
                    <input type="hidden" name="group" value="{{ $activeGroupId }}">
                @endif
                <input class="admin-input" type="text" name="q" value="{{ $groupSearch ?? '' }}" placeholder="Search subgroups..." style="max-width: 320px; padding: 10px 12px;">
                <button class="admin-button admin-button-secondary" type="submit" style="padding: 10px 14px;">Filter</button>
                @if (! empty($groupSearch))
                    <a class="admin-link" href="{{ route('admin.league-management.groups.index', ['league' => $league, 'groupCard' => $groupCard] + ($ageGroupKey ? ['age_group_key' => $ageGroupKey] : []) + ($activeGroupId ? ['group' => $activeGroupId] : [])) }}">Clear</a>
                @endif
            </form>

            @if (method_exists($groups, 'hasPages') && $groups->hasPages())
                <div class="admin-pagination" style="margin: 0;">
                    @if ($groups->onFirstPage())
                        <span>Previous</span>
                    @else
                        <a href="{{ $groups->previousPageUrl() }}">Previous</a>
                    @endif

                    <strong>Page {{ $groups->currentPage() }} of {{ $groups->lastPage() }}</strong>

                    @if ($groups->hasMorePages())
                        <a href="{{ $groups->nextPageUrl() }}">Next</a>
                    @else
                        <span>Next</span>
                    @endif
                </div>
            @endif
        </div>

        <div class="admin-group-tabs" aria-label="Subgroup tabs">
            @foreach ($groups as $g)
                <a class="admin-group-tab {{ (int) $g->id === (int) $activeGroupId ? 'is-active' : '' }}"
                   href="{{ route('admin.league-management.groups.index', ['league' => $league, 'groupCard' => $groupCard] + ($ageGroupKey ? ['age_group_key' => $ageGroupKey] : []) + ['group' => $g->id]) }}#group-{{ $g->id }}">
                    <span>{{ $g->name }}</span>
                    @if ($playerSchemaReady)
                        <span class="admin-group-pill">{{ (int) ($g->roster_count ?? 0) }}</span>
                    @endif
                </a>
            @endforeach
        </div>

        @if (! $schemaReady)
            <div class="admin-alert admin-alert-error">
                Subgroups table not ready. Run migrations first.
            </div>
        @endif

        @if (! $playerSchemaReady)
            <div class="admin-alert admin-alert-error">
                Player assignments need <code>league_registrations.group_id</code> and <code>group_card_id</code>. Run migrations, then refresh.
            </div>
        @endif

        @if (session('status'))
            <div class="admin-alert admin-alert-success">{{ session('status') }}</div>
        @endif

        @if ($playerSchemaReady && $activeGroup)
            <div class="admin-card" style="margin: 1.5rem 0; padding: 1.25rem 1.5rem; box-shadow: none; border: 1px solid rgba(0,0,0,0.08);">
                <h2 class="admin-card-title" style="font-size: 1.1rem; margin-bottom: 0.75rem;">
                    {{ $activeGroup->name }}
                    <span class="admin-badge" style="margin-left: 0.5rem;">{{ $activeGroup->roster_count }} {{ $activeGroup->roster_count === 1 ? 'entry' : 'entries' }}</span>
                </h2>
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Player</th>
                                <th>Photo</th>
                                <th>Payment</th>
                                @if ($isDoublesGroupCard ?? false)
                                    <th>Partner</th>
                                @endif
                                <th>Subgroup</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($activeGroupRoster as $entry)
                                @php $reg = $entry['registration']; @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $entry['display_name'] }}</strong>
                                        <div style="font-size: 0.85rem; opacity: 0.85;">{{ $entry['display_subtitle'] !== '' ? $entry['display_subtitle'] : '—' }}</div>
                                    </td>
                                    <td>
                                        @php $avatarSrc = $entry['user']?->avatar_path ?: 'upload/user-avatar/default-user-pic.png'; @endphp
                                        <img src="{{ asset($avatarSrc) }}" alt="Avatar" width="48" height="48" style="width:48px;height:48px;border-radius:999px;object-fit:cover;border:1px solid #d7ead9;">
                                    </td>
                                    <td>
                                        <span class="admin-badge">{{ ucfirst($reg->payment_status ?? 'pending') }}</span>
                                    </td>
                                    @if ($isDoublesGroupCard ?? false)
                                        <td>
                                            @include('admin.league-management.partials.partner-assign-field', [
                                                'league' => $league,
                                                'groupCard' => $groupCard,
                                                'reg' => $reg,
                                                'partnerOptionsByRegId' => $partnerOptionsByRegId,
                                                'currentPartnerRegIdByRegId' => $currentPartnerRegIdByRegId,
                                            ])
                                        </td>
                                    @endif
                                    <td>
                                        <form method="POST" action="{{ route('admin.league-management.players.update-group', [$league, $groupCard, $reg]) }}" class="admin-assign">
                                            @csrf
                                            @method('PUT')
                                            <select class="admin-input" name="group_id" aria-label="Assign subgroup">
                                                <option value="">Unassigned</option>
                                                @foreach ($allGroups as $g)
                                                    <option value="{{ $g->id }}" @selected(($reg->group_id ?? null) == $g->id)>{{ $g->name }}</option>
                                                @endforeach
                                            </select>
                                            <button class="admin-button" type="submit">Update</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ ($isDoublesGroupCard ?? false) ? 5 : 4 }}">
                                        <p class="admin-card-text" style="margin:0;">No players in this subgroup yet. Assign them from <strong>Unassigned</strong> below or <strong>All players</strong>.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @elseif ($groups->count() === 0)
            <div class="admin-empty-state" style="margin-top: 20px;">
                <i class="fa-solid fa-users-line" aria-hidden="true"></i>
                <p>No subgroups found for this group.</p>
            </div>
        @endif

        @if ($playerSchemaReady && $unassignedRoster->isNotEmpty())
            <div class="admin-card" style="margin-bottom: 1.5rem; padding: 1.25rem 1.5rem; box-shadow: none; border: 1px dashed rgba(0,0,0,0.15);" id="unassigned-players">
                <h2 class="admin-card-title" style="font-size: 1.1rem; margin-bottom: 0.75rem;">
                    Unassigned
                    <span class="admin-badge" style="margin-left: 0.5rem;">{{ $unassignedRoster->count() }}</span>
                </h2>
                <p class="admin-card-text" style="margin-bottom: 1rem;">
                    These players are in this group but not in a subgroup yet.
                    @if ($otherGroupCards->isNotEmpty())
                        Use <strong>Move to group</strong> to send them to another division (they will disappear from this list).
                    @endif
                </p>
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Player</th>
                                <th>Photo</th>
                                <th>Payment</th>
                                @if ($isDoublesGroupCard ?? false)
                                    <th>Partner</th>
                                @endif
                                <th>Assign to subgroup</th>
                                @if ($otherGroupCards->isNotEmpty())
                                    <th>Move to group</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($unassignedRoster as $entry)
                                @php $reg = $entry['registration']; @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $entry['display_name'] }}</strong>
                                        <div style="font-size: 0.85rem; opacity: 0.85;">{{ $entry['display_subtitle'] !== '' ? $entry['display_subtitle'] : '—' }}</div>
                                    </td>
                                    <td>
                                        @php $avatarSrc = $entry['user']?->avatar_path ?: 'upload/user-avatar/default-user-pic.png'; @endphp
                                        <img src="{{ asset($avatarSrc) }}" alt="Avatar" width="48" height="48" style="width:48px;height:48px;border-radius:999px;object-fit:cover;border:1px solid #d7ead9;">
                                    </td>
                                    <td>
                                        <span class="admin-badge">{{ ucfirst($reg->payment_status ?? 'pending') }}</span>
                                    </td>
                                    @if ($isDoublesGroupCard ?? false)
                                        <td>
                                            @include('admin.league-management.partials.partner-assign-field', [
                                                'league' => $league,
                                                'groupCard' => $groupCard,
                                                'reg' => $reg,
                                                'partnerOptionsByRegId' => $partnerOptionsByRegId,
                                                'currentPartnerRegIdByRegId' => $currentPartnerRegIdByRegId,
                                            ])
                                        </td>
                                    @endif
                                    <td>
                                        <form method="POST" action="{{ route('admin.league-management.players.update-group', [$league, $groupCard, $reg]) }}" class="admin-assign">
                                            @csrf
                                            @method('PUT')
                                            <select class="admin-input" name="group_id" aria-label="Assign subgroup">
                                                <option value="">Unassigned</option>
                                                @foreach ($allGroups as $g)
                                                    <option value="{{ $g->id }}">{{ $g->name }}</option>
                                                @endforeach
                                            </select>
                                            <button class="admin-button" type="submit">Update</button>
                                        </form>
                                    </td>
                                    @if ($otherGroupCards->isNotEmpty())
                                        <td>
                                            <form method="POST" action="{{ route('admin.league-management.players.update-subgroup', [$league, $groupCard, $reg]) }}" class="admin-assign">
                                                @csrf
                                                @method('PUT')
                                                <select class="admin-input" name="target_group_card_id" aria-label="Target group" required>
                                                    <option value="">Choose group</option>
                                                    @foreach ($otherGroupCards as $card)
                                                        <option value="{{ $card->id }}">{{ $card->name }} ({{ ucfirst($card->tag ?? 'mixed') }})</option>
                                                    @endforeach
                                                </select>
                                                <button class="admin-button admin-button-secondary" type="submit">Move</button>
                                            </form>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </section>
@endsection
