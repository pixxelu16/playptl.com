<div class="admin-form-group">
    <label class="admin-label" for="value">Skill Level Value</label>
    <input class="admin-input" id="value" type="text" name="value" value="{{ old('value', $skill->value) }}" placeholder="e.g. 3.5, 4.0, not-sure" required>
    <p class="admin-field-hint">The identifier of this skill level. Use numbers (e.g. 3.5) or <code>not-sure</code>.</p>
</div>

<div class="admin-form-group">
    <label class="admin-label" for="display_order">Display Order</label>
    <input class="admin-input" id="display_order" type="number" name="display_order" min="0" step="1" value="{{ old('display_order', $skill->display_order ?? 0) }}" required>
    <p class="admin-field-hint">Lower numbers appear first in lists and options dropdowns.</p>
</div>
