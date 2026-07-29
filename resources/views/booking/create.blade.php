@extends('layouts.website')

@section('nav_active', 'player-services')
@section('title', 'Book ' . $user->name . ' | Premier Tennis League')

@section('content')

{{-- Hero --}}
<section class="relative overflow-hidden bg-[#1A2F1A] py-14">
    <div class="mx-auto max-w-[1200px] px-5 sm:px-8">
        <div class="mx-auto max-w-[1200px] px-5 sm:px-8">
            <nav class="mb-4 flex flex-wrap items-center gap-1 text-xs font-semibold uppercase tracking-widest text-gray-300">
                <a href="{{ url('/') }}" class="hover:text-[#B4F000] transition">Home</a>
                <span class="mx-1">&gt;&gt;</span>
                <a href="{{ $roleName === 'mentor' ? route('player-services.mentors') : route('player-services.coaches') }}"
                   class="hover:text-[#B4F000] transition">
                    {{ $roleName === 'mentor' ? 'Mentors' : 'Coach Marketplace' }}
                </a>
                <span class="mx-1">&gt;&gt;</span>
                <a href="{{ route('player-services.' . $roleName . '.show', $user->username) }}"
                   class="hover:text-[#B4F000] transition">{{ $user->name }}</a>
                <span class="mx-1">&gt;&gt;</span>
                <span class="text-[#B4F000]">Book Session</span>
            </nav>
            <h1 class="league-1 text-[clamp(2.8rem,8vw,4.5rem)] font-normal uppercase leading-[0.95] tracking-[0.02em]">
                <span class="text-white">BOOK</span><span class="text-[#B4F000]"> {{ strtoupper($roleName) }}</span>
            </h1>
        </div>
    </div>
</section>

