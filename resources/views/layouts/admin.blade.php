<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.favicon')
    <title>@yield('title', 'Admin Dashboard')</title>
    <meta name="description" content="@yield('meta_description', 'Admin dashboard for managing '.config('app.name', 'playptl').'.')">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@400;500;600;700;800&family=Montserrat:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('admin/css/admin.css') }}">
    @stack('styles')
</head>
<body class="admin-body">
    <div class="admin-shell" data-admin-shell>
        <aside class="admin-sidebar">
            <div class="admin-brand">
                <span class="admin-brand-full">Admin Panel</span>
                <span class="admin-brand-short" aria-hidden="true">AP</span>
            </div>

            <nav class="admin-nav" aria-label="Admin navigation">
                <div class="admin-nav-section">
                    <p class="admin-nav-label">Main</p>
                    <a class="admin-nav-link {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}" href="{{ route('admin.dashboard') }}">
                        <span class="admin-nav-icon" aria-hidden="true"><i class="fa-solid fa-gauge-high"></i></span>
                        <span>Dashboard</span>
                    </a>
                    <a class="admin-nav-link {{ request()->routeIs('admin.leagues.*', 'admin.league-management.*') ? 'is-active' : '' }}" href="{{ route('admin.leagues.index') }}">
                        <span class="admin-nav-icon" aria-hidden="true"><i class="fa-solid fa-trophy"></i></span>
                        <span>Tournaments</span>
                    </a>
                    <a class="admin-nav-link {{ request()->routeIs('admin.group-cards.*') ? 'is-active' : '' }}" href="{{ route('admin.group-cards.index') }}">
                        <span class="admin-nav-icon" aria-hidden="true"><i class="fa-solid fa-table-cells-large"></i></span>
                        <span>Groups</span>
                    </a>
                    <a class="admin-nav-link {{ request()->routeIs('admin.groups.*') ? 'is-active' : '' }}" href="{{ route('admin.groups.index') }}">
                        <span class="admin-nav-icon" aria-hidden="true"><i class="fa-solid fa-users-line"></i></span>
                        <span>Subgroups</span>
                    </a>
                    <a class="admin-nav-link {{ request()->routeIs('admin.players.*') ? 'is-active' : '' }}" href="{{ route('admin.players.index') }}">
                        <span class="admin-nav-icon" aria-hidden="true"><i class="fa-solid fa-user"></i></span>
                        <span>Players</span>
                    </a>
                    <a class="admin-nav-link {{ request()->routeIs('admin.payment-histories.*') ? 'is-active' : '' }}" href="{{ route('admin.payment-histories.index') }}">
                        <span class="admin-nav-icon" aria-hidden="true"><i class="fa-solid fa-receipt"></i></span>
                        <span>Payment History</span>
                    </a>
                    <a class="admin-nav-link {{ request()->routeIs('admin.charity-causes.*') ? 'is-active' : '' }}" href="{{ route('admin.charity-causes.index') }}">
                        <span class="admin-nav-icon" aria-hidden="true"><i class="fa-solid fa-heart"></i></span>
                        <span>Charity Causes</span>
                    </a>
                    <a class="admin-nav-link {{ request()->routeIs('admin.charity-donations.*') ? 'is-active' : '' }}" href="{{ route('admin.charity-donations.index') }}">
                        <span class="admin-nav-icon" aria-hidden="true"><i class="fa-solid fa-hand-holding-heart"></i></span>
                        <span>Charity Donations</span>
                    </a>
                    <a class="admin-nav-link {{ request()->routeIs('admin.announcements.*') ? 'is-active' : '' }}" href="{{ route('admin.announcements.index') }}">
                        <span class="admin-nav-icon" aria-hidden="true"><i class="fa-solid fa-bullhorn"></i></span>
                        <span>Announcements</span>
                    </a>
                </div>

                <div class="admin-nav-section">
                    <p class="admin-nav-label">Account</p>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="admin-nav-button" type="submit">
                            <span class="admin-nav-icon" aria-hidden="true"><i class="fa-solid fa-arrow-right-from-bracket"></i></span>
                            <span>Logout</span>
                        </button>
                    </form>
                </div>
            </nav>
        </aside>

        <div class="admin-main">
            <header class="admin-topbar">
                <button class="admin-menu-toggle" type="button" aria-label="Toggle navigation" aria-expanded="true" data-sidebar-toggle>
                    <i class="fa-solid fa-bars" aria-hidden="true"></i>
                </button>

                <div class="admin-topbar-actions">
                    @php
                        $adminAvatarSrc = auth()->user()->avatar_path ?: 'upload/user-avatar/default-user-pic.png';
                    @endphp
                    <button class="admin-user-menu" type="button" aria-expanded="false" data-user-menu-toggle>
                        <img class="admin-avatar" src="{{ asset($adminAvatarSrc) }}" alt="Profile photo" width="36" height="36">
                        <span>Hi, {{ auth()->user()->name }}</span>
                        <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
                    </button>

                    <div class="admin-user-dropdown" data-user-dropdown>
                        <a href="{{ route('admin.profile') }}">
                            <i class="fa-solid fa-user-gear" aria-hidden="true"></i>
                            <span>Profile Settings</span>
                        </a>
                        <a href="{{ route('admin.password.edit') }}">
                            <i class="fa-solid fa-key" aria-hidden="true"></i>
                            <span>Change Password</span>
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit">
                                <i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i>
                                <span>Logout</span>
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <main class="admin-content">
                @yield('content')
            </main>
        </div>
    </div>
    <div id="admin-confirm-modal" class="admin-modal" hidden aria-hidden="true">
        <button type="button" class="admin-modal-backdrop" data-admin-confirm-cancel aria-label="Close"></button>
        <div class="admin-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="admin-confirm-title">
            <h2 id="admin-confirm-title" class="admin-modal-title">Are you sure?</h2>
            <p id="admin-confirm-message" class="admin-modal-footer-note"></p>
            <div class="admin-modal-actions">
                <button type="button" class="admin-modal-btn-cancel" data-admin-confirm-cancel>Cancel</button>
                <button type="button" class="admin-modal-btn-primary" id="admin-confirm-ok">Confirm</button>
            </div>
        </div>
    </div>
    @php
        $adminJsV = max(
            @filemtime(public_path('admin/js/admin-form-submit-lock.js')) ?: 0,
            @filemtime(public_path('admin/js/admin-confirm.js')) ?: 0
        );
    @endphp
    <script src="{{ asset('admin/js/admin-form-submit-lock.js') }}?v={{ $adminJsV }}" defer></script>
    <script src="{{ asset('admin/js/admin-confirm.js') }}?v={{ $adminJsV }}" defer></script>
    <script>
        document.querySelector('[data-sidebar-toggle]')?.addEventListener('click', function () {
            const shell = document.querySelector('[data-admin-shell]');
            const collapsed = shell?.classList.toggle('is-sidebar-collapsed') ?? false;

            this.setAttribute('aria-expanded', String(! collapsed));
        });

        const userMenuToggle = document.querySelector('[data-user-menu-toggle]');
        const userDropdown = document.querySelector('[data-user-dropdown]');

        userMenuToggle?.addEventListener('click', function (event) {
            event.stopPropagation();
            const isOpen = userDropdown?.classList.toggle('is-open') ?? false;

            this.setAttribute('aria-expanded', String(isOpen));
        });

        document.addEventListener('click', function () {
            userDropdown?.classList.remove('is-open');
            userMenuToggle?.setAttribute('aria-expanded', 'false');
        });
    </script>
    @stack('scripts')
</body>
</html>
