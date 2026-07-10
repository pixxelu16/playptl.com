<div
    id="profile-section-personal"
    class="overflow-hidden rounded-[12px] bg-white p-6 shadow-[0_1px_8px_rgba(0,0,0,0.06)] ring-1 ring-[#E0E0E0] sm:p-8"
>
    <h3 class="mb-6 text-[18px] font-bold leading-tight text-[#333333] sm:text-[20px]">Personal Information</h3>

    @if (! empty($playerTournamentGroups))
        <div class="mb-6 space-y-4">
            @if (! empty($currentTournamentGroups))
                <div class="space-y-3">
                    <h4 class="text-[13px] font-bold uppercase tracking-wide text-[#424242]">
                        Active Tournaments{{ count($currentTournamentGroups) > 1 ? ' ('.count($currentTournamentGroups).')' : '' }}
                    </h4>
                    @foreach ($currentTournamentGroups as $currentGroup)
                        <div class="rounded-lg border-2 border-[#66A157] bg-[#F3FAF1] px-4 py-4 sm:px-5">
                            <div class="mb-3 flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center rounded-full bg-[#66A157] px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wide text-white">Active</span>
                                <span class="text-[13px] font-medium text-[#5a9048]">{{ $currentGroup['window'] }}</span>
                            </div>
                            <h4 class="text-[17px] font-bold text-[#333333] sm:text-[18px]">{{ $currentGroup['tournament'] }}</h4>
                            <div class="mt-3 space-y-2">
                                @foreach ($currentGroup['registrations'] as $entry)
                                    <div class="rounded-md border border-[#C8E6C0] bg-white px-3 py-2.5 sm:px-4">
                                        <dl class="grid grid-cols-1 gap-2 text-[13px] sm:grid-cols-3 sm:gap-3">
                                            <div>
                                                <dt class="font-semibold text-[#666666]">Group</dt>
                                                <dd class="mt-0.5 font-medium text-[#333333]">{{ $entry['group'] }}</dd>
                                            </div>
                                            <div>
                                                <dt class="font-semibold text-[#666666]">Subgroup</dt>
                                                <dd class="mt-0.5 font-medium text-[#333333]">{{ $entry['subgroup'] }}</dd>
                                            </div>
                                            <div>
                                                <dt class="font-semibold text-[#666666]">Format</dt>
                                                <dd class="mt-0.5 font-medium text-[#333333]">{{ $entry['format'] }}</dd>
                                            </div>
                                        </dl>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            @php
                $otherTournamentGroups = collect($playerTournamentGroups)->filter(fn ($g) => ! ($g['is_current'] ?? false))->values();
            @endphp
            @if ($otherTournamentGroups->isNotEmpty())
                <div class="rounded-lg border border-[#E0E0E0] bg-[#F9FBF9] px-4 py-4 sm:px-5">
                    <h4 class="mb-3 text-[13px] font-bold uppercase tracking-wide text-[#424242]">
                        {{ ! empty($currentTournamentGroups) ? 'Other Tournaments' : 'My Tournaments' }}
                    </h4>
                    <div class="space-y-3">
                        @foreach ($otherTournamentGroups as $group)
                            <div class="rounded-md border border-[#E8ECE8] bg-white px-3 py-3 sm:px-4">
                                <div class="flex flex-wrap items-start justify-between gap-2">
                                    <p class="text-[15px] font-bold text-[#333333]">{{ $group['tournament'] }}</p>
                                    <span class="inline-flex shrink-0 items-center rounded-full bg-[#EEF2F0] px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-[#666666]">{{ $group['status_label'] }}</span>
                                </div>
                                <p class="mt-1 text-[13px] font-medium text-[#666666]">{{ $group['window'] }}</p>
                                @foreach ($group['registrations'] as $entry)
                                    <dl class="mt-2 grid grid-cols-1 gap-2 border-t border-[#F0F0F0] pt-2 text-[13px] sm:grid-cols-3 sm:gap-3 {{ ! $loop->first ? 'mt-3' : '' }}">
                                        <div>
                                            <dt class="font-semibold text-[#666666]">Group</dt>
                                            <dd class="mt-0.5 font-medium text-[#333333]">{{ $entry['group'] }}</dd>
                                        </div>
                                        <div>
                                            <dt class="font-semibold text-[#666666]">Subgroup</dt>
                                            <dd class="mt-0.5 font-medium text-[#333333]">{{ $entry['subgroup'] }}</dd>
                                        </div>
                                        <div>
                                            <dt class="font-semibold text-[#666666]">Format</dt>
                                            <dd class="mt-0.5 font-medium text-[#333333]">{{ $entry['format'] }}</dd>
                                        </div>
                                    </dl>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @else
        <div class="mb-6 rounded-lg border border-dashed border-[#D1D5DB] bg-[#FAFAFA] px-4 py-4 text-[14px] text-[#666666]">
            You are not registered in an active tournament yet.
            <a href="{{ route('player.profile.league') }}" class="font-semibold text-[#66A157] underline hover:opacity-90">Choose League</a>
            to join one.
        </div>
    @endif

    <form class="space-y-5" action="{{ route('player.profile.update') }}" method="post" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <input type="hidden" name="league_id" value="{{ $leagueId }}">
        <input type="hidden" name="group_card_id" value="{{ $groupCardId }}">
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div>
                <label for="mp-first" class="{{ $profileLabelClass }}">First Name</label>
                <input id="mp-first" name="first_name" type="text" value="{{ old('first_name', $myProfile['firstName']) }}" placeholder="Enter first name" class="{{ $profileInputClass }}" autocomplete="given-name" />
            </div>
            <div>
                <label for="mp-last" class="{{ $profileLabelClass }}">Last Name</label>
                <input id="mp-last" name="last_name" type="text" value="{{ old('last_name', $myProfile['lastName']) }}" placeholder="Enter last name" class="{{ $profileInputClass }}" autocomplete="family-name" />
            </div>
            <div>
                <label for="mp-dob" class="{{ $profileLabelClass }}">Date Of Birth</label>
                <input id="mp-dob" name="date_of_birth" type="date" value="{{ old('date_of_birth', $myProfile['dob']) }}" class="{{ $profileInputClass }}" />
            </div>
            <div>
                <label for="mp-ntrp" class="{{ $profileLabelClass }}">NTRP Rating</label>
                <div class="relative">
                    <select id="mp-ntrp" name="ntrp" class="{{ $profileInputClass }} appearance-none pr-10">
                        <option value="" @selected(old('ntrp', $myProfile['ntrp']) === '')>Select rating</option>
                        @foreach (['2.5', '3.0', '3.5', '4.0', '4.5', '5.0'] as $r)
                            <option value="{{ $r }}" @selected(old('ntrp', $myProfile['ntrp']) === $r)>{{ $r }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label for="mp-email" class="{{ $profileLabelClass }}">Email Address</label>
                <input id="mp-email" type="email" value="{{ $myProfile['email'] }}" class="{{ $profileInputReadonlyClass }} bg-[#EEF2F0] text-[#6B7280]" disabled />
            </div>
            <div>
                <label for="mp-phone" class="{{ $profileLabelClass }}">Phone Number</label>
                <input id="mp-phone" name="phone" type="tel" value="{{ old('phone', $myProfile['phone']) }}" placeholder="Enter phone number" class="{{ $profileInputClass }}" autocomplete="tel" />
            </div>
            <div>
                <label for="mp-city" class="{{ $profileLabelClass }}">City / Location</label>
                <input id="mp-city" name="city" type="text" value="{{ old('city', $myProfile['city']) }}" placeholder="Enter city" class="{{ $profileInputClass }}" />
            </div>
            <div>
                <label for="mp-court" class="{{ $profileLabelClass }}">Home Court</label>
                <input id="mp-court" name="home_court" type="text" value="{{ old('home_court', $myProfile['homeCourt']) }}" placeholder="Home court" class="{{ $profileInputClass }}" />
            </div>
        </div>
        <div>
            <label for="mp-hand" class="{{ $profileLabelClass }}">Dominant Hand</label>
            <div class="relative max-w-full sm:max-w-md">
                <select id="mp-hand" name="dominant_hand" class="{{ $profileInputClass }} appearance-none pr-10">
                    @foreach (['Right', 'Left', 'Ambidextrous'] as $h)
                        <option value="{{ $h }}" @selected(old('dominant_hand', $myProfile['dominantHand']) === $h)>{{ $h }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="sm:col-span-2">
            <label for="mp-avatar-personal" class="{{ $profileLabelClass }}">Profile photo</label>
            <input
                id="mp-avatar-personal"
                name="avatar"
                type="file"
                accept="image/jpeg,image/png,image/webp"
                class="block w-full cursor-pointer text-[15px] text-[#424242] file:mr-4 file:cursor-pointer file:rounded-lg file:border file:border-[#D1D5DB] file:bg-[#F3F4F6] file:px-4 file:py-2.5 file:text-[14px] file:font-semibold file:text-[#333333] hover:file:bg-[#E5E7EB] sm:text-[16px]"
            />
            <p class="mt-1.5 text-[12px] font-normal text-[#666666] sm:text-[13px]">JPG, PNG, or WebP up to 2MB.</p>
        </div>
        <div class="flex flex-wrap gap-3 pt-2">
            <a href="{{ route('player.my-profile') }}" class="rounded-lg border border-[#E0E0E0] bg-[#F3F4F6] px-6 py-2.5 text-[14px] font-semibold text-[#424242] transition hover:bg-[#E5E7EB] sm:text-[15px]">
                Cancel
            </a>
            <button type="submit" class="rounded-lg bg-[#66A157] px-6 py-2.5 text-[14px] font-semibold text-white shadow-sm transition hover:bg-[#5a9048] sm:text-[15px]">
                Save Change
            </button>
        </div>
    </form>
</div>

@push('profile_scripts')
    <script>
        (function () {
            document.querySelectorAll('[data-profile-jump-upload]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    document.getElementById('mp-avatar-personal')?.click();
                });
            });
            if (window.location.hash === '#mp-avatar-personal') {
                setTimeout(function () {
                    document.getElementById('mp-avatar-personal')?.focus();
                }, 100);
            }
        })();
    </script>
@endpush