<section class="bg-[#E8F5E9] py-12">
    <div class="mx-auto max-w-[1100px] px-5 sm:px-8">
        <div class="flex flex-col lg:flex-row gap-8 items-start">

            {{-- LEFT: Provider Summary Card --}}
            <div class="w-full lg:w-72 flex-shrink-0">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 text-center sticky top-6">
                    {{-- Avatar --}}
                    @if($user->avatar_path)
                        <img src="{{ asset('storage/' . $user->avatar_path) }}" alt="{{ $user->name }}"
                             class="h-24 w-24 rounded-full object-cover mx-auto mb-4 ring-4 ring-[#E8F7E9] shadow">
                    @else
                        @php
                            $initials = strtoupper(substr($user->first_name ?? $user->name, 0, 1) . substr($user->last_name ?? '', 0, 1)) ?: strtoupper(substr($user->name, 0, 2));
                        @endphp
                        <div class="flex h-24 w-24 items-center justify-center rounded-full bg-[#5DA44E] text-3xl font-bold text-white mx-auto mb-4 ring-4 ring-[#E8F7E9]">
                            {{ $initials }}
                        </div>
                    @endif
                    <h2 class="text-lg font-bold text-gray-900 mb-1">{{ $user->name }}</h2>
                    <span class="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-emerald-50 text-[#5DA44E] border border-emerald-100 mb-4">
                        {{ ucfirst($roleName) }}
                    </span>
                    @if($user->city || $user->state)
                        <p class="text-sm text-gray-500 mb-3"><i class="fa-solid fa-location-dot text-[#5DA44E] mr-1"></i>{{ $user->city }}{{ $user->city && $user->state ? ', ' : '' }}{{ $user->state }}</p>
                    @endif
                    @if($user->profile_rate > 0)
                        <div class="mt-3 p-3 bg-[#E8F7E9] rounded-xl">
                             <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Hourly Rate</p>
                             <p class="text-2xl font-extrabold text-[#5DA44E]">{{ $currencySymbol }}{{ number_format($user->profile_rate, 2) }}<span class="text-xs font-normal text-gray-400">/hr</span></p>
                        </div>
                    @else
                        <div class="mt-3 p-3 bg-[#E8F7E9] rounded-xl">
                            <p class="text-lg font-extrabold text-[#5DA44E]">Free Session</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- RIGHT: Booking Form --}}
            <div class="flex-1">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">Booking Details</h2>

                    @if(session('error'))
                        <div class="mb-6 flex items-start gap-3 rounded-xl bg-red-50 border border-red-200 p-4 text-sm text-red-700">
                            <i class="fa-solid fa-circle-exclamation mt-0.5 flex-shrink-0"></i>
                            <span>{{ session('error') }}</span>
                        </div>
                    @endif

                    <form id="bookingForm" action="{{ route('booking.store') }}" method="POST" class="space-y-6">
                        @csrf
                        <input type="hidden" name="provider_id" value="{{ $user->id }}">
                        <input type="hidden" name="stripe_charge_id" id="stripeChargeId">

                        {{-- Message --}}
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-2" for="message">
                                Message to {{ ucfirst($roleName) }}
                            </label>
                            <textarea id="message" name="message" rows="4"
                                placeholder="Introduce yourself, share your goals, and explain what you're looking to achieve..."
                                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-[#5DA44E] focus:outline-none focus:ring-2 focus:ring-[#5DA44E]/20 resize-none transition">{{ old('message') }}</textarea>
                            @error('message')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>

                        {{-- Contact Info --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2" for="student_location">Your Location</label>
                                <input id="student_location" name="student_location" type="text"
                                    value="{{ old('student_location') }}"
                                    placeholder="City, State"
                                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-[#5DA44E] focus:outline-none focus:ring-2 focus:ring-[#5DA44E]/20 transition">
                                @error('student_location')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2" for="student_phone">Your Phone Number</label>
                                <input id="student_phone" name="student_phone" type="tel"
                                    value="{{ old('student_phone') }}"
                                    placeholder="+1 234 567 8900"
                                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-[#5DA44E] focus:outline-none focus:ring-2 focus:ring-[#5DA44E]/20 transition">
                                @error('student_phone')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        {{-- Dates & Hours --}}
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2" for="from_date">From Date</label>
                                <input id="from_date" name="from_date" type="date"
                                    value="{{ old('from_date') }}"
                                    min="{{ date('Y-m-d') }}"
                                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900 focus:border-[#5DA44E] focus:outline-none focus:ring-2 focus:ring-[#5DA44E]/20 transition">
                                @error('from_date')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2" for="to_date">To Date</label>
                                <input id="to_date" name="to_date" type="date"
                                    value="{{ old('to_date') }}"
                                    min="{{ date('Y-m-d') }}"
                                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900 focus:border-[#5DA44E] focus:outline-none focus:ring-2 focus:ring-[#5DA44E]/20 transition">
                                @error('to_date')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2" for="booking_time">Preferred Time</label>
                                <select id="booking_time" name="booking_time"
                                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900 focus:border-[#5DA44E] focus:outline-none focus:ring-2 focus:ring-[#5DA44E]/20 transition">
                                    @php
                                        $startTime = \Carbon\Carbon::parse('05:00');
                                        $endTime = \Carbon\Carbon::parse('22:00');
                                    @endphp
                                    @while($startTime->lte($endTime))
                                        @php
                                            $timeVal = $startTime->format('H:i');
                                            $timeLabel = $startTime->format('h:i A');
                                        @endphp
                                        <option value="{{ $timeVal }}" @selected(old('booking_time', '10:00') === $timeVal)>
                                            {{ $timeLabel }}
                                        </option>
                                        @php
                                            $startTime->addMinutes(30);
                                        @endphp
                                    @endwhile
                                </select>
                                @error('booking_time')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2" for="hours_per_day">Hours / Day</label>
                                <input id="hours_per_day" name="hours_per_day" type="number"
                                    value="{{ old('hours_per_day', 1) }}"
                                    min="0.5" max="24" step="0.5"
                                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900 focus:border-[#5DA44E] focus:outline-none focus:ring-2 focus:ring-[#5DA44E]/20 transition">
                                @error('hours_per_day')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        {{-- Live Booking Summary --}}
                        <div id="bookingSummary" class="hidden rounded-2xl bg-[#E8F7E9] border border-[#c3e6cb] p-5">
                            <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-4">Booking Summary</h3>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Hourly Rate</span>
                                    <span class="font-semibold text-gray-800" id="summaryRate">
                                        @if($user->profile_rate > 0) {{ $currencySymbol }}{{ number_format($user->profile_rate, 2) }}/hr @else Free @endif
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Total Days</span>
                                    <span class="font-semibold text-gray-800" id="summaryDays">—</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Hours / Day</span>
                                    <span class="font-semibold text-gray-800" id="summaryHrsDay">—</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Total Hours</span>
                                    <span class="font-semibold text-gray-800" id="summaryTotalHrs">—</span>
                                </div>
                                <div class="border-t border-[#c3e6cb] pt-2 mt-2 flex justify-between items-center">
                                    <span class="font-bold text-gray-700">Total Amount</span>
                                    <span class="text-2xl font-extrabold text-[#5DA44E]" id="summaryTotal">—</span>
                                </div>
                            </div>
                        </div>

                        {{-- Stripe Payment (only if rate > 0) --}}
                        @if($user->profile_rate > 0)
                            <div id="paymentSection">
                                <div class="mb-4 flex items-start gap-3 rounded-xl bg-amber-50 border border-amber-200 p-4 text-sm text-amber-800">
                                    <i class="fa-solid fa-shield-halved mt-0.5 flex-shrink-0 text-amber-500"></i>
                                    <span>
                                        <strong>Secure Payment:</strong> Your card will be charged when you submit the booking.
                                        If the {{ ucfirst($roleName) }} declines, a <strong>full refund</strong> will be issued automatically.
                                    </span>
                                </div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Card Details</label>
                                <div id="card-element"
                                     class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 min-h-[46px]"></div>
                            </div>
                        @else
                            <div class="flex items-start gap-3 rounded-xl bg-emerald-50 border border-emerald-200 p-4 text-sm text-[#065f46]">
                                <i class="fa-solid fa-circle-check mt-0.5 flex-shrink-0 text-[#5DA44E]"></i>
                                <span>This is a <strong>free session</strong>. No payment required — just submit your request!</span>
                            </div>
                        @endif

                        {{-- Validation Errors Container --}}
                        <div id="card-errors" class="mt-4 hidden items-start gap-3 rounded-xl bg-red-50 border border-red-200 p-4 text-sm text-red-700">
                            <i class="fa-solid fa-circle-exclamation mt-0.5 flex-shrink-0 text-red-500"></i>
                            <span id="card-errors-text"></span>
                        </div>

                        {{-- Submit --}}
                        <button id="submitBtn" type="submit"
                                class="mt-4 flex w-full items-center justify-center gap-3 rounded-xl bg-[#5DA44E] hover:bg-[#4d8f40] px-6 py-4 text-base font-bold text-white shadow-md transition duration-300 disabled:opacity-60 disabled:cursor-not-allowed">
                            <i class="fa-solid fa-calendar-check"></i>
                            <span id="submitBtnText">Submit Booking Request</span>
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</section>

@endsection

@push('scripts')
@if($user->profile_rate > 0)
<script src="https://js.stripe.com/v3/"></script>
@endif
<script>
const hourlyRate    = {{ (float)($user->profile_rate ?? 0) }};
const currencySymbol = '{{ addslashes($currencySymbol) }}';
const providerId    = {{ $user->id }};
const isPaid        = hourlyRate > 0;

// ── Live Booking Summary ─────────────────────────────────────
const fromInput  = document.getElementById('from_date');
const toInput    = document.getElementById('to_date');
const hrsInput   = document.getElementById('hours_per_day');
const summary    = document.getElementById('bookingSummary');

function daysBetween(from, to) {
    const d1 = new Date(from), d2 = new Date(to);
    if (isNaN(d1) || isNaN(d2) || d2 < d1) return 0;
    return Math.round((d2 - d1) / 86400000) + 1;
}

function updateSummary() {
    const days = daysBetween(fromInput.value, toInput.value);
    const hrs  = parseFloat(hrsInput.value) || 0;
    if (days <= 0 || hrs <= 0) { summary.classList.add('hidden'); return; }

    const totalHrs    = days * hrs;
    const totalAmount = totalHrs * hourlyRate;

    document.getElementById('summaryDays').textContent    = days + ' day' + (days > 1 ? 's' : '');
    document.getElementById('summaryHrsDay').textContent  = hrs + ' hr' + (hrs !== 1 ? 's' : '');
    document.getElementById('summaryTotalHrs').textContent = totalHrs + ' hrs';
    document.getElementById('summaryTotal').textContent   = isPaid ? currencySymbol + totalAmount.toFixed(2) : 'Free';
    summary.classList.remove('hidden');
}

fromInput.addEventListener('change', () => { if (toInput.value && toInput.value < fromInput.value) toInput.value = fromInput.value; updateSummary(); });
toInput.addEventListener('change', updateSummary);
hrsInput.addEventListener('input', updateSummary);

function highlightDateFields(highlight) {
    const inputs = [fromInput, toInput, document.getElementById('booking_time')];
    inputs.forEach(input => {
        if (highlight) {
            input.style.border = '2px solid #ef4444';
            input.style.boxShadow = '0 0 0 3px rgba(239, 68, 68, 0.2)';
        } else {
            input.style.border = '';
            input.style.boxShadow = '';
        }
    });
}

function showError(message) {
    const errContainer = document.getElementById('card-errors');
    const errText = document.getElementById('card-errors-text');
    if (!errContainer) return;

    if (message) {
        errText.textContent = message;
        errContainer.classList.remove('hidden');
        errContainer.classList.add('flex');
        if (message.includes('available') || message.includes('dates') || message.includes('time') || message.includes('overlap')) {
            highlightDateFields(true);
        } else {
            highlightDateFields(false);
        }
    } else {
        errContainer.classList.add('hidden');
        errContainer.classList.remove('flex');
        highlightDateFields(false);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    updateSummary();
    const sessionError = @json(session('error'));
    if (sessionError && (sessionError.includes('available') || sessionError.includes('dates') || sessionError.includes('time') || sessionError.includes('overlap'))) {
        highlightDateFields(true);
    }
});

// ── Stripe Integration ────────────────────────────────────────
@if($user->profile_rate > 0)
const stripe      = Stripe('{{ $stripePublishableKey }}');
const elements    = stripe.elements();
const cardElement = elements.create('card', {
    style: { base: { fontSize: '15px', color: '#111827', fontFamily: 'inherit', '::placeholder': { color: '#9ca3af' } } }
});
cardElement.mount('#card-element');
cardElement.on('change', (e) => {
    showError(e.error ? e.error.message : '');
});

const form      = document.getElementById('bookingForm');
const submitBtn = document.getElementById('submitBtn');
const submitTxt = document.getElementById('submitBtnText');

form.addEventListener('submit', async function(e) {
    e.preventDefault();
    showError(''); // Clear previous errors

    // Custom Client-Side JS validation
    const message = document.getElementById('message').value.trim();
    const location = document.getElementById('student_location').value.trim();
    const phone = document.getElementById('student_phone').value.trim();
    const fromVal = fromInput.value.trim();
    const toVal = toInput.value.trim();
    const timeVal = document.getElementById('booking_time').value.trim();
    const hrsVal = hrsInput.value.trim();

    if (!message) {
        showError('Message to {{ $roleName }} is required.');
        return;
    }
    if (!location) {
        showError('Your Location is required.');
        return;
    }
    if (!phone) {
        showError('Your Phone Number is required.');
        return;
    }
    if (!fromVal) {
        showError('From Date is required.');
        return;
    }
    if (!toVal) {
        showError('To Date is required.');
        return;
    }
    if (!timeVal) {
        showError('Preferred Time is required.');
        return;
    }
    if (!hrsVal || parseFloat(hrsVal) <= 0) {
        showError('Hours / Day must be at least 0.5.');
        return;
    }

    submitBtn.disabled = true;

    @if($user->profile_rate > 0)
    submitTxt.textContent = 'Processing...';

    // Fetch PaymentIntent
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const piRes = await fetch('{{ route('booking.payment-intent') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({
            provider_id:   providerId,
            from_date:     fromInput.value,
            to_date:       toInput.value,
            booking_time:  document.getElementById('booking_time').value,
            hours_per_day: hrsInput.value,
        }),
    });

    const piData = await piRes.json();
    if (!piRes.ok) {
        showError(piData.message || 'Payment setup failed.');
        submitBtn.disabled = false;
        submitTxt.textContent = 'Submit Booking Request';
        return;
    }

    // Confirm card payment
    const { paymentIntent, error } = await stripe.confirmCardPayment(piData.client_secret, {
        payment_method: { card: cardElement },
    });

    if (error) {
        showError(error.message);
        submitBtn.disabled = false;
        submitTxt.textContent = 'Submit Booking Request';
        return;
    }

    // Set charge ID and submit form
    document.getElementById('stripeChargeId').value = paymentIntent.id;
    form.submit();
    @else
    submitTxt.textContent = 'Submitting...';
    form.submit();
    @endif
});
@endif
</script>
@endpush
