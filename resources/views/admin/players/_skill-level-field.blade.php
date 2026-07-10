@php
    $registrationSkillLevelValues = \App\Support\AdminPlayerLeagueRegistrationService::skillLevelValues();
    $currentSkillLevel = old('skill_level', $currentSkillLevel ?? null);
@endphp

<div class="admin-form-group">
    <label class="admin-label" for="skill_level">Skill Level</label>
    <select class="admin-input" id="skill_level" name="skill_level">
        <option value="" @selected($currentSkillLevel === null || $currentSkillLevel === '')>—</option>
        @foreach ($registrationSkillLevelValues as $skillValue)
            <option value="{{ $skillValue }}" @selected($currentSkillLevel == $skillValue)>{{ $skillValue === 'not-sure' ? 'Not Sure' : $skillValue }}</option>
        @endforeach
    </select>
</div>
