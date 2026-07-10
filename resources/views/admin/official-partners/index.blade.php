@extends('layouts.admin')

@section('title', 'Manage Official Partners | '.config('app.name', 'playptl'))
@section('meta_description', 'Manage official partner logos shown on the homepage.')

@section('content')
    <section class="admin-card">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-card-title">Manage Official Partners</h1>
                <p class="admin-card-text">Add, edit, or remove partner logos shown in the homepage partners marquee.</p>
            </div>
            <a class="admin-button admin-button-link" href="{{ route('admin.official-partners.create') }}">
                <i class="fa-solid fa-plus" aria-hidden="true"></i>
                <span>Add Partner</span>
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
                        <th>Name</th>
                        <th>Website</th>
                        <th>Order</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($officialPartners as $partner)
                        <tr>
                            <td>
                                <img src="{{ asset($partner->logo_path) }}" alt="{{ $partner->name }}" style="width: 96px; height: 56px; object-fit: contain; border-radius: 8px; background: #fff; padding: 6px;">
                            </td>
                            <td><strong>{{ $partner->name }}</strong></td>
                            <td>
                                @if ($partner->website_url)
                                    <a href="{{ $partner->website_url }}" target="_blank" rel="noopener noreferrer">{{ $partner->website_url }}</a>
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $partner->display_order }}</td>
                            <td><span class="admin-badge">{{ $partner->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td>
                                <div class="admin-table-actions">
                                    <a href="{{ route('admin.official-partners.edit', $partner) }}" title="Edit"><i class="fa-solid fa-pen" aria-hidden="true"></i></a>
                                    <form method="POST" action="{{ route('admin.official-partners.destroy', $partner) }}" onsubmit="return confirm('Delete this partner?')">
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
                                    <i class="fa-solid fa-handshake" aria-hidden="true"></i>
                                    <p>No official partners yet. Add your first partner logo.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($officialPartners->hasPages())
            <div class="admin-pagination">
                @if ($officialPartners->onFirstPage())
                    <span>Previous</span>
                @else
                    <a href="{{ $officialPartners->previousPageUrl() }}">Previous</a>
                @endif

                <strong>Page {{ $officialPartners->currentPage() }} of {{ $officialPartners->lastPage() }}</strong>

                @if ($officialPartners->hasMorePages())
                    <a href="{{ $officialPartners->nextPageUrl() }}">Next</a>
                @else
                    <span>Next</span>
                @endif
            </div>
        @endif
    </section>
@endsection
