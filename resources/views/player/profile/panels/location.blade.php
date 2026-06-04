<div
    id="profile-section-location"
    class="overflow-hidden rounded-[10px] bg-white p-6 shadow-[0_4px_12px_rgba(0,0,0,0.05)] ring-1 ring-[#E0E0E0] sm:p-8 sm:px-[28px] sm:py-[26px]"
>
    <h3 class="mb-2 text-[18px] font-bold leading-tight text-[#000000] sm:text-[20px]">My Matches</h3>
    @if (! empty($profileLeagueName))
        <p class="mb-6 text-[13px] text-[#666666] sm:text-[14px]">
            <strong>{{ $profileLeagueName }}</strong>
            @if (! empty($profileDivisionName))
                · {{ $profileDivisionName }}
            @endif
        </p>
    @else
        <p class="mb-6 text-[13px] text-[#666666] sm:text-[14px]">Register for a league to see your scheduled matches.</p>
    @endif

    @if (session('status'))
        <div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-[14px] font-semibold text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    @php
        $leagueDays = $playerLeagueScheduleDays ?? ($playerScheduleDays ?? []);
        $playoffDays = $playerPlayoffScheduleDays ?? [];
        $hasLeague = $leagueDays !== [];
        $hasPlayoff = $playoffDays !== [];
    @endphp

    @if ($hasLeague)
        <div class="player-matches-section">
            <div class="player-matches-section__head">
                <h4 class="player-matches-section__title">Group matches</h4>
                <p class="player-matches-section__hint">Group-stage schedule and scores</p>
            </div>
            @include('partials.match-schedule-cards', [
                'scheduleDays' => $leagueDays,
                'highlightUserId' => auth()->id(),
                'useVenueModal' => true,
                'showPlayerMatchActions' => true,
                'compactCards' => true,
                'weeksInColumns' => true,
                'emptyMessage' => '',
            ])
        </div>
    @endif

    @if ($hasPlayoff)
        <div class="player-matches-section {{ $hasLeague ? 'mt-8' : '' }}">
            <div class="player-matches-section__head">
                <h4 class="player-matches-section__title">Playoff matches</h4>
                <p class="player-matches-section__hint">Knockout bracket — upload photos, set venue &amp; time, and enter scores</p>
            </div>
            @include('partials.match-schedule-cards', [
                'scheduleDays' => $playoffDays,
                'highlightUserId' => auth()->id(),
                'useVenueModal' => true,
                'showPlayerMatchActions' => true,
                'compactCards' => true,
                'weeksInColumns' => true,
                'emptyMessage' => '',
            ])
        </div>
    @endif

    @if (! $hasLeague && ! $hasPlayoff)
        <p class="rounded-[10px] bg-[#F9FAFB] px-4 py-8 text-center text-[14px] font-medium text-[#757575] ring-1 ring-[#E8E8E8] sm:text-[15px]">
            No matches scheduled for you in this league yet. They will appear here automatically after the admin sets the schedule.
        </p>
    @endif

    @if ($errors->any())
        <div class="mb-5 mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-[14px] font-semibold text-red-700">
            {{ $errors->first() }}
        </div>
    @endif
</div>

<div
    id="player-venue-modal"
    class="fixed inset-0 z-[200] hidden items-center justify-center bg-black/40 p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="player-venue-modal-title"
>
    <div class="w-full max-w-[360px] rounded-[10px] bg-white p-5 shadow-xl ring-1 ring-black/10">
        <div class="mb-4 flex items-start justify-between gap-3">
            <div>
                <h4 id="player-venue-modal-title" class="text-[16px] font-bold text-[#212121]">Update venue &amp; time</h4>
                <p id="player-venue-modal-match" class="mt-0.5 text-[12px] text-[#757575]"></p>
            </div>
            <button
                type="button"
                class="js-close-venue-modal rounded-lg p-1 text-[#757575] hover:bg-[#F3F4F6]"
                aria-label="Close"
            >
                <span class="text-[20px] leading-none">&times;</span>
            </button>
        </div>

        <form method="post" action="{{ route('player.profile.location.update') }}" id="player-venue-modal-form" class="space-y-4">
            @csrf
            <input type="hidden" name="match" id="player-venue-modal-match-id" value="">
            <input type="hidden" name="match_kind" id="player-venue-modal-match-kind" value="group">

            <p id="player-venue-modal-date-hint" class="hidden text-[11px] leading-snug text-[#757575]"></p>

            <div>
                <label for="player-venue-modal-date" class="{{ $scheduleLabelClass }}">
                    Date <span class="font-bold text-red-600">*</span>
                </label>
                <input
                    id="player-venue-modal-date"
                    name="schedule_date"
                    type="date"
                    required
                    class="{{ $scheduleInputClass }}"
                />
            </div>
            <div>
                <label for="player-venue-modal-time" class="{{ $scheduleLabelClass }}">
                    Time <span class="font-bold text-red-600">*</span>
                </label>
                <input
                    id="player-venue-modal-time"
                    name="schedule_time"
                    type="time"
                    step="60"
                    required
                    class="{{ $scheduleInputClass }}"
                />
            </div>
            <div>
                <label for="player-venue-modal-venue" class="{{ $scheduleLabelClass }}">Venue / Location</label>
                <input
                    id="player-venue-modal-venue"
                    name="schedule_venue"
                    type="text"
                    placeholder="Club or city (e.g. Kangra)"
                    class="{{ $scheduleInputClass }}"
                    autocomplete="off"
                />
            </div>
            <div>
                <label for="player-venue-modal-court" class="{{ $scheduleLabelClass }}">Court</label>
                <input
                    id="player-venue-modal-court"
                    name="schedule_court"
                    type="text"
                    placeholder="Court 1"
                    maxlength="64"
                    class="{{ $scheduleInputClass }}"
                    autocomplete="off"
                />
            </div>

            <div class="flex flex-wrap justify-end gap-2 pt-1">
                <button
                    type="button"
                    class="js-close-venue-modal rounded-lg border border-[#DDDDDD] bg-[#F3F4F6] px-4 py-2 text-[13px] font-semibold text-[#333333] hover:bg-[#E8EAED]"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    class="rounded-lg bg-[#66A157] px-4 py-2 text-[13px] font-semibold text-white shadow-sm hover:bg-[#5a9048]"
                >
                    Save
                </button>
            </div>
        </form>
    </div>
