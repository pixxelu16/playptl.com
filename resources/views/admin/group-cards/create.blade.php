@extends('layouts.admin')

@section('title', 'Add Group Card | '.config('app.name', 'playptl'))
@section('meta_description', 'Create a new group card from the admin dashboard.')

@section('content')
    <section class="admin-card">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-card-title">Add Group Card</h1>
                <p class="admin-card-text">Add a card used in the League groups listing section.</p>
            </div>
            <a class="admin-link" href="{{ route('admin.group-cards.index') }}">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                <span>Back to Group Cards</span>
            </a>
        </div>

        @if ($errors->any())
            <div class="admin-alert admin-alert-error">{{ $errors->first() }}</div>
        @endif

        <form class="admin-form admin-form-wide" method="POST" action="{{ route('admin.group-cards.store') }}">
            @include('admin.group-cards._form')

            <button class="admin-button" type="submit">Create Group Card</button>
        </form>
    </section>
@endsection
