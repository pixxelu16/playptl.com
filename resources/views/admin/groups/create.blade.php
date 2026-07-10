@extends('layouts.admin')

@section('title', 'Add Subgroup | '.config('app.name', 'playptl'))
@section('meta_description', 'Create a new subgroup from the admin dashboard.')

@section('content')
    <section class="admin-card">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-card-title">Add Subgroup</h1>
                <p class="admin-card-text">Add a subgroup with name, status, and description.</p>
            </div>
            <a class="admin-link" href="{{ route('admin.groups.index') }}">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                <span>Back to Subgroups</span>
            </a>
        </div>

        @if ($errors->any())
            <div class="admin-alert admin-alert-error">{{ $errors->first() }}</div>
        @endif

        <form class="admin-form admin-form-wide" method="POST" action="{{ route('admin.groups.store') }}">
            @include('admin.groups._form')

            <button class="admin-button" type="submit">Create Subgroup</button>
        </form>
    </section>
@endsection
