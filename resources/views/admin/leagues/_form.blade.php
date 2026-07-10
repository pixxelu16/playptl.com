@csrf
@php
    $selectedGroupCardIds = old('group_card_ids', $league->exists ? $league->groupCards->pluck('id')->all() : []);
    $singlesEntryFee = old('singles_entry_fee', \App\Support\LeagueEntryFee::dollarsInputValue($league->exists ? $league : null, 'singles'));
    $doublesEntryFee = old('doubles_entry_fee', \App\Support\LeagueEntryFee::dollarsInputValue($league->exists ? $league : null, 'doubles'));
@endphp

<div class="admin-form-grid">
    <div class="admin-form-group" style="grid-column: 1 / -1;">
        <label class="admin-label" for="name">Tournament Name</label>
        <input class="admin-input" id="name" type="text" name="name" value="{{ old('name', $league->name) }}" required>
    </div>

    <div class="admin-form-group">
        <label class="admin-label" for="start_date">Start Date</label>
        <input
            class="admin-input"
            id="start_date"
            type="date"
            name="start_date"
            value="{{ old('start_date', $league->start_date?->format('Y-m-d') ?? '') }}"
        >
    </div>

    <div class="admin-form-group">
        <label class="admin-label" for="end_date">End Date</label>
        <input
            class="admin-input"
            id="end_date"
            type="date"
            name="end_date"
            value="{{ old('end_date', $league->end_date?->format('Y-m-d') ?? '') }}"
        >
    </div>

    <div class="admin-form-group">
        <label class="admin-label" for="singles_entry_fee">Singles entry fee (USD)</label>
        <input
            class="admin-input"
            id="singles_entry_fee"
            type="number"
            name="singles_entry_fee"
            min="0"
            step="0.01"
            inputmode="decimal"
            value="{{ $singlesEntryFee }}"
            required
        >
    </div>

    <div class="admin-form-group">
        <label class="admin-label" for="doubles_entry_fee">Doubles entry fee (USD)</label>
        <input
            class="admin-input"
            id="doubles_entry_fee"
            type="number"
            name="doubles_entry_fee"
            min="0"
            step="0.01"
            inputmode="decimal"
            value="{{ $doublesEntryFee }}"
            required
        >
    </div>
</div>

<div class="admin-form-group">
    <label class="admin-label" for="logo">Tournament Logo</label>
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
    <p class="admin-field-hint">Select one or more groups for this tournament.</p>
    <div class="admin-checkbox-grid">
        @forelse ($groupCards ?? [] as $groupCard)
            <label class="admin-checkbox-inline">
                <input type="checkbox" name="group_card_ids[]" value="{{ $groupCard->id }}" @checked(in_array($groupCard->id, $selectedGroupCardIds, true))>
                <span>
                    {{ $groupCard->name }}
                    @if ($groupCard->skill_level_match)
                        <small class="admin-muted">({{ $groupCard->skill_level_match }})</small>
                    @endif
                </span>
            </label>
        @empty
            <p class="admin-muted">No groups yet. Add them from the Groups section first.</p>
        @endforelse
    </div>
</div>

<div class="admin-form-group">
    <label class="admin-label" for="description">Tournament Description</label>
    <textarea class="admin-input admin-textarea" id="description" name="description">{{ old('description', $league->description) }}</textarea>
</div>

<div class="admin-form-group">
    <label class="admin-label" for="stats">Tournament Status</label>
    <select class="admin-input" id="stats" name="stats">
        <option value="">Select status</option>
        <option value="active" @selected(old('stats', $league->stats) === 'active')>Active</option>
        <option value="deactive" @selected(old('stats', $league->stats) === 'deactive')>Deactive</option>
        <option value="upcoming" @selected(old('stats', $league->stats) === 'upcoming')>Upcoming</option>
        <option value="completed" @selected(old('stats', $league->stats) === 'completed')>Completed</option>
    </select>
</div>
