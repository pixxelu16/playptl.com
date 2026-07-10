@extends('layouts.admin')

@section('title', 'Manage Subgroups | '.config('app.name', 'playptl'))
@section('meta_description', 'Manage subgroups from the admin dashboard.')

@section('content')
    <section class="admin-card">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-card-title">Manage Subgroups</h1>
                <p class="admin-card-text">Create, edit, view, and delete player subgroups (A, B, C, D, etc.).</p>
            </div>
            <a class="admin-button admin-button-link" href="{{ route('admin.groups.create') }}">
                <i class="fa-solid fa-plus" aria-hidden="true"></i>
                <span>Add Subgroup</span>
            </a>
        </div>

        @if (session('status'))
            <div class="admin-alert admin-alert-success">{{ session('status') }}</div>
        @endif

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Subgroup Name</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th style="width:120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($groups as $group)
                        <tr>
                            <td><strong>{{ $group->name }}</strong></td>
                            <td>{{ $group->description ? Str::limit($group->description, 80) : '-' }}</td>
                            <td><span class="admin-badge">{{ ucfirst($group->status) }}</span></td>
                            <td>
                                <div class="admin-table-actions" style="justify-content:flex-end;">
                                    <a href="{{ route('admin.groups.show', $group) }}" title="View"><i class="fa-solid fa-eye" aria-hidden="true"></i></a>
                                    <a href="{{ route('admin.groups.edit', $group) }}" title="Edit"><i class="fa-solid fa-pen" aria-hidden="true"></i></a>
                                    <form method="POST" action="{{ route('admin.groups.destroy', $group) }}" onsubmit="return confirm('Delete this subgroup?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Delete"><i class="fa-solid fa-trash" aria-hidden="true"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="admin-empty-state">
                                    <i class="fa-solid fa-users-line" aria-hidden="true"></i>
                                    <p>No subgroups found. Create your first subgroup.</p>
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
