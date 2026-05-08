@extends('layouts.admin')

@section('title', 'Profile Settings | '.config('app.name', 'playptl'))
@section('meta_description', 'Update admin profile settings.')

@section('content')
    <section class="admin-card">
        <h1 class="admin-card-title">Profile Settings</h1>
        <p class="admin-card-text">Update your profile details.</p>

        @if (session('status'))
            <div class="admin-alert admin-alert-success">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="admin-alert admin-alert-error">
                {{ $errors->first() }}
            </div>
        @endif

        <form class="admin-form admin-form-wide" method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="admin-form-grid">
                <div class="admin-form-group" style="grid-column: 1 / -1; display:flex; gap: 14px; align-items:center;">
                    @php
                        $avatarSrc = auth()->user()->avatar_path ?: 'upload/user-avatar/default-user-pic.png';
                    @endphp
                    <img src="{{ asset($avatarSrc) }}" alt="Profile photo" width="72" height="72" style="width:72px;height:72px;border-radius:999px;object-fit:cover;border:1px solid #d7ead9;">
                    <div style="flex:1;">
                        <label class="admin-label" for="avatar">Profile Picture</label>
                        <input class="admin-input" id="avatar" type="file" name="avatar" accept="image/*">
                        <p class="admin-card-text" style="margin-top: 8px; font-size: 13px; opacity: .8;">JPG/PNG/WebP, max 2MB.</p>
                    </div>
                </div>
                <div class="admin-form-group">
                    <label class="admin-label" for="first_name">First Name</label>
                    <input class="admin-input" id="first_name" type="text" name="first_name" value="{{ old('first_name', auth()->user()->first_name) }}">
                </div>

                <div class="admin-form-group">
                    <label class="admin-label" for="last_name">Last Name</label>
                    <input class="admin-input" id="last_name" type="text" name="last_name" value="{{ old('last_name', auth()->user()->last_name) }}">
                </div>

                <div class="admin-form-group" style="grid-column: 1 / -1;">
                    <label class="admin-label" for="name">Display Name</label>
                    <input class="admin-input" id="name" type="text" name="name" value="{{ old('name', auth()->user()->name) }}" required>
                </div>

                <div class="admin-form-group">
                    <label class="admin-label" for="email">Email</label>
                    <input class="admin-input" id="email" type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required>
                </div>

                <div class="admin-form-group">
                    <label class="admin-label" for="phone">Phone</label>
                    <input class="admin-input" id="phone" type="text" name="phone" value="{{ old('phone', auth()->user()->phone) }}">
                </div>

                <div class="admin-form-group">
                    <label class="admin-label" for="city">City</label>
                    <input class="admin-input" id="city" type="text" name="city" value="{{ old('city', auth()->user()->city) }}">
                </div>

                <div class="admin-form-group">
                    <label class="admin-label" for="state">State</label>
                    <input class="admin-input" id="state" type="text" name="state" value="{{ old('state', auth()->user()->state) }}">
                </div>

                <div class="admin-form-group">
                    <label class="admin-label" for="sex">Gender</label>
                    @php $sex = old('sex', auth()->user()->sex); @endphp
                    <select class="admin-input" id="sex" name="sex">
                        <option value="" @selected($sex === null || $sex === '')>—</option>
                        <option value="male" @selected($sex === 'male')>Male</option>
                        <option value="female" @selected($sex === 'female')>Female</option>
                    </select>
                </div>
            </div>

            <button class="admin-button" type="submit">Save Changes</button>
        </form>
    </section>
@endsection
