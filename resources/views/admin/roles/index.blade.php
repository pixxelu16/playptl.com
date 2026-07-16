@extends('layouts.admin')

@section('title', 'Manage Roles & Permissions | '.config('app.name', 'playptl'))
@section('meta_description', 'Configure application roles and permissions.')

@section('content')
    <section class="admin-card">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-card-title">Roles & Permissions</h1>
                <p class="admin-card-text">Define custom user roles and allocate access permissions for management modules.</p>
            </div>
            <a class="admin-button admin-button-link" href="{{ route('admin.roles.create') }}">
                <i class="fa-solid fa-plus" aria-hidden="true"></i>
                <span>Add Custom Role</span>
            </a>
        </div>

        @if (session('success'))
            <div class="admin-alert admin-alert-success">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="admin-alert admin-alert-danger" style="background-color: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-weight: 500;">
                {{ session('error') }}
            </div>
        @endif

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Role Name</th>
                        <th>Permissions</th>
                        <th style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($roles as $role)
                        <tr>
                            <td>
                                <strong style="font-size: 15px; color: #111;">{{ $role->name }}</strong>
                                @if (in_array($role->name, ['Super Admin', 'Admin', 'Player', 'Coach', 'Mentor', 'Student'], true))
                                    <span style="font-size: 10px; background-color: #e0f2fe; color: #0369a1; padding: 2px 6px; border-radius: 9999px; margin-left: 6px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">System</span>
                                @endif
                            </td>
                            <td>
                                @if ($role->name === 'Super Admin')
                                    <span style="font-size: 11px; background-color: #f0fdf4; color: #166534; padding: 2px 8px; border-radius: 6px; font-weight: 600; border: 1px solid #bbf7d0;">
                                        All Access Bypass (Implicit Grant)
                                    </span>
                                @else
                                    <div class="flex flex-wrap gap-1.5" style="display: flex; flex-wrap: wrap; gap: 6px;">
                                        @forelse ($role->permissions as $perm)
                                            <span style="font-size: 11px; background-color: #f3f4f6; color: #374151; padding: 2px 8px; border-radius: 6px; font-weight: 500; border: 1px solid #e5e7eb;">
                                                {{ $perm->name }}
                                            </span>
                                        @empty
                                            <span class="admin-muted" style="color: #9ca3af; font-style: italic; font-size: 13px;">No permissions assigned.</span>
                                        @endforelse
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="admin-table-actions">
                                    <a href="{{ route('admin.roles.edit', $role) }}" title="Edit / Assign Permissions">
                                        <i class="fa-solid fa-pen" aria-hidden="true"></i>
                                    </a>
                                    @if (!in_array($role->name, ['Super Admin', 'Admin', 'Player', 'Coach', 'Mentor', 'Student'], true))
                                        <form class="delete-role-form" method="POST" action="{{ route('admin.roles.destroy', $role) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Delete Role"><i class="fa-solid fa-trash" aria-hidden="true"></i></button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="admin-muted">No roles configured.</td>
                        </tr>
                    @endempty
                </tbody>
            </table>
        </div>
    </section>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.delete-role-form').forEach(function (form) {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "Do you want to delete this custom role? This will unassign it from all users and cannot be undone.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
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
