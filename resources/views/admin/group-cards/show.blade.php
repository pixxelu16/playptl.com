@extends('layouts.admin')

@section('title', $groupCard->name.' | '.config('app.name', 'playptl'))
@section('meta_description', 'View sub group details from the admin dashboard.')

@section('content')
    <section class="admin-card">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-card-title">{{ $groupCard->name }}</h1>
                <p class="admin-card-text">Sub group details and visibility status.</p>
            </div>
            <div class="admin-header-actions">
                <a class="admin-link" href="{{ route('admin.group-cards.index') }}">
                    <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                    <span>Back</span>
                </a>
                <a class="admin-button admin-button-link" href="{{ route('admin.group-cards.edit', $groupCard) }}">
                    <i class="fa-solid fa-pen" aria-hidden="true"></i>
                    <span>Edit Sub Group</span>
                </a>
            </div>
        </div>

        <div class="admin-detail-list admin-detail-list-wide">
            <div>
                <span>Slug</span>
                <strong>{{ $groupCard->slug ?? '—' }}</strong>
            </div>
            <div>
                <span>Skill level match</span>
                <strong>{{ $groupCard->skill_level_match ?? '—' }}</strong>
            </div>
            <div>
                <span>Tag</span>
                <strong>{{ strtoupper($groupCard->tag) }}</strong>
            </div>
            <div>
                <span>Assigned Groups</span>
                @php($assignedGroups = $groupCard->relationLoaded('groups') ? $groupCard->groups : $groupCard->groups()->orderBy('name')->get())
                @if ($assignedGroups->isEmpty())
                    <strong>—</strong>
                @else
                    <ul class="admin-group-tag-list">
                        @foreach ($assignedGroups as $g)
                            <li><span class="admin-badge">{{ $g->name }}</span></li>
                        @endforeach
                    </ul>
                @endif
            </div>
            <div>
                <span>Players Count</span>
                <strong>{{ $groupCard->players_count }}</strong>
            </div>
            <div>
                <span>Display Order</span>
                <strong>{{ $groupCard->display_order }}</strong>
            </div>
            <div>
                <span>Status</span>
                <strong>{{ ucfirst($groupCard->status) }}</strong>
            </div>
        </div>
    </section>
@endsection
