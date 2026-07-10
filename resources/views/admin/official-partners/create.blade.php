@extends('layouts.admin')

@section('title', 'Add Official Partner | '.config('app.name', 'playptl'))
@section('meta_description', 'Create a new official partner for the homepage.')

@section('content')
    <section class="admin-card">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-card-title">Add Official Partner</h1>
                <p class="admin-card-text">Upload a partner logo for the homepage partners section.</p>
            </div>
            <a class="admin-link" href="{{ route('admin.official-partners.index') }}">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                <span>Back to Official Partners</span>
            </a>
        </div>

        @if ($errors->any())
            <div class="admin-alert admin-alert-error">{{ $errors->first() }}</div>
        @endif

        <form class="admin-form admin-form-wide" method="POST" action="{{ route('admin.official-partners.store') }}" enctype="multipart/form-data">
            @csrf
            @include('admin.official-partners._form')

            <button class="admin-button" type="submit">Create Partner</button>
        </form>
    </section>
@endsection
