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
                <label class="admin-label">Category Type <span style="color:#dc2626">*</span></label>
                <div style="display: flex; gap: 20px; margin-top: 6px;">
                    @php 
                        $currentTypes = explode(',', $category->type ?? 'single,doubles');
                        $oldTypes = old('types', $currentTypes); 
                    @endphp
                    <label style="display: flex; align-items: center; gap: 8px; font-weight: 500; cursor: pointer;">
                        <input type="checkbox" name="types[]" value="single" @checked(in_array('single', $oldTypes))>
                        <span>Singles</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; font-weight: 500; cursor: pointer;">
                        <input type="checkbox" name="types[]" value="doubles" @checked(in_array('doubles', $oldTypes))>
                        <span>Doubles</span>
                    </label>
                </div>
                <p class="admin-field-hint">Select whether this category applies to Singles, Doubles, or both.</p>
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
