<div class="admin-form-group">
    <label class="admin-label" for="name">Partner Name</label>
    <input class="admin-input" id="name" type="text" name="name" value="{{ old('name', $officialPartner->name) }}" required>
    <p class="admin-field-hint">Used for image alt text and admin reference.</p>
</div>

<div class="admin-form-group">
    <label class="admin-label" for="logo">Partner Logo</label>
    <input class="admin-input" id="logo" type="file" name="logo" accept="image/*" @if (! $officialPartner->exists) required @endif>
    @if ($officialPartner->logo_path)
        <div class="admin-current-logo" style="margin-top: 12px;">
            <img src="{{ asset($officialPartner->logo_path) }}" alt="{{ $officialPartner->name }}" style="max-width: 220px; max-height: 90px; object-fit: contain; border-radius: 10px; background: #fff; padding: 8px;">
            <span>Current logo</span>
        </div>
    @endif
</div>

<div class="admin-form-group">
    <label class="admin-label" for="website_url">Website URL (optional)</label>
    <input class="admin-input" id="website_url" type="url" name="website_url" value="{{ old('website_url', $officialPartner->website_url) }}" placeholder="https://example.com">
</div>

<div class="admin-form-grid">
    <div class="admin-form-group">
        <label class="admin-label" for="display_order">Display order</label>
        <input class="admin-input" id="display_order" type="number" name="display_order" min="0" step="1" value="{{ old('display_order', $officialPartner->display_order ?? 0) }}">
        <p class="admin-field-hint">Lower numbers appear first in the homepage marquee.</p>
    </div>

    <div class="admin-form-group">
        <label class="admin-label" for="is_active">Status</label>
        <label class="admin-checkbox-inline" style="margin-top: 8px;">
            <input type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', $officialPartner->is_active ?? true))>
            <span>Active (show on homepage)</span>
        </label>
    </div>
</div>
