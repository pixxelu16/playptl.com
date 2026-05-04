@extends('layouts.admin')

@section('title', $announcement->title.' | '.config('app.name', 'playptl'))
@section('meta_description', 'View announcement details from the admin dashboard.')

@section('content')
    <section class="admin-card">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-card-title">{{ $announcement->title }}</h1>
                <p class="admin-card-text">Announcement details and display settings.</p>
            </div>
            <div class="admin-header-actions">
                <a class="admin-link" href="{{ route('admin.announcements.index') }}">
                    <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                    <span>Back</span>
                </a>
                <a class="admin-button admin-button-link" href="{{ route('admin.announcements.edit', $announcement) }}">
                    <i class="fa-solid fa-pen" aria-hidden="true"></i>
                    <span>Edit Announcement</span>
                </a>
            </div>
        </div>

        <div class="admin-detail-list admin-detail-list-wide">
            <div>
                <span>Announcement Type</span>
                <strong>{{ ucfirst($announcement->type) }}</strong>
            </div>
            <div>
                <span>Announcement Date</span>
                <strong>{{ $announcement->announcement_date->format('M d, Y') }}</strong>
            </div>
            <div>
                <span>Featured</span>
                <strong>{{ $announcement->is_featured ? 'Yes' : 'No' }}</strong>
            </div>
            <div>
                <span>Status</span>
                <strong>{{ $announcement->is_active ? 'Active' : 'Inactive' }}</strong>
            </div>
            <div>
                <span>Description</span>
                <p>{{ $announcement->description }}</p>
            </div>
        </div>
    </section>
@endsection
