@php $currentRole = auth()->user()->hasRole('Mentor') ? 'Mentor' : 'Coach'; @endphp
@extends('layouts.role-profile', ['roleName' => $currentRole, 'activeSection' => 'bookings'])

@section('profile_panel')
<div class="space-y-6">

    <a href="{{ route('provider.bookings') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-[#5DA44E] hover:underline">
        <i class="fa-solid fa-arrow-left"></i> Back to Bookings
    </a>

    @if(session('success'))
        <div class="flex items-center gap-2 rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800">
            <i class="fa-solid fa-circle-check text-[#5DA44E]"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="flex items-center gap-2 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
            <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
        </div>
    @endif

    @php
        $statusColors = [
            'pending'   => 'bg-yellow-100 text-yellow-800 border-yellow-300',
            'accepted'  => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'rejected'  => 'bg-red-100 text-red-800 border-red-300',
            'cancelled' => 'bg-gray-100 text-gray-600 border-gray-300',
            'completed' => 'bg-blue-100 text-blue-800 border-blue-300',
        ];
        $sc = $statusColors[$booking->status] ?? 'bg-gray-100 text-gray-600 border-gray-300';
    @endphp

    <div class="overflow-hidden rounded-[12px] bg-white shadow-[0_1px_8px_rgba(0,0,0,0.06)] ring-1 ring-[#E0E0E0]">
        <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100">
            <div>
                <h3 class="text-xl font-bold text-gray-900">Booking from {{ $booking->student->name }}</h3>
                <p class="text-sm text-gray-500">Submitted {{ $booking->created_at->diffForHumans() }}</p>
            </div>
            <span class="px-4 py-1.5 rounded-full text-sm font-bold border {{ $sc }}">{{ $booking->statusLabel() }}</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-0 divide-y md:divide-y-0 md:divide-x divide-gray-100">
            {{-- Left: Booking Details --}}
            <div class="p-6 space-y-4">
                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Session Details</h4>
                <div class="space-y-3 text-sm">
                    <div class="flex items-start gap-3">
                        <i class="fa-solid fa-user text-[#5DA44E] mt-0.5 w-4"></i>
                        <div><p class="text-xs text-gray-400">Student</p><p class="font-semibold text-gray-800">{{ $booking->student->name }}</p></div>
                    </div>
                    @if($booking->student->email)
                    <div class="flex items-start gap-3">
                        <i class="fa-solid fa-envelope text-[#5DA44E] mt-0.5 w-4"></i>
                        <div><p class="text-xs text-gray-400">Student Email</p><p class="font-semibold text-gray-800">{{ $booking->student->email }}</p></div>
                    </div>
                    @endif
                    @if($booking->student_location)
                    <div class="flex items-start gap-3">
                        <i class="fa-solid fa-location-dot text-[#5DA44E] mt-0.5 w-4"></i>
                        <div><p class="text-xs text-gray-400">Student Location</p><p class="font-semibold text-gray-800">{{ $booking->student_location }}</p></div>
                    </div>
                    @endif
                    @if($booking->student_phone)
                    <div class="flex items-start gap-3">
                        <i class="fa-solid fa-phone text-[#5DA44E] mt-0.5 w-4"></i>
                        <div><p class="text-xs text-gray-400">Student Phone</p><p class="font-semibold text-gray-800">{{ $booking->student_phone }}</p></div>
                    </div>
                    @endif
                    <div class="flex items-start gap-3">
                        <i class="fa-solid fa-calendar text-[#5DA44E] mt-0.5 w-4"></i>
                        <div><p class="text-xs text-gray-400">Dates</p><p class="font-semibold text-gray-800">{{ $booking->from_date->format('M d, Y') }} → {{ $booking->to_date->format('M d, Y') }}</p></div>
                    </div>
                    <div class="flex items-start gap-3">
                        <i class="fa-solid fa-clock text-[#5DA44E] mt-0.5 w-4"></i>
                        <div><p class="text-xs text-gray-400">Duration</p><p class="font-semibold text-gray-800">{{ $booking->total_days }} days × {{ $booking->hours_per_day }} hrs/day = {{ $booking->total_hours }} hrs</p></div>
                    </div>
                    @if($booking->booking_time)
                    <div class="flex items-start gap-3">
                        <i class="fa-solid fa-hourglass-start text-[#5DA44E] mt-0.5 w-4"></i>
                        <div><p class="text-xs text-gray-400">Preferred Time</p><p class="font-semibold text-gray-800">{{ Carbon\Carbon::parse($booking->booking_time)->format('g:i A') }}</p></div>
                    </div>
                    @endif
                    @if($booking->message)
                    <div class="flex items-start gap-3">
                        <i class="fa-solid fa-message text-[#5DA44E] mt-0.5 w-4"></i>
                        <div><p class="text-xs text-gray-400">Student's Message</p><p class="text-gray-700 italic mt-1">"{{ $booking->message }}"</p></div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Right: Payment & Actions --}}
            <div class="p-6 space-y-4">
                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Payment & Earnings</h4>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Total Charged to Student</span>
                        <span class="font-semibold text-gray-800">@if($booking->total_amount > 0) {{ $currencySymbol }}{{ number_format($booking->total_amount, 2) }} @else Free @endif</span>
                    </div>
                    <div class="flex justify-between text-sm text-amber-700">
                        <span>Platform Share ({{ number_format(100 - $booking->commission_rate, 2) }}%)</span>
                        <span class="font-semibold">- {{ $currencySymbol }}{{ number_format($booking->commission_amount, 2) }}</span>
                    </div>
                    <div class="border-t border-gray-100 pt-2 mt-2 flex justify-between items-center">
                        <span class="font-bold text-gray-700">Your Earnings ({{ $booking->commission_rate }}%)</span>
                        <span class="text-xl font-extrabold text-[#5DA44E]">{{ $currencySymbol }}{{ number_format($booking->provider_amount, 2) }}</span>
                    </div>
                    @if($booking->stripe_refund_id)
                    <div class="flex justify-between text-red-600 text-xs">
                        <span>Refund Issued</span><span class="font-bold">Refund ID: {{ $booking->stripe_refund_id }}</span>
                    </div>
                    @endif
                </div>

                {{-- Accept / Reject Buttons --}}
                @if($booking->isPending())
                <div class="mt-6 space-y-3 pt-4 border-t border-gray-100">
                    <form action="{{ route('provider.booking.accept', $booking) }}" method="POST" class="confirm-form"
                          data-title="Accept Booking" data-text="Are you sure you want to accept this booking request? The student will be notified." data-confirm-text="Yes, accept booking" data-confirm-color="#5DA44E">
                        @csrf @method('PATCH')
                        <button type="submit"
                                class="w-full flex items-center justify-center gap-2 rounded-xl bg-[#5DA44E] hover:bg-[#4d8f40] px-4 py-3 text-sm font-bold text-white shadow-sm transition">
                            <i class="fa-solid fa-check"></i> Accept Booking
                        </button>
                    </form>
                    <form action="{{ route('provider.booking.reject', $booking) }}" method="POST" class="confirm-form"
                          data-title="Reject Booking" data-text="Are you sure you want to reject this booking?{{ $booking->total_amount > 0 ? ' A full refund will be automatically issued to the student.' : '' }}" data-confirm-text="Yes, reject booking" data-confirm-color="#dc2626">
                        @csrf @method('PATCH')
                        <button type="submit"
                                class="w-full flex items-center justify-center gap-2 rounded-xl border border-red-200 bg-red-50 hover:bg-red-100 px-4 py-3 text-sm font-bold text-red-600 transition">
                            <i class="fa-solid fa-xmark"></i> Reject Booking
                            @if($booking->total_amount > 0) (Student will be fully refunded) @endif
                        </button>
                    </form>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.confirm-form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const title = form.getAttribute('data-title') || 'Are you sure?';
            const text = form.getAttribute('data-text') || 'Do you want to proceed?';
            const confirmBtnText = form.getAttribute('data-confirm-text') || 'Yes, proceed';
            const confirmColor = form.getAttribute('data-confirm-color') || '#5DA44E';
            
            Swal.fire({
                title: title,
                text: text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: confirmColor,
                cancelButtonColor: '#6b7280',
                confirmButtonText: confirmBtnText,
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
});
</script>
@endpush
