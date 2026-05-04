@extends('layouts.admin')

@section('title', $league->name.' | '.config('app.name', 'playptl'))
@section('meta_description', 'View league details from the admin dashboard.')

@section('content')
    <section class="admin-card">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-card-title">{{ $league->name }}</h1>
                <p class="admin-card-text">League details and schedule information.</p>
            </div>
            <div class="admin-header-actions">
                <a class="admin-link" href="{{ route('admin.leagues.index') }}">
                    <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                    <span>Back</span>
                </a>
                <a class="admin-button admin-button-link" href="{{ route('admin.leagues.edit', $league) }}">
                    <i class="fa-solid fa-pen" aria-hidden="true"></i>
                    <span>Edit League</span>
                </a>
            </div>
        </div>

        <div class="admin-detail-grid">
            <div class="admin-detail-logo">
                @if ($league->logo_path)
                    <img src="{{ asset($league->logo_path) }}" alt="{{ $league->name }} logo">
                @else
                    <span>No Logo</span>
                @endif
            </div>

            <div class="admin-detail-list">
                <div>
                    <span>League Type</span>
                    <strong>{{ ucfirst($league->type) }}</strong>
                </div>
                <div>
                    <span>Start Date</span>
                    <strong>{{ $league->start_date?->format('M d, Y') ?? '-' }}</strong>
                </div>
                <div>
                    <span>End Date</span>
                    <strong>{{ $league->end_date?->format('M d, Y') ?? '-' }}</strong>
                </div>
                <div>
                    <span>League Description</span>
                    <p>{{ $league->description ?: '-' }}</p>
                </div>
                <div>
                    <span>League Stats</span>
                    <p>{{ $league->stats ? ucfirst($league->stats) : '-' }}</p>
                </div>
                <div>
                    <span>Assigned Groups</span>
                    @if ($league->groups->isEmpty())
                        <p>-</p>
                    @else
                        <ul class="admin-group-tag-list">
                            @foreach ($league->groups as $group)
                                <li><span class="admin-badge">{{ $group->name }}</span></li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection
