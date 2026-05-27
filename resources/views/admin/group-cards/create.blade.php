@extends('layouts.admin')

@section('title', 'Add Group | '.config('app.name', 'playptl'))
@section('meta_description', 'Create a new group from the admin dashboard.')

@section('content')
    <section class="admin-card">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-card-title">Add Group</h1>
                <p class="admin-card-text">Create a new group.</p>
            </div>
            <a class="admin-link" href="{{ route('admin.group-cards.index') }}">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                <span>Back to Groups</span>
            </a>
        </div>

        @if ($errors->any())
            <div class="admin-alert admin-alert-error">
                <ul style="margin:0;padding-left:1.1rem;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form class="admin-form admin-form-wide" method="POST" action="{{ route('admin.group-cards.store') }}">
            @include('admin.group-cards._form')

            <button class="admin-button" type="submit">Create Group</button>
        </form>
    </section>
@endsection
