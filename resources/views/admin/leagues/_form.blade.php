@csrf
@php
    $today = now()->toDateString();
    $selectedGroupCardIds = old('group_card_ids', $league->exists ? $league->groupCards->pluck('id')->all() : []);
@endphp

<div class="admin-form-grid">
    <div class="admin-form-group" style="grid-column: 1 / -1;">
        <label class="admin-label" for="name">League Name</label>
        <input class="admin-input" id="name" type="text" name="name" value="{{ old('name', $league->name) }}" required>
    </div>

    <div class="admin-form-group">
        <label class="admin-label" for="start_date">League Start Date</label>
        <input class="admin-input" id="start_date" type="date" name="start_date" @if (! $league->exists) min="{{ $today }}" @endif value="{{ old('start_date', optional($league->start_date)->format('Y-m-d')) }}">
        <p class="admin-field-hint">Optional — set on the Matches page to auto-schedule. Weeks end on Sunday.</p>
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
    <span class="admin-label">Assign Groups</span>
    <p class="admin-field-hint">Select one or more groups for this league listing section.</p>
    <div class="admin-checkbox-grid">
        @forelse ($groupCards ?? [] as $groupCard)
            <label class="admin-checkbox-inline">
                <input type="checkbox" name="group_card_ids[]" value="{{ $groupCard->id }}" @checked(in_array($groupCard->id, $selectedGroupCardIds, true))>
                <span>
                    {{ $groupCard->name }}
                </span>
            </label>
        @empty
            <p class="admin-muted">No groups yet. Add them from the Groups section first.</p>
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
