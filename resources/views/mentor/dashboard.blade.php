@extends('layouts.role-profile', ['roleName' => 'Mentor', 'activeSection' => 'dashboard'])

@section('profile_panel')
    <div class="space-y-6">
        {{-- Welcome and Quick Intro --}}
        <div class="overflow-hidden rounded-[12px] bg-white p-6 shadow-[0_1px_8px_rgba(0,0,0,0.06)] ring-1 ring-[#E0E0E0] sm:p-8">
            <h3 class="mb-2 text-[20px] font-bold text-[#333333]">Welcome to your Mentor Dashboard</h3>
            <p class="text-gray-600">Hello, {{ $user->name }}. Here is an overview of your bookings, sessions, and earnings.</p>
        </div>

        {{-- Statistics Grid --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-[0_1px_5px_rgba(0,0,0,0.03)] ring-1 ring-[#E0E0E0]">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Net Earnings</span>
                <p class="mt-2 text-2xl font-black text-emerald-600">
                    {{ $currencySymbol }}{{ number_format($revenue, 2) }}
                </p>
                <div class="mt-2 text-xs text-gray-400">Total payouts received</div>
            </div>
            
            <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-[0_1px_5px_rgba(0,0,0,0.03)] ring-1 ring-[#E0E0E0]">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Students</span>
                <p class="mt-2 text-2xl font-black text-gray-800">
                    {{ $studentsCount }}
                </p>
                <div class="mt-2 text-xs text-gray-400">Unique students joined</div>
            </div>

            <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-[0_1px_5px_rgba(0,0,0,0.03)] ring-1 ring-[#E0E0E0]">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Bookings</span>
                <p class="mt-2 text-2xl font-black text-[#66A157]">
                    {{ $totalBookings }}
                </p>
                <div class="mt-2 text-xs text-gray-400">
                    <span class="text-amber-500 font-bold">{{ $pendingBookings }} pending</span> / 
                    <span class="text-blue-500 font-bold">{{ $acceptedBookings }} accepted</span>
                </div>
            </div>
        </div>

        {{-- Recent Bookings Table --}}
        <div class="overflow-hidden rounded-[12px] bg-white shadow-[0_1px_8px_rgba(0,0,0,0.06)] ring-1 ring-[#E0E0E0] p-6 sm:p-8">
            <div class="flex items-center justify-between border-b border-gray-100 pb-4 mb-5">
                <h4 class="text-md font-bold text-gray-800">Recent Booking Requests</h4>
                <a href="{{ route('provider.bookings') }}" class="text-sm font-semibold text-[#66A157] hover:underline">
                    View All &rarr;
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th scope="col" class="px-5 py-3 font-bold">Student</th>
                            <th scope="col" class="px-5 py-3 font-bold">Session Dates</th>
                            <th scope="col" class="px-5 py-3 font-bold">Total Amount</th>
                            <th scope="col" class="px-5 py-3 font-bold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($recentBookings as $booking)
                            <tr class="hover:bg-gray-50/50">
                                <td class="px-5 py-3.5">
                                    <div class="font-bold text-gray-800">{{ $booking->student->name }}</div>
                                    <div class="text-xs text-gray-400">{{ $booking->student->email }}</div>
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="font-semibold text-gray-700">
                                        {{ $booking->from_date->format('M d, Y') }}
                                    </div>
                                    <div class="text-xs text-gray-400">
                                        {{ $booking->total_hours }} hours
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 font-bold text-gray-700">
                                    {{ $currencySymbol }}{{ number_format($booking->total_amount, 2) }}
                                </td>
                                <td class="px-5 py-3.5">
                                    @php
                                        $statusClasses = [
                                            'pending' => 'bg-amber-50 text-amber-700 border-amber-200',
                                            'accepted' => 'bg-blue-50 text-blue-700 border-blue-200',
                                            'rejected' => 'bg-red-50 text-red-700 border-red-200',
                                            'cancelled' => 'bg-gray-50 text-gray-500 border-gray-200',
                                            'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        ];
                                        $class = $statusClasses[$booking->status] ?? 'bg-gray-50 text-gray-700 border-gray-200';
                                    @endphp
                                    <span class="px-2 py-0.5 text-xs font-bold border rounded-full uppercase tracking-wider {{ $class }}">
                                        {{ $booking->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-8 text-center text-gray-400 italic">
                                    No bookings received yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Profile Details Grid --}}
        <div class="overflow-hidden rounded-[12px] bg-white p-6 shadow-[0_1px_8px_rgba(0,0,0,0.06)] ring-1 ring-[#E0E0E0] sm:p-8">
            <h4 class="mb-4 text-md font-bold text-gray-800 border-b border-gray-100 pb-3">Your Account Information</h4>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="rounded-lg border border-[#E0E0E0] p-4 bg-gray-50">
                    <span class="text-xs font-semibold text-gray-500 uppercase">Email Address</span>
                    <p class="mt-1 font-bold text-gray-800 text-[14px]">{{ $user->email }}</p>
                </div>
                <div class="rounded-lg border border-[#E0E0E0] p-4 bg-gray-50">
                    <span class="text-xs font-semibold text-gray-500 uppercase">Phone Number</span>
                    <p class="mt-1 font-bold text-gray-800 text-[14px]">{{ $user->phone }}</p>
                </div>
                <div class="rounded-lg border border-[#E0E0E0] p-4 bg-gray-50">
                    <span class="text-xs font-semibold text-gray-500 uppercase">City</span>
                    <p class="mt-1 font-bold text-gray-800 text-[14px]">{{ $user->city }}</p>
                </div>
                <div class="rounded-lg border border-[#E0E0E0] p-4 bg-gray-50">
                    <span class="text-xs font-semibold text-gray-500 uppercase">State</span>
                    <p class="mt-1 font-bold text-gray-800 text-[14px]">{{ $user->state }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection
