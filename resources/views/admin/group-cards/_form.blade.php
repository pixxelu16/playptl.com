@csrf
@php
    $selectedGroupIds = old('group_ids', $groupCard->exists ? ($groupCard->groups?->pluck('id')->all() ?? []) : []);
    $selectedFormat = old('playoff_format', $groupCard->playoff_format ?? 'round_of_16');
    $spotDefaults = \App\Support\GroupPlayoffConfig::fromGroupCard(
        $groupCard->exists ? $groupCard : new \App\Models\GroupCard(['playoff_format' => $selectedFormat])
    );
    $quarterSpots = old('playoff_quarter_spots', $groupCard->playoff_quarter_spots ?? $spotDefaults->quarterSpots);
    $r16Spots = old('playoff_r16_spots', $groupCard->playoff_r16_spots ?? $spotDefaults->r16Spots);
    $ppqSpots = old('playoff_ppq_spots', $groupCard->playoff_ppq_spots ?? $spotDefaults->ppqSpots);
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

    <div class="admin-form-group" style="grid-column: 1 / -1;">
        <label class="admin-label" for="playoff_format">Playoff format</label>
        <select class="admin-input" id="playoff_format" name="playoff_format" required>
            @foreach (($playoffFormatOptions ?? []) as $opt)
                <option value="{{ $opt['value'] }}" @selected($selectedFormat === $opt['value'])>{{ $opt['label'] }}</option>
            @endforeach
        </select>
    </div>

    <div id="playoff-spots-quarter" class="admin-form-group playoff-spots-field" style="display:none;">
        <label class="admin-label" for="playoff_quarter_spots" id="playoff_quarter_spots_label">Players → Quarter (direct)</label>
        <input class="admin-input" id="playoff_quarter_spots" type="number" name="playoff_quarter_spots" min="2" max="64" value="{{ $quarterSpots }}">
        <p class="admin-field-hint" id="playoff_quarter_spots_hint"></p>
    </div>

    <div id="playoff-spots-r16" class="admin-form-group playoff-spots-field">
        <label class="admin-label" for="playoff_r16_spots" id="playoff_r16_spots_label">Players → Round of 16</label>
        <input class="admin-input" id="playoff_r16_spots" type="number" name="playoff_r16_spots" min="2" max="64" value="{{ $r16Spots }}">
        <p class="admin-field-hint" id="playoff_r16_spots_hint"></p>
    </div>

    <div id="playoff-spots-ppq" class="admin-form-group playoff-spots-field" style="display:none;">
        <label class="admin-label" for="playoff_ppq_spots">Players → Pre-Pre-Q</label>
        <input class="admin-input" id="playoff_ppq_spots" type="number" name="playoff_ppq_spots" min="2" max="64" value="{{ $ppqSpots }}">
        <p class="admin-field-hint">Must be even (pairs). Winners can feed into Round of 16 away slots.</p>
    </div>

    <div class="admin-form-group" style="grid-column: 1 / -1;">
        <div class="admin-alert" style="background:#f4faf4;border-color:#c5dfc6;color:#2f4a2d;">
            <strong>Automatic assignment by rank</strong>
            <p id="playoff-format-summary" style="margin:0.5rem 0 0;line-height:1.55;font-size:0.9rem;"></p>
        </div>
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
    <span class="admin-label">Assign Subgroups</span>
    <p class="admin-field-hint">Select one or more subgroups for this group.</p>
    <div class="admin-checkbox-grid">
        @forelse (($groups ?? []) as $group)
            <label class="admin-checkbox-inline">
                <input type="checkbox" name="group_ids[]" value="{{ $group->id }}" @checked(in_array($group->id, $selectedGroupIds, true))>
                <span>{{ $group->name }} <small class="admin-muted">({{ ucfirst($group->status) }})</small></span>
            </label>
        @empty
            <p class="admin-muted">No subgroups yet. Add subgroups from the Subgroups section first.</p>
        @endforelse
    </div>
</div>

