@extends('layouts.admin')

@section('title', 'Manage Leagues | '.config('app.name', 'playptl'))
@section('meta_description', 'Manage leagues from the admin dashboard.')

@section('content')
    <section class="admin-card">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-card-title">Manage Leagues</h1>
                <p class="admin-card-text">Create, edit, view, and delete league records.</p>
            </div>
            <a class="admin-button admin-button-link" href="{{ route('admin.leagues.create') }}">
                <i class="fa-solid fa-plus" aria-hidden="true"></i>
                <span>Add League</span>
            </a>
        </div>

        @if (session('status'))
            <div class="admin-alert admin-alert-success">{{ session('status') }}</div>
        @endif

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Logo</th>
                        <th>League Name</th>
                        <th>Type</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($leagues as $league)
                        <tr>
                            <td>
                                @if ($league->logo_path)
                                    <img class="admin-table-logo" src="{{ asset($league->logo_path) }}" alt="{{ $league->name }} logo">
                                @else
                                    <span class="admin-logo-placeholder">NA</span>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $league->name }}</strong>
                                @if ($league->description)
                                    <span>{{ Str::limit($league->description, 70) }}</span>
                                @endif
                            </td>
                            <td><span class="admin-badge">{{ ucfirst($league->type) }}</span></td>
                            <td>{{ $league->start_date?->format('M d, Y') ?? '-' }}</td>
                            <td>{{ $league->end_date?->format('M d, Y') ?? '-' }}</td>
                            <td>
                                <div class="admin-table-actions">
                                    <a href="{{ route('admin.leagues.show', $league) }}" title="View"><i class="fa-solid fa-eye" aria-hidden="true"></i></a>
                                    <a href="{{ route('admin.leagues.edit', $league) }}" title="Edit"><i class="fa-solid fa-pen" aria-hidden="true"></i></a>
                                    <form method="POST" action="{{ route('admin.leagues.destroy', $league) }}" onsubmit="return confirm('Delete this league?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Delete"><i class="fa-solid fa-trash" aria-hidden="true"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
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
