@extends('layouts.admin')

@section('title', 'Edit Official Partner | '.config('app.name', 'playptl'))
@section('meta_description', 'Edit an official partner shown on the homepage.')

@section('content')
    <section class="admin-card">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-card-title">Edit Official Partner</h1>
                <p class="admin-card-text">Update partner details for {{ $officialPartner->name }}.</p>
            </div>
            <a class="admin-link" href="{{ route('admin.official-partners.index') }}">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                <span>Back to Official Partners</span>
            </a>
        </div>

        @if ($errors->any())
            <div class="admin-alert admin-alert-error">{{ $errors->first() }}</div>
        @endif

        <form class="admin-form admin-form-wide" method="POST" action="{{ route('admin.official-partners.update', $officialPartner) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('admin.official-partners._form')

            <button class="admin-button" type="submit">Update Partner</button>
        </form>
    </section>
@endsection