<script>
    (function () {
        var formatSelect = document.getElementById('playoff_format');
        var summary = document.getElementById('playoff-format-summary');
        var quarterWrap = document.getElementById('playoff-spots-quarter');
        var r16Wrap = document.getElementById('playoff-spots-r16');
        var ppqWrap = document.getElementById('playoff-spots-ppq');
        var quarterInput = document.getElementById('playoff_quarter_spots');
        var r16Input = document.getElementById('playoff_r16_spots');
        var ppqInput = document.getElementById('playoff_ppq_spots');
        var r16Label = document.getElementById('playoff_r16_spots_label');
        var r16Hint = document.getElementById('playoff_r16_spots_hint');

        if (!formatSelect || !summary) return;

        var defaults = {
            top4_quarter_rest_r16: { quarter: 4, r16: 8, ppq: 0 },
            pre_pre_q_r16: { quarter: 0, r16: 8, ppq: 16 },
            round_of_16: { quarter: 0, r16: 16, ppq: 0 },
            direct_quarter: { quarter: 8, r16: 0, ppq: 0 },
        };

        function intVal(el, fallback) {
            var n = parseInt(el && el.value ? el.value : String(fallback), 10);
            return isNaN(n) ? fallback : n;
        }

        function buildSummary(format, q, r16, ppq) {
            var lines = [];
            var rank = 1;
            if ((format === 'top4_quarter_rest_r16' || format === 'direct_quarter') && q > 0) {
                lines.push('Rank ' + rank + '–' + q + ': ' + q + ' players → Quarter (direct)');
                rank = q + 1;
            }
            if (r16 > 0) {
                var end = rank + r16 - 1;
                var r16Label = format === 'pre_pre_q_r16' ? 'Round of 16 (direct home)' : 'Round of 16';
                lines.push('Rank ' + rank + '–' + end + ': ' + r16 + ' players → ' + r16Label);
                rank = end + 1;
            }
            if (ppq > 0) {
                var endP = rank + ppq - 1;
                var wins = Math.floor(ppq / 2);
                lines.push('Rank ' + rank + '–' + endP + ': ' + ppq + ' players → Pre-Pre-Q (' + wins + ' matches)');
                rank = endP + 1;
            }
            lines.push('Rank ' + rank + '+: eliminated');
            return lines.join('<br>');
        }

        var quarterHint = document.getElementById('playoff_quarter_spots_hint');
        var lastFormat = formatSelect.value;

        function setSpotFieldState(input, wrap, active) {
            if (!input) return;
            if (active) {
                input.disabled = false;
                wrap.style.display = '';
            } else {
                input.disabled = true;
                input.removeAttribute('min');
                input.value = '';
                wrap.style.display = 'none';
            }
        }

        function applySpotDefaults(d, force) {
            if (!force) return;
            if (!quarterInput.disabled) quarterInput.value = d.quarter > 0 ? String(d.quarter) : '';
            if (!r16Input.disabled) r16Input.value = d.r16 > 0 ? String(d.r16) : '';
            if (!ppqInput.disabled) ppqInput.value = d.ppq > 0 ? String(d.ppq) : '';
        }

        function applyFormatUi(forceDefaults) {
            var format = formatSelect.value;
            var d = defaults[format] || defaults.round_of_16;

            var isDirectQuarter = format === 'direct_quarter';
            var isRoundOf16Only = format === 'round_of_16';
            var showQuarter = format === 'top4_quarter_rest_r16' || isDirectQuarter;
            var showR16 = !isDirectQuarter;
            var showPpq = format === 'pre_pre_q_r16';

            setSpotFieldState(quarterInput, quarterWrap, showQuarter);
            setSpotFieldState(r16Input, r16Wrap, showR16);
            setSpotFieldState(ppqInput, ppqWrap, showPpq);

            if (showQuarter) {
                if (isDirectQuarter) {
                    document.getElementById('playoff_quarter_spots_label').textContent = 'Players → Quarter (direct)';
                    quarterHint.textContent = 'Standard: 8 players (4 quarterfinal matches). No Round of 16, no Pre-Pre-Q.';
                    quarterInput.min = '2';
                } else if (format === 'top4_quarter_rest_r16') {
                    document.getElementById('playoff_quarter_spots_label').textContent = 'Players → Quarter (direct)';
                    quarterHint.textContent = 'Usually 4 players (top 4 seeds). Next ranks play Round of 16 below.';
                    quarterInput.min = '1';
                }
            }

            if (showR16) {
                if (isRoundOf16Only) {
                    r16Label.textContent = 'Players → Round of 16';
                    r16Hint.textContent = 'Standard: 16 players (8 Round of 16 matches). Everyone else eliminated.';
                    r16Input.min = '2';
                } else if (format === 'pre_pre_q_r16') {
                    r16Label.textContent = 'Players → Round of 16 (direct seeds)';
                    r16Hint.textContent = 'Standard: 8 direct home seeds. Pre-Pre-Q winners fill away slots (usually 8).';
                    r16Input.min = '2';
                } else {
                    r16Label.textContent = 'Players → Round of 16';
                    r16Hint.textContent = 'Players after quarter seeds (standard: 8 = ranks 5–12).';
                    r16Input.min = '2';
                }
            }

            if (showPpq) {
                ppqInput.min = '2';
            }

            if (forceDefaults) {
                applySpotDefaults(d, true);
            }

            var q = (format === 'top4_quarter_rest_r16' || isDirectQuarter) ? intVal(quarterInput, d.quarter) : 0;
            var r16 = isDirectQuarter ? 0 : intVal(r16Input, d.r16);
            var ppq = format === 'pre_pre_q_r16' ? intVal(ppqInput, d.ppq) : 0;

            summary.innerHTML = buildSummary(format, q, r16, ppq);
        }

        formatSelect.addEventListener('change', function () {
            if (formatSelect.value !== lastFormat) {
                lastFormat = formatSelect.value;
                applyFormatUi(true);
            } else {
                applyFormatUi(false);
            }
        });
        [quarterInput, r16Input, ppqInput].forEach(function (el) {
            if (el) el.addEventListener('input', function () { applyFormatUi(false); });
        });
        applyFormatUi(false);
    })();
</script>
