@extends('layouts.admin')

@section('title', 'Profile Settings | '.config('app.name', 'playptl'))
@section('meta_description', 'Update admin profile settings.')

@section('content')
    <section class="admin-card">
        <h1 class="admin-card-title">Profile Settings</h1>
        <p class="admin-card-text">Update your admin name and email address.</p>

        @if (session('status'))
            <div class="admin-alert admin-alert-success">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="admin-alert admin-alert-error">
                {{ $errors->first() }}
            </div>
        @endif

        <form class="admin-form" method="POST" action="{{ route('admin.profile.update') }}">
            @csrf
            @method('PUT')

            <div class="admin-form-group">
                <label class="admin-label" for="name">Name</label>
                <input class="admin-input" id="name" type="text" name="name" value="{{ old('name', auth()->user()->name) }}" required>
            </div>

            <div class="admin-form-group">
                <label class="admin-label" for="email">Email</label>
                <input class="admin-input" id="email" type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required>
            </div>

            <button class="admin-button" type="submit">Save Changes</button>
        </form>
    </section>
@endsection
