<div class="admin-form-group">
    <label class="admin-label" for="age_group_key">Age Group</label>
    <select class="admin-input" id="age_group_key" name="age_group_key" @disabled(empty($canEditAgeGroup))>
        <option value="" @selected(($currentAgeGroupKey ?? '') === '')>—</option>
        @foreach ($ageBrackets as $key => $label)
            <option value="{{ $key }}" @selected(($currentAgeGroupKey ?? '') === $key)>{{ $label }}</option>
        @endforeach
    </select>
    @if (empty($canEditAgeGroup))
        <p class="admin-card-text" style="margin-top: 8px; font-size: 13px; opacity: .8;">
            Age group can be edited after the player is registered in a tournament.
        </p>
    @endif
</div>
