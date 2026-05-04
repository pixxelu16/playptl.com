@extends('layouts.website')

@section('header_theme', 'light')

@section('title', 'Reset Password | '.config('app.name', 'playptl'))
@section('meta_description', 'Set a new secure password for your '.config('app.name', 'playptl').' account.')

@section('content')
    <div class="mx-auto flex min-h-[calc(100vh-280px)] max-w-[1400px] items-center justify-center px-5 py-16 sm:px-8 lg:px-14">
        <div class="w-full max-w-md rounded-ui border border-white/10 bg-[rgba(10,15,24,0.92)] p-8 shadow-xl backdrop-blur-sm">
            <h1 class="text-3xl font-bold uppercase tracking-wide text-white sm:text-4xl">Reset password</h1>
            <p class="mt-2 text-[15px] leading-relaxed text-white/65">Choose a new password for your account.</p>

            <form class="mt-8 space-y-5" method="POST" action="{{ route('password.store') }}">
                @csrf

                <input type="hidden" name="token" value="{{ $token }}">

                <div>
                    <label class="block text-[13px] font-semibold text-white/90" for="email">Email address</label>
                    <input id="email" type="email" name="email" value="{{ old('email', $email) }}" required autofocus autocomplete="username"
                        class="mt-1.5 w-full rounded-ui border border-white/15 bg-white/5 px-4 py-3 text-[15px] text-white placeholder:text-white/35 outline-none transition focus:border-lime/45 focus:ring-2 focus:ring-lime/25">
                </div>

                <div>
                    <label class="block text-[13px] font-semibold text-white/90" for="password">New password</label>
                    <input id="password" type="password" name="password" required autocomplete="new-password"
                        class="mt-1.5 w-full rounded-ui border border-white/15 bg-white/5 px-4 py-3 text-[15px] text-white placeholder:text-white/35 outline-none transition focus:border-lime/45 focus:ring-2 focus:ring-lime/25">
                </div>

                <div>
                    <label class="block text-[13px] font-semibold text-white/90" for="password_confirmation">Confirm new password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                        class="mt-1.5 w-full rounded-ui border border-white/15 bg-white/5 px-4 py-3 text-[15px] text-white placeholder:text-white/35 outline-none transition focus:border-lime/45 focus:ring-2 focus:ring-lime/25">
                </div>

                <button class="w-full rounded-ui bg-brand px-5 py-3 text-[15px] font-bold text-white transition-opacity hover:opacity-95" type="submit">Reset password</button>
            </form>
        </div>
    </div>
@endsection
