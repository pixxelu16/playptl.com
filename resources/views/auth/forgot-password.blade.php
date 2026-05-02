@extends('layouts.website')

@section('title', 'Forgot Password | '.config('app.name', 'playptl'))
@section('meta_description', 'Request a secure password reset link for your '.config('app.name', 'playptl').' account.')

@section('content')
    <div class="mx-auto flex min-h-[calc(100vh-280px)] max-w-[1400px] items-center justify-center px-5 py-16 sm:px-8 lg:px-14">
        <div class="w-full max-w-md rounded-ui border border-white/10 bg-[rgba(10,15,24,0.92)] p-8 shadow-xl backdrop-blur-sm">
            <h1 class="text-3xl font-bold uppercase tracking-wide text-white sm:text-4xl">Forgot password</h1>
            <p class="mt-2 text-[15px] leading-relaxed text-white/65">Enter your email and we will send a password reset link.</p>

            <form class="mt-8 space-y-5" method="POST" action="{{ route('password.email') }}">
                @csrf

                <div>
                    <label class="block text-[13px] font-semibold text-white/90" for="email">Email address</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                        class="mt-1.5 w-full rounded-ui border border-white/15 bg-white/5 px-4 py-3 text-[15px] text-white placeholder:text-white/35 outline-none transition focus:border-lime/45 focus:ring-2 focus:ring-lime/25">
                </div>

                <button class="w-full rounded-ui bg-brand px-5 py-3 text-[15px] font-bold text-white transition-opacity hover:opacity-95" type="submit">Send reset link</button>
            </form>

            <div class="mt-8 text-center text-[14px] text-white/60">
                Remember password? <a href="{{ route('login') }}" class="font-semibold text-lime transition-colors hover:text-lime/90">Back to login</a>
            </div>
        </div>
    </div>
@endsection
