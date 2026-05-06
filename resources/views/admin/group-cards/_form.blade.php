@csrf

<div class="admin-form-grid">
    <div class="admin-form-group">
        <label class="admin-label" for="name">Card Name</label>
        <input class="admin-input" id="name" type="text" name="name" value="{{ old('name', $groupCard->name) }}" required>
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
        <label class="admin-label" for="groups_count">Groups Count</label>
        <input class="admin-input" id="groups_count" type="number" name="groups_count" min="0" value="{{ old('groups_count', $groupCard->groups_count ?? 0) }}" required>
    </div>

    <div class="admin-form-group">
        <label class="admin-label" for="display_order">Display Order</label>
        <input class="admin-input" id="display_order" type="number" name="display_order" min="0" value="{{ old('display_order', $groupCard->display_order ?? 0) }}">
    </div>

    <div class="admin-form-group">
        <label class="admin-label" for="status">Card Status</label>
        <select class="admin-input" id="status" name="status" required>
            <option value="active" @selected(old('status', $groupCard->status) === 'active')>Active</option>
            <option value="deactive" @selected(old('status', $groupCard->status) === 'deactive')>Deactive</option>
        </select>
    </div>
</div>