</div>

@push('profile_scripts')
    <script src="{{ asset('admin/js/match-result-fields.js') }}" defer></script>
    <script>
        (function () {
            var modal = document.getElementById('player-venue-modal');
            if (!modal) return;

            var matchIdInput = document.getElementById('player-venue-modal-match-id');
            var matchKindInput = document.getElementById('player-venue-modal-match-kind');
            var matchLabel = document.getElementById('player-venue-modal-match');
            var dateHint = document.getElementById('player-venue-modal-date-hint');
            var dateInput = document.getElementById('player-venue-modal-date');
            var timeInput = document.getElementById('player-venue-modal-time');
            var venueInput = document.getElementById('player-venue-modal-venue');
            var courtInput = document.getElementById('player-venue-modal-court');
            function openModal(btn) {
                matchIdInput.value = btn.getAttribute('data-match-id') || '';
                if (matchKindInput) {
                    matchKindInput.value = btn.getAttribute('data-match-kind') || 'group';
                }
                matchLabel.textContent = btn.getAttribute('data-match-label') || '';
                dateInput.value = btn.getAttribute('data-date') || '';
                timeInput.value = btn.getAttribute('data-time') || '';
                venueInput.value = btn.getAttribute('data-venue') || '';
                courtInput.value = btn.getAttribute('data-court') || '';
                var minD = btn.getAttribute('data-date-min') || '';
                var maxD = btn.getAttribute('data-date-max') || '';
                var hint = btn.getAttribute('data-date-hint') || '';
                dateInput.min = minD;
                dateInput.max = maxD;
                dateInput.title = hint;
                if (dateHint) {
                    if (hint) {
                        dateHint.textContent = 'Allowed dates: ' + hint + '.';
                        dateHint.classList.remove('hidden');
                    } else {
                        dateHint.textContent = '';
                        dateHint.classList.add('hidden');
                    }
                }
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.style.overflow = 'hidden';
            }

            function closeModal() {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.style.overflow = '';
            }

            document.querySelectorAll('.js-open-venue-modal').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    openModal(btn);
                });
            });

            modal.querySelectorAll('.js-close-venue-modal').forEach(function (btn) {
                btn.addEventListener('click', closeModal);
            });

            modal.addEventListener('click', function (e) {
                if (e.target === modal) closeModal();
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
            });
        })();
    </script>
@endpush

@push('styles')
    @include('partials.match-scoreboard-styles')
    <style>
        .player-matches-section__head {
            margin-bottom: 0.75rem;
        }
        .player-matches-section__title {
            margin: 0;
            font-size: 14px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #2f7a2a;
        }
        .player-matches-section__hint {
            margin: 0.2rem 0 0;
            font-size: 12px;
            color: #757575;
        }
        .player-match-result {
            margin-top: 0.35rem;
            padding: 0.45rem 0.5rem;
            border: 1px solid #e3ebe4;
            border-radius: 8px;
            background: #f9fbf9;
        }
        .player-match-result__head {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.35rem 0.5rem;
            margin-bottom: 0.35rem;
        }
        .player-match-result__title {
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #2f7a2a;
        }
        .player-match-result__type {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem 0.65rem;
        }
        .player-match-result__radio {
            display: inline-flex;
            align-items: center;
            gap: 0.2rem;
            font-size: 10px;
            font-weight: 600;
            color: #333;
        }
        .player-match-result__walkover {
            margin-bottom: 0.35rem;
        }
        .player-match-result__select {
            width: 100%;
            border: 1px solid #d7ead9;
            border-radius: 6px;
            padding: 0.25rem 0.4rem;
            font-size: 11px;
        }
        .match-score-entry--player {
            margin-top: 0;
            padding: 0.3rem 0.35rem;
        }
        .match-score-entry--player .match-score-entry__input {
            width: 2rem;
            height: 1.5rem;
            padding: 0.1rem;
            border: 1px solid #d7ead9;
            border-radius: 5px;
            text-align: center;
            font-size: 12px;
            font-weight: 700;
            background: #fff;
        }
        .match-score-entry--player .match-score-entry__input:focus {
            outline: 2px solid rgba(102, 161, 87, 0.35);
            border-color: #66a157;
        }
        .player-match-save-score {
            margin-top: 0.4rem;
            width: 100%;
            border-radius: 6px;
            padding: 0.35rem 0.5rem;
            font-size: 11px;
            font-weight: 700;
        }
    </style>
@endpush
