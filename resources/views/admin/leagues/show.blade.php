@extends('layouts.admin')

@section('title', $league->name.' | '.config('app.name', 'playptl'))
@section('meta_description', 'View league details from the admin dashboard.')

@section('content')
    @php
        $defaultLeagueLogo = asset('frontend/images/champion.png');
    @endphp
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
                <a class="admin-link" href="{{ route('admin.league-management.show', $league) }}">
                    <i class="fa-solid fa-sitemap" aria-hidden="true"></i>
                    <span>Manage Groups/Players</span>
                </a>
                <a class="admin-button admin-button-link" href="{{ route('admin.leagues.edit', $league) }}">
                    <i class="fa-solid fa-pen" aria-hidden="true"></i>
                    <span>Edit League</span>
                </a>
            </div>
        </div>

        <div class="admin-detail-grid">
            <div class="admin-detail-logo">
                <img
                    src="{{ $league->logo_path ? asset($league->logo_path) : $defaultLeagueLogo }}"
                    alt="{{ $league->name }} logo"
                >
            </div>

            <div class="admin-detail-list">
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
                    <span>Entry fees</span>
                    <p>
                        <strong>Singles:</strong> ${{ \App\Support\LeagueEntryFee::formatDollars(\App\Support\LeagueEntryFee::singlesCents($league)) }}
                        &nbsp;|&nbsp;
                        <strong>Doubles:</strong> ${{ \App\Support\LeagueEntryFee::formatDollars(\App\Support\LeagueEntryFee::doublesCents($league)) }}
                    </p>
                </div>
                <div>
                    <span>Registrations</span>
                    <p>
                        <strong>Singles:</strong> {{ $singlesCount ?? 0 }}
                        &nbsp;|&nbsp;
                        <strong>Doubles:</strong> {{ $doublesCount ?? 0 }}
                    </p>
                </div>
                <div>
                    <span>Assigned Groups</span>
                    @if ($league->groupCards->isEmpty())
                        <p>-</p>
                    @else
                        <ul class="admin-group-tag-list">
                            @foreach ($league->groupCards as $groupCard)
                                <li>
                                    <span class="admin-badge">
                                        {{ $groupCard->name }}
                                        ({{ strtoupper($groupCard->tag) }})
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection
