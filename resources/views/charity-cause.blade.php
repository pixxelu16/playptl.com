@extends('layouts.website')

@section('nav_active', 'charity')

@section('title', $charityCause->title.' | Charity | Premier Tennis League')
@section('meta_description', Str::limit($charityCause->description, 160))

@section('header_class', 'absolute inset-x-0 top-0 z-[100] bg-transparent px-5 pb-4 pt-6 sm:px-8 lg:px-14')

@section('content')
    <main>
        <section class="relative flex min-h-[420px] flex-col overflow-hidden sm:min-h-[480px]">
            <img
                src="{{ asset($charityCause->image_path) }}"
                alt=""
                class="absolute inset-0 z-0 h-full w-full object-cover"
                width="1600"
                height="900"
            />
            <div class="pointer-events-none absolute inset-0 z-[1] bg-[rgba(0,0,0,0.62)]" aria-hidden="true"></div>

            <div class="relative z-10 mx-auto flex w-full max-w-[1400px] flex-1 flex-col justify-end px-5 py-10 sm:px-8 sm:py-14 lg:px-14">
                <nav class="mb-4 flex flex-wrap items-center gap-x-1 gap-y-2 text-[13px] font-semibold uppercase tracking-[0.24em] text-[#C1D72E]" aria-label="Breadcrumb">
                    <a href="{{ url('/') }}" class="text-[#C1D72E] transition-opacity hover:opacity-90">Home</a>
                    <span class="mx-1">&gt;&gt;</span>
                    <a href="{{ route('charity') }}" class="text-[#C1D72E] transition-opacity hover:opacity-90">Charity</a>
                    <span class="mx-1">&gt;&gt;</span>
                    <span class="text-[#C1D72E]">{{ $charityCause->title }}</span>
                </nav>

                <h1 class="max-w-4xl text-[clamp(2rem,5vw,3.25rem)] font-bold leading-tight text-white">{{ $charityCause->title }}</h1>
                <p class="mt-4 max-w-3xl text-[15px] leading-relaxed text-white/90 sm:text-[16px]">{{ $charityCause->description }}</p>
            </div>
        </section>

        <section class="bg-[#E4F7E7] py-10 font-sans antialiased sm:py-12 lg:py-16" aria-labelledby="charity-support-heading">
            <div class="mx-auto max-w-[900px] px-5 sm:px-8 lg:px-14">
                <h2 id="charity-support-heading" class="text-center text-[clamp(1.125rem,2.5vw,1.5rem)] font-bold uppercase tracking-[0.08em] text-[#1B3022]">
                    How Would You Like To Help?
                </h2>
                <p class="mx-auto mt-3 max-w-2xl text-center text-[14px] leading-relaxed text-[#666666] sm:text-[15px]">
                    Choose a contribution type for this cause. You can donate materials, volunteer your time, or make a monetary donation.
                </p>

                <div class="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <button
                        type="button"
                        data-charity-type="material"
                        class="charity-type-btn rounded-[14px] border border-[#D7EAD9] bg-white px-5 py-6 text-left shadow-sm transition hover:border-[#60a04b] hover:shadow-md"
                    >
                        <p class="text-[12px] font-semibold uppercase tracking-wide text-[#60a04b]">Material</p>
                        <p class="mt-2 text-[18px] font-bold text-[#212121]">Donate Items</p>
                        <p class="mt-2 text-[13px] leading-relaxed text-[#666666]">Share equipment, supplies, or other materials.</p>
                    </button>
                    <button
                        type="button"
                        data-charity-type="person"
                        class="charity-type-btn rounded-[14px] border border-[#D7EAD9] bg-white px-5 py-6 text-left shadow-sm transition hover:border-[#60a04b] hover:shadow-md"
                    >
                        <p class="text-[12px] font-semibold uppercase tracking-wide text-[#60a04b]">Person</p>
                        <p class="mt-2 text-[18px] font-bold text-[#212121]">Volunteer</p>
                        <p class="mt-2 text-[13px] leading-relaxed text-[#666666]">Offer your time and support as a volunteer.</p>
                    </button>
                    <button
                        type="button"
                        data-charity-type="money"
                        class="charity-type-btn rounded-[14px] border border-[#D7EAD9] bg-white px-5 py-6 text-left shadow-sm transition hover:border-[#60a04b] hover:shadow-md"
                    >
                        <p class="text-[12px] font-semibold uppercase tracking-wide text-[#60a04b]">Money</p>
                        <p class="mt-2 text-[18px] font-bold text-[#212121]">Monetary Donation</p>
                        <p class="mt-2 text-[13px] leading-relaxed text-[#666666]">Contribute securely with card payment.</p>
                    </button>
                </div>

                <div
                    id="charity-contribute-panel"
                    class="mt-8 hidden rounded-[14px] bg-white p-6 shadow-[0_2px_16px_rgba(0,0,0,0.06)] ring-1 ring-black/[0.05] sm:p-8"
                >
                    <h3 id="charity-contribute-title" class="text-[20px] font-bold text-[#212121]"></h3>
                    <p id="charity-contribute-help" class="mt-2 text-[14px] text-[#666666]"></p>

                    <div id="charity-contribute-alert" hidden class="mt-4 rounded-lg border px-4 py-3 text-[13px] font-semibold"></div>

                    <form id="charity-contribute-form" class="mt-6 space-y-4">
                        <input type="hidden" id="charity-contribute-type" name="donation_type" value="">

                        <div>
                            <label for="charity-contribute-name" class="mb-1 block text-[13px] font-semibold text-[#424242]">Full name <span class="text-red-600">*</span></label>
                            <input id="charity-contribute-name" name="donor_name" type="text" required class="min-h-[44px] w-full rounded-lg border border-[#E0E0E0] bg-white px-4 py-2.5 text-[15px] text-[#212121] outline-none focus:border-[#60a04b] focus:ring-2 focus:ring-[#60a04b]/20" />
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="charity-contribute-email" class="mb-1 block text-[13px] font-semibold text-[#424242]">Email</label>
                                <input id="charity-contribute-email" name="email" type="email" class="min-h-[44px] w-full rounded-lg border border-[#E0E0E0] bg-white px-4 py-2.5 text-[15px] text-[#212121] outline-none focus:border-[#60a04b] focus:ring-2 focus:ring-[#60a04b]/20" />
                            </div>
                            <div>
                                <label for="charity-contribute-phone" class="mb-1 block text-[13px] font-semibold text-[#424242]">Phone</label>
                                <input id="charity-contribute-phone" name="phone" type="text" class="min-h-[44px] w-full rounded-lg border border-[#E0E0E0] bg-white px-4 py-2.5 text-[15px] text-[#212121] outline-none focus:border-[#60a04b] focus:ring-2 focus:ring-[#60a04b]/20" />
                            </div>
                        </div>

                        <div id="charity-material-field" class="hidden">
                            <label for="charity-contribute-material-detail" class="mb-1 block text-[13px] font-semibold text-[#424242]">What material are you donating? <span class="text-red-600">*</span></label>
                            <input id="charity-contribute-material-detail" name="material_detail" type="text" class="min-h-[44px] w-full rounded-lg border border-[#E0E0E0] bg-white px-4 py-2.5 text-[15px] text-[#212121] outline-none focus:border-[#60a04b] focus:ring-2 focus:ring-[#60a04b]/20" />
                        </div>

                        <div id="charity-quantity-field">
                            <label for="charity-contribute-quantity" id="charity-contribute-quantity-label" class="mb-1 block text-[13px] font-semibold text-[#424242]">Quantity <span class="text-red-600">*</span></label>
                            <input id="charity-contribute-quantity" name="quantity" type="number" min="0.01" step="0.01" class="min-h-[44px] w-full rounded-lg border border-[#E0E0E0] bg-white px-4 py-2.5 text-[15px] text-[#212121] outline-none focus:border-[#60a04b] focus:ring-2 focus:ring-[#60a04b]/20" />
                        </div>

                        <div id="charity-money-fields" class="hidden space-y-4">
                            <div>
                                <label for="charity-contribute-amount" class="mb-1 block text-[13px] font-semibold text-[#424242]">Donation amount ($) <span class="text-red-600">*</span></label>
                                <input id="charity-contribute-amount" name="amount" type="text" inputmode="decimal" value="25" class="min-h-[44px] w-full rounded-lg border border-[#E0E0E0] bg-white px-4 py-2.5 text-[15px] text-[#212121] outline-none focus:border-[#60a04b] focus:ring-2 focus:ring-[#60a04b]/20" />
                            </div>

                            <div>
                                <label for="charity-contribute-address" class="mb-1 block text-[13px] font-semibold text-[#424242]">Street address <span class="text-red-600">*</span></label>
                                <input id="charity-contribute-address" name="address" type="text" autocomplete="street-address" class="min-h-[44px] w-full rounded-lg border border-[#E0E0E0] bg-white px-4 py-2.5 text-[15px] text-[#212121] outline-none focus:border-[#60a04b] focus:ring-2 focus:ring-[#60a04b]/20" />
                            </div>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="charity-contribute-city" class="mb-1 block text-[13px] font-semibold text-[#424242]">City <span class="text-red-600">*</span></label>
                                    <input id="charity-contribute-city" name="city" type="text" autocomplete="address-level2" class="min-h-[44px] w-full rounded-lg border border-[#E0E0E0] bg-white px-4 py-2.5 text-[15px] text-[#212121] outline-none focus:border-[#60a04b] focus:ring-2 focus:ring-[#60a04b]/20" />
                                </div>
                                <div>
                                    <label for="charity-contribute-state" class="mb-1 block text-[13px] font-semibold text-[#424242]">State <span class="text-red-600">*</span></label>
                                    <input id="charity-contribute-state" name="state" type="text" autocomplete="address-level1" class="min-h-[44px] w-full rounded-lg border border-[#E0E0E0] bg-white px-4 py-2.5 text-[15px] text-[#212121] outline-none focus:border-[#60a04b] focus:ring-2 focus:ring-[#60a04b]/20" />
                                </div>
                            </div>

                            <div>
                                <label for="charity-contribute-zip" class="mb-1 block text-[13px] font-semibold text-[#424242]">ZIP / Postal code</label>
                                <input id="charity-contribute-zip" name="zip" type="text" autocomplete="postal-code" class="min-h-[44px] w-full rounded-lg border border-[#E0E0E0] bg-white px-4 py-2.5 text-[15px] text-[#212121] outline-none focus:border-[#60a04b] focus:ring-2 focus:ring-[#60a04b]/20" />
                            </div>

                            <div>
                                <label class="mb-1 block text-[13px] font-semibold text-[#424242]">Card details <span class="text-red-600">*</span></label>
                                <div id="charity-contribute-card-element" class="min-h-[46px] rounded-lg border border-[#E0E0E0] bg-white px-4 py-3"></div>
                                <p id="charity-contribute-card-error" hidden class="mt-1 text-[12px] font-semibold text-red-600"></p>
                            </div>

                            <p id="charity-contribute-loader" class="hidden text-[13px] font-medium text-[#60a04b]">Processing payment…</p>
                        </div>

                        <button id="charity-contribute-submit" type="submit" class="inline-flex min-h-[48px] items-center justify-center rounded-lg bg-[#60a04b] px-8 py-3 text-[15px] font-bold text-white hover:opacity-95">
                            Submit Contribution
                        </button>
                    </form>
                </div>
            </div>
        </section>
    </main>

    @push('scripts')
        <script>
            window.charityCausePage = {
                causeId: {{ $charityCause->id }},
                causeTitle: @json($charityCause->title),
                contributeUrl: @json(route('charity.cause.contribute', $charityCause)),
                stripeKey: @json($stripePublishableKey ?? ''),
                paymentIntentUrl: @json(route('charity.donation.payment-intent')),
                storeUrl: @json(route('charity.donation.store')),
            };
        </script>
        <script src="{{ asset('frontend/js/charity-cause.js') }}?v={{ @filemtime(public_path('frontend/js/charity-cause.js')) ?: time() }}"></script>
    @endpush
@endsection
