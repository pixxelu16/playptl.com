@extends('layouts.admin')

@section('title', 'Change Password | '.config('app.name', 'playptl'))
@section('meta_description', 'Change admin account password.')

@section('content')
    <section class="admin-card">
        <h1 class="admin-card-title">Change Password</h1>
        <p class="admin-card-text">Use a strong password to keep your admin account secure.</p>

        @if (session('status'))
            <div class="admin-alert admin-alert-success">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="admin-alert admin-alert-error">
                {{ $errors->first() }}
            </div>
        @endif

        <form class="admin-form" method="POST" action="{{ route('admin.password.update') }}">
            @csrf
            @method('PUT')

            <div class="admin-form-group">
                <label class="admin-label" for="current_password">Current Password</label>
                <input class="admin-input" id="current_password" type="password" name="current_password" required autocomplete="current-password">
            </div>

            <div class="admin-form-group">
                <label class="admin-label" for="password">New Password</label>
                <input class="admin-input" id="password" type="password" name="password" required autocomplete="new-password">
            </div>

            <div class="admin-form-group">
                <label class="admin-label" for="password_confirmation">Confirm New Password</label>
                <input class="admin-input" id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
            </div>

            <button class="admin-button" type="submit">Update Password</button>
        </form>
    </section>
@endsection
