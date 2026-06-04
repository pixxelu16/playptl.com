@extends('layouts.admin')

@section('title', 'Add Tournament | '.config('app.name', 'playptl'))
@section('meta_description', 'Create a new tournament from the admin dashboard.')

@section('content')
    <section class="admin-card">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-card-title">Add Tournament</h1>
                <p class="admin-card-text">Enter tournament details and upload a logo.</p>
            </div>
            <a class="admin-link" href="{{ route('admin.leagues.index') }}">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                <span>Back to Tournaments</span>
            </a>
        </div>

        @if ($errors->any())
            <div class="admin-alert admin-alert-error">{{ $errors->first() }}</div>
        @endif

        <form class="admin-form admin-form-wide" method="POST" action="{{ route('admin.leagues.store') }}" enctype="multipart/form-data">
            @include('admin.leagues._form')

            <button class="admin-button" type="submit">Create Tournament</button>
        </form>
    </section>

@endsection
