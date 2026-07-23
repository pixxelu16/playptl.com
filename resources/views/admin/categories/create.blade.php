@extends('layouts.admin')

@section('title', 'Add Category | '.config('app.name', 'playptl'))
@section('meta_description', 'Configure a new tournament category.')

@section('content')
    <section class="admin-card">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-card-title">Add Category</h1>
                <p class="admin-card-text">Define a new tournament category (e.g. Mixed, Youth, Singles, Doubles).</p>
            </div>
            <a class="admin-link" href="{{ route('admin.categories.index') }}">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                <span>Back to Categories</span>
            </a>
        </div>

        @if ($errors->any())
            <div class="admin-alert admin-alert-error">{{ $errors->first() }}</div>
        @endif

        <form class="admin-form" method="POST" action="{{ route('admin.categories.store') }}">
            @csrf
            <div class="admin-form-group">
                <label class="admin-label" for="name">Category Name</label>
                <input class="admin-input" id="name" type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Mixed, Youth" required>
                <p class="admin-field-hint">The unique name/label of this category.</p>
            </div>

            <div class="admin-form-group">
                <label class="admin-label" for="menu_order">Menu Order</label>
                <input class="admin-input" id="menu_order" type="number" name="menu_order" value="{{ old('menu_order', 0) }}" min="0" step="1" required>
                <p class="admin-field-hint">The order this category appears in menus (lower numbers first).</p>
            </div>

            <button class="admin-button" type="submit">Create Category</button>
        </form>
    </section>
@endsection
