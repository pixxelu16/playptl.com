@extends('layouts.admin')

@section('title', 'Add Skill Level | '.config('app.name', 'playptl'))
@section('meta_description', 'Configure a new player skill level.')

@section('content')
    <section class="admin-card">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-card-title">Add Skill Level</h1>
                <p class="admin-card-text">Define a new skill level value (e.g. 3.5, 4.0, or not-sure) and its display order.</p>
            </div>
            <a class="admin-link" href="{{ route('admin.skills.index') }}">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                <span>Back to Skill Levels</span>
            </a>
        </div>

        @if ($errors->any())
            <div class="admin-alert admin-alert-error">{{ $errors->first() }}</div>
        @endif

        <form class="admin-form" method="POST" action="{{ route('admin.skills.store') }}">
            @csrf
            @include('admin.skills._form')

            <button class="admin-button" type="submit">Create Skill Level</button>
        </form>
    </section>
@endsection
