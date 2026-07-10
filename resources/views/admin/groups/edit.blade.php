@extends('layouts.admin')

@section('title', 'Edit Subgroup | '.config('app.name', 'playptl'))
@section('meta_description', 'Edit subgroup details from the admin dashboard.')

@section('content')
    <section class="admin-card">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-card-title">Edit Subgroup</h1>
                <p class="admin-card-text">Update name, status, and description for {{ $group->name }}.</p>
            </div>
            <a class="admin-link" href="{{ route('admin.groups.index') }}">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                <span>Back to Subgroups</span>
            </a>
        </div>

        @if ($errors->any())
            <div class="admin-alert admin-alert-error">{{ $errors->first() }}</div>
        @endif

        <form class="admin-form admin-form-wide" method="POST" action="{{ route('admin.groups.update', $group) }}">
            @method('PUT')
            @include('admin.groups._form')

            <button class="admin-button" type="submit">Update Subgroup</button>
        </form>
    </section>
@endsection
