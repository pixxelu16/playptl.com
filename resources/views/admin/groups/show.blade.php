@extends('layouts.admin')

@section('title', $group->name.' | '.config('app.name', 'playptl'))
@section('meta_description', 'View group details from the admin dashboard.')

@section('content')
    <section class="admin-card">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-card-title">{{ $group->name }}</h1>
                <p class="admin-card-text">Group details and status.</p>
            </div>
            <div class="admin-header-actions">
                <a class="admin-link" href="{{ route('admin.groups.index') }}">
                    <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                    <span>Back</span>
                </a>
                <a class="admin-button admin-button-link" href="{{ route('admin.groups.edit', $group) }}">
                    <i class="fa-solid fa-pen" aria-hidden="true"></i>
                    <span>Edit Group</span>
                </a>
            </div>
        </div>

        <div class="admin-detail-list admin-detail-list-wide">
            <div>
                <span>Players Count</span>
                <strong>{{ $group->players_count }}</strong>
            </div>
            <div>
                <span>Group Status</span>
                <strong>{{ ucfirst($group->status) }}</strong>
            </div>
            <div>
                <span>Group Description</span>
                <p>{{ $group->description ?: '-' }}</p>
            </div>
        </div>
    </section>
@endsection
