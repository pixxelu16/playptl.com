<div class="admin-form-group">
    <label class="admin-label" for="title">Title</label>
    <input class="admin-input" id="title" type="text" name="title" value="{{ old('title', $charityCause->title) }}" required>
</div>

<div class="admin-form-group">
    <label class="admin-label" for="description">Purpose / Description</label>
    <textarea class="admin-input admin-textarea" id="description" name="description" rows="6" required>{{ old('description', $charityCause->description) }}</textarea>
    <p class="admin-field-hint">Explain what this charity supports and why it matters.</p>
</div>

<div class="admin-form-group">
    <label class="admin-label" for="image">Charity Image</label>
    <input class="admin-input" id="image" type="file" name="image" accept="image/*" @if (! $charityCause->exists) required @endif>
    @if ($charityCause->image_path)
        <div class="admin-current-logo" style="margin-top: 12px;">
            <img src="{{ asset($charityCause->image_path) }}" alt="{{ $charityCause->title }}" style="max-width: 220px; border-radius: 10px;">
            <span>Current image</span>
        </div>
    @endif
</div>

<div class="admin-form-grid">
    <div class="admin-form-group">
        <label class="admin-label" for="display_order">Display order</label>
        <input class="admin-input" id="display_order" type="number" name="display_order" min="0" step="1" value="{{ old('display_order', $charityCause->display_order ?? 0) }}">
        <p class="admin-field-hint">Lower numbers appear first on the charity page.</p>
    </div>

    <div class="admin-form-group">
        <label class="admin-label" for="is_active">Status</label>
        <label class="admin-checkbox-inline" style="margin-top: 8px;">
            <input type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', $charityCause->is_active ?? true))>
            <span>Active (show on charity page)</span>
        </label>
    </div>
</div>
