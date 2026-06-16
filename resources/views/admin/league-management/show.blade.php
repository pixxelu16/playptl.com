@extends('layouts.admin')

@section('title', $league->name.' Management | '.config('app.name', 'playptl'))
@section('meta_description', 'Manage groups, subgroups and players for a tournament.')

@section('content')
    <section class="admin-card">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-card-title">{{ $league->name }} — Management</h1>
                <p class="admin-card-text">Tournament → Groups → Subgroups → Players (self-registration on website).</p>
            </div>
            <div class="admin-header-actions">
                <a class="admin-link" href="{{ route('admin.league-management.index') }}">
                    <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                    <span>Back</span>
                </a>
                <a class="admin-button admin-button-link" href="{{ route('admin.leagues.edit', $league) }}">
                    <i class="fa-solid fa-pen" aria-hidden="true"></i>
                    <span>Edit Tournament</span>
                </a>
            </div>
        </div>

        @if (! $tablesReady)
            <div class="admin-alert admin-alert-error">
                Tournament management tables are not available yet. Create & run migrations first (no website changes needed).
            </div>
        @endif

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Group</th>
                        <th>Tag</th>
                        <th>Registered</th>
                        <th>Assigned</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($league->groupCards as $groupCard)
                        <tr>
                            <td>
                                <strong>{{ $groupCard->name }}</strong>
                            </td>
                            <td><span class="admin-badge">{{ strtoupper($groupCard->tag) }}</span></td>
                            @php
                                $s = $cardStats[$groupCard->id] ?? ['groups_count' => 0, 'registrations_count' => 0, 'assigned_count' => 0];
                            @endphp
                            <td>{{ $s['registrations_count'] }}</td>
                            <td>{{ $s['assigned_count'] }}</td>
                            <td>
                                <div class="admin-table-actions">
                                    <a href="{{ route('admin.league-management.assign-players.index', [$league, $groupCard]) }}" title="Assign player">
                                        <i class="fa-solid fa-plus" aria-hidden="true"></i>
                                    </a>
                                    <a href="{{ route('admin.league-management.groups.index', [$league, $groupCard]) }}" title="Subgroups &amp; players">
                                        <i class="fa-solid fa-layer-group" aria-hidden="true"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="admin-empty-state">
                                    <i class="fa-solid fa-table-cells-large" aria-hidden="true"></i>
                                    <p>No groups are assigned to this tournament. Edit the tournament and assign groups.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection

