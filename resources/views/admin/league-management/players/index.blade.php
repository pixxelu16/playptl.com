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
                        <th>Photo</th>
                        <th>Payment</th>
                        <th>Assigned Subgroup</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rosterEntries as $entry)
                        @php $reg = $entry['registration']; @endphp
                        <tr>
                            <td>
                                <strong>{{ $entry['display_name'] }}</strong>
                                <span>{{ $entry['display_subtitle'] !== '' ? $entry['display_subtitle'] : '—' }}</span>
                            </td>
                            <td>
                                @php $avatarSrc = $entry['user']?->avatar_path ?: 'upload/user-avatar/default-user-pic.png'; @endphp
                                <img src="{{ asset($avatarSrc) }}" alt="Avatar" width="48" height="48" style="width:48px;height:48px;border-radius:999px;object-fit:cover;border:1px solid #d7ead9;">
                            </td>
                            <td>
                                <span class="admin-badge">{{ ucfirst($reg->payment_status ?? 'pending') }}</span>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.league-management.players.update-group', [$league, $groupCard, $reg]) }}" class="admin-assign">
                                    @csrf
                                    @method('PUT')
                                    <select class="admin-input" name="group_id" aria-label="Assign subgroup">
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
                            <td colspan="4">
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
@endsection

