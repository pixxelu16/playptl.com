@extends('layouts.admin')

@section('title', 'Add Player | '.config('app.name', 'playptl'))
@section('meta_description', 'Create a new player account.')

@section('content')
    <section class="admin-card">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-card-title">Add Player</h1>
                <p class="admin-card-text">Create a player and email login details automatically.</p>
            </div>
            <a class="admin-link" href="{{ route('admin.players.index', ['tab' => $tab]) }}">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                <span>Back</span>
            </a>
        </div>

        @if ($errors->any())
            <div class="admin-alert admin-alert-error">
                <ul style="margin:0; padding-left: 18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form class="admin-form admin-form-wide" method="POST" action="{{ route('admin.players.store', ['tab' => $tab]) }}" enctype="multipart/form-data">
            @csrf

            <div class="admin-form-grid">
                <div class="admin-form-group" style="grid-column: 1 / -1; display:flex; gap: 14px; align-items:center;">
                    <img src="{{ asset('upload/user-avatar/default-user-pic.png') }}" alt="Default avatar" width="72" height="72" style="width:72px;height:72px;border-radius:999px;object-fit:cover;border:1px solid #d7ead9;">
                    <div style="flex:1;">
                        <label class="admin-label" for="avatar">Profile Picture (optional)</label>
                        <input class="admin-input" id="avatar" type="file" name="avatar" accept="image/*">
                    </div>
                </div>
                <div class="admin-form-group" style="grid-column: 1 / -1;">
                    <label class="admin-label" for="name">Name</label>
                    <input class="admin-input" id="name" name="name" type="text" value="{{ old('name') }}" required>
                </div>

                <div class="admin-form-group">
                    <label class="admin-label" for="email">Email</label>
                    <input class="admin-input" id="email" name="email" type="email" value="{{ old('email') }}" required>
                </div>

                <div class="admin-form-group">
                    <label class="admin-label" for="phone">Phone</label>
                    <input class="admin-input" id="phone" name="phone" type="text" value="{{ old('phone') }}">
                </div>

                <div class="admin-form-group">
                    <label class="admin-label" for="city">City</label>
                    <input class="admin-input" id="city" name="city" type="text" value="{{ old('city') }}">
                </div>

                <div class="admin-form-group">
                    <label class="admin-label" for="state">State</label>
                    <input class="admin-input" id="state" name="state" type="text" value="{{ old('state') }}">
                </div>

                <div class="admin-form-group">
                    <label class="admin-label" for="sex">Sex</label>
                    @php $sex = old('sex'); @endphp
                    <select class="admin-input" id="sex" name="sex">
                        <option value="" @selected($sex === null || $sex === '')>—</option>
                        <option value="male" @selected($sex === 'male')>Male</option>
                        <option value="female" @selected($sex === 'female')>Female</option>
                    </select>
                </div>

                <div class="admin-form-group">
                    <label class="admin-label" for="status">Status</label>
                    @php $status = old('status', 'active'); @endphp
                    <select class="admin-input" id="status" name="status" required>
                        <option value="active" @selected($status === 'active')>Active</option>
                        <option value="pending" @selected($status === 'pending')>Pending</option>
                        <option value="suspend" @selected($status === 'suspend')>Suspend</option>
                    </select>
                </div>
            </div>

            <div style="display:flex; gap: 10px; margin-top: 18px;">
                <button class="admin-button" type="submit">Create Player</button>
                <a class="admin-button admin-button-secondary" href="{{ route('admin.players.index', ['tab' => $tab]) }}">Cancel</a>
            </div>
        </form>
    </section>
@endsection

