@extends('layouts.admin')

@section('title', 'Manage Skill Levels | '.config('app.name', 'playptl'))
@section('meta_description', 'Manage the player skill levels configuration.')

@section('content')
    <section class="admin-card">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-card-title">Manage Skill Levels</h1>
                <p class="admin-card-text">Configure and sort the player skill levels displayed in registration and profiles.</p>
            </div>
            <a class="admin-button admin-button-link" href="{{ route('admin.skills.create') }}">
                <i class="fa-solid fa-plus" aria-hidden="true"></i>
                <span>Add Skill Level</span>
            </a>
        </div>

        @if (session('status'))
            <div class="admin-alert admin-alert-success">{{ session('status') }}</div>
        @endif

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Value</th>
                        <th>Display Order</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($skills as $skill)
                        <tr>
                            <td><strong>{{ $skill->value === 'not-sure' ? 'Not Sure' : $skill->value }}</strong></td>
                            <td>{{ $skill->display_order }}</td>
                            <td>
                                <div class="admin-table-actions">
                                    <a href="{{ route('admin.skills.edit', $skill) }}" title="Edit"><i class="fa-solid fa-pen" aria-hidden="true"></i></a>
                                    <form class="delete-skill-form" method="POST" action="{{ route('admin.skills.destroy', $skill) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Delete"><i class="fa-solid fa-trash" aria-hidden="true"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="admin-muted">No skill levels configured.</td>
                        </tr>
                    @endempty
                </tbody>
            </table>
        </div>

        @if ($skills->hasPages())
            <div class="admin-pagination-container">
                {{ $skills->links() }}
            </div>
        @endif
    </section>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.delete-skill-form').forEach(function (form) {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "Do you want to delete this skill level? This cannot be undone.",
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
