@extends('layouts.website')

@section('title', 'Register | '.config('app.name', 'playptl'))
@section('meta_description', 'Create a new '.config('app.name', 'playptl').' account as an admin, organiser, or player.')

@section('content')
    <div class="mx-auto flex min-h-[calc(100vh-280px)] max-w-[1400px] items-center justify-center px-5 py-16 sm:px-8 lg:px-14">
        <div class="w-full max-w-xl rounded-ui border border-white/10 bg-[rgba(10,15,24,0.92)] p-8 shadow-xl backdrop-blur-sm">
            <h1 class="text-3xl font-bold uppercase tracking-wide text-white sm:text-4xl">Create account</h1>
            <p class="mt-2 text-[15px] leading-relaxed text-white/65">Register a new account to access the dashboard.</p>

            <form class="mt-8 space-y-5" method="POST" action="{{ route('register') }}">
                @csrf

                <div>
                    <label class="block text-[13px] font-semibold text-white/90" for="name">Name</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                        class="mt-1.5 w-full rounded-ui border border-white/15 bg-white/5 px-4 py-3 text-[15px] text-white placeholder:text-white/35 outline-none transition focus:border-lime/45 focus:ring-2 focus:ring-lime/25">
                </div>

                <div>
                    <label class="block text-[13px] font-semibold text-white/90" for="email">Email address</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                        class="mt-1.5 w-full rounded-ui border border-white/15 bg-white/5 px-4 py-3 text-[15px] text-white placeholder:text-white/35 outline-none transition focus:border-lime/45 focus:ring-2 focus:ring-lime/25">
                </div>

                <div>
                    <label class="block text-[13px] font-semibold text-white/90" for="role">Account role</label>
                    <select id="role" name="role" required
                        class="mt-1.5 w-full rounded-ui border border-white/15 bg-white/5 px-4 py-3 text-[15px] text-white outline-none transition focus:border-lime/45 focus:ring-2 focus:ring-lime/25">
                        <option value="player" @selected(old('role', 'player') === 'player')>Player</option>
                        <option value="organiser" @selected(old('role') === 'organiser')>Organiser</option>
                        <option value="admin" @selected(old('role') === 'admin')>Admin</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[13px] font-semibold text-white/90" for="password">Password</label>
                    <input id="password" type="password" name="password" required autocomplete="new-password"
                        class="mt-1.5 w-full rounded-ui border border-white/15 bg-white/5 px-4 py-3 text-[15px] text-white placeholder:text-white/35 outline-none transition focus:border-lime/45 focus:ring-2 focus:ring-lime/25">
                </div>

                <div>
                    <label class="block text-[13px] font-semibold text-white/90" for="password_confirmation">Confirm password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                        class="mt-1.5 w-full rounded-ui border border-white/15 bg-white/5 px-4 py-3 text-[15px] text-white placeholder:text-white/35 outline-none transition focus:border-lime/45 focus:ring-2 focus:ring-lime/25">
                </div>

                <button class="w-full rounded-ui bg-brand px-5 py-3 text-[15px] font-bold text-white transition-opacity hover:opacity-95" type="submit">Register</button>
            </form>

            <div class="mt-8 text-center text-[14px] text-white/60">
                Already have an account? <a href="{{ route('login') }}" class="font-semibold text-lime transition-colors hover:text-lime/90">Login</a>
            </div>
        </div>
    </div>
@endsection
