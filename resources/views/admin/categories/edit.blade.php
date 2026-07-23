@extends('layouts.admin')

@section('title', 'Edit Category | '.config('app.name', 'playptl'))
@section('meta_description', 'Modify an existing tournament category.')

@section('content')
    <section class="admin-card">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-card-title">Edit Category: {{ $category->name }}</h1>
                <p class="admin-card-text">Modify category settings.</p>
            </div>
            <a class="admin-link" href="{{ route('admin.categories.index') }}">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                <span>Back to Categories</span>
            </a>
        </div>

        @if ($errors->any())
            <div class="admin-alert admin-alert-error">{{ $errors->first() }}</div>
        @endif

        <form class="admin-form" method="POST" action="{{ route('admin.categories.update', $category) }}">
            @csrf
            @method('PUT')
            <div class="admin-form-group">
                <label class="admin-label" for="name">Category Name</label>
                <input class="admin-input" id="name" type="text" name="name" value="{{ old('name', $category->name) }}" placeholder="e.g. Mixed, Youth" required>
                <p class="admin-field-hint">The unique name/label of this category.</p>
            </div>

            <div class="admin-form-group">
                <label class="admin-label" for="menu_order">Menu Order</label>
                <input class="admin-input" id="menu_order" type="number" name="menu_order" value="{{ old('menu_order', $category->menu_order) }}" min="0" step="1" required>
                <p class="admin-field-hint">The order this category appears in menus (lower numbers first).</p>
            </div>

            <button class="admin-button" type="submit">Save Changes</button>
        </form>
    </section>
@endsection
