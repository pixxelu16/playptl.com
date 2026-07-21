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
                    @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('manage skills'))
                    <a class="admin-nav-link {{ request()->routeIs('admin.skills.*') ? 'is-active' : '' }}" href="{{ route('admin.skills.index') }}">
                        <span class="admin-nav-icon" aria-hidden="true"><i class="fa-solid fa-sliders"></i></span>
                        <span>Skills</span>
                    </a>
                    @endif
                    @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('manage leagues'))
                    <a class="admin-nav-link {{ request()->routeIs('admin.leagues.*', 'admin.league-management.*') ? 'is-active' : '' }}" href="{{ route('admin.leagues.index') }}">
                        <span class="admin-nav-icon" aria-hidden="true"><i class="fa-solid fa-trophy"></i></span>
                        <span>Tournaments</span>
                    </a>
                    @endif
                    @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('manage group cards'))
                    <a class="admin-nav-link {{ request()->routeIs('admin.group-cards.*') ? 'is-active' : '' }}" href="{{ route('admin.group-cards.index') }}">
                        <span class="admin-nav-icon" aria-hidden="true"><i class="fa-solid fa-table-cells-large"></i></span>
                        <span>Groups</span>
                    </a>
                    @endif
                    @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('manage groups'))
                    <a class="admin-nav-link {{ request()->routeIs('admin.groups.*') ? 'is-active' : '' }}" href="{{ route('admin.groups.index') }}">
                        <span class="admin-nav-icon" aria-hidden="true"><i class="fa-solid fa-users-line"></i></span>
                        <span>Subgroups</span>
                    </a>
                    @endif
                    @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('manage players'))
                    <a class="admin-nav-link {{ request()->routeIs('admin.players.*') ? 'is-active' : '' }}" href="{{ route('admin.players.index') }}">
                        <span class="admin-nav-icon" aria-hidden="true"><i class="fa-solid fa-user"></i></span>
                        <span>Players</span>
                    </a>
                    @endif
                    @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('manage users'))
                    @php
                        $pendingProvidersCount = \App\Models\User::whereIn('role', [\App\Enums\UserRole::Mentor, \App\Enums\UserRole::Coach])->where('status', 'pending')->count();
                    @endphp
                    <a class="admin-nav-link {{ request()->routeIs('admin.provider-requests.*') ? 'is-active' : '' }}" href="{{ route('admin.provider-requests.index') }}">
                        <span class="admin-nav-icon" aria-hidden="true"><i class="fa-solid fa-user-check"></i></span>
                        <span style="flex:1;">Coach & Mentor Requests</span>
                        @if($pendingProvidersCount > 0)
                            <span style="background:#E11D48; color:#fff; font-size:11px; font-weight:700; padding:2px 7px; border-radius:10px; margin-left:auto;">{{ $pendingProvidersCount }}</span>
                        @endif
                    </a>
                    <a class="admin-nav-link {{ request()->routeIs('admin.users.*') ? 'is-active' : '' }}" href="{{ route('admin.users.index') }}">
                        <span class="admin-nav-icon" aria-hidden="true"><i class="fa-solid fa-users"></i></span>
                        <span>Users</span>
                    </a>
                    @endif
                    @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('manage payment history'))
                    <a class="admin-nav-link {{ request()->routeIs('admin.payment-histories.*') ? 'is-active' : '' }}" href="{{ route('admin.payment-histories.index') }}">
                        <span class="admin-nav-icon" aria-hidden="true"><i class="fa-solid fa-receipt"></i></span>
                        <span>Payment History</span>
                    </a>
                    @endif
                    @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('manage charity causes'))
                    <a class="admin-nav-link {{ request()->routeIs('admin.charity-causes.*') ? 'is-active' : '' }}" href="{{ route('admin.charity-causes.index') }}">
                        <span class="admin-nav-icon" aria-hidden="true"><i class="fa-solid fa-heart"></i></span>
                        <span>Charity Causes</span>
                    </a>
                    @endif
                    @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('manage charity donations'))
                    <a class="admin-nav-link {{ request()->routeIs('admin.charity-donations.*') ? 'is-active' : '' }}" href="{{ route('admin.charity-donations.index') }}">
                        <span class="admin-nav-icon" aria-hidden="true"><i class="fa-solid fa-hand-holding-heart"></i></span>
                        <span>Charity Donations</span>
                    </a>
                    @endif
                    @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('manage announcements'))
                    <a class="admin-nav-link {{ request()->routeIs('admin.announcements.*') ? 'is-active' : '' }}" href="{{ route('admin.announcements.index') }}">
                        <span class="admin-nav-icon" aria-hidden="true"><i class="fa-solid fa-bullhorn"></i></span>
                        <span>Announcements</span>
                    </a>
                    @endif
                    @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('manage official partners'))
                    <a class="admin-nav-link {{ request()->routeIs('admin.official-partners.*') ? 'is-active' : '' }}" href="{{ route('admin.official-partners.index') }}">
                        <span class="admin-nav-icon" aria-hidden="true"><i class="fa-solid fa-handshake"></i></span>
                        <span>Official Partners</span>
                    </a>
                    @endif
                    @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('manage roles'))
                    <a class="admin-nav-link {{ request()->routeIs('admin.roles.*') ? 'is-active' : '' }}" href="{{ route('admin.roles.index') }}">
                        <span class="admin-nav-icon" aria-hidden="true"><i class="fa-solid fa-shield-halved"></i></span>
                        <span>Roles & Permissions</span>
                    </a>
                    @endif
                    @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('manage bookings'))
                    <a class="admin-nav-link {{ request()->routeIs('admin.bookings.*') ? 'is-active' : '' }}" href="{{ route('admin.bookings.index') }}">
                        <span class="admin-nav-icon" aria-hidden="true"><i class="fa-solid fa-calendar-check"></i></span>
                        <span>Bookings</span>
                    </a>
                    @endif
                    @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('manage gallery'))
                    <a class="admin-nav-link {{ request()->routeIs('admin.gallery.*') ? 'is-active' : '' }}" href="{{ route('admin.gallery.index') }}">
                        <span class="admin-nav-icon" aria-hidden="true"><i class="fa-solid fa-image"></i></span>
                        <span>Gallery</span>
                    </a>
                    @endif
                    @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('manage settings'))
                    <a class="admin-nav-link {{ request()->routeIs('admin.contact-settings.*') ? 'is-active' : '' }}" href="{{ route('admin.contact-settings.edit') }}">
                        <span class="admin-nav-icon" aria-hidden="true"><i class="fa-solid fa-gear"></i></span>
                        <span>Site Settings</span>
                    </a>
                    @endif
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

        <button type="button" class="admin-sidebar-backdrop" data-admin-sidebar-backdrop hidden aria-label="Close navigation"></button>

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
        (function () {
            const shell = document.querySelector('[data-admin-shell]');
            const toggle = document.querySelector('[data-sidebar-toggle]');
            const backdrop = document.querySelector('[data-admin-sidebar-backdrop]');
            const mobileQuery = window.matchMedia('(max-width: 1023px)');

            function isMobileNav() {
                return mobileQuery.matches;
            }

            function closeMobileNav() {
                shell?.classList.remove('is-mobile-nav-open');
                if (backdrop) {
                    backdrop.hidden = true;
                }
                toggle?.setAttribute('aria-expanded', 'false');
                document.body.classList.remove('admin-mobile-nav-open');
            }

            function openMobileNav() {
                shell?.classList.add('is-mobile-nav-open');
                if (backdrop) {
                    backdrop.hidden = false;
                }
                toggle?.setAttribute('aria-expanded', 'true');
                document.body.classList.add('admin-mobile-nav-open');
            }

            toggle?.addEventListener('click', function () {
                if (isMobileNav()) {
                    if (shell?.classList.contains('is-mobile-nav-open')) {
                        closeMobileNav();
                    } else {
                        openMobileNav();
                    }
                    return;
                }

                const collapsed = shell?.classList.toggle('is-sidebar-collapsed') ?? false;
                this.setAttribute('aria-expanded', String(! collapsed));
            });

            backdrop?.addEventListener('click', closeMobileNav);

            document.querySelectorAll('.admin-sidebar a, .admin-sidebar .admin-nav-button').forEach(function (el) {
                el.addEventListener('click', function () {
                    if (isMobileNav()) {
                        closeMobileNav();
                    }
                });
            });

            mobileQuery.addEventListener('change', function () {
                if (! isMobileNav()) {
                    closeMobileNav();
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    closeMobileNav();
                }
            });
        })();

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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jsencrypt/3.3.2/jsencrypt.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const passwordInputs = document.querySelectorAll('input[type="password"]');
            passwordInputs.forEach(input => {
                if (input.name && input.name.includes('stripe')) return;
                
                const wrapper = document.createElement('div');
                wrapper.className = 'relative w-full';
                wrapper.style.position = 'relative';
                wrapper.style.width = '100%';
                wrapper.style.display = 'block';
                
                input.parentNode.insertBefore(wrapper, input);
                wrapper.appendChild(input);
                
                input.style.paddingRight = '42px';
                
                const toggleBtn = document.createElement('button');
                toggleBtn.type = 'button';
                toggleBtn.style.position = 'absolute';
                toggleBtn.style.right = '12px';
                toggleBtn.style.top = '50%';
                toggleBtn.style.transform = 'translateY(-50%)';
                toggleBtn.style.background = 'none';
                toggleBtn.style.border = 'none';
                toggleBtn.style.padding = '0';
                toggleBtn.style.margin = '0';
                toggleBtn.style.cursor = 'pointer';
                toggleBtn.style.color = '#9ca3af';
                toggleBtn.style.zIndex = '10';
                toggleBtn.style.display = 'flex';
                toggleBtn.style.alignItems = 'center';
                toggleBtn.style.justifyContent = 'center';
                
                const eyeOpenSvg = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width: 20px; height: 20px; pointer-events: none;"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>`;
                const eyeClosedSvg = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width: 20px; height: 20px; pointer-events: none;"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>`;
                
                toggleBtn.innerHTML = eyeOpenSvg;
                wrapper.appendChild(toggleBtn);
                
                toggleBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (input.type === 'password') {
                        input.type = 'text';
                        toggleBtn.innerHTML = eyeClosedSvg;
                    } else {
                        input.type = 'password';
                        toggleBtn.innerHTML = eyeOpenSvg;
                    }
                });
            });

            // Handle RSA Encryption on Form Submissions (User authentication passwords only)
            const forms = document.querySelectorAll('form');
            const publicKey = @json(\App\Support\PasswordEncryptionHelper::getPublicKey());

            if (publicKey && window.JSEncrypt) {
                forms.forEach(form => {
                    const passFields = form.querySelectorAll('input[name="password"], input[name="password_confirmation"], input[name="current_password"]');
                    if (passFields.length === 0) return;

                    form.addEventListener('submit', function (e) {
                        // Prevent multiple encryptions if form is re-submitted
                        if (form.dataset.encrypted === 'true') return;

                        const crypt = new JSEncrypt();
                        crypt.setPublicKey(publicKey);

                        passFields.forEach(field => {
                            const val = field.value;
                            if (val) {
                                // Encrypt the value with the public key
                                const encrypted = crypt.encrypt(val);
                                if (encrypted) {
                                    field.value = encrypted;
                                }
                            }
                        });

                        form.dataset.encrypted = 'true';
                    });
                });
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Intercept forms with method DELETE or containing onsubmit confirm
            document.querySelectorAll('form').forEach(function (form) {
                var methodInput = form.querySelector('input[name="_method"]');
                var isDeleteForm = methodInput && methodInput.value.toUpperCase() === 'DELETE';
                var onsubmitAttr = form.getAttribute('onsubmit') || '';

                if (isDeleteForm || onsubmitAttr.indexOf('confirm(') !== -1) {
                    form.removeAttribute('onsubmit');
                    form.addEventListener('submit', function (e) {
                        if (form.dataset.confirmed === 'true') {
                            return true;
                        }
                        e.preventDefault();
                        
                        var customMsg = form.getAttribute('data-confirm-message') || "Are you sure you want to delete this record? This action cannot be undone.";

                        Swal.fire({
                            title: 'Are you sure?',
                            text: customMsg,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#dc2626',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: 'Yes, delete it!',
                            cancelButtonText: 'Cancel'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                form.dataset.confirmed = 'true';
                                form.submit();
                            }
                        });
                    });
                }
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
