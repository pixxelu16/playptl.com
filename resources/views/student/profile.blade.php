@extends('layouts.role-profile', ['roleName' => 'Student', 'activeSection' => 'profile'])

@section('profile_panel')
    {{-- Personal Info Card --}}
    <div class="overflow-hidden rounded-[12px] bg-white p-6 shadow-[0_1px_8px_rgba(0,0,0,0.06)] ring-1 ring-[#E0E0E0] sm:p-8">
        <h3 class="mb-6 text-[18px] font-bold leading-tight text-[#333333] sm:text-[20px]">Personal Information</h3>

        @if ($errors->any())
            <div class="mb-4 rounded-lg bg-red-50 p-4 border border-red-200">
                <ul class="list-disc pl-5 text-sm text-red-600">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('student.profile.update') }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">First Name *</label>
                    <input type="text" name="first_name" value="{{ old('first_name', $user->first_name) }}" required
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-[#66A157] focus:outline-none focus:ring-1 focus:ring-[#66A157]" />
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Last Name *</label>
                    <input type="text" name="last_name" value="{{ old('last_name', $user->last_name) }}" required
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-[#66A157] focus:outline-none focus:ring-1 focus:ring-[#66A157]" />
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Email Address *</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-[#66A157] focus:outline-none focus:ring-1 focus:ring-[#66A157]" />
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Phone Number *</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" required
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-[#66A157] focus:outline-none focus:ring-1 focus:ring-[#66A157]" />
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">City *</label>
                    <input type="text" name="city" value="{{ old('city', $user->city) }}" required
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-[#66A157] focus:outline-none focus:ring-1 focus:ring-[#66A157]" />
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">State *</label>
                    <input type="text" name="state" value="{{ old('state', $user->state) }}" required
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-[#66A157] focus:outline-none focus:ring-1 focus:ring-[#66A157]" />
                </div>
            </div>

            <div class="pt-2">
                <button type="submit" class="rounded-md bg-[#66A157] px-4 py-2 text-[14px] font-bold text-white hover:bg-[#5a9048] transition">
                    Save Changes
                </button>
            </div>
        </form>
    </div>

    {{-- Password Change Card --}}
    <div class="overflow-hidden rounded-[12px] bg-white p-6 shadow-[0_1px_8px_rgba(0,0,0,0.06)] ring-1 ring-[#E0E0E0] sm:p-8 mt-6">
        <h3 class="mb-6 text-[18px] font-bold leading-tight text-[#333333] sm:text-[20px]">Change Password</h3>

        <form method="POST" action="{{ route('student.password.update') }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Current Password *</label>
                <input type="password" name="current_password" required
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-[#66A157] focus:outline-none focus:ring-1 focus:ring-[#66A157]" />
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">New Password *</label>
                    <input type="password" name="password" required
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-[#66A157] focus:outline-none focus:ring-1 focus:ring-[#66A157]" />
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Confirm New Password *</label>
                    <input type="password" name="password_confirmation" required
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-[#66A157] focus:outline-none focus:ring-1 focus:ring-[#66A157]" />
                </div>
            </div>

            <div class="pt-2">
                <button type="submit" class="rounded-md bg-[#66A157] px-4 py-2 text-[14px] font-bold text-white hover:bg-[#5a9048] transition">
                    Change Password
                </button>
            </div>
        </form>
    </div>
@endsection
