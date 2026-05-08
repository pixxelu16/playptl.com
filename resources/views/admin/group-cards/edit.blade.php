@extends('layouts.admin')

@section('title', 'Edit Sub Group | '.config('app.name', 'playptl'))
@section('meta_description', 'Edit sub group details from the admin dashboard.')

@section('content')
    <section class="admin-card">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-card-title">Edit Sub Group</h1>
                <p class="admin-card-text">Update sub group details for {{ $groupCard->name }}.</p>
            </div>
            <a class="admin-link" href="{{ route('admin.group-cards.index') }}">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                <span>Back to Sub Groups</span>
            </a>
        </div>

        @if ($errors->any())
            <div class="admin-alert admin-alert-error">{{ $errors->first() }}</div>
        @endif

        <form class="admin-form admin-form-wide" method="POST" action="{{ route('admin.group-cards.update', $groupCard) }}">
            @method('PUT')
            @include('admin.group-cards._form')

            <button class="admin-button" type="submit">Update Sub Group</button>
        </form>
    </section>
@endsection
