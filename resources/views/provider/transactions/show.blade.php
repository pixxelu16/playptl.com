@extends('layouts.role-profile', ['roleName' => $roleName, 'activeSection' => 'transactions'])

@section('profile_panel')
    <div class="overflow-hidden rounded-[12px] bg-white p-6 shadow-[0_1px_8px_rgba(0,0,0,0.06)] ring-1 ring-[#E0E0E0] sm:p-8">
        <div class="flex items-center justify-between border-b border-gray-100 pb-4 mb-6">
            <div>
                <h3 class="text-[20px] font-bold text-[#333333]">Transaction Details</h3>
                <p class="text-xs text-gray-400 mt-1">Booking Ref: #{{ $booking->id }} | Created on {{ $booking->created_at->format('M d, Y') }}</p>
            </div>
            <a href="{{ route(strtolower($roleName) . '.transactions.index') }}" class="rounded-lg border border-[#E0E0E0] bg-white px-4 py-2 text-xs font-semibold text-[#333333] transition hover:bg-gray-50">
                &larr; Back to Transactions
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            {{-- Student & Session Info --}}
            <div class="space-y-4 rounded-xl border border-gray-100 p-5 bg-gray-50/50">
                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Client & Session</h4>
                <div class="space-y-2 text-sm">
                    <p class="text-gray-500">Student Name: <span class="font-bold text-gray-800">{{ $booking->student->name }}</span></p>
                    <p class="text-gray-500">Student Phone: <span class="font-semibold text-gray-700">{{ $booking->student_phone ?: 'N/A' }}</span></p>
                    <p class="text-gray-500">Location: <span class="font-semibold text-gray-700">{{ $booking->student_location ?: 'Online / Digital Session' }}</span></p>
                    <p class="text-gray-500">Session Dates: <span class="font-bold text-gray-800">{{ $booking->from_date->format('M d, Y') }} @if($booking->to_date->ne($booking->from_date)) to {{ $booking->to_date->format('M d, Y') }} @endif</span></p>
                    <p class="text-gray-500">Daily Time Slot: <span class="font-semibold text-gray-700">{{ \Carbon\Carbon::createFromFormat('H:i:s', $booking->booking_time)->format('h:i A') }}</span></p>
                </div>
            </div>

            {{-- Hours & Rate Metrics --}}
            <div class="space-y-4 rounded-xl border border-gray-100 p-5 bg-gray-50/50">
                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Duration & Payout Metrics</h4>
                <div class="space-y-2 text-sm">
                    <p class="text-gray-500">Hours / Day: <span class="font-semibold text-gray-700">{{ $booking->hours_per_day }} Hours</span></p>
                    <p class="text-gray-500">Total Hours: <span class="font-bold text-gray-800">{{ $booking->total_hours }} Hours</span></p>
                    <p class="text-gray-500">Hourly Rate: <span class="font-semibold text-gray-700">{{ $currencySymbol }}{{ number_format($booking->hourly_rate, 2) }} / hr</span></p>
                    <p class="text-gray-500">Payout Status: 
                        <span class="px-2.5 py-0.5 text-xs font-bold rounded-full border uppercase tracking-wider {{ $booking->payout_status === 'paid' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200' }}">
                            {{ $booking->payout_status ?: 'Unpaid' }}
                        </span>
                    </p>
                    @if($booking->payout_paid_at)
                        <p class="text-gray-500">Paid At: <span class="font-semibold text-gray-700">{{ $booking->payout_paid_at->format('M d, Y h:i A') }}</span></p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Financial Calculations Receipt --}}
        <div class="rounded-xl border border-gray-200 overflow-hidden">
            <div class="bg-gray-50 px-5 py-3 border-b border-gray-200">
                <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider">Earnings Calculation breakdown</h4>
            </div>
            <div class="p-5 space-y-4 text-sm divide-y divide-gray-100">
                <div class="flex justify-between font-medium text-gray-600 pb-3">
                    <span>Base Amount ({{ $booking->total_hours }} Hours @ {{ $currencySymbol }}{{ number_format($booking->hourly_rate, 2) }}/hr)</span>
                    <span class="text-gray-800">{{ $currencySymbol }}{{ number_format($booking->total_amount, 2) }}</span>
                </div>
                <div class="flex justify-between font-medium text-red-600 py-3">
                    <span>Platform Commission Deduction ({{ 100 - $booking->commission_rate }}%)</span>
                    <span>-{{ $currencySymbol }}{{ number_format($booking->commission_amount, 2) }}</span>
                </div>
                <div class="flex justify-between items-center font-bold text-lg pt-3">
                    <span class="text-gray-900">Your Net Earnings Payout</span>
                    <span class="text-emerald-600 text-xl">{{ $currencySymbol }}{{ number_format($booking->provider_amount, 2) }}</span>
                </div>
            </div>
        </div>

        @if($booking->message)
            <div class="mt-6 p-4 rounded-xl border border-gray-100 bg-gray-50/50">
                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Message from Student</h4>
                <p class="text-sm text-gray-600 italic whitespace-pre-line">"{{ $booking->message }}"</p>
            </div>
        @endif
    </div>
@endsection
