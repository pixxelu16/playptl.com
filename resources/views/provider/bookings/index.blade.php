@php $currentRole = auth()->user()->hasRole('Mentor') ? 'Mentor' : 'Coach'; @endphp
@extends('layouts.role-profile', ['roleName' => $currentRole, 'activeSection' => 'bookings'])

@section('profile_panel')
<div class="overflow-hidden rounded-[12px] bg-white shadow-[0_1px_8px_rgba(0,0,0,0.06)] ring-1 ring-[#E0E0E0]">

    {{-- Header --}}
    <div class="px-6 py-5 border-b border-gray-100">
        <h3 class="text-xl font-bold text-gray-900">Booking Requests</h3>
        <p class="text-sm text-gray-500 mt-1">Review and respond to incoming session requests from Students.</p>
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

    {{-- Status Filter Tabs --}}
    <div class="flex gap-1 px-6 pt-4 flex-wrap">
        @foreach(['all' => 'All', 'pending' => 'Pending', 'accepted' => 'Accepted', 'rejected' => 'Rejected', 'cancelled' => 'Cancelled'] as $key => $label)
            <a href="{{ route('provider.bookings', ['status' => $key]) }}"
               class="px-4 py-1.5 rounded-full text-xs font-bold border transition
                      {{ $status === $key ? 'bg-[#5DA44E] text-white border-[#5DA44E]' : 'bg-gray-100 text-gray-600 border-gray-200 hover:border-[#5DA44E] hover:text-[#5DA44E]' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    @if($bookings->isEmpty())
        <div class="flex flex-col items-center justify-center py-16 text-center px-6">
            <div class="h-16 w-16 rounded-full bg-[#E8F7E9] flex items-center justify-center mb-4">
                <i class="fa-solid fa-calendar-xmark text-2xl text-[#5DA44E]"></i>
            </div>
            <h4 class="font-bold text-gray-700 mb-2">No Bookings Found</h4>
            <p class="text-sm text-gray-500">No booking requests match the selected filter.</p>
        </div>
    @else
        <div class="divide-y divide-gray-50 mt-4">
            @foreach($bookings as $booking)
            @php
                $colors = [
                    'pending'   => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                    'accepted'  => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    'rejected'  => 'bg-red-50 text-red-700 border-red-200',
                    'cancelled' => 'bg-gray-100 text-gray-500 border-gray-200',
                    'completed' => 'bg-blue-50 text-blue-700 border-blue-200',
                ];
                $c = $colors[$booking->status] ?? 'bg-gray-100 text-gray-500 border-gray-200';
            @endphp
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 px-6 py-4 hover:bg-gray-50 transition">
                <div class="flex items-start gap-4">
                    {{-- Student Avatar --}}
                    @if($booking->student->avatar_path)
                        <img src="{{ asset('storage/' . $booking->student->avatar_path) }}" alt="{{ $booking->student->name }}"
                             class="h-12 w-12 rounded-full object-cover flex-shrink-0 ring-2 ring-[#E8F7E9]">
                    @else
                        @php $si = strtoupper(substr($booking->student->first_name ?? $booking->student->name, 0, 1) . substr($booking->student->last_name ?? '', 0, 1)); @endphp
                        <div class="h-12 w-12 rounded-full bg-[#5DA44E] flex items-center justify-center text-white font-bold text-sm flex-shrink-0">{{ $si ?: '??' }}</div>
                    @endif
                    <div>
                        <p class="font-bold text-gray-900 text-sm">{{ $booking->student->name }}</p>
                        <p class="text-xs text-gray-500">Student</p>
                        <p class="text-xs text-gray-400 mt-0.5">
                            {{ $booking->from_date->format('M d') }} → {{ $booking->to_date->format('M d, Y') }}
                            &bull; {{ $booking->total_days }}d &bull; {{ $booking->total_hours }} hrs
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
                    <span class="px-3 py-1 text-xs font-bold rounded-full border {{ $c }}">
                        {{ $booking->statusLabel() }}
                    </span>
                    <a href="{{ route('provider.booking.show', $booking) }}"
                       class="text-xs font-bold text-[#5DA44E] hover:underline">View</a>
                    @if($booking->isPending())
                        <form action="{{ route('provider.booking.accept', $booking) }}" method="POST">
                            @csrf @method('PATCH')
                            <button type="submit"
                                    class="text-xs font-bold text-emerald-600 border border-emerald-300 bg-emerald-50 hover:bg-emerald-100 px-3 py-1.5 rounded-lg transition">
                                Accept
                            </button>
                        </form>
                        <form action="{{ route('provider.booking.reject', $booking) }}" method="POST"
                              onsubmit="return confirm('Reject this booking?{{ $booking->total_amount > 0 ? ' A full refund will be issued to the student.' : '' }}')">
                            @csrf @method('PATCH')
                            <button type="submit"
                                    class="text-xs font-bold text-red-600 border border-red-200 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg transition">
                                Reject
                            </button>
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
