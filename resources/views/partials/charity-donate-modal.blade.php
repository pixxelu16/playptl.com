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
                <p id="charity-donate-modal-subtitle" class="mt-1 text-[13px] text-[#757575]">
                    @if (! empty($selectedCharityCause))
                        Supporting: {{ $selectedCharityCause->title }}
                    @else
                        Your contribution supports our charity programs.
                    @endif
                </p>
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
            <input
                type="hidden"
                id="charity-donate-cause-id"
                name="charity_cause_id"
                value="{{ $selectedCharityCause->id ?? '' }}"
            />

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
                <input id="charity-donate-name" name="donor_name" type="text" required autocomplete="name" class="min-h-[44px] w-full rounded-lg border border-[#E0E0E0] px-4 py-2.5 text-[15px] text-[#212121] outline-none transition-colors focus:border-[#60a04b] focus:ring-2 focus:ring-[#60a04b]/20" />
            </div>

            <div>
                <label for="charity-donate-email" class="mb-1 block text-[13px] font-semibold text-[#424242]">Email</label>
                <input id="charity-donate-email" name="email" type="email" autocomplete="email" class="min-h-[44px] w-full rounded-lg border border-[#E0E0E0] px-4 py-2.5 text-[15px] text-[#212121] outline-none transition-colors focus:border-[#60a04b] focus:ring-2 focus:ring-[#60a04b]/20" />
            </div>

            <div>
                <label for="charity-donate-address" class="mb-1 block text-[13px] font-semibold text-[#424242]">Street address <span class="text-red-600">*</span></label>
                <input id="charity-donate-address" name="address" type="text" required autocomplete="street-address" class="min-h-[44px] w-full rounded-lg border border-[#E0E0E0] px-4 py-2.5 text-[15px] text-[#212121] outline-none transition-colors focus:border-[#60a04b] focus:ring-2 focus:ring-[#60a04b]/20" />
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label for="charity-donate-city" class="mb-1 block text-[13px] font-semibold text-[#424242]">City <span class="text-red-600">*</span></label>
                    <input id="charity-donate-city" name="city" type="text" required autocomplete="address-level2" class="min-h-[44px] w-full rounded-lg border border-[#E0E0E0] px-4 py-2.5 text-[15px] text-[#212121] outline-none transition-colors focus:border-[#60a04b] focus:ring-2 focus:ring-[#60a04b]/20" />
                </div>
                <div>
                    <label for="charity-donate-state" class="mb-1 block text-[13px] font-semibold text-[#424242]">State <span class="text-red-600">*</span></label>
                    <input id="charity-donate-state" name="state" type="text" required autocomplete="address-level1" class="min-h-[44px] w-full rounded-lg border border-[#E0E0E0] px-4 py-2.5 text-[15px] text-[#212121] outline-none transition-colors focus:border-[#60a04b] focus:ring-2 focus:ring-[#60a04b]/20" />
                </div>
            </div>

            <div>
                <label for="charity-donate-zip" class="mb-1 block text-[13px] font-semibold text-[#424242]">ZIP / Postal code</label>
                <input id="charity-donate-zip" name="zip" type="text" autocomplete="postal-code" class="min-h-[44px] w-full rounded-lg border border-[#E0E0E0] px-4 py-2.5 text-[15px] text-[#212121] outline-none transition-colors focus:border-[#60a04b] focus:ring-2 focus:ring-[#60a04b]/20" />
            </div>

            <div>
                <label class="mb-1 block text-[13px] font-semibold text-[#424242]">Card details <span class="text-red-600">*</span></label>
                <div id="charity-donate-card-element" class="min-h-[46px] rounded-lg border border-[#E0E0E0] bg-white px-4 py-3"></div>
                <p id="charity-donate-card-error" hidden class="mt-1 text-[12px] font-semibold text-red-600"></p>
            </div>

            <div class="flex flex-col gap-3 pt-2 sm:flex-row sm:items-center sm:justify-between">
                <p id="charity-donate-modal-loader" class="hidden text-[13px] font-medium text-[#60a04b]">Processing payment…</p>
                <button id="charity-donate-modal-submit" type="submit" class="inline-flex min-h-[48px] w-full items-center justify-center rounded-lg bg-[#60a04b] px-8 py-3 text-[15px] font-bold text-white shadow-sm transition-opacity hover:opacity-95 sm:ml-auto sm:w-auto">
                    Donate $25
                </button>
            </div>
        </form>
    </div>
</div>
