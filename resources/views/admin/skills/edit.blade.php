@extends('layouts.admin')

@section('title', 'Edit Skill Level | '.config('app.name', 'playptl'))
@section('meta_description', 'Update an existing player skill level.')

@section('content')
    <section class="admin-card">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-card-title">Edit Skill Level</h1>
                <p class="admin-card-text">Modify the value or display order of the skill level.</p>
            </div>
            <a class="admin-link" href="{{ route('admin.skills.index') }}">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                <span>Back to Skill Levels</span>
            </a>
        </div>

        @if ($errors->any())
            <div class="admin-alert admin-alert-error">{{ $errors->first() }}</div>
        @endif

        <form class="admin-form" method="POST" action="{{ route('admin.skills.update', $skill) }}">
            @csrf
            @method('PUT')
            @include('admin.skills._form')

            <button class="admin-button" type="submit">Update Skill Level</button>
        </form>
    </section>
@endsection
