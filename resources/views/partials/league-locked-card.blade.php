<div class="rounded-xl bg-white p-8 text-center shadow-[0_2px_12px_rgba(45,74,45,0.08)] ring-1 ring-[#e1f0e1] sm:p-12">
    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-[#E8F5E9] text-[#4CAF50]">
        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
        </svg>
    </div>
    <h3 class="mt-4 text-[20px] font-bold text-[#2d4a2d] sm:text-[22px]">Tournament Content Locked</h3>
    @auth
        <p class="mx-auto mt-2 max-w-lg text-[15px] font-medium text-[#5a8f5a]">
            Players, schedules, standings, and playoff details are only accessible to registered participants of this tournament. Register now for <span class="font-semibold text-[#2d4a2d]">{{ $breadcrumbLeagueLabel ?? 'this tournament' }}</span> to view all division data.
        </p>
        <div class="mt-6 flex flex-wrap items-center justify-center gap-4">
            <a href="{{ route('register.tournament-groups') }}" class="inline-flex items-center justify-center rounded-lg bg-[#5E9E52] px-6 py-3 text-[15px] font-bold text-white shadow-sm transition-colors hover:bg-[#4CAF50]">
                Register for Tournament
            </a>
        </div>
    @else
        <p class="mx-auto mt-2 max-w-lg text-[15px] font-medium text-[#5a8f5a]">
            Please log in and register as a player in this tournament to view players, schedules, standings, and playoffs.
        </p>
        <div class="mt-6 flex flex-wrap items-center justify-center gap-4">
            <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-lg bg-[#2d4a2d] px-6 py-3 text-[15px] font-bold text-white shadow-sm transition-colors hover:bg-[#1b2e1b]">
                Log In
            </a>
            <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-lg bg-[#5E9E52] px-6 py-3 text-[15px] font-bold text-white shadow-sm transition-colors hover:bg-[#4CAF50]">
                Register Account
            </a>
        </div>
    @endauth
</div>
