@extends('layouts.role-profile', ['roleName' => 'Student', 'activeSection' => 'bookings'])

@section('profile_panel')
<div class="overflow-hidden rounded-[12px] bg-white shadow-[0_1px_8px_rgba(0,0,0,0.06)] ring-1 ring-[#E0E0E0]">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 px-6 py-5 border-b border-gray-100">
        <div>
            <h3 class="text-xl font-bold text-gray-900">My Bookings</h3>
            <p class="text-sm text-gray-400 mt-0.5">Book a Mentor for guidance or a Coach for training sessions.</p>
        </div>
        <div class="flex flex-wrap gap-2 flex-shrink-0">
            <a href="{{ route('player-services.mentors') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-[#5DA44E] hover:bg-[#4d8f40] px-4 py-2 text-sm font-bold text-white transition">
                <i class="fa-solid fa-user-graduate"></i> Book a Mentor
            </a>
            <a href="{{ route('player-services.coaches') }}"
               class="inline-flex items-center gap-2 rounded-lg border border-[#5DA44E] text-[#5DA44E] hover:bg-[#E8F7E9] px-4 py-2 text-sm font-bold transition">
                <i class="fa-solid fa-whistle"></i> Book a Coach
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mx-6 mt-4 flex items-center gap-2 rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800">
            <i class="fa-solid fa-circle-check text-[#5DA44E]"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mx-6 mt-4 flex items-center gap-2 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
            <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
        </div>
    @endif

    @if($bookings->isEmpty())
        <div class="flex flex-col items-center justify-center py-16 text-center px-6">
            <div class="h-16 w-16 rounded-full bg-[#E8F7E9] flex items-center justify-center mb-4">
                <i class="fa-solid fa-calendar-xmark text-2xl text-[#5DA44E]"></i>
            </div>
            <h4 class="font-bold text-gray-700 mb-2">No Bookings Yet</h4>
            <p class="text-sm text-gray-500 mb-6">You haven't made any booking requests yet. Browse our Mentors and Coaches to get started.</p>
            <a href="{{ route('player-services.mentors') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-[#5DA44E] hover:bg-[#4d8f40] px-5 py-2.5 text-sm font-bold text-white transition">
                Browse Mentors
            </a>
        </div>
    @else
        <div class="divide-y divide-gray-50">
            @foreach($bookings as $booking)
            @php
                $colors = [
                    'pending'   => ['bg' => 'bg-yellow-50',  'text' => 'text-yellow-700',  'border' => 'border-yellow-200'],
                    'accepted'  => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200'],
                    'rejected'  => ['bg' => 'bg-red-50',     'text' => 'text-red-700',     'border' => 'border-red-200'],
                    'cancelled' => ['bg' => 'bg-gray-100',   'text' => 'text-gray-500',    'border' => 'border-gray-200'],
                    'completed' => ['bg' => 'bg-blue-50',    'text' => 'text-blue-700',    'border' => 'border-blue-200'],
                ];
                $c = $colors[$booking->status] ?? $colors['cancelled'];
            @endphp
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 px-6 py-4 hover:bg-gray-50 transition">
                <div class="flex items-start gap-4">
                    {{-- Provider Avatar --}}
                    @if($booking->provider->avatar_path)
                        <img src="{{ asset('storage/' . $booking->provider->avatar_path) }}" alt="{{ $booking->provider->name }}"
                             class="h-12 w-12 rounded-full object-cover flex-shrink-0 ring-2 ring-[#E8F7E9]">
                    @else
                        @php $pi = strtoupper(substr($booking->provider->first_name ?? $booking->provider->name, 0, 1) . substr($booking->provider->last_name ?? '', 0, 1)); @endphp
                        <div class="h-12 w-12 rounded-full bg-[#5DA44E] flex items-center justify-center text-white font-bold text-sm flex-shrink-0">{{ $pi ?: '??' }}</div>
                    @endif
                    <div>
                        <p class="font-bold text-gray-900 text-sm">{{ $booking->provider->name }}</p>
                        <p class="text-xs text-gray-500 capitalize">{{ $booking->provider_type }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">
                            {{ $booking->from_date->format('M d') }} → {{ $booking->to_date->format('M d, Y') }}
                            &bull; {{ $booking->total_days }} day{{ $booking->total_days > 1 ? 's' : '' }}
                            &bull; {{ $booking->total_hours }} hrs
                            @if($booking->booking_time)
                                &bull; <span class="text-[#5DA44E] font-medium"><i class="fa-solid fa-clock"></i> {{ Carbon\Carbon::parse($booking->booking_time)->format('g:i A') }}</span>
                            @endif
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3 flex-shrink-0">
                    <span class="text-sm font-bold text-gray-700">
                        @if($booking->total_amount > 0) {{ $currencySymbol }}{{ number_format($booking->total_amount, 2) }} @else Free @endif
                    </span>
                    <span class="px-3 py-1 text-xs font-bold rounded-full border {{ $c['bg'] }} {{ $c['text'] }} {{ $c['border'] }}">
                        {{ $booking->statusLabel() }}
                    </span>
                    <a href="{{ route('student.booking.show', $booking) }}"
                       class="text-xs font-bold text-[#5DA44E] hover:underline">View</a>
                    @if($booking->canBeCancelledByStudent())
                        <form action="{{ route('student.booking.cancel', $booking) }}" method="POST"
                              onsubmit="return confirm('Cancel this booking?')">
                            @csrf @method('PATCH')
                            <button type="submit" class="text-xs font-bold text-red-500 hover:underline">Cancel</button>
                        </form>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $bookings->links() }}
        </div>
    @endif
</div>
@endsection
