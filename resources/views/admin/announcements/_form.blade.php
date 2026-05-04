@csrf
@php($today = now()->toDateString())

<div class="admin-form-grid">
    <div class="admin-form-group">
        <label class="admin-label" for="title">Announcement Title</label>
        <input class="admin-input" id="title" type="text" name="title" value="{{ old('title', $announcement->title) }}" required>
    </div>

    <div class="admin-form-group">
        <label class="admin-label" for="type">Announcement Type</label>
        <select class="admin-input" id="type" name="type" required>
            <option value="">Select Type</option>
            <option value="news" @selected(old('type', $announcement->type) === 'news')>News</option>
            <option value="notice" @selected(old('type', $announcement->type) === 'notice')>Notice</option>
            <option value="update" @selected(old('type', $announcement->type) === 'update')>Update</option>
            <option value="event" @selected(old('type', $announcement->type) === 'event')>Event</option>
        </select>
    </div>

    <div class="admin-form-group">
        <label class="admin-label" for="announcement_date">Announcement Date</label>
        <input class="admin-input" id="announcement_date" type="date" name="announcement_date" min="{{ $today }}" value="{{ old('announcement_date', optional($announcement->announcement_date)->format('Y-m-d')) }}" required>
    </div>

    <div class="admin-form-group">
        <label class="admin-label" for="is_active">Status</label>
        <select class="admin-input" id="is_active" name="is_active">
            <option value="1" @selected((string) old('is_active', (int) $announcement->is_active) === '1')>Active</option>
            <option value="0" @selected((string) old('is_active', (int) $announcement->is_active) === '0')>Inactive</option>
        </select>
    </div>
</div>

<div class="admin-form-group">
    <label class="admin-label" for="description">Announcement Description</label>
    <textarea class="admin-input admin-textarea" id="description" name="description" required>{{ old('description', $announcement->description) }}</textarea>
</div>

<label class="admin-checkbox">
    <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $announcement->is_featured))>
    <span>Show as featured announcement</span>
</label>
