@php
    $navClass = fn (string $section) => $activeSection === $section ? $profileNavActive : $profileNavInactive;
@endphp
<nav id="profile-side-nav" class="space-y-2 p-4" aria-label="Profile sections">
    <a href="{{ route('player.my-profile') }}" class="{{ $navClass('personal') }}">Personal Information</a>
    <a href="{{ route('player.profile.league') }}" class="{{ $navClass('league') }}">Choose League</a>
    <a href="{{ route('player.profile.password') }}" class="{{ $navClass('password') }}">Password &amp; Security</a>
    <a href="{{ route('player.profile.location') }}" class="{{ $navClass('location') }}">My Matches</a>
    <a href="{{ route('player.profile.upload') }}" class="{{ $navClass('upload') }}">Upload Image</a>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="w-full rounded-lg border border-red-200 bg-white px-4 py-3 text-center text-[14px] font-semibold leading-snug text-red-600 transition-colors hover:bg-red-50 sm:text-[15px]">
            Logout
        </button>
    </form>
</nav>
