@extends('layouts.admin')

@section('title', 'All Players | '.config('app.name', 'playptl'))
@section('meta_description', 'View all players registered on the platform.')

@section('content')
    @php
        $indexQuery = array_filter([
            'league_id' => $leagueId,
            'skill_sort' => $skillSort,
            'search' => $search ?? '',
            'page' => $players->currentPage() > 1 ? $players->currentPage() : null,
        ], fn ($value) => $value !== null && $value !== '');
        $nextSkillSort = $skillSort === 'asc' ? 'desc' : 'asc';
        $skillSortQuery = array_merge($indexQuery, ['skill_sort' => $nextSkillSort]);
    @endphp

    <section class="admin-card">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-card-title">All Players</h1>
                <p class="admin-card-text">View registered players. New players join via the website register page or their player profile — not from admin.</p>
            </div>
        </div>

        @if (session('status'))
            <div class="admin-alert admin-alert-success">{{ session('status') }}</div>
        @endif

        <div class="admin-table-wrap" style="margin-bottom: 14px;">
            <form method="GET" action="{{ route('admin.players.index') }}" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
                <input type="hidden" name="skill_sort" value="{{ $skillSort }}">
                <div>
                    <label class="admin-label" for="search">Search</label>
                    <input class="admin-input" type="text" name="search" id="search" value="{{ $search ?? '' }}" placeholder="Name, email, phone, city..." style="min-width:220px;">
                </div>
                <div>
                    <label class="admin-label" for="league_id">Tournament</label>
                    <select class="admin-input" name="league_id" id="league_id">
                        <option value="" @selected($leagueId === null)>All</option>
                        @foreach ($leagues as $league)
                            <option value="{{ $league->id }}" @selected($leagueId === (int) $league->id)>{{ $league->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="admin-button" type="submit" style="padding:10px 16px;">
                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                    <span>Search</span>
                </button>
            </form>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Photo</th>
                        <th>Name</th>
                        <th>Login as</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>City</th>
                        <th>State</th>
                        <th>Sex</th>
                        <th>
                            <a href="{{ route('admin.players.index', $skillSortQuery) }}" class="admin-link" style="font-weight:700;">
                                Skill Level
                                @if ($skillSort === 'asc')
                                    <i class="fa-solid fa-arrow-up-short-wide" aria-hidden="true"></i>
                                @else
                                    <i class="fa-solid fa-arrow-down-wide-short" aria-hidden="true"></i>
                                @endif
                            </a>
                        </th>
                        <th>Status</th>
                        <th>Active tournaments</th>
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
                            <td>
                                @php $loginAs = strtolower((string) ($player->registration_type ?? '')); @endphp
                                @if ($loginAs === 'singles' || $loginAs === 'doubles')
                                    <span class="admin-badge">{{ ucfirst($loginAs) }}</span>
                                @else
                                    <span class="admin-muted">—</span>
                                @endif
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
                            <td style="min-width:200px;">
                                @php $activeTournaments = $playerActiveTournaments[(int) $player->id] ?? []; @endphp
                                @if ($activeTournaments !== [])
                                    <div style="display:flex;flex-direction:column;gap:8px;">
                                        @foreach ($activeTournaments as $tournament)
                                            <div style="padding:6px 8px;border:1px solid #d7ead9;border-radius:6px;background:#f9fbf9;">
                                                <div style="font-weight:700;font-size:13px;color:#333;">{{ $tournament['tournament'] }}</div>
                                                <div style="font-size:11px;color:#5a9048;margin-top:2px;">{{ $tournament['window'] }}</div>
                                                @foreach ($tournament['registrations'] as $entry)
                                                    <div style="font-size:11px;color:#666;margin-top:4px;">
                                                        {{ $entry['group'] }}
                                                        @if (($entry['subgroup'] ?? '') !== 'Unassigned')
                                                            · {{ $entry['subgroup'] }}
                                                        @endif
                                                        · {{ $entry['format'] }}
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="admin-muted">—</span>
                                @endif
                            </td>
                            <td>{{ $player->created_at?->format('M d, Y') ?? '-' }}</td>
                            <td>
                                <div class="admin-table-actions">
                                    <a href="{{ route('admin.players.edit', ['player' => $player] + $indexQuery) }}" title="Edit player">
                                        <i class="fa-solid fa-pen" aria-hidden="true"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.players.destroy', ['player' => $player] + $indexQuery) }}" onsubmit="return confirm('Delete this player? This will also remove their registrations and payments history.');" style="display:inline;">
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
                            <td colspan="14">
                                <div class="admin-empty-state">
                                    <i class="fa-solid fa-user" aria-hidden="true"></i>
                                    <p>No players found{{ $leagueId ? ' for this tournament' : '' }}{{ ($search ?? '') !== '' ? ' matching your search' : '' }}.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($players->total() > 0)
            <p class="admin-muted" style="margin-top:14px;">
                Showing {{ $players->firstItem() }}–{{ $players->lastItem() }} of {{ $players->total() }} players
            </p>
        @endif

        @if ($players->hasPages())
            @php
                $pageStart = max(1, $players->currentPage() - 2);
                $pageEnd = min($players->lastPage(), $players->currentPage() + 2);
            @endphp
            <div class="admin-pagination">
                @if ($players->onFirstPage())
                    <span>Previous</span>
                @else
                    <a href="{{ $players->previousPageUrl() }}">Previous</a>
                @endif

                @if ($pageStart > 1)
                    <a href="{{ $players->url(1) }}">1</a>
                    @if ($pageStart > 2)
                        <span>…</span>
                    @endif
                @endif

                @for ($page = $pageStart; $page <= $pageEnd; $page++)
                    @if ($page === $players->currentPage())
                        <strong>{{ $page }}</strong>
                    @else
                        <a href="{{ $players->url($page) }}">{{ $page }}</a>
                    @endif
                @endfor

                @if ($pageEnd < $players->lastPage())
                    @if ($pageEnd < $players->lastPage() - 1)
                        <span>…</span>
                    @endif
                    <a href="{{ $players->url($players->lastPage()) }}">{{ $players->lastPage() }}</a>
                @endif

                @if ($players->hasMorePages())
                    <a href="{{ $players->nextPageUrl() }}">Next</a>
                @else
                    <span>Next</span>
                @endif
            </div>
        @endif
    </section>
@endsection
