@extends('layouts.admin')

@section('title', $charityCause->title.' | Charity Cause | '.config('app.name', 'playptl'))
@section('meta_description', 'View charity cause details.')

@section('content')
    <section class="admin-card">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-card-title">{{ $charityCause->title }}</h1>
                <p class="admin-card-text">Charity cause details.</p>
            </div>
            <div class="admin-page-header-actions">
                <a class="admin-link" href="{{ route('admin.charity-causes.index') }}">
                    <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                    <span>Back</span>
                </a>
                <a class="admin-button admin-button-link" href="{{ route('admin.charity-causes.edit', $charityCause) }}">
                    <i class="fa-solid fa-pen" aria-hidden="true"></i>
                    <span>Edit</span>
                </a>
            </div>
        </div>

        @if ($charityCause->image_path)
            <div style="margin-bottom: 20px;">
                <img src="{{ asset($charityCause->image_path) }}" alt="{{ $charityCause->title }}" style="max-width: 100%; max-height: 320px; border-radius: 12px; object-fit: cover;">
            </div>
        @endif

        <div class="admin-table-wrap" style="padding: 18px;">
            <p><strong>Status:</strong> {{ $charityCause->is_active ? 'Active' : 'Inactive' }}</p>
            <p><strong>Display order:</strong> {{ $charityCause->display_order }}</p>
            <p style="margin-top: 16px;"><strong>Purpose / Description</strong></p>
            <p style="white-space: pre-line; color: #4b5563;">{{ $charityCause->description }}</p>
        </div>
    </section>
@endsection
