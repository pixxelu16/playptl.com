@extends('layouts.admin')

@section('title', 'Add Subgroup | '.$league->name.' | '.config('app.name', 'playptl'))
@section('meta_description', 'Create a subgroup for a tournament and group.')

@section('content')
    <section class="admin-card">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-card-title">Add Subgroup</h1>
                <p class="admin-card-text">Tournament: <strong>{{ $league->name }}</strong> · Group: <strong>{{ $groupCard->name }}</strong></p>
            </div>
            <a class="admin-link" href="{{ route('admin.league-management.groups.index', [$league, $groupCard, 'age_group_key' => $ageGroupKey]) }}">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                <span>Back</span>
            </a>
        </div>

        @if (! $schemaReady)
            <div class="admin-alert admin-alert-error">
                Groups schema not ready. Run the migrations that add `group_card_id` and `age_group_key` to `groups`.
            </div>
        @endif

        @if ($errors->any())
            <div class="admin-alert admin-alert-error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('admin.league-management.groups.store', [$league, $groupCard]) }}">
            @csrf
            <input type="hidden" name="age_group_key" value="{{ old('age_group_key', $ageGroupKey) }}">

            @include('admin.groups._form', ['group' => $group])

            <button class="admin-button" type="submit">Create Subgroup</button>
        </form>
    </section>
@endsection

