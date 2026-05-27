@extends('layouts.admin')

@section('title', 'Manage Groups | '.config('app.name', 'playptl'))
@section('meta_description', 'Manage groups from the admin dashboard.')

@section('content')
    <section class="admin-card">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-card-title">Manage Groups</h1>
                <p class="admin-card-text">Create, edit, view, and delete groups.</p>
            </div>
            <a class="admin-button admin-button-link" href="{{ route('admin.group-cards.create') }}">
                <i class="fa-solid fa-plus" aria-hidden="true"></i>
                <span>Add Group</span>
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
                            <td><span class="admin-badge">{{ ucfirst($groupCard->status) }}</span></td>
                            <td>{{ $groupCard->display_order }}</td>
                            <td>
                                <div class="admin-table-actions">
                                    <a href="{{ route('admin.group-cards.show', $groupCard) }}" title="View"><i class="fa-solid fa-eye" aria-hidden="true"></i></a>
                                    <a href="{{ route('admin.group-cards.edit', $groupCard) }}" title="Edit"><i class="fa-solid fa-pen" aria-hidden="true"></i></a>
                                    <form method="POST" action="{{ route('admin.group-cards.destroy', $groupCard) }}" onsubmit="return confirm('Delete this group?')">
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
                                    <i class="fa-solid fa-table-cells-large" aria-hidden="true"></i>
                                    <p>No groups found. Create your first one.</p>
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
