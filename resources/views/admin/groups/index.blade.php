@extends('layouts.admin')

@section('title', 'Manage Groups | '.config('app.name', 'playptl'))
@section('meta_description', 'Manage groups from the admin dashboard.')

@section('content')
    <section class="admin-card">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-card-title">Manage Groups</h1>
                <p class="admin-card-text">Create, edit, view, and delete player groups.</p>
            </div>
            <a class="admin-button admin-button-link" href="{{ route('admin.groups.create') }}">
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
                        <th>Group Name</th>
                        <th>Description</th>
                        <th>Players Count</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($groups as $group)
                        <tr>
                            <td><strong>{{ $group->name }}</strong></td>
                            <td>{{ $group->description ? Str::limit($group->description, 80) : '-' }}</td>
                            <td>{{ $group->players_count }}</td>
                            <td><span class="admin-badge">{{ ucfirst($group->status) }}</span></td>
                            <td>
                                <div class="admin-table-actions">
                                    <a href="{{ route('admin.groups.show', $group) }}" title="View"><i class="fa-solid fa-eye" aria-hidden="true"></i></a>
                                    <a href="{{ route('admin.groups.edit', $group) }}" title="Edit"><i class="fa-solid fa-pen" aria-hidden="true"></i></a>
                                    <form method="POST" action="{{ route('admin.groups.destroy', $group) }}" onsubmit="return confirm('Delete this group?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Delete"><i class="fa-solid fa-trash" aria-hidden="true"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="admin-empty-state">
                                    <i class="fa-solid fa-users-line" aria-hidden="true"></i>
                                    <p>No groups found. Create your first group.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($groups->hasPages())
            <div class="admin-pagination">
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
    </section>
@endsection
