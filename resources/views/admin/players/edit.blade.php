@extends('layouts.admin')

@section('title', 'Edit Player | '.config('app.name', 'playptl'))
@section('meta_description', 'Edit player profile details.')

@section('content')
    <section class="admin-card">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-card-title">Edit Player</h1>
                <p class="admin-card-text">Update player details and registration type.</p>
            </div>
            <a class="admin-link" href="{{ route('admin.players.index', $indexQuery ?? []) }}">
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

        <form class="admin-form admin-form-wide" method="POST" action="{{ route('admin.players.update', ['player' => $player] + ($indexQuery ?? [])) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="admin-form-grid">
                <div class="admin-form-group" style="grid-column: 1 / -1; display:flex; gap: 14px; align-items:center;">
                    @php
                        $avatarSrc = $player->avatar_path ?: 'upload/user-avatar/default-user-pic.png';
                    @endphp
                    <img src="{{ asset($avatarSrc) }}" alt="Player avatar" width="72" height="72" style="width:72px;height:72px;border-radius:999px;object-fit:cover;border:1px solid #d7ead9;">
                    <div style="flex:1;">
                        <label class="admin-label" for="avatar">Profile Picture</label>
                        <input class="admin-input" id="avatar" type="file" name="avatar" accept="image/*">
                    </div>
                </div>
                <div class="admin-form-group" style="grid-column: 1 / -1;">
                    <label class="admin-label" for="name">Name</label>
                    <input class="admin-input" id="name" name="name" type="text" value="{{ old('name', $player->name) }}" required>
                </div>

                <div class="admin-form-group">
                    <label class="admin-label" for="email">Email</label>
                    <input class="admin-input" id="email" type="email" value="{{ $player->email }}" disabled style="background:#f3f4f6;border-color:#e5e7eb;color:#6b7280;cursor:not-allowed;">
                </div>

                <div class="admin-form-group">
                    <label class="admin-label" for="phone">Phone</label>
                    <input class="admin-input" id="phone" name="phone" type="text" value="{{ old('phone', $player->phone) }}">
                </div>

                <div class="admin-form-group">
                    <label class="admin-label" for="city">City</label>
                    <input class="admin-input" id="city" name="city" type="text" value="{{ old('city', $player->city) }}">
                </div>

                <div class="admin-form-group">
                    <label class="admin-label" for="state">State</label>
                    <input class="admin-input" id="state" name="state" type="text" value="{{ old('state', $player->state) }}">
                </div>

                <div class="admin-form-group">
                    <label class="admin-label" for="sex">Sex</label>
                    <select class="admin-input" id="sex" name="sex">
                        @php $sex = old('sex', $player->sex); @endphp
                        <option value="" @selected($sex === null || $sex === '')>—</option>
                        <option value="male" @selected($sex === 'male')>Male</option>
                        <option value="female" @selected($sex === 'female')>Female</option>
                    </select>
                </div>

                <div class="admin-form-group">
                    <label class="admin-label" for="status">Status</label>
                    @php $status = old('status', $player->status ?? 'active'); @endphp
                    <select class="admin-input" id="status" name="status" required>
                        <option value="active" @selected($status === 'active')>Active</option>
                        <option value="pending" @selected($status === 'pending')>Pending</option>
                        <option value="suspend" @selected($status === 'suspend')>Suspend</option>
                    </select>
                </div>

                @include('admin.players._skill-level-field', [
                    'currentSkillLevel' => old('skill_level', $player->skill_level),
                ])

                @include('admin.players._age-group-field', [
                    'ageBrackets' => $ageBrackets,
                    'currentAgeGroupKey' => $currentAgeGroupKey,
                    'canEditAgeGroup' => $canEditAgeGroup,
                ])
            </div>

            <div style="display:flex; gap: 10px; margin-top: 18px;">
                <button class="admin-button" type="submit">Save Changes</button>
                <a class="admin-button admin-button-secondary" href="{{ route('admin.players.index', $indexQuery ?? []) }}">Cancel</a>
            </div>
        </form>
    </section>
@endsection

