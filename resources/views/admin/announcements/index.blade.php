@extends('layouts.admin')

@section('title', 'Manage Announcements | '.config('app.name', 'playptl'))
@section('meta_description', 'Manage announcements from the admin dashboard.')

@section('content')
    <section class="admin-card">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-card-title">Manage Announcements</h1>
                <p class="admin-card-text">Create, edit, view, and delete homepage announcements.</p>
            </div>
            <a class="admin-button admin-button-link" href="{{ route('admin.announcements.create') }}">
                <i class="fa-solid fa-plus" aria-hidden="true"></i>
                <span>Add Announcement</span>
            </a>
        </div>

        @if (session('status'))
            <div class="admin-alert admin-alert-success">{{ session('status') }}</div>
        @endif

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Date</th>
                        <th>Featured</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($announcements as $announcement)
                        <tr>
                            <td>
                                <strong>{{ $announcement->title }}</strong>
                                <span>{{ Str::limit($announcement->description, 80) }}</span>
                            </td>
                            <td><span class="admin-badge">{{ ucfirst($announcement->type) }}</span></td>
                            <td>{{ $announcement->announcement_date->format('M d, Y') }}</td>
                            <td>{{ $announcement->is_featured ? 'Yes' : 'No' }}</td>
                            <td><span class="admin-badge">{{ $announcement->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td>
                                <div class="admin-table-actions">
                                    <a href="{{ route('admin.announcements.show', $announcement) }}" title="View"><i class="fa-solid fa-eye" aria-hidden="true"></i></a>
                                    <a href="{{ route('admin.announcements.edit', $announcement) }}" title="Edit"><i class="fa-solid fa-pen" aria-hidden="true"></i></a>
                                    <form method="POST" action="{{ route('admin.announcements.destroy', $announcement) }}" onsubmit="return confirm('Delete this announcement?')">
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
                                    <i class="fa-solid fa-bullhorn" aria-hidden="true"></i>
                                    <p>No announcements found. Create your first announcement.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($announcements->hasPages())
            <div class="admin-pagination">
                @if ($announcements->onFirstPage())
                    <span>Previous</span>
                @else
                    <a href="{{ $announcements->previousPageUrl() }}">Previous</a>
                @endif

                <strong>Page {{ $announcements->currentPage() }} of {{ $announcements->lastPage() }}</strong>

                @if ($announcements->hasMorePages())
                    <a href="{{ $announcements->nextPageUrl() }}">Next</a>
                @else
                    <span>Next</span>
                @endif
            </div>
        @endif
    </section>
@endsection
