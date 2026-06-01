@extends('layouts.admin')

@section('title', 'Manage Charity Causes | '.config('app.name', 'playptl'))
@section('meta_description', 'Manage charity causes shown on the public charity page.')

@section('content')
    <section class="admin-card">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-card-title">Manage Charity Causes</h1>
                <p class="admin-card-text">Add charity titles, purpose descriptions, and images for the public charity page.</p>
            </div>
            <a class="admin-button admin-button-link" href="{{ route('admin.charity-causes.create') }}">
                <i class="fa-solid fa-plus" aria-hidden="true"></i>
                <span>Add Charity Cause</span>
            </a>
        </div>

        @if (session('status'))
            <div class="admin-alert admin-alert-success">{{ session('status') }}</div>
        @endif

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Title</th>
                        <th>Order</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($charityCauses as $cause)
                        <tr>
                            <td>
                                @if ($cause->image_path)
                                    <img src="{{ asset($cause->image_path) }}" alt="" style="width: 72px; height: 48px; object-fit: cover; border-radius: 8px;">
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                <strong>{{ $cause->title }}</strong>
                                <span>{{ Str::limit($cause->description, 90) }}</span>
                            </td>
                            <td>{{ $cause->display_order }}</td>
                            <td><span class="admin-badge">{{ $cause->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td>
                                <div class="admin-table-actions">
                                    <a href="{{ route('admin.charity-causes.show', $cause) }}" title="View"><i class="fa-solid fa-eye" aria-hidden="true"></i></a>
                                    <a href="{{ route('admin.charity-causes.edit', $cause) }}" title="Edit"><i class="fa-solid fa-pen" aria-hidden="true"></i></a>
                                    <form method="POST" action="{{ route('admin.charity-causes.destroy', $cause) }}" onsubmit="return confirm('Delete this charity cause?')">
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
                                    <i class="fa-solid fa-hand-holding-heart" aria-hidden="true"></i>
                                    <p>No charity causes yet. Add your first charity cause.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($charityCauses->hasPages())
            <div class="admin-pagination">
                @if ($charityCauses->onFirstPage())
                    <span>Previous</span>
                @else
                    <a href="{{ $charityCauses->previousPageUrl() }}">Previous</a>
                @endif

                <strong>Page {{ $charityCauses->currentPage() }} of {{ $charityCauses->lastPage() }}</strong>

                @if ($charityCauses->hasMorePages())
                    <a href="{{ $charityCauses->nextPageUrl() }}">Next</a>
                @else
                    <span>Next</span>
                @endif
            </div>
        @endif
    </section>
@endsection
