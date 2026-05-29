@extends('layouts.website')

@section('nav_active', 'charity')

@section('title', 'Beyond the Baseline | Charity | Premier Tennis League')
@section('meta_description', 'Premier Tennis League charity partners — every match gives back. Season 2026.')

@section('header_class', 'absolute inset-x-0 top-0 z-[100] bg-transparent px-5 pb-4 pt-6 sm:px-8 lg:px-14')

@section('content')
    <main>
        <section class="relative flex h-[685px] min-h-[685px] flex-col overflow-hidden">
            <video class="absolute inset-0 z-0 h-full w-full object-cover" autoplay muted loop playsinline preload="auto" aria-hidden="true">
                <source src="{{ asset('frontend/videos/hero-section-video.mp4') }}" type="video/mp4">
            </video>

            <div class="pointer-events-none absolute inset-0 z-[1] bg-[rgba(0,0,0,0.6)]" aria-hidden="true"></div>

            <div class="relative z-10 mx-auto flex w-full max-w-[1400px] flex-1 flex-col items-start justify-center px-5 py-12 text-left sm:py-16">
                <header class="w-full max-w-5xl text-left">
                    <nav class="mb-6 flex flex-wrap items-center justify-start gap-x-1 gap-y-2 text-[14px] font-semibold uppercase tracking-[0.28em] text-[#C1D72E] sm:mb-8" aria-label="Breadcrumb">
                        <a href="{{ url('/') }}" class="text-[#C1D72E] transition-opacity hover:opacity-90">Home</a>
                        <span class="mx-1 sm:mx-2">&gt;&gt;</span>
                        <span class="text-[#C1D72E]">Charity</span>
                    </nav>

                    <h1 class="league-1 text-[clamp(4.5rem,11vw,5rem)] font-normal uppercase leading-[0.95] tracking-[0.02em]">
                        <span class="text-white">BEYOND THE </span><span class="text-[#C1D72E]">BASELINE</span>
                    </h1>

                    <p class="mt-8 max-w-4xl font-['Montserrat',ui-sans-serif,system-ui,sans-serif] text-[18px] font-medium leading-relaxed text-white sm:mt-10">
                        <span class="text-[#C1D72E]">&#8226;</span>
                        <span class="mx-2">6 Charity Partners</span>
                        <span class="text-[#C1D72E]">&#8226;</span>
                        <span class="mx-2">Season 2026</span>
                        <span class="text-[#C1D72E]">&#8226;</span>
                        <span class="mx-2">Every match gives back</span>
                    </p>
                </header>
            </div>
        </section>

        <section class="bg-[#E4F7E7] py-10 font-sans antialiased sm:py-12 lg:py-14" aria-labelledby="charity-goal-heading">
            <div class="mx-auto max-w-[1400px] px-5 sm:px-8 lg:px-14">
                <h2 id="charity-goal-heading" class="sr-only">Total donations raised</h2>
                <div class="relative overflow-hidden rounded-[15px] bg-white p-6 shadow-[0_2px_16px_rgba(0,0,0,0.06)] ring-1 ring-black/[0.05] sm:p-8 lg:p-10">
                    <div class="pointer-events-none absolute inset-y-0 right-0 z-0 w-[min(42%,280px)] opacity-95" aria-hidden="true">
                        <div class="absolute inset-0 bg-[radial-gradient(ellipse_115%_95%_at_100%_50%,rgba(96,160,75,0.18)_0%,rgba(96,160,75,0.06)_38%,transparent_62%)]"></div>
                        <div class="absolute inset-0 bg-[radial-gradient(ellipse_90%_78%_at_96%_48%,rgba(96,160,75,0.1)_0%,transparent_52%)]"></div>
                    </div>

                    <div class="relative z-[1] flex flex-col items-stretch gap-8 lg:flex-row lg:items-center lg:justify-between lg:gap-10">
                        <div class="min-w-0 shrink-0 text-left lg:max-w-[280px]">
                            <p class="text-[13px] font-medium leading-snug text-[#60a04b] sm:text-[14px]">Total Donations Raised</p>
                            <p
                                id="charity-total-raised"
                                class="mt-1.5 text-[clamp(1.75rem,4vw,2.35rem)] font-bold tabular-nums leading-tight tracking-tight text-[#212121]"
                            >
                                {{ $total_raised_formatted }}
                            </p>
                        </div>

                        <div class="min-w-0 flex-1 lg:mx-4 lg:max-w-none">
                            <div class="mb-2 flex flex-wrap items-center justify-between gap-x-4 gap-y-1 text-[12px] font-normal leading-snug text-[#757575] sm:text-[13px]">
                                <span>$0</span>
                                <span id="charity-progress-label" class="font-medium text-[#212121]">{{ $progress_label }}</span>
                                <span id="charity-progress-scale-max">{{ $bar_scale_max_formatted }}</span>
                            </div>
                            <div
                                id="charity-progress-bar"
                                class="h-3 w-full overflow-hidden rounded-full bg-[#E8EDE8]"
                                role="progressbar"
                                aria-valuemin="0"
                                aria-valuemax="{{ $bar_scale_max }}"
                                aria-valuenow="{{ $total_raised }}"
                                aria-label="Donations raised"
                            >
                                <div
                                    id="charity-progress-fill"
                                    class="h-full rounded-full bg-[#60a04b] transition-[width] duration-500"
                                    style="width: {{ $progress_percent }}%"
                                ></div>
                            </div>
                        </div>

                        <div class="flex shrink-0 justify-start lg:justify-end">
                            <button
                                type="button"
                                data-open-charity-donate
                                data-donate-amount="25"
                                class="inline-flex min-h-[48px] items-center justify-center rounded-lg bg-[#60a04b] px-8 py-3 text-[15px] font-bold text-white shadow-sm transition-opacity hover:opacity-95 sm:min-h-[52px] sm:px-10 sm:text-[16px]"
                            >
                                Donate Now
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="bg-[#E4F7E7] py-10 font-sans antialiased sm:py-12 lg:py-16 lg:pb-20" aria-labelledby="charity-donate-cta-heading">
            <div class="mx-auto max-w-[1400px] px-5 sm:px-8 lg:px-14">
                <h2 id="charity-donate-cta-heading" class="sr-only">Make a donation</h2>
                <div
                    id="charity-donate-widget"
                    class="flex flex-col gap-10 rounded-[14px] bg-[#2D5A27] px-6 py-10 shadow-[0_4px_24px_rgba(45,90,39,0.2)] sm:px-10 sm:py-12 lg:flex-row lg:items-center lg:justify-between lg:gap-12 lg:px-14 lg:py-12 xl:px-16"
                >
                    <div class="min-w-0 max-w-xl shrink-0 lg:flex-1">
                        <p class="text-[clamp(1.25rem,3.5vw,2rem)] font-bold leading-[1.25] text-white lg:text-[28px] xl:text-[32px]">
                            Every Match Plays For <span class="text-[#A4D433]">Something Bigger</span>
                        </p>
                        <p class="mt-4 max-w-xl text-[14px] font-normal leading-[1.5] text-white sm:text-[15px] lg:text-[16px]">
                            All Divisions Now Open. Voyagers, Challengers, Warriors And Mixed Doubles Brackets Accepting Entries Until May 15.
                        </p>
                    </div>

                    <div class="w-full min-w-0 shrink-0 lg:max-w-[520px] lg:flex-1">
                        <div class="mb-4 grid grid-cols-4 gap-2 sm:gap-3" role="group" aria-label="Choose amount">
                            <button
                                type="button"
                                data-donate-preset="10"
                                class="donate-preset-btn flex min-h-[44px] items-center justify-center rounded-md border border-white/25 bg-white/10 px-1 text-[13px] font-semibold text-white transition-colors hover:bg-white/15 sm:px-2 sm:text-[14px]"
                            >
                                $10
                            </button>
                            <button
                                type="button"
                                data-donate-preset="25"
                                class="donate-preset-btn flex min-h-[44px] items-center justify-center rounded-md border border-white bg-white px-1 text-[13px] font-bold text-[#A4D433] sm:px-2 sm:text-[14px]"
                            >
                                $25
                            </button>
                            <button
                                type="button"
                                data-donate-preset="50"
                                class="donate-preset-btn flex min-h-[44px] items-center justify-center rounded-md border border-white/25 bg-white/10 px-1 text-[13px] font-semibold text-white transition-colors hover:bg-white/15 sm:px-2 sm:text-[14px]"
                            >
                                $50
                            </button>
                            <button
                                type="button"
                                data-donate-preset="100"
                                class="donate-preset-btn flex min-h-[44px] items-center justify-center rounded-md border border-white/25 bg-white/10 px-1 text-[13px] font-semibold text-white transition-colors hover:bg-white/15 sm:px-2 sm:text-[14px]"
                            >
                                $100
                            </button>
                        </div>
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-stretch">
                            <label class="sr-only" for="charity-donate-other">Other amount</label>
                            <input
                                id="charity-donate-other"
                                type="text"
                                inputmode="decimal"
                                autocomplete="off"
                                placeholder="$ Other amount"
                                class="min-h-[48px] w-full flex-1 rounded-md border border-white/20 bg-[#3D6B37]/90 px-4 py-3 text-[15px] text-white placeholder:text-[#B8D4B4] outline-none ring-0 transition-colors placeholder:font-normal focus:border-white/40 focus:bg-[#3D6B37]"
                            />
                            <button
                                id="charity-donate-submit"
                                type="button"
                                data-open-charity-donate
                                class="inline-flex min-h-[48px] shrink-0 items-center justify-center whitespace-nowrap rounded-lg bg-white px-6 py-3 text-[15px] font-bold text-[#A4D433] shadow-sm transition-opacity hover:opacity-95 sm:min-h-[48px] sm:px-8 sm:text-[16px]"
                            >
                                Donate $25
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <div
        id="charity-donate-modal"
        class="fixed inset-0 z-[200] hidden items-center justify-center bg-black/50 p-4"
        role="dialog"
        aria-modal="true"
        aria-labelledby="charity-donate-modal-title"
        data-stripe-key="{{ $stripePublishableKey ?? '' }}"
        data-payment-intent-url="{{ route('charity.donation.payment-intent') }}"
        data-store-url="{{ route('charity.donation.store') }}"
    >
        <div class="relative max-h-[92vh] w-full max-w-[520px] overflow-y-auto rounded-[14px] bg-white p-6 shadow-xl ring-1 ring-black/10 sm:p-8">
            <div class="mb-5 flex items-start justify-between gap-3">
                <div>
                    <h3 id="charity-donate-modal-title" class="text-[20px] font-bold text-[#212121]">Make a Donation</h3>
                    <p class="mt-1 text-[13px] text-[#757575]">Your contribution supports youth tennis programs and scholarships.</p>
                </div>
                <button
                    type="button"
                    data-close-charity-donate
                    class="rounded-lg p-1 text-[#757575] transition-colors hover:bg-[#F3F4F6]"
                    aria-label="Close"
                >
                    <span class="text-[22px] leading-none">&times;</span>
                </button>
            </div>

            <div
                id="charity-donate-modal-alert"
                hidden
                role="alert"
                class="mb-4 rounded-lg border px-4 py-3 text-[13px] font-semibold"
            ></div>

            <form id="charity-donate-form" class="space-y-4">
                <div>
                    <label for="charity-donate-modal-amount" class="mb-1 block text-[13px] font-semibold text-[#424242]">Donation amount ($)</label>
                    <input
                        id="charity-donate-modal-amount"
                        type="text"
                        inputmode="decimal"
                        required
                        value="25"
                        class="min-h-[44px] w-full rounded-lg border border-[#E0E0E0] px-4 py-2.5 text-[15px] text-[#212121] outline-none transition-colors focus:border-[#60a04b] focus:ring-2 focus:ring-[#60a04b]/20"
                    />
                </div>

                <div>
                    <label for="charity-donate-name" class="mb-1 block text-[13px] font-semibold text-[#424242]">Full name <span class="text-red-600">*</span></label>
                    <input
                        id="charity-donate-name"
                        name="donor_name"
                        type="text"
                        required
                        autocomplete="name"
                        class="min-h-[44px] w-full rounded-lg border border-[#E0E0E0] px-4 py-2.5 text-[15px] text-[#212121] outline-none transition-colors focus:border-[#60a04b] focus:ring-2 focus:ring-[#60a04b]/20"
                    />
                </div>

                <div>
                    <label for="charity-donate-email" class="mb-1 block text-[13px] font-semibold text-[#424242]">Email</label>
                    <input
                        id="charity-donate-email"
                        name="email"
                        type="email"
                        autocomplete="email"
                        class="min-h-[44px] w-full rounded-lg border border-[#E0E0E0] px-4 py-2.5 text-[15px] text-[#212121] outline-none transition-colors focus:border-[#60a04b] focus:ring-2 focus:ring-[#60a04b]/20"
                    />
                </div>

                <div>
                    <label for="charity-donate-address" class="mb-1 block text-[13px] font-semibold text-[#424242]">Street address <span class="text-red-600">*</span></label>
                    <input
                        id="charity-donate-address"
                        name="address"
                        type="text"
                        required
                        autocomplete="street-address"
                        class="min-h-[44px] w-full rounded-lg border border-[#E0E0E0] px-4 py-2.5 text-[15px] text-[#212121] outline-none transition-colors focus:border-[#60a04b] focus:ring-2 focus:ring-[#60a04b]/20"
                    />
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="charity-donate-city" class="mb-1 block text-[13px] font-semibold text-[#424242]">City <span class="text-red-600">*</span></label>
                        <input
                            id="charity-donate-city"
                            name="city"
                            type="text"
                            required
                            autocomplete="address-level2"
                            class="min-h-[44px] w-full rounded-lg border border-[#E0E0E0] px-4 py-2.5 text-[15px] text-[#212121] outline-none transition-colors focus:border-[#60a04b] focus:ring-2 focus:ring-[#60a04b]/20"
                        />
                    </div>
                    <div>
                        <label for="charity-donate-state" class="mb-1 block text-[13px] font-semibold text-[#424242]">State <span class="text-red-600">*</span></label>
                        <input
                            id="charity-donate-state"
                            name="state"
                            type="text"
                            required
                            autocomplete="address-level1"
                            class="min-h-[44px] w-full rounded-lg border border-[#E0E0E0] px-4 py-2.5 text-[15px] text-[#212121] outline-none transition-colors focus:border-[#60a04b] focus:ring-2 focus:ring-[#60a04b]/20"
                        />
                    </div>
                </div>

                <div>
                    <label for="charity-donate-zip" class="mb-1 block text-[13px] font-semibold text-[#424242]">ZIP / Postal code</label>
                    <input
                        id="charity-donate-zip"
                        name="zip"
                        type="text"
                        autocomplete="postal-code"
                        class="min-h-[44px] w-full rounded-lg border border-[#E0E0E0] px-4 py-2.5 text-[15px] text-[#212121] outline-none transition-colors focus:border-[#60a04b] focus:ring-2 focus:ring-[#60a04b]/20"
                    />
                </div>

                <div>
                    <label class="mb-1 block text-[13px] font-semibold text-[#424242]">Card details <span class="text-red-600">*</span></label>
                    <div id="charity-donate-card-element" class="min-h-[46px] rounded-lg border border-[#E0E0E0] bg-white px-4 py-3"></div>
                    <p id="charity-donate-card-error" hidden class="mt-1 text-[12px] font-semibold text-red-600"></p>
                </div>

                <div class="flex flex-col gap-3 pt-2 sm:flex-row sm:items-center sm:justify-between">
                    <p id="charity-donate-modal-loader" class="hidden text-[13px] font-medium text-[#60a04b]">Processing payment…</p>
                    <button
                        id="charity-donate-modal-submit"
                        type="submit"
                        class="inline-flex min-h-[48px] w-full items-center justify-center rounded-lg bg-[#60a04b] px-8 py-3 text-[15px] font-bold text-white shadow-sm transition-opacity hover:opacity-95 sm:ml-auto sm:w-auto"
                    >
                        Donate $25
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            (function () {
                var root = document.getElementById('charity-donate-widget');
                if (!root) return;
                var presets = root.querySelectorAll('[data-donate-preset]');
                var other = document.getElementById('charity-donate-other');
                var submit = document.getElementById('charity-donate-submit');
                var inactiveCls =
                    'donate-preset-btn flex min-h-[44px] items-center justify-center rounded-md border border-white/25 bg-white/10 px-1 text-[13px] font-semibold text-white transition-colors hover:bg-white/15 sm:px-2 sm:text-[14px]';
                var activeCls =
                    'donate-preset-btn flex min-h-[44px] items-center justify-center rounded-md border border-white bg-white px-1 text-[13px] font-bold text-[#A4D433] sm:px-2 sm:text-[14px]';
                var selected = 25;

                function parseMoney(str) {
                    var n = parseFloat(String(str).replace(/[^0-9.]/g, ''), 10);
                    return isNaN(n) ? null : n;
                }

                function updateSubmitLabel(amount) {
                    if (!submit || amount == null || amount <= 0) return;
                    var rounded = Math.round(amount * 100) / 100;
                    submit.textContent = 'Donate $' + rounded;
                }

                function setPresetActive(amt) {
                    selected = amt;
                    presets.forEach(function (btn) {
                        var v = parseInt(btn.getAttribute('data-donate-preset'), 10);
                        btn.className = v === amt ? activeCls : inactiveCls;
                    });
                    updateSubmitLabel(amt);
                }

                presets.forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        if (other) other.value = '';
                        setPresetActive(parseInt(btn.getAttribute('data-donate-preset'), 10));
                    });
                });

                if (other) {
                    other.addEventListener('input', function () {
                        var val = parseMoney(other.value);
                        if (val != null && val > 0) {
                            presets.forEach(function (btn) {
                                btn.className = inactiveCls;
                            });
                            updateSubmitLabel(val);
                        } else if (!other.value.trim()) {
                            setPresetActive(selected);
                        }
                    });
                    other.addEventListener('focus', function () {
                        presets.forEach(function (btn) {
                            btn.className = inactiveCls;
                        });
                    });
                    other.addEventListener('blur', function () {
                        if (!other.value.trim()) {
                            setPresetActive(selected);
                        }
                    });
                }

                setPresetActive(25);
            })();
        </script>
    @endpush
@endsection
