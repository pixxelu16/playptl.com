@extends('layouts.admin')

@section('title', 'Add Charity Cause | '.config('app.name', 'playptl'))
@section('meta_description', 'Create a new charity cause for the public charity page.')

@section('content')
    <section class="admin-card">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-card-title">Add Charity Cause</h1>
                <p class="admin-card-text">Add title, purpose, and image for a charity shown on the website.</p>
            </div>
            <a class="admin-link" href="{{ route('admin.charity-causes.index') }}">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                <span>Back to Charity Causes</span>
            </a>
        </div>

        @if ($errors->any())
            <div class="admin-alert admin-alert-error">{{ $errors->first() }}</div>
        @endif

        <form class="admin-form admin-form-wide" method="POST" action="{{ route('admin.charity-causes.store') }}" enctype="multipart/form-data">
            @csrf
            @include('admin.charity-causes._form')

            <button class="admin-button" type="submit">Create Charity Cause</button>
        </form>
    </section>
@endsection
