@extends('layouts.admin')

@section('title', 'Edit Charity Cause | '.config('app.name', 'playptl'))
@section('meta_description', 'Edit a charity cause for the public charity page.')

@section('content')
    <section class="admin-card">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-card-title">Edit Charity Cause</h1>
                <p class="admin-card-text">Update title, purpose, image, or visibility.</p>
            </div>
            <a class="admin-link" href="{{ route('admin.charity-causes.index') }}">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                <span>Back to Charity Causes</span>
            </a>
        </div>

        @if ($errors->any())
            <div class="admin-alert admin-alert-error">{{ $errors->first() }}</div>
        @endif

        <form class="admin-form admin-form-wide" method="POST" action="{{ route('admin.charity-causes.update', $charityCause) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('admin.charity-causes._form')

            <button class="admin-button" type="submit">Update Charity Cause</button>
        </form>
    </section>
@endsection
