@extends('layouts.admin')

@section('title', 'Manage Group Cards | '.config('app.name', 'playptl'))
@section('meta_description', 'Manage group cards from the admin dashboard.')

@section('content')
    <section class="admin-card">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-card-title">Manage Group Cards</h1>
                <p class="admin-card-text">Create, edit, view, and delete league group cards.</p>
            </div>
            <a class="admin-button admin-button-link" href="{{ route('admin.group-cards.create') }}">
                <i class="fa-solid fa-plus" aria-hidden="true"></i>
                <span>Add Group Card</span>
            </a>
        </div>

        @if (session('status'))
            <div class="admin-alert admin-alert-success">{{ session('status') }}</div>
        @endif

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Card Name</th>
                        <th>Tag</th>
                        <th>Players</th>
                        <th>Groups</th>
                        <th>Status</th>
                        <th>Order</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($groupCards as $groupCard)
                        <tr>
                            <td><strong>{{ $groupCard->name }}</strong></td>
                            <td>{{ strtoupper($groupCard->tag) }}</td>
                            <td>{{ $groupCard->players_count }}</td>
                            <td>{{ $groupCard->groups_count }}</td>
                            <td><span class="admin-badge">{{ ucfirst($groupCard->status) }}</span></td>
                            <td>{{ $groupCard->display_order }}</td>
                            <td>
                                <div class="admin-table-actions">
                                    <a href="{{ route('admin.group-cards.show', $groupCard) }}" title="View"><i class="fa-solid fa-eye" aria-hidden="true"></i></a>
                                    <a href="{{ route('admin.group-cards.edit', $groupCard) }}" title="Edit"><i class="fa-solid fa-pen" aria-hidden="true"></i></a>
                                    <form method="POST" action="{{ route('admin.group-cards.destroy', $groupCard) }}" onsubmit="return confirm('Delete this group card?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Delete"><i class="fa-solid fa-trash" aria-hidden="true"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="admin-empty-state">
                                    <i class="fa-solid fa-table-cells-large" aria-hidden="true"></i>
                                    <p>No group cards found. Create your first card.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($groupCards->hasPages())
            <div class="admin-pagination">
                @if ($groupCards->onFirstPage())
                    <span>Previous</span>
                @else
                    <a href="{{ $groupCards->previousPageUrl() }}">Previous</a>
                @endif

                <strong>Page {{ $groupCards->currentPage() }} of {{ $groupCards->lastPage() }}</strong>

                @if ($groupCards->hasMorePages())
                    <a href="{{ $groupCards->nextPageUrl() }}">Next</a>
                @else
                    <span>Next</span>
                @endif
            </div>
        @endif
    </section>
@endsection
