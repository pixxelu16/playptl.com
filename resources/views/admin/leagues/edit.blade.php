@extends('layouts.admin')

@section('title', 'Edit League | '.config('app.name', 'playptl'))
@section('meta_description', 'Edit league details from the admin dashboard.')

@section('content')
    <section class="admin-card">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-card-title">Edit League</h1>
                <p class="admin-card-text">Update league details for {{ $league->name }}.</p>
            </div>
            <a class="admin-link" href="{{ route('admin.leagues.index') }}">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                <span>Back to Leagues</span>
            </a>
        </div>

        @if ($errors->any())
            <div class="admin-alert admin-alert-error">{{ $errors->first() }}</div>
        @endif

        <form class="admin-form admin-form-wide" method="POST" action="{{ route('admin.leagues.update', $league) }}" enctype="multipart/form-data">
            @method('PUT')
            @include('admin.leagues._form')

            <button class="admin-button" type="submit">Update League</button>
        </form>
    </section>

@endsection
