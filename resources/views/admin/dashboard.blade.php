@extends('layouts.admin')

@section('title', 'Admin Dashboard | '.config('app.name', 'playptl'))
@section('meta_description', 'Admin dashboard for managing users, organisers, players, and platform settings.')

@section('content')
    <section class="admin-card">
        <div class="admin-dashboard-hero">
            <div>
                <h1 class="admin-card-title">Admin Dashboard</h1>
                <p class="admin-card-text">Welcome, {{ auth()->user()->name }}. Use the quick stats below to jump into management.</p>
            </div>
        </div>

        <div class="admin-stats-grid">
            <a class="admin-stat-card admin-stat-card--leagues" href="{{ route('admin.leagues.index') }}">
                <span class="admin-stat-icon" aria-hidden="true"><i class="fa-solid fa-trophy"></i></span>
                <div class="admin-stat-body">
                    <span class="admin-stat-label">Total Tournaments</span>
                    <strong class="admin-stat-value">{{ $leaguesCount ?? 0 }}</strong>
                    <span class="admin-stat-hint">Manage tournaments</span>
                </div>
                <span class="admin-stat-chevron" aria-hidden="true"><i class="fa-solid fa-chevron-right"></i></span>
            </a>

            <a class="admin-stat-card admin-stat-card--announcements" href="{{ route('admin.announcements.index') }}">
                <span class="admin-stat-icon" aria-hidden="true"><i class="fa-solid fa-bullhorn"></i></span>
                <div class="admin-stat-body">
                    <span class="admin-stat-label">Total Announcements</span>
                    <strong class="admin-stat-value">{{ $announcementsCount ?? 0 }}</strong>
                    <span class="admin-stat-hint">Manage announcements</span>
                </div>
                <span class="admin-stat-chevron" aria-hidden="true"><i class="fa-solid fa-chevron-right"></i></span>
            </a>

            <a class="admin-stat-card admin-stat-card--groups" href="{{ route('admin.league-management.index') }}">
                <span class="admin-stat-icon" aria-hidden="true"><i class="fa-solid fa-users-line"></i></span>
                <div class="admin-stat-body">
                    <span class="admin-stat-label">Total Subgroups</span>
                    <strong class="admin-stat-value">{{ $groupsCount ?? 0 }}</strong>
                    <span class="admin-stat-hint">Manage subgroups (A, B, C, D…)</span>
                </div>
                <span class="admin-stat-chevron" aria-hidden="true"><i class="fa-solid fa-chevron-right"></i></span>
            </a>

            <a class="admin-stat-card admin-stat-card--groups" href="{{ route('admin.group-cards.index') }}">
                <span class="admin-stat-icon" aria-hidden="true"><i class="fa-solid fa-table-cells-large"></i></span>
                <div class="admin-stat-body">
                    <span class="admin-stat-label">Total Groups</span>
                    <strong class="admin-stat-value">{{ $groupCardsCount ?? 0 }}</strong>
                    <span class="admin-stat-hint">Manage groups (Voyagers, Challengers…)</span>
                </div>
                <span class="admin-stat-chevron" aria-hidden="true"><i class="fa-solid fa-chevron-right"></i></span>
            </a>

            <a class="admin-stat-card admin-stat-card--groups" href="{{ route('admin.players.index') }}">
                <span class="admin-stat-icon" aria-hidden="true"><i class="fa-solid fa-user"></i></span>
                <div class="admin-stat-body">
                    <span class="admin-stat-label">Total Players</span>
                    <strong class="admin-stat-value">{{ $playersCount ?? 0 }}</strong>
                    <span class="admin-stat-hint">Manage players</span>
                </div>
                <span class="admin-stat-chevron" aria-hidden="true"><i class="fa-solid fa-chevron-right"></i></span>
            </a>

            <a class="admin-stat-card admin-stat-card--groups" href="{{ route('admin.payment-histories.index') }}">
                <span class="admin-stat-icon" aria-hidden="true"><i class="fa-solid fa-credit-card"></i></span>
                <div class="admin-stat-body">
                    <span class="admin-stat-label">Total Payments</span>
                    <strong class="admin-stat-value">{{ $paymentsCount ?? 0 }}</strong>
                    <span class="admin-stat-hint">Payment history</span>
                </div>
                <span class="admin-stat-chevron" aria-hidden="true"><i class="fa-solid fa-chevron-right"></i></span>
            </a>
        </div>
    </section>
@endsection
