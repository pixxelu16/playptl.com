@csrf
@php
    $today = now()->toDateString();
    $selectedGroupIds = old('group_ids', $league->exists ? $league->groups->pluck('id')->all() : []);
    $selectedGroupCardIds = old('group_card_ids', $league->exists ? $league->groupCards->pluck('id')->all() : []);
@endphp

<div class="admin-form-grid">
    <div class="admin-form-group" style="grid-column: 1 / -1;">
        <label class="admin-label" for="name">League Name</label>
        <input class="admin-input" id="name" type="text" name="name" value="{{ old('name', $league->name) }}" required>
    </div>

    <div class="admin-form-group">
        <label class="admin-label" for="start_date">League Start Date</label>
        <input class="admin-input" id="start_date" type="date" name="start_date" min="{{ $today }}" value="{{ old('start_date', optional($league->start_date)->format('Y-m-d')) }}">
    </div>

    <div class="admin-form-group">
        <label class="admin-label" for="end_date">League End Date</label>
        <input class="admin-input" id="end_date" type="date" name="end_date" min="{{ $today }}" value="{{ old('end_date', optional($league->end_date)->format('Y-m-d')) }}">
    </div>
</div>

<div class="admin-form-group">
    <label class="admin-label" for="logo">League Logo</label>
    <input class="admin-input" id="logo" type="file" name="logo" accept="image/*">
    @if ($league->logo_path)
        <div class="admin-current-logo">
            <img src="{{ asset($league->logo_path) }}" alt="{{ $league->name }} logo">
            <span>Current logo</span>
        </div>
    @endif
</div>

<div class="admin-form-group">
    <span class="admin-label">Assign Group Cards</span>
    <p class="admin-field-hint">Select one or more group cards for this league listing section.</p>
    <div class="admin-checkbox-grid">
        @forelse ($groupCards ?? [] as $groupCard)
            <label class="admin-checkbox-inline">
                <input type="checkbox" name="group_card_ids[]" value="{{ $groupCard->id }}" @checked(in_array($groupCard->id, $selectedGroupCardIds, true))>
                <span>
                    {{ $groupCard->name }}
                    <small class="admin-muted">
                        ({{ strtoupper($groupCard->tag) }} · {{ $groupCard->players_count }} players · {{ $groupCard->groups_count }} groups · {{ ucfirst($groupCard->status) }})
                    </small>
                </span>
            </label>
        @empty
            <p class="admin-muted">No group cards yet. Add them from the Group Cards section first.</p>
        @endforelse
    </div>
</div>

<div class="admin-form-group">
    <label class="admin-label" for="description">League Description</label>
    <textarea class="admin-input admin-textarea" id="description" name="description">{{ old('description', $league->description) }}</textarea>
</div>

<div class="admin-form-group">
    <label class="admin-label" for="stats">League Stats</label>
    <select class="admin-input" id="stats" name="stats">
        <option value="">Select Stats</option>
        <option value="active" @selected(old('stats', $league->stats) === 'active')>Active</option>
        <option value="deactive" @selected(old('stats', $league->stats) === 'deactive')>Deactive</option>
        <option value="upcoming" @selected(old('stats', $league->stats) === 'upcoming')>Upcoming</option>
        <option value="completed" @selected(old('stats', $league->stats) === 'completed')>Completed</option>
    </select>
</div>

<div class="admin-form-group">
    <span class="admin-label">Assign Groups</span>
    <p class="admin-field-hint">Select one or more groups linked to this league.</p>
    <div class="admin-checkbox-grid">
        @forelse ($groups ?? [] as $group)
            <label class="admin-checkbox-inline">
                <input type="checkbox" name="group_ids[]" value="{{ $group->id }}" @checked(in_array($group->id, $selectedGroupIds, true))>
                <span>{{ $group->name }} <small class="admin-muted">({{ $group->players_count }} players · {{ ucfirst($group->status) }})</small></span>
            </label>
        @empty
            <p class="admin-muted">No groups yet. Add groups from the Groups section first.</p>
        @endforelse
    </div>
</div>
