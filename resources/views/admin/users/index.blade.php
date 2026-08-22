@extends('layouts.admin')

@section('title', 'Manage Users | ' . config('app.name', 'playptl'))
@section('meta_description', 'View, add, edit, or delete user accounts and manage their roles/permissions.')

@section('content')
    <section class="admin-card">
        <div class="admin-page-header" style="margin-bottom: 24px;">
            <div>
                <h1 class="admin-card-title">Manage Users</h1>
                <p class="admin-card-text">View and manage system users, their login credentials, and assign administrative permissions.</p>
            </div>
            <a href="{{ route('admin.users.create') }}" class="admin-button">
                <i class="fa-solid fa-plus" aria-hidden="true" style="margin-right: 6px;"></i>
                <span>Add User</span>
            </a>
        </div>

        @if (session('status'))
            <div class="admin-alert admin-alert-success" style="margin-bottom: 20px;">{{ session('status') }}</div>
        @endif

        @if (session('error'))
            <div class="admin-alert admin-alert-error" style="margin-bottom: 20px;">{{ session('error') }}</div>
        @endif

        {{-- Filters Section --}}
        <div style="background: #f8fafc; padding: 16px; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 24px;">
            <form method="GET" action="{{ route('admin.users.index') }}" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; align-items: flex-end;">
                <div>
                    <label for="search" style="font-size: 13px; font-weight: 600; color: #475569; display: block; margin-bottom: 6px;">Search User</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Name, email, username..." class="admin-input" style="height: 40px; border-radius: 6px; border: 1px solid #cbd5e1; width: 100%; padding: 8px 12px; font-size: 14px;">
                </div>
                <div>
                    <label for="role" style="font-size: 13px; font-weight: 600; color: #475569; display: block; margin-bottom: 6px;">Filter by Role</label>
                    <select name="role" id="role" class="admin-input" style="height: 40px; border-radius: 6px; border: 1px solid #cbd5e1; width: 100%; padding: 8px 12px; font-size: 14px;">
                        <option value="">-- All Roles --</option>
                        @foreach($filterRoles as $rName)
                            <option value="{{ $rName }}" {{ request('role') === $rName || strtolower(request('role') ?? '') === strtolower($rName) ? 'selected' : '' }}>{{ $rName }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="per_page" style="font-size: 13px; font-weight: 600; color: #475569; display: block; margin-bottom: 6px;">Per Page</label>
                    <select name="per_page" id="per_page" class="admin-input" style="height: 40px; border-radius: 6px; border: 1px solid #cbd5e1; width: 100%; padding: 8px 12px; font-size: 14px;">
                        @foreach([10, 20, 50, 100] as $num)
                            <option value="{{ $num }}" {{ $perPage === $num ? 'selected' : '' }}>{{ $num }} items</option>
                        @endforeach
                    </select>
                </div>
                <div style="display: flex; gap: 8px;">
                    <button type="submit" class="admin-button" style="height: 40px; padding: 0 16px; flex: 1;">Filter</button>
                    <a href="{{ route('admin.users.index') }}" class="admin-button admin-button-secondary" style="height: 40px; padding: 0 16px; display: inline-flex; align-items: center; justify-content: center; background: #e2e8f0; color: #334155; border: none;">Reset</a>
                </div>
            </form>
        </div>

        {{-- Listing Table --}}
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 80px;">User</th>
                        <th>Name / Username</th>
                        <th>Email</th>
                        <th>Base Role</th>
                        <th>Spatie Roles & Permissions</th>
                        <th style="width: 150px;">Registered</th>
                        <th style="width: 120px; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr style="vertical-align: middle;">
                            <td>
                                @if($user->avatar_path)
                                    <img src="{{ asset('storage/' . $user->avatar_path) }}" alt="{{ $user->name }}" style="width: 44px; height: 44px; object-fit: cover; border-radius: 50%; border: 1px solid #cbd5e1;">
                                @else
                                    @php $initials = strtoupper(substr($user->first_name ?? $user->name, 0, 1) . substr($user->last_name ?? '', 0, 1)) ?: strtoupper(substr($user->name, 0, 2)); @endphp
                                    <div style="width: 44px; height: 44px; background: #5DA44E; color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; border-radius: 50%; font-size: 14px;">
                                        {{ $initials }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div style="font-weight: 600; color: #1e293b;">{{ $user->name }}
                                    @if($user->is_locked)
                                        <span title="Locked at {{ $user->locked_at?->format('M d Y H:i') }}" style="display:inline-block;margin-left:6px;font-size:10px;background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;padding:2px 7px;border-radius:20px;font-weight:700;vertical-align:middle;">🔒 LOCKED</span>
                                    @endif
                                </div>
                                <div style="font-size: 13px; color: #64748b; margin-top: 2px; white-space: nowrap;">&#64;{{ $user->username }}</div>
                            </td>
                            <td>
                                <div style="font-size: 14px;">{{ $user->email }}</div>
                            </td>
                            <td>
                                <span style="display: inline-block; font-size: 11px; text-transform: uppercase; font-weight: 700; background: #f1f5f9; color: #475569; padding: 4px 8px; border-radius: 9999px;">
                                    {{ is_object($user->role) && property_exists($user->role, 'value') ? $user->role->value : $user->role }}
                                </span>
                            </td>
                            <td>
                                {{-- Spatie Roles --}}
                                @if($user->roles->isNotEmpty())
                                    <div style="display: flex; flex-wrap: wrap; gap: 4px; margin-bottom: 4px;">
                                        @foreach($user->roles as $role)
                                            <span style="font-size: 11px; background: #e0f2fe; color: #0369a1; padding: 2px 6px; border-radius: 4px; font-weight: 600;">
                                                Role: {{ $role->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif

                                {{-- Direct Spatie Permissions --}}
                                @if($user->permissions->isNotEmpty())
                                    <div style="display: flex; flex-wrap: wrap; gap: 4px;">
                                        @foreach($user->permissions as $perm)
                                            <span style="font-size: 10px; background: #fef2f2; color: #b91c1c; padding: 2px 6px; border-radius: 4px; font-weight: 500;">
                                                {{ $perm->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif

                                @if($user->roles->isEmpty() && $user->permissions->isEmpty())
                                    <span style="color: #94a3b8; font-size: 13px; font-style: italic;">No specific admin assignments</span>
                                @endif
                            </td>
                            <td>
                                <div style="font-size: 13px; color: #64748b;">
                                    {{ $user->created_at ? $user->created_at->format('M d, Y') : '—' }}
                                </div>
                            </td>
                            <td style="text-align: right;">
                                <div class="admin-table-actions" style="justify-content: flex-end;">
                                    @if($user->is_locked)
                                        <form method="POST" action="{{ route('admin.users.unblock', $user) }}" style="margin:0;display:inline;">
                                            @csrf
                                            <button type="submit" title="Unblock Account" style="color: #16a34a;">
                                                <i class="fa-solid fa-lock-open" aria-hidden="true"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <a href="{{ route('admin.users.edit', $user) }}" title="Edit User">
                                        <i class="fa-solid fa-pen" aria-hidden="true"></i>
                                    </a>
                                    @if($user->id !== auth()->id())
                                        <form class="delete-user-form" method="POST" action="{{ route('admin.users.destroy', $user) }}" style="margin: 0; display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Delete User">
                                                <i class="fa-solid fa-trash" aria-hidden="true"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="admin-muted" style="text-align: center; padding: 30px;">No users found matching the criteria.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->total() > 0)
            <p class="admin-muted" style="margin-top:14px;">
                Showing {{ $users->firstItem() }}–{{ $users->lastItem() }} of {{ $users->total() }} users
            </p>
        @endif

        @if ($users->hasPages())
            @php
                $pageStart = max(1, $users->currentPage() - 2);
                $pageEnd = min($users->lastPage(), $users->currentPage() + 2);
            @endphp
            <div class="admin-pagination">
                @if ($users->onFirstPage())
                    <span>Previous</span>
                @else
                    <a href="{{ $users->previousPageUrl() }}">Previous</a>
                @endif

                @if ($pageStart > 1)
                    <a href="{{ $users->url(1) }}">1</a>
                    @if ($pageStart > 2)
                        <span>…</span>
                    @endif
                @endif

                @for ($page = $pageStart; $page <= $pageEnd; $page++)
                    @if ($page === $users->currentPage())
                        <strong>{{ $page }}</strong>
                    @else
                        <a href="{{ $users->url($page) }}">{{ $page }}</a>
                    @endif
                @endfor

                @if ($pageEnd < $users->lastPage())
                    @if ($pageEnd < $users->lastPage() - 1)
                        <span>…</span>
                    @endif
                    <a href="{{ $users->url($users->lastPage()) }}">{{ $users->lastPage() }}</a>
                @endif

                @if ($users->hasMorePages())
                    <a href="{{ $users->nextPageUrl() }}">Next</a>
                @else
                    <span>Next</span>
                @endif
            </div>
        @endif
    </section>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.delete-user-form').forEach(function (form) {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "Do you want to delete this user? All their details and associated records will be permanently removed.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc2626',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, delete user',
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
