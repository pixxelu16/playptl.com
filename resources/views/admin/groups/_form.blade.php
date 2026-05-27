@csrf

<div class="admin-form-grid">
    <div class="admin-form-group">
        <label class="admin-label" for="name">Subgroup Name</label>
        <input class="admin-input" id="name" type="text" name="name" value="{{ old('name', $group->name) }}" required>
    </div>

    <div class="admin-form-group">
        <label class="admin-label" for="status">Subgroup Status</label>
        <select class="admin-input" id="status" name="status" required>
            <option value="active" @selected(old('status', $group->status) === 'active')>Active</option>
            <option value="deactive" @selected(old('status', $group->status) === 'deactive')>Deactive</option>
        </select>
    </div>
</div>

<div class="admin-form-group">
    <label class="admin-label" for="description">Subgroup Description</label>
    <textarea class="admin-input admin-textarea" id="description" name="description">{{ old('description', $group->description) }}</textarea>
</div>
