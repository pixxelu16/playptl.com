@extends('layouts.role-profile', ['roleName' => 'Mentor', 'activeSection' => 'dashboard'])

@section('profile_panel')
    <div class="overflow-hidden rounded-[12px] bg-white p-6 shadow-[0_1px_8px_rgba(0,0,0,0.06)] ring-1 ring-[#E0E0E0] sm:p-8">
        <h3 class="mb-4 text-[20px] font-bold text-[#333333]">Welcome to your Mentor Dashboard</h3>
        <p class="text-gray-600 mb-6">Hello, {{ $user->name }}. This is your dedicated dashboard where you can manage your settings and view your account information.</p>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="rounded-lg border border-[#E0E0E0] p-4 bg-gray-50">
                <span class="text-sm font-semibold text-gray-500 uppercase">Email Address</span>
                <p class="mt-1 font-bold text-gray-800 text-[15px]">{{ $user->email }}</p>
            </div>
            <div class="rounded-lg border border-[#E0E0E0] p-4 bg-gray-50">
                <span class="text-sm font-semibold text-gray-500 uppercase">Phone Number</span>
                <p class="mt-1 font-bold text-gray-800 text-[15px]">{{ $user->phone }}</p>
            </div>
            <div class="rounded-lg border border-[#E0E0E0] p-4 bg-gray-50">
                <span class="text-sm font-semibold text-gray-500 uppercase">City</span>
                <p class="mt-1 font-bold text-gray-800 text-[15px]">{{ $user->city }}</p>
            </div>
            <div class="rounded-lg border border-[#E0E0E0] p-4 bg-gray-50">
                <span class="text-sm font-semibold text-gray-500 uppercase">State</span>
                <p class="mt-1 font-bold text-gray-800 text-[15px]">{{ $user->state }}</p>
            </div>
        </div>
    </div>
@endsection
