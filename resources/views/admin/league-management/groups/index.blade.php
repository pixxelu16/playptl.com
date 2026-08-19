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
                                @if ($isDoublesGroupCard ?? false)
                                    <th>Team Name</th>
                                @endif
                                <th>Photo</th>
                                <th>Payment</th>
                                @if ($isDoublesGroupCard ?? false)
                                    <th>Partner</th>
                                @endif
                                <th>Subgroup</th>
                                @if ($otherGroupCards->isNotEmpty())
                                    <th>Move to group</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($activeGroupRoster as $entry)
                                @php $reg = $entry['registration']; @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $entry['player_names'] ?? $entry['display_name'] }}</strong>
                                        <div style="font-size: 0.85rem; opacity: 0.85;">{{ $entry['display_subtitle'] !== '' ? $entry['display_subtitle'] : '—' }}</div>
                                    </td>
                                    @if ($isDoublesGroupCard ?? false)
                                        <td style="white-space: nowrap;">
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <span style="font-weight: 600; color: #1e293b;">{{ $entry['team_name'] ?: '—' }}</span>
                                                <button type="button" 
                                                    class="admin-icon-btn js-open-team-modal"
                                                    title="Edit Team Name"
                                                    data-team-name="{{ $entry['team_name'] ?: ($reg->team_name ?? '') }}"
                                                    data-player-names="{{ $entry['player_names'] ?? $entry['display_name'] }}"
                                                    data-update-url="{{ route('admin.league-management.players.update-team-name', [$league, $groupCard, $reg]) }}"
                                                    style="background: #f1f5f9; border: 1px solid #cbd5e1; color: #334155; width: 28px; height: 28px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.15s ease;">
                                                    <i class="fa-solid fa-pen-to-square" style="font-size: 13px;"></i>
                                                </button>
                                            </div>
                                        </td>
                                    @endif
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
                                                'currentPartnerUserIdByRegId' => $currentPartnerUserIdByRegId,
                                            ])
                                        </td>
                                    @endif
                                    <td>
                                        <form method="POST" action="{{ route('admin.league-management.players.update-group', [$league, $groupCard, $reg]) }}" class="admin-assign">
                                             @csrf
                                             @method('PUT')
                                             <select class="admin-input select2-search" name="group_id" aria-label="Assign subgroup" data-select2-width="140px" style="width:140px;">
                                                 <option value="">Unassigned</option>
                                                 @foreach ($allGroups as $g)
                                                     <option value="{{ $g->id }}" @selected(($reg->group_id ?? null) == $g->id)>{{ $g->name }}</option>
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
                                                <select class="admin-input select2-search" name="target_group_card_id" aria-label="Target group" data-select2-width="180px" style="width:180px;" required>
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
                            @empty
                                <tr>
                                    <td colspan="{{ (($isDoublesGroupCard ?? false) ? 6 : 4) + ($otherGroupCards->isNotEmpty() ? 1 : 0) }}">
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
                                @if ($isDoublesGroupCard ?? false)
                                    <th>Team Name</th>
                                @endif
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
                                        <strong>{{ $entry['player_names'] ?? $entry['display_name'] }}</strong>
                                        <div style="font-size: 0.85rem; opacity: 0.85;">{{ $entry['display_subtitle'] !== '' ? $entry['display_subtitle'] : '—' }}</div>
                                    </td>
                                    @if ($isDoublesGroupCard ?? false)
                                        <td style="white-space: nowrap;">
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <span style="font-weight: 600; color: #1e293b;">{{ $entry['team_name'] ?: '—' }}</span>
                                                <button type="button" 
                                                    class="admin-icon-btn js-open-team-modal"
                                                    title="Edit Team Name"
                                                    data-team-name="{{ $entry['team_name'] ?: ($reg->team_name ?? '') }}"
                                                    data-player-names="{{ $entry['player_names'] ?? $entry['display_name'] }}"
                                                    data-update-url="{{ route('admin.league-management.players.update-team-name', [$league, $groupCard, $reg]) }}"
                                                    style="background: #f1f5f9; border: 1px solid #cbd5e1; color: #334155; width: 28px; height: 28px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.15s ease;">
                                                    <i class="fa-solid fa-pen-to-square" style="font-size: 13px;"></i>
                                                </button>
                                            </div>
                                        </td>
                                    @endif
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
                                                'currentPartnerUserIdByRegId' => $currentPartnerUserIdByRegId,
                                            ])
                                        </td>
                                    @endif
                                    <td>
                                        <form method="POST" action="{{ route('admin.league-management.players.update-group', [$league, $groupCard, $reg]) }}" class="admin-assign">
                                            @csrf
                                            @method('PUT')
                                            <select class="admin-input select2-search" name="group_id" aria-label="Assign subgroup" data-select2-width="140px" style="width:140px;">
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
                                                <select class="admin-input select2-search" name="target_group_card_id" aria-label="Target group" data-select2-width="180px" style="width:180px;" required>
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

    <!-- Edit Team Name Modal -->
    <div id="editTeamNameModal" style="display:none; position: fixed; z-index: 99999; inset: 0; background: rgba(15, 23, 42, 0.5); backdrop-filter: blur(3px); align-items: center; justify-content: center; padding: 1rem;">
        <div style="background: #ffffff; border-radius: 14px; width: 100%; max-width: 480px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); overflow: hidden;">
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 1.25rem 1.5rem; border-bottom: 1px solid #e2e8f0; background: #f8fafc;">
                <div>
                    <h3 style="margin: 0; font-size: 1.15rem; font-weight: 700; color: #0f172a;">Edit Team Name</h3>
                    <p id="modalPlayerNames" style="margin: 4px 0 0 0; font-size: 0.85rem; color: #64748b; font-weight: 500;"></p>
                </div>
                <button type="button" class="js-close-team-modal" style="background: none; border: none; font-size: 1.5rem; line-height: 1; color: #94a3b8; cursor: pointer; padding: 4px 8px; border-radius: 6px;" aria-label="Close">&times;</button>
            </div>
            <form id="editTeamNameForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div style="padding: 1.5rem;">
                    <label for="modalTeamNameInput" style="display: block; font-size: 0.875rem; font-weight: 600; color: #334155; margin-bottom: 0.5rem;">Team Name</label>
                    <input type="text" id="modalTeamNameInput" name="team_name" class="admin-input" placeholder="e.g. Double Trouble" style="width: 100%; height: 42px; border-radius: 8px; border: 1px solid #cbd5e1; padding: 0 12px; font-size: 0.95rem; box-sizing: border-box;" required autofocus>
                    <p style="margin: 0.5rem 0 0 0; font-size: 0.8rem; color: #64748b;">This team name will be displayed across brackets, match schedules, and standings.</p>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px; padding: 1rem 1.5rem; background: #f8fafc; border-top: 1px solid #e2e8f0;">
                    <button type="button" class="admin-button admin-button-secondary js-close-team-modal" style="padding: 8px 16px; border-radius: 6px;">Cancel</button>
                    <button type="submit" class="admin-button" style="padding: 8px 20px; border-radius: 6px; background: #5fa252; color: #ffffff; border: none; font-weight: 600; cursor: pointer;">Update Team Name</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var modal = document.getElementById('editTeamNameModal');
            var form = document.getElementById('editTeamNameForm');
            var input = document.getElementById('modalTeamNameInput');
            var playerNamesEl = document.getElementById('modalPlayerNames');

            document.querySelectorAll('.js-open-team-modal').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var updateUrl = btn.getAttribute('data-update-url');
                    var currentTeamName = btn.getAttribute('data-team-name') || '';
                    var playerNames = btn.getAttribute('data-player-names') || '';

                    form.setAttribute('action', updateUrl);
                    input.value = currentTeamName;
                    playerNamesEl.textContent = playerNames ? 'Players: ' + playerNames : '';

                    modal.style.display = 'flex';
                    setTimeout(function () { input.focus(); }, 50);
                });
            });

            document.querySelectorAll('.js-close-team-modal').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    modal.style.display = 'none';
                });
            });

            modal.addEventListener('click', function (e) {
                if (e.target === modal) {
                    modal.style.display = 'none';
                }
            });
        });
    </script>
    @endpush
@endsection
