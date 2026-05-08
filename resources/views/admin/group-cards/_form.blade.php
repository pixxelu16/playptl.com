@csrf
@php
    $selectedGroupIds = old('group_ids', $groupCard->exists ? ($groupCard->groups?->pluck('id')->all() ?? []) : []);
@endphp

<div class="admin-form-grid">
    <div class="admin-form-group">
        <label class="admin-label" for="name">Name</label>
        <input class="admin-input" id="name" type="text" name="name" value="{{ old('name', $groupCard->name) }}" required>
    </div>

    <div class="admin-form-group">
        <label class="admin-label" for="skill_level_match">Skill level match (optional)</label>
        <select class="admin-input" id="skill_level_match" name="skill_level_match">
            <option value="">—</option>
            @foreach (['3', '3.25', '3.5', '3.75', '4', '4.25', '4.5', '4.75', '5'] as $lvl)
                <option value="{{ $lvl }}" @selected(old('skill_level_match', $groupCard->skill_level_match) == $lvl)>{{ $lvl }}</option>
            @endforeach
            <option value="not-sure" @selected(old('skill_level_match', $groupCard->skill_level_match) === 'not-sure')>Not Sure</option>
        </select>
    </div>

    <div class="admin-form-group">
        <label class="admin-label" for="tag">Tag</label>
        <select class="admin-input" id="tag" name="tag" required>
            <option value="single" @selected(old('tag', $groupCard->tag) === 'single')>Single</option>
            <option value="doubles" @selected(old('tag', $groupCard->tag) === 'doubles')>Doubles</option>
            <option value="mixed" @selected(old('tag', $groupCard->tag) === 'mixed')>Mixed</option>
            <option value="youth" @selected(old('tag', $groupCard->tag) === 'youth')>Youth</option>
        </select>
    </div>

    <div class="admin-form-group">
        <label class="admin-label" for="players_count">Players Count</label>
        <input class="admin-input" id="players_count" type="number" name="players_count" min="0" value="{{ old('players_count', $groupCard->players_count ?? 0) }}" required>
    </div>

    <div class="admin-form-group">
        <label class="admin-label" for="display_order">Display Order</label>
        <input class="admin-input" id="display_order" type="number" name="display_order" min="0" value="{{ old('display_order', $groupCard->display_order ?? 0) }}">
    </div>

    <div class="admin-form-group">
        <label class="admin-label" for="status">Status</label>
        <select class="admin-input" id="status" name="status" required>
            <option value="active" @selected(old('status', $groupCard->status) === 'active')>Active</option>
            <option value="deactive" @selected(old('status', $groupCard->status) === 'deactive')>Deactive</option>
        </select>
    </div>
</div>

<div class="admin-form-group">
    <span class="admin-label">Assign Groups</span>
    <p class="admin-field-hint">Select one or more groups for this group card.</p>
    <div class="admin-checkbox-grid">
        @forelse (($groups ?? []) as $group)
            <label class="admin-checkbox-inline">
                <input type="checkbox" name="group_ids[]" value="{{ $group->id }}" @checked(in_array($group->id, $selectedGroupIds, true))>
                <span>{{ $group->name }} <small class="admin-muted">({{ $group->players_count }} players · {{ ucfirst($group->status) }})</small></span>
            </label>
        @empty
            <p class="admin-muted">No groups yet. Add groups from the Groups section first.</p>
        @endforelse
    </div>
</div>
