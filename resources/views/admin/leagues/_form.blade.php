@csrf
@php
    $today = now()->toDateString();
    $selectedGroupCardIds = old('group_card_ids', $league->exists ? $league->groupCards->pluck('id')->all() : []);
    $singlesEntryFee = old('singles_entry_fee', \App\Support\LeagueEntryFee::dollarsInputValue($league->exists ? $league : null, 'singles'));
    $doublesEntryFee = old('doubles_entry_fee', \App\Support\LeagueEntryFee::dollarsInputValue($league->exists ? $league : null, 'doubles'));
    $entryFeePlayerType = old('entry_fee_player_type', 'singles');
    if (! in_array($entryFeePlayerType, ['singles', 'doubles'], true)) {
        $entryFeePlayerType = 'singles';
    }
    $visibleEntryFee = $entryFeePlayerType === 'doubles' ? $doublesEntryFee : $singlesEntryFee;
@endphp

<div class="admin-form-grid">
    <div class="admin-form-group" style="grid-column: 1 / -1;">
        <label class="admin-label" for="name">League Name</label>
        <input class="admin-input" id="name" type="text" name="name" value="{{ old('name', $league->name) }}" required>
    </div>

    <div class="admin-form-group">
        <label class="admin-label" for="start_date">League Start Date</label>
        <input class="admin-input" id="start_date" type="date" name="start_date" @if (! $league->exists) min="{{ $today }}" @endif value="{{ old('start_date', optional($league->start_date)->format('Y-m-d')) }}">
    </div>

    <div class="admin-form-group" id="league-entry-fee-block">
        <label class="admin-label" for="entry_fee_player_type">Player type</label>
        <select class="admin-input" id="entry_fee_player_type" name="entry_fee_player_type">
            <option value="singles" @selected($entryFeePlayerType === 'singles')>Singles</option>
            <option value="doubles" @selected($entryFeePlayerType === 'doubles')>Doubles</option>
        </select>

        <label class="admin-label" for="entry_fee_visible" style="margin-top: 0.75rem; display: block;">Entry fee (USD)</label>
        <input
            class="admin-input"
            id="entry_fee_visible"
            type="number"
            min="0"
            step="0.01"
            inputmode="decimal"
            value="{{ $visibleEntryFee }}"
            required
        >

        <input type="hidden" name="singles_entry_fee" id="singles_entry_fee" value="{{ $singlesEntryFee }}">
        <input type="hidden" name="doubles_entry_fee" id="doubles_entry_fee" value="{{ $doublesEntryFee }}">
    </div>
</div>

@push('scripts')
    <script>
        (function () {
            var typeSelect = document.getElementById('entry_fee_player_type');
            var visibleInput = document.getElementById('entry_fee_visible');
            var singlesHidden = document.getElementById('singles_entry_fee');
            var doublesHidden = document.getElementById('doubles_entry_fee');
            if (!typeSelect || !visibleInput || !singlesHidden || !doublesHidden) return;

            function activeType() {
                return typeSelect.value === 'doubles' ? 'doubles' : 'singles';
            }

            function hiddenFor(type) {
                return type === 'doubles' ? doublesHidden : singlesHidden;
            }

            function syncVisibleToHidden() {
                hiddenFor(activeType()).value = visibleInput.value;
            }

            function switchType() {
                var next = activeType();
                var prev = next === 'doubles' ? 'singles' : 'doubles';
                hiddenFor(prev).value = visibleInput.value;
                visibleInput.value = hiddenFor(next).value;
            }

            typeSelect.addEventListener('change', switchType);
            visibleInput.addEventListener('input', syncVisibleToHidden);

            var form = typeSelect.closest('form');
            if (form) {
                form.addEventListener('submit', syncVisibleToHidden);
            }
        })();
    </script>
@endpush

<div class="admin-form-group">
    <label class="admin-label" for="logo">League Logo</label>
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
    <p class="admin-field-hint">Select one or more groups for this league listing section.</p>
    <div class="admin-checkbox-grid">
        @forelse ($groupCards ?? [] as $groupCard)
            <label class="admin-checkbox-inline">
                <input type="checkbox" name="group_card_ids[]" value="{{ $groupCard->id }}" @checked(in_array($groupCard->id, $selectedGroupCardIds, true))>
                <span>
                    {{ $groupCard->name }}
                </span>
            </label>
        @empty
            <p class="admin-muted">No groups yet. Add them from the Groups section first.</p>
        @endforelse
    </div>
</div>

<div class="admin-form-group">
    <label class="admin-label" for="description">League Description</label>
    <textarea class="admin-input admin-textarea" id="description" name="description">{{ old('description', $league->description) }}</textarea>
</div>

<div class="admin-form-group">
    <label class="admin-label" for="stats">League Stats</label>
    <select class="admin-input" id="stats" name="stats">
        <option value="">Select Stats</option>
        <option value="active" @selected(old('stats', $league->stats) === 'active')>Active</option>
        <option value="deactive" @selected(old('stats', $league->stats) === 'deactive')>Deactive</option>
        <option value="upcoming" @selected(old('stats', $league->stats) === 'upcoming')>Upcoming</option>
        <option value="completed" @selected(old('stats', $league->stats) === 'completed')>Completed</option>
    </select>
</div>
