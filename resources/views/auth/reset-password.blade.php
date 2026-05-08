@extends('layouts.website')

@section('suppress_global_errors', '1')

@section('header_theme', 'light')
@section('header_logo_path', 'frontend/images/logo-2.png')

@section('page_bg', '#f4faf4')
@section('body_class', 'min-h-screen overflow-x-hidden font-sans antialiased text-[#333333]')

@section('title', 'Reset Password | '.config('app.name', 'playptl'))
@section('meta_description', 'Set a new secure password for your '.config('app.name', 'playptl').' account.')

@push('styles')
<style>
    .login-page input::placeholder {
        color: #888888;
    }
    .login-page input:focus {
        outline: none;
        border-color: #61a153;
        box-shadow: 0 0 0 2px rgba(97, 161, 83, 0.2);
    }
</style>
@endpush

@section('content')
    <div class="login-page flex min-h-[calc(100vh-200px)] flex-col items-center justify-center px-4 py-12 sm:px-6 lg:px-8 bg-[#E4F7E7]">
        @if ($errors->any())
            <div class="mb-4 w-full max-w-[960px] rounded-[5px] border border-red-200 bg-red-50 px-4 py-3 text-center text-[13px] font-medium text-red-800" role="alert">
                <ul class="m-0 list-none space-y-1 p-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="w-full max-w-[960px] overflow-hidden rounded-[12px] bg-white shadow-[0_8px_30px_rgba(0,0,0,0.08)]">
            <div class="flex flex-col lg:flex-row">
                {{-- Left: form --}}
                <div class="flex w-full flex-col justify-center px-10 py-10 sm:px-14 sm:py-[60px] lg:w-1/2 lg:max-w-[50%]">
                    <h1 class="text-center text-[28px] font-medium leading-tight text-[#333333]">Reset Password</h1>
                    <p class="mt-4 text-center text-[14px] leading-relaxed text-[#888888]">Choose a new password for your account.</p>

                    <form class="mt-8 space-y-5" method="POST" action="{{ route('password.store') }}" autocomplete="on">
                        @csrf

                        <input type="hidden" name="token" value="{{ $token }}">

                        <div>
                            <label class="mb-1.5 block text-[12px] font-bold text-black" for="email">Email</label>
                            <input id="email" type="email" name="email" value="{{ old('email', $email) }}" required autofocus
                                autocomplete="username"
                                placeholder="Enter Your email"
                                class="h-[45px] w-full rounded-[5px] border border-[#dddddd] bg-white px-3 text-[15px] text-[#333333] @error('email') border-red-500 @enderror">
                            @error('email')
                                <p class="mt-1.5 text-[12px] font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-[12px] font-bold text-black" for="password">New password</label>
                            <input id="password" type="password" name="password" required autocomplete="new-password"
                                placeholder="Enter your new password"
                                class="h-[45px] w-full rounded-[5px] border border-[#dddddd] bg-white px-3 text-[15px] text-[#333333] @error('password') border-red-500 @enderror">
                            @error('password')
                                <p class="mt-1.5 text-[12px] font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-[12px] font-bold text-black" for="password_confirmation">Confirm new password</label>
                            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                                placeholder="Confirm your new password"
                                class="h-[45px] w-full rounded-[5px] border border-[#dddddd] bg-white px-3 text-[15px] text-[#333333]">
                        </div>

                        <button type="submit"
                            class="h-[45px] w-full rounded-[5px] bg-[#61a153] text-[15px] font-bold text-white transition-opacity hover:opacity-95">
                            Reset Password
                        </button>
                    </form>

                    <p class="mt-10 text-center text-[14px] text-[#888888]">
                        Remember your password?
                        <a href="{{ route('login') }}" class="font-bold text-[#61a153] underline hover:opacity-90">Back to login</a>
                    </p>
                </div>

                {{-- Right: image --}}
                <div class="relative min-h-[260px] w-full lg:min-h-[480px] lg:w-1/2 lg:max-w-[50%]">
                    <img src="{{ asset('frontend/images/man-focused-tennis-game 2.png') }}" alt="Tennis player with racket"
                        class="h-full min-h-[260px] w-full object-cover lg:absolute lg:inset-0 lg:min-h-full lg:rounded-r-[12px]"
                        width="480" height="640" loading="eager" decoding="async">
                </div>
            </div>
        </div>
    </div>
@endsection
