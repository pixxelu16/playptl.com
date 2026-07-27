@extends('layouts.admin')

@section('title', 'Manage Categories | '.config('app.name', 'playptl'))
@section('meta_description', 'Manage the registration and profile categories configuration.')

@section('content')
    <section class="admin-card">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-card-title">Manage Categories</h1>
                <p class="admin-card-text">Configure and manage registration categories for tournaments.</p>
            </div>
            <a class="admin-button admin-button-link" href="{{ route('admin.categories.create') }}">
                <i class="fa-solid fa-plus" aria-hidden="true"></i>
                <span>Add Category</span>
            </a>
        </div>

        @if (session('status'))
            <div class="admin-alert admin-alert-success">{{ session('status') }}</div>
        @endif

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Menu Order</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        @php
                            $types = explode(',', $category->type ?? 'single,doubles');
                            $typeLabels = array_map(function($t) {
                                return $t === 'single' ? 'Single' : ($t === 'doubles' ? 'Doubles' : ucfirst($t));
                            }, $types);
                        @endphp
                        <tr>
                            <td><strong>{{ $category->name }}</strong></td>
                            <td>
                                @foreach($typeLabels as $label)
                                    <span class="admin-badge" style="background:#eef2ff; color:#3730a3; padding:2px 8px; border-radius:4px; font-size:12px; font-weight:600; margin-right:4px;">{{ $label }}</span>
                                @endforeach
                            </td>
                            <td>{{ $category->menu_order }}</td>
                            <td>{{ $category->created_at ? $category->created_at->format('M d, Y H:i') : '—' }}</td>
                            <td>
                                <div class="admin-table-actions">
                                    <a href="{{ route('admin.categories.edit', $category) }}" title="Edit"><i class="fa-solid fa-pen" aria-hidden="true"></i></a>
                                    <form class="delete-category-form" method="POST" action="{{ route('admin.categories.destroy', $category) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Delete"><i class="fa-solid fa-trash" aria-hidden="true"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="admin-muted">No categories configured.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($categories->hasPages())
            <div class="admin-pagination-container">
                {{ $categories->links() }}
            </div>
        @endif
    </section>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.delete-category-form').forEach(function (form) {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "Do you want to delete this category? This cannot be undone.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#5cb85c',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, delete it!',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>
@endpush
