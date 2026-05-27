@extends('layouts.admin')

@section('title', 'Add to League | '.config('app.name', 'playptl'))
@section('meta_description', 'Register a player into a league from admin.')

@section('content')
    <section class="admin-card">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-card-title">Add player to league</h1>
                <p class="admin-card-text">
                    Player: <strong>{{ $player->name }}</strong> ({{ $player->email }}) · Type: <strong>{{ ucfirst($player->registration_type ?? 'singles') }}</strong>
                </p>
            </div>
            <a class="admin-link" href="{{ route('admin.players.index', ['tab' => $player->registration_type ?? 'singles']) }}">
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

        <form class="admin-form admin-form-wide" method="POST" action="{{ route('admin.players.league-registrations.store', $player) }}">
            @csrf

            <div class="admin-form-grid">
                <div class="admin-form-group" style="grid-column: 1 / -1;">
                    <label class="admin-label" for="league_id">League</label>
                    <select class="admin-input" id="league_id" name="league_id" required>
                        <option value="">Select league</option>
                        @foreach ($leagues as $league)
                            <option value="{{ $league->id }}" @selected((int) old('league_id') === (int) $league->id)>
                                {{ $league->name }}{{ ($league->stats ?? '') ? ' — '.ucfirst($league->stats) : '' }}
                            </option>
                        @endforeach
                    </select>
                    <p class="admin-card-text" style="margin-top: 8px; font-size: 13px; opacity: .8;">
                        Note: Group is auto-selected by matching this player’s type + the skill level.
                    </p>
                </div>

                <div class="admin-form-group">
                    <label class="admin-label" for="skill_level">Skill Level</label>
                    @php
                        $registrationSkillLevelValues = ['3', '3.25', '3.5', '3.75', '4', '4.25', '4.5', '4.75', '5', 'not-sure'];
                        $selectedSkill = old('skill_level');
                    @endphp
                    <select class="admin-input" id="skill_level" name="skill_level" required>
                        <option value="">Select skill level</option>
                        @foreach ($registrationSkillLevelValues as $skillValue)
                            <option value="{{ $skillValue }}" @selected($selectedSkill == $skillValue)>{{ $skillValue === 'not-sure' ? 'Not Sure' : $skillValue }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="admin-form-group">
                    <label class="admin-label" for="age_group_key">Age Group</label>
                    <select class="admin-input" id="age_group_key" name="age_group_key" required>
                        <option value="">Select age group</option>
                        @foreach ($ageBrackets as $key => $label)
                            <option value="{{ $key }}" @selected(old('age_group_key') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div style="display:flex; gap: 10px; margin-top: 18px;">
                <button class="admin-button" type="submit">Add to league</button>
                <a class="admin-button admin-button-secondary" href="{{ route('admin.players.index', ['tab' => $player->registration_type ?? 'singles']) }}">Cancel</a>
            </div>
        </form>
    </section>
@endsection

