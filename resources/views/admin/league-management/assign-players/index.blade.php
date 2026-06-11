@extends('layouts.admin')

@section('title', 'Assign players | '.config('app.name', 'playptl'))
@section('meta_description', 'Assign players to a tournament group.')

@section('content')
    <section class="admin-card">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-card-title">Assign players</h1>
                <p class="admin-card-text">
                    Tournament: <strong>{{ $league->name }}</strong> · Group: <strong>{{ $groupCard->name }}</strong>
                    · Type: <strong>{{ ucfirst($registrationType) }}</strong>
                </p>
                <p class="admin-card-text" style="margin-top: 8px; font-size: 13px;">
                    Only players <strong>not yet assigned</strong> to this group are listed below.
                    Each player can only be in <strong>one {{ $registrationType }} group</strong> per tournament (but may play both singles and doubles in the same tournament).
                    @if (! empty($groupSkillLevel))
                        This group skill tier: <strong>{{ $groupSkillLevel }}</strong>.
                    @endif
                </p>
            </div>
            <a class="admin-link" href="{{ route('admin.league-management.show', $league) }}">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                <span>Back</span>
            </a>
        </div>

        @if (session('status'))
            <div class="admin-alert admin-alert-success">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="admin-alert admin-alert-error">
                <ul style="margin:0; padding-left: 18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (! $schemaReady)
            <div class="admin-alert admin-alert-error">Tournament registration tables are not ready. Run migrations first.</div>
        @else
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Skill level</th>
                            <th>Today</th>
                            <th>Assign</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($players as $player)
                            @php
                                $todayLeague = $todayMatchLeagues[(int) $player->id] ?? null;
                                $playerSkill = $playerSkillLevels[(int) $player->id] ?? null;
                            @endphp
                            <tr>
                                <td><strong>{{ $player->name }}</strong></td>
                                <td>{{ $player->email }}</td>
                                <td>
                                    @if ($playerSkill)
                                        <strong>{{ $playerSkill === 'not-sure' ? 'Not sure' : $playerSkill }}</strong>
                                    @else
                                        <span class="admin-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($todayLeague)
                                        <span class="admin-badge" style="background:#fff3e0;color:#e65100;">
                                            Today: match in {{ $todayLeague }}
                                        </span>
                                    @else
                                        <span class="admin-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('admin.league-management.assign-players.store', [$league, $groupCard]) }}">
                                        @csrf
                                        <input type="hidden" name="user_id" value="{{ $player->id }}">
                                        <button class="admin-button" type="submit" style="padding: 8px 14px; font-size: 13px;">
                                            <i class="fa-solid fa-plus" aria-hidden="true"></i>
                                            Assign
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <div class="admin-empty-state">
                                        <i class="fa-solid fa-user" aria-hidden="true"></i>
                                        <p>No unassigned {{ $registrationType }} players left. Everyone is already in this group, or no players exist on the platform.</p>
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
        @endif
    </section>
@endsection
