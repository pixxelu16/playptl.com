<section class="site-hero relative flex flex-col overflow-hidden">
    <img class="absolute inset-0 z-0 h-full w-full object-cover" src="{{ asset('frontend/images/hero_tennis_banner.png') }}" alt="Tennis Banner Background" aria-hidden="true">

    <div class="pointer-events-none absolute inset-0 z-[1] bg-gradient-to-b from-[rgba(8,15,28,0.88)] via-[rgba(8,15,28,0.35)] via-40% to-[rgba(8,15,28,0.55)]" aria-hidden="true"></div>

    <div class="pointer-events-none absolute right-0 top-0 z-[2] h-[min(45vh,420px)] w-[min(55vw,520px)] opacity-[0.14]" aria-hidden="true">
        <div class="absolute right-[8%] top-[12%] h-24 w-24 rounded-full bg-[#e8d94a] blur-2xl"></div>
        <div class="absolute right-[22%] top-[28%] h-16 w-16 rounded-full bg-[#c9b832] blur-xl"></div>
        <div class="absolute right-[5%] top-[38%] h-10 w-10 rounded-full bg-[#f5e85a] blur-lg"></div>
    </div>

    <div class="relative z-10 mx-auto flex w-full max-w-[1400px] flex-1 flex-col justify-center px-5 pb-24 pt-36 sm:px-8 sm:pb-28 sm:pt-40 lg:px-14 lg:pb-32 lg:pt-44">
        <header class="max-w-4xl">
            <nav class="mb-6 text-[11px] font-semibold uppercase tracking-[0.28em] text-white sm:mb-8 sm:text-xs md:text-[13px]" aria-label="Breadcrumb">
                <a href="{{ url('/') }}" class="text-white transition-colors hover:text-white/90">Home</a>
                <span class="mx-2 inline text-[#c1e82c] sm:mx-3">&gt;&gt;</span>
                <span class="text-[#c1e82c]">{{ $heroBreadcrumb }}</span>
            </nav>

            <h1 class="league-1 text-[clamp(2.2rem,7vw,4rem)] font-normal uppercase leading-[0.95] tracking-[0.02em]">
                <span class="text-white">{{ $heroTitleLight }}</span>@if (($heroTitleAccent ?? '') !== '')<span class="text-[#c1e82c]"> {{ $heroTitleAccent }}</span>@endif
            </h1>

            @if (! empty($heroMetaItems))
                <p class="mt-8 max-w-4xl font-['Montserrat',ui-sans-serif,system-ui,sans-serif] text-[13px] font-medium leading-relaxed text-white sm:mt-10 sm:text-[15px] md:text-base">
                    @foreach ($heroMetaItems as $heroMetaItem)
                        <span class="text-[#c1e82c]">&#8226;</span>
                        <span class="mx-1.5 sm:mx-2">{{ $heroMetaItem }}</span>
                    @endforeach
                </p>
            @endif
        </header>
    </div>
</section>
