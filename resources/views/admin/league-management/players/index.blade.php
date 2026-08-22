@extends('layouts.admin')

@section('title', 'Players | '.$league->name.' | '.config('app.name', 'playptl'))
@section('meta_description', 'View players/registrations and assign them to subgroups.')

@section('content')
    <section class="admin-card">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-card-title">Players — {{ $league->name }}</h1>
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
                'activeGroupId' => 0,
                'playerSchemaReady' => $schemaReady,
                'active' => 'players',
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
                    flex-wrap: wrap;
                }
                .admin-assign .admin-input {
                    width: 200px;
                    max-width: 200px;
                }
            }
        </style>

        @if (! $schemaReady)
            <div class="admin-alert admin-alert-error">
                Player assignment schema not ready yet. Run migrations for `league_registrations` (`group_id`, `age_group_key`) and `groups` (`group_card_id`, `age_group_key`).
            </div>
        @endif

        @if (session('status'))
            <div class="admin-alert admin-alert-success">{{ session('status') }}</div>
        @endif

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
                        <th>Assigned Subgroup</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rosterEntries as $entry)
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
                                        @foreach ($groups as $g)
                                            <option value="{{ $g->id }}" @selected(($reg->group_id ?? null) == $g->id)>{{ $g->name }}</option>
                                        @endforeach
                                    </select>
                                    <button class="admin-button" type="submit">Update</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ ($isDoublesGroupCard ?? false) ? 6 : 4 }}">
                                <div class="admin-empty-state">
                                    <i class="fa-solid fa-user" aria-hidden="true"></i>
                                    <p>No registrations found for this tournament/card filter.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($rosterEntries->hasPages())
            <div class="admin-pagination">
                @if ($rosterEntries->onFirstPage())
                    <span>Previous</span>
                @else
                    <a href="{{ $rosterEntries->previousPageUrl() }}">Previous</a>
                @endif

                <strong>Page {{ $rosterEntries->currentPage() }} of {{ $rosterEntries->lastPage() }}</strong>

                @if ($rosterEntries->hasMorePages())
                    <a href="{{ $rosterEntries->nextPageUrl() }}">Next</a>
                @else
                    <span>Next</span>
                @endif
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

