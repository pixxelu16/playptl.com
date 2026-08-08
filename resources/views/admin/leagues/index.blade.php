@extends('layouts.admin')

@section('title', 'Manage Tournaments | '.config('app.name', 'playptl'))
@section('meta_description', 'Manage tournaments from the admin dashboard.')

@section('content')
    @php
        $defaultLeagueLogo = asset('frontend/images/champion.png');
    @endphp
    <section class="admin-card">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-card-title">Manage Tournaments</h1>
                <p class="admin-card-text">Create, edit, view, and delete tournament records.</p>
            </div>
            <a class="admin-button admin-button-link" href="{{ route('admin.leagues.create') }}">
                <i class="fa-solid fa-plus" aria-hidden="true"></i>
                <span>Add Tournament</span>
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
                        <th>Tournament Name</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Show in Menu</th>
                        <th>Realize Tournament</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($leagues as $league)
                        <tr>
                            <td>
                                <img
                                    class="admin-table-logo"
                                    src="{{ $league->logo_path ? asset($league->logo_path) : $defaultLeagueLogo }}"
                                    alt="{{ $league->name }} logo"
                                >
                            </td>
                            <td>
                                <strong>{{ $league->name }}</strong>
                                @if ($league->description)
                                    <span>{{ Str::limit($league->description, 70) }}</span>
                                @endif
                            </td>
                            <td>{{ $league->start_date?->format('M d, Y') ?? '-' }}</td>
                            <td>{{ $league->end_date?->format('M d, Y') ?? '-' }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.leagues.toggle-menu', $league) }}" class="admin-menu-toggle-form">
                                    @csrf
                                    @method('PATCH')
                                    <label class="admin-switch-wrap" title="Toggle Menu Visibility">
                                        <span class="admin-switch">
                                            <input
                                                type="checkbox"
                                                class="admin-menu-toggle-checkbox"
                                                data-url="{{ route('admin.leagues.toggle-menu', $league) }}"
                                                @checked($league->show_in_menu)
                                            >
                                            <span class="admin-switch-slider"></span>
                                        </span>
                                        <span class="admin-switch-label {{ $league->show_in_menu ? 'is-enabled' : 'is-disabled' }}">
                                            {{ $league->show_in_menu ? 'Enabled' : 'Disabled' }}
                                        </span>
                                    </label>
                                </form>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.leagues.toggle-realize', $league) }}" class="admin-realize-toggle-form">
                                    @csrf
                                    @method('PATCH')
                                    <label class="admin-switch-wrap" title="Toggle Realize Tournament">
                                        <span class="admin-switch">
                                            <input
                                                type="checkbox"
                                                class="admin-realize-toggle-checkbox"
                                                data-url="{{ route('admin.leagues.toggle-realize', $league) }}"
                                                @checked($league->realize_tournament)
                                            >
                                            <span class="admin-switch-slider"></span>
                                        </span>
                                        <span class="admin-switch-label {{ $league->realize_tournament ? 'is-enabled' : 'is-disabled' }}">
                                            {{ $league->realize_tournament ? 'Enabled' : 'Disabled' }}
                                        </span>
                                    </label>
                                </form>
                            </td>
                            <td>
                                <div class="admin-table-actions">
                                    <a href="{{ route('admin.leagues.show', $league) }}" title="View"><i class="fa-solid fa-eye" aria-hidden="true"></i></a>
                                    <a href="{{ route('admin.leagues.edit', $league) }}" title="Edit"><i class="fa-solid fa-pen" aria-hidden="true"></i></a>
                                    <form method="POST" action="{{ route('admin.leagues.destroy', $league) }}" onsubmit="return confirm('Delete this tournament?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Delete"><i class="fa-solid fa-trash" aria-hidden="true"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="admin-empty-state">
                                    <i class="fa-solid fa-trophy" aria-hidden="true"></i>
                                    <p>No tournaments found. Create your first tournament.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($leagues->hasPages())
            <div class="admin-pagination">
                @if ($leagues->onFirstPage())
                    <span>Previous</span>
                @else
                    <a href="{{ $leagues->previousPageUrl() }}">Previous</a>
                @endif

                <strong>Page {{ $leagues->currentPage() }} of {{ $leagues->lastPage() }}</strong>

                @if ($leagues->hasMorePages())
                    <a href="{{ $leagues->nextPageUrl() }}">Next</a>
                @else
                    <span>Next</span>
                @endif
            </div>
        @endif
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggleCheckboxes = document.querySelectorAll('.admin-menu-toggle-checkbox, .admin-realize-toggle-checkbox');
            toggleCheckboxes.forEach(function (checkbox) {
                checkbox.addEventListener('change', function () {
                    const form = this.closest('form');
                    const url = this.dataset.url || form.action;
                    const labelSpan = form.querySelector('.admin-switch-label');
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || form.querySelector('input[name="_token"]').value;

                    fetch(url, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({})
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            if (labelSpan) {
                                const isEnabled = data.show_in_menu !== undefined ? data.show_in_menu : data.realize_tournament;
                                labelSpan.textContent = isEnabled ? 'Enabled' : 'Disabled';
                                labelSpan.className = 'admin-switch-label ' + (isEnabled ? 'is-enabled' : 'is-disabled');
                            }
                        } else {
                            this.checked = !this.checked;
                        }
                    })
                    .catch(error => {
                        console.error('Failed to toggle status:', error);
                        form.submit();
                    });
                });
            });
        });
    </script>
@endsection
