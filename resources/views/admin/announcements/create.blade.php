@extends('layouts.admin')

@section('title', 'Add Announcement | '.config('app.name', 'playptl'))
@section('meta_description', 'Create a new announcement from the admin dashboard.')

@section('content')
    <section class="admin-card">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-card-title">Add Announcement</h1>
                <p class="admin-card-text">Add a homepage announcement for players and visitors.</p>
            </div>
            <a class="admin-link" href="{{ route('admin.announcements.index') }}">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                <span>Back to Announcements</span>
            </a>
        </div>

        @if ($errors->any())
            <div class="admin-alert admin-alert-error">{{ $errors->first() }}</div>
        @endif

        <form class="admin-form admin-form-wide" method="POST" action="{{ route('admin.announcements.store') }}">
            @include('admin.announcements._form')

            <button class="admin-button" type="submit">Create Announcement</button>
        </form>
    </section>
@endsection
