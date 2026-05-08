@extends('layouts.admin')

@section('title', 'League Management | '.config('app.name', 'playptl'))
@section('meta_description', 'Manage leagues, group cards, groups, and players from the admin dashboard.')

@section('content')
    <section class="admin-card">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-card-title">League Management</h1>
                <p class="admin-card-text">Pick a league to manage its group cards, age brackets, groups, and player assignments.</p>
            </div>
            <a class="admin-link" href="{{ route('admin.leagues.index') }}">
                <i class="fa-solid fa-trophy" aria-hidden="true"></i>
                <span>Manage leagues</span>
            </a>
        </div>

        @if (session('status'))
            <div class="admin-alert admin-alert-success">{{ session('status') }}</div>
        @endif

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>League</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($leagues as $league)
                        <tr>
                            <td>
                                <strong>{{ $league->name }}</strong>
                                @if ($league->stats)
                                    <span>{{ ucfirst($league->stats) }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="admin-table-actions">
                                    <a href="{{ route('admin.league-management.show', $league) }}" title="Manage"><i class="fa-solid fa-sitemap" aria-hidden="true"></i></a>
                                    <a href="{{ route('admin.leagues.show', $league) }}" title="View league"><i class="fa-solid fa-eye" aria-hidden="true"></i></a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2">
                                <div class="admin-empty-state">
                                    <i class="fa-solid fa-trophy" aria-hidden="true"></i>
                                    <p>No leagues found. Create your first league.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($leagues->hasPages())
            <div class="admin-pagination">
                @if ($leagues->onFirstPage())
                    <span>Previous</span>
                @else
                    <a href="{{ $leagues->previousPageUrl() }}">Previous</a>
                @endif

                <strong>Page {{ $leagues->currentPage() }} of {{ $leagues->lastPage() }}</strong>

                @if ($leagues->hasMorePages())
                    <a href="{{ $leagues->nextPageUrl() }}">Next</a>
                @else
                    <span>Next</span>
                @endif
            </div>
        @endif
    </section>
@endsection

