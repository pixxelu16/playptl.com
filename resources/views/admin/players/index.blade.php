@extends('layouts.admin')

@section('title', 'All Players | '.config('app.name', 'playptl'))
@section('meta_description', 'View all players registered on the platform.')

@section('content')
    <section class="admin-card">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-card-title">All Players</h1>
                <p class="admin-card-text">View registered players. Use tabs to switch between Singles and Doubles.</p>
            </div>
            <a class="admin-button admin-button-link" href="{{ route('admin.players.create', ['tab' => $tab]) }}">
                <i class="fa-solid fa-plus" aria-hidden="true"></i>
                <span>Add Player</span>
            </a>
        </div>

        @if (session('status'))
            <div class="admin-alert admin-alert-success">{{ session('status') }}</div>
        @endif

        <div class="admin-table-wrap" style="margin-bottom: 14px;">
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <a class="admin-button admin-button-link {{ $tab === 'singles' ? '' : 'admin-button-secondary' }}"
                   href="{{ route('admin.players.index', ['tab' => 'singles']) }}">
                    Singles
                </a>
                <a class="admin-button admin-button-link {{ $tab === 'doubles' ? '' : 'admin-button-secondary' }}"
                   href="{{ route('admin.players.index', ['tab' => 'doubles']) }}">
                    Doubles
                </a>
            </div>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Photo</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>City</th>
                        <th>State</th>
                        <th>Sex</th>
                        <th>Skill Level</th>
                        <th>Status</th>
                        <th>Registered</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($players as $player)
                        <tr>
                            <td>{{ $player->id }}</td>
                            <td>
                                @php $avatarSrc = $player->avatar_path ?: 'upload/user-avatar/default-user-pic.png'; @endphp
                                <img src="{{ asset($avatarSrc) }}" alt="Avatar" width="48" height="48" style="width:48px;height:48px;border-radius:999px;object-fit:cover;border:1px solid #d7ead9;">
                            </td>
                            <td>
                                @php
                                    $rawName = (string) ($player->name ?? '');
                                    $displayName = trim(preg_split('/\s*&\s*/', $rawName)[0] ?? $rawName);
                                @endphp
                                <strong>{{ $displayName !== '' ? $displayName : '—' }}</strong>
                            </td>
                            <td>{{ $player->email }}</td>
                            <td>{{ $player->phone ?? '-' }}</td>
                            <td>{{ $player->city ?? '-' }}</td>
                            <td>{{ $player->state ?? '-' }}</td>
                            <td>{{ $player->sex ?? '-' }}</td>
                            <td>
                                @php $skillLevel = \App\Support\UserSkillLevel::resolvedFor($player); @endphp
                                @if ($skillLevel === 'not-sure')
                                    Not Sure
                                @elseif ($skillLevel)
                                    {{ $skillLevel }}
                                @else
                                    —
                                @endif
                            </td>
                            <td><span class="admin-badge">{{ ucfirst($player->status ?? 'active') }}</span></td>
                            <td>{{ $player->created_at?->format('M d, Y') ?? '-' }}</td>
                            <td>
                                <div class="admin-table-actions">
                                    <a href="{{ route('admin.players.edit', ['player' => $player, 'tab' => $tab]) }}" title="Edit player">
                                        <i class="fa-solid fa-pen" aria-hidden="true"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.players.destroy', ['player' => $player, 'tab' => $tab]) }}" onsubmit="return confirm('Delete this player? This will also remove their registrations and payments history.');" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Delete player" style="background:none;border:0;padding:0;cursor:pointer;color:inherit;">
                                            <i class="fa-solid fa-trash" aria-hidden="true"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12">
                                <div class="admin-empty-state">
                                    <i class="fa-solid fa-user" aria-hidden="true"></i>
                                    <p>No {{ $tab }} players found.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($players->hasPages())
            <div class="admin-pagination">
                @if ($players->onFirstPage())
                    <span>Previous</span>
                @else
                    <a href="{{ $players->previousPageUrl() }}">Previous</a>
                @endif

                <strong>Page {{ $players->currentPage() }} of {{ $players->lastPage() }}</strong>

                @if ($players->hasMorePages())
                    <a href="{{ $players->nextPageUrl() }}">Next</a>
                @else
                    <span>Next</span>
                @endif
            </div>
        @endif
    </section>
@endsection
