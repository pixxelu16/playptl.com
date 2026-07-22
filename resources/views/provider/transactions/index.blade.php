@extends('layouts.role-profile', ['roleName' => $roleName, 'activeSection' => 'transactions'])

@section('profile_panel')
    <div class="overflow-hidden rounded-[12px] bg-white p-6 shadow-[0_1px_8px_rgba(0,0,0,0.06)] ring-1 ring-[#E0E0E0] sm:p-8">
        <h3 class="mb-2 text-[20px] font-bold text-[#333333]">Transaction & Earnings History</h3>
        <p class="text-gray-600 mb-8 text-sm">Track your lesson bookings payments, platform commissions, and net earnings.</p>

        {{-- Financial Summary Cards --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 mb-8">
            <div class="rounded-xl border border-emerald-100 p-5 bg-emerald-50/50 flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-emerald-700 uppercase tracking-wider">Your Net Earnings</span>
                    <p class="mt-2 text-2xl font-black text-emerald-800">
                        {{ $currencySymbol }}{{ number_format($totalRevenue, 2) }}
                    </p>
                </div>
                <div class="h-12 w-12 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600">
                    <i class="fa-solid fa-wallet text-xl"></i>
                </div>
            </div>
            <div class="rounded-xl border border-blue-100 p-5 bg-blue-50/50 flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-blue-700 uppercase tracking-wider">Platform Commission Paid</span>
                    <p class="mt-2 text-2xl font-black text-blue-800">
                        {{ $currencySymbol }}{{ number_format($totalCommissionPaid, 2) }}
                    </p>
                </div>
                <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                    <i class="fa-solid fa-percent text-xl"></i>
                </div>
            </div>
        </div>

        {{-- Transaction Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th scope="col" class="px-6 py-3.5 font-bold">Transaction ID / Student</th>
                        <th scope="col" class="px-6 py-3.5 font-bold">Session Dates</th>
                        <th scope="col" class="px-6 py-3.5 font-bold">Gross Amount</th>
                        <th scope="col" class="px-6 py-3.5 font-bold">Commission</th>
                        <th scope="col" class="px-6 py-3.5 font-bold">Net Earnings</th>
                        <th scope="col" class="px-6 py-3.5 font-bold">Status</th>
                        <th scope="col" class="px-6 py-3.5 font-bold text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($bookings as $booking)
                        <tr class="hover:bg-gray-50/80 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-900 text-sm">
                                    {{ $booking->stripe_charge_id ? substr($booking->stripe_charge_id, 0, 15) . '...' : 'Free Session' }}
                                </div>
                                <div class="text-xs text-gray-400 mt-0.5">
                                    Student: <span class="font-semibold text-gray-600">{{ $booking->student->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-semibold text-gray-700">
                                    {{ $booking->from_date->format('M d, Y') }}
                                </span>
                                <div class="text-xs text-gray-400">
                                    {{ $booking->total_hours }} hrs @ {{ $currencySymbol }}{{ number_format($booking->hourly_rate, 2) }}/hr
                                </div>
                            </td>
                            <td class="px-6 py-4 font-semibold text-gray-700">
                                {{ $currencySymbol }}{{ number_format($booking->total_amount, 2) }}
                            </td>
                            <td class="px-6 py-4 text-xs text-red-600 font-semibold">
                                -{{ $currencySymbol }}{{ number_format($booking->commission_amount, 2) }}
                                <span class="text-gray-400">({{ $booking->commission_rate }}%)</span>
                            </td>
                            <td class="px-6 py-4 font-extrabold text-emerald-600">
                                {{ $currencySymbol }}{{ number_format($booking->provider_amount, 2) }}
                            </td>
                            <td class="px-6 py-4">
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
                                <span class="px-2.5 py-1 text-xs font-bold border rounded-full uppercase tracking-wider {{ $class }}">
                                    {{ $booking->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route(strtolower($roleName) . '.transactions.show', $booking->id) }}"
                                   class="text-[#5DA44E] hover:text-[#4d8f40] font-bold text-xs uppercase tracking-wider transition-colors">
                                    Details &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-gray-400 italic">
                                No transactions or earnings records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $bookings->links() }}
        </div>
    </div>
@endsection
