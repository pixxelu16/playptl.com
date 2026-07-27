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
                                    <div class="rounded-md border border-[#C8E6C0] bg-white px-3 py-3 sm:px-4">
                                        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-[#E8ECE8] pb-2.5 mb-3">
                                            <span class="text-[13px] font-bold text-[#333333] uppercase tracking-wide">{{ $entry['format'] }} — {{ $entry['category'] }}</span>
                                            <button type="button"
                                                    onclick='openRegistrationDetailsModal(@json($entry), @json($currentGroup["tournament"]))'
                                                    class="inline-flex items-center rounded-md bg-[#66A157] px-3 py-1 text-[12px] font-bold text-white shadow-sm transition hover:bg-[#549648]">
                                                View Details
                                            </button>
                                        </div>
                                        <dl class="grid grid-cols-2 gap-2 text-[13px] sm:grid-cols-4 sm:gap-3">
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
                                            <div>
                                                <dt class="font-semibold text-[#666666]">Category</dt>
                                                <dd class="mt-0.5 font-medium text-[#333333]">{{ $entry['category'] ?? '—' }}</dd>
                                            </div>
                                            <div>
                                                <dt class="font-semibold text-[#666666]">Skill Level</dt>
                                                <dd class="mt-0.5 font-medium text-[#333333]">{{ $entry['skill_level'] ?? '—' }}</dd>
                                            </div>
                                            @if(!empty($entry['is_doubles']))
                                                <div>
                                                    <dt class="font-semibold text-[#666666] flex items-center gap-1.5">
                                                        Team Name
                                                        <button type="button" 
                                                                onclick="openTeamNameModal('{{ $entry['id'] }}', '{{ addslashes($entry['team_name'] ?? '') }}')"
                                                                class="inline-flex items-center text-[#66A157] hover:text-[#4d7d40] text-[11px] font-bold underline"
                                                                title="Edit Team Name">
                                                            (Edit)
                                                        </button>
                                                    </dt>
                                                    <dd class="mt-0.5 font-medium text-[#333333]">{{ $entry['team_name'] ?? '—' }}</dd>
                                                </div>
                                            @elseif(!empty($entry['team_name']))
                                                <div>
                                                    <dt class="font-semibold text-[#666666]">Team Name</dt>
                                                    <dd class="mt-0.5 font-medium text-[#333333]">{{ $entry['team_name'] }}</dd>
                                                </div>
                                            @endif
                                            @if(!empty($entry['partner_name']))
                                                <div>
                                                    <dt class="font-semibold text-[#666666]">Partner</dt>
                                                    <dd class="mt-0.5 font-medium text-[#333333]">{{ $entry['partner_name'] }}</dd>
                                                </div>
                                            @endif
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
                                    <div class="mt-3 rounded-md border border-[#E0E0E0] bg-white px-3 py-3 sm:px-4">
                                        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-[#F0F0F0] pb-2.5 mb-3">
                                            <span class="text-[13px] font-bold text-[#333333] uppercase tracking-wide">{{ $entry['format'] }} — {{ $entry['category'] }}</span>
                                            <button type="button"
                                                    onclick='openRegistrationDetailsModal(@json($entry), @json($group["tournament"]))'
                                                    class="inline-flex items-center rounded-md bg-[#66A157] px-3 py-1 text-[12px] font-bold text-white shadow-sm transition hover:bg-[#549648]">
                                                View Details
                                            </button>
                                        </div>
                                        <dl class="grid grid-cols-2 gap-2 text-[13px] sm:grid-cols-4 sm:gap-3">
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
                                            <div>
                                                <dt class="font-semibold text-[#666666]">Category</dt>
                                                <dd class="mt-0.5 font-medium text-[#333333]">{{ $entry['category'] ?? '—' }}</dd>
                                            </div>
                                            <div>
                                                <dt class="font-semibold text-[#666666]">Skill Level</dt>
                                                <dd class="mt-0.5 font-medium text-[#333333]">{{ $entry['skill_level'] ?? '—' }}</dd>
                                            </div>
                                            @if(!empty($entry['is_doubles']))
                                                <div>
                                                    <dt class="font-semibold text-[#666666] flex items-center gap-1.5">
                                                        Team Name
                                                        <button type="button" 
                                                                onclick="openTeamNameModal('{{ $entry['id'] }}', '{{ addslashes($entry['team_name'] ?? '') }}')"
                                                                class="inline-flex items-center text-[#66A157] hover:text-[#4d7d40] text-[11px] font-bold underline"
                                                                title="Edit Team Name">
                                                            (Edit)
                                                        </button>
                                                    </dt>
                                                    <dd class="mt-0.5 font-medium text-[#333333]">{{ $entry['team_name'] ?? '—' }}</dd>
                                                </div>
                                            @elseif(!empty($entry['team_name']))
                                                <div>
                                                    <dt class="font-semibold text-[#666666]">Team Name</dt>
                                                    <dd class="mt-0.5 font-medium text-[#333333]">{{ $entry['team_name'] }}</dd>
                                                </div>
                                            @endif
                                            @if(!empty($entry['partner_name']))
                                                <div>
                                                    <dt class="font-semibold text-[#666666]">Partner</dt>
                                                    <dd class="mt-0.5 font-medium text-[#333333]">{{ $entry['partner_name'] }}</dd>
                                                </div>
                                            @endif
                                        </dl>
                                    </div>
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

    {{-- Become a Student / Become a Mentor buttons --}}
    @if(!auth()->user()->hasRole('Student') || !auth()->user()->hasRole('Mentor'))
        <div class="mb-6 rounded-[12px] bg-gradient-to-r from-[#E8F5E9] to-[#C8E6C9] p-5 border border-[#A5D6A7] shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
            <div>
                <h4 class="text-[15px] font-bold text-[#1b5e20]">Expand Your Tennis Journey</h4>
                <p class="text-[12px] text-[#2e7d32] mt-0.5">Learn from pro coaches or share your experience as a mentor in the community.</p>
            </div>
            <div class="flex gap-2 w-full sm:w-auto">
                @if(!auth()->user()->hasRole('Student'))
                    <form method="POST" action="{{ route('player.become-student') }}" class="flex-1 sm:flex-initial">
                        @csrf
                        <button type="submit" class="w-full rounded-lg bg-[#2E7D32] hover:bg-[#1B5E20] px-4 py-2 text-xs font-bold text-white transition-colors shadow-sm">
                            Become a Student
                        </button>
                    </form>
                @endif
                @if(!auth()->user()->hasRole('Mentor'))
                    @if(auth()->user()->mentor_status === 'pending')
                        <button type="button" disabled class="w-full rounded-lg bg-gray-400 cursor-not-allowed px-4 py-2 text-xs font-bold text-white shadow-sm flex-1 sm:flex-initial">
                            Mentor Request Pending Approval
                        </button>
                    @else
                        <form method="POST" action="{{ route('player.become-mentor') }}" class="flex-1 sm:flex-initial">
                            @csrf
                            <button type="submit" class="w-full rounded-lg bg-[#1565C0] hover:bg-[#0D47A1] px-4 py-2 text-xs font-bold text-white transition-colors shadow-sm">
                                Become a Mentor
                            </button>
                        </form>
                    @endif
                @endif
            </div>
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

<!-- Edit Team Name Modal -->
<div id="edit-team-name-modal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-md rounded-[12px] bg-white p-6 shadow-xl">
        <div class="flex items-center justify-between border-b border-[#EEEEEE] pb-3">
            <h4 class="text-[16px] font-bold text-[#333333]">Update Team Name</h4>
            <button type="button" onclick="closeTeamNameModal()" class="text-[#888888] hover:text-[#333333] text-xl font-bold">&times;</button>
        </div>
        <form action="{{ route('player.profile.team-name.update') }}" method="POST" class="mt-4 space-y-4">
            @csrf
            <input type="hidden" name="registration_id" id="modal-team-name-registration-id" value="">
            <div>
                <label for="modal-team-name-input" class="{{ $profileLabelClass }}">Team Name <span class="text-red-600">*</span></label>
                <input type="text" 
                       id="modal-team-name-input" 
                       name="team_name" 
                       required 
                       placeholder="Enter new team name" 
                       class="{{ $profileInputClass }}">
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeTeamNameModal()" class="rounded-lg border border-[#DDDDDD] bg-white px-4 py-2 text-[14px] font-semibold text-[#444444] hover:bg-[#F5F5F5]">
                    Cancel
                </button>
                <button type="submit" class="rounded-lg bg-[#66A157] px-4 py-2 text-[14px] font-semibold text-white hover:bg-[#549648]">
                    Save Team Name
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Registration Details Modal -->
<div id="registration-details-modal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-lg rounded-[12px] bg-white p-6 shadow-xl">
        <div class="flex items-center justify-between border-b border-[#EEEEEE] pb-3">
            <h4 class="text-[17px] font-bold text-[#333333]" id="reg-modal-tournament-title">Registration Details</h4>
            <button type="button" onclick="closeRegistrationDetailsModal()" class="text-[#888888] hover:text-[#333333] text-xl font-bold">&times;</button>
        </div>
        <div class="mt-4 space-y-4 text-[14px]">
            <div class="grid grid-cols-2 gap-4 rounded-lg bg-[#F9FBF9] p-4 border border-[#E8ECE8]">
                <div>
                    <span class="block text-[12px] font-semibold text-[#666666]">Format</span>
                    <span class="font-bold text-[#333333]" id="reg-modal-format">—</span>
                </div>
                <div>
                    <span class="block text-[12px] font-semibold text-[#666666]">Category</span>
                    <span class="font-bold text-[#333333]" id="reg-modal-category">—</span>
                </div>
                <div>
                    <span class="block text-[12px] font-semibold text-[#666666]">Group / Division</span>
                    <span class="font-bold text-[#333333]" id="reg-modal-group">—</span>
                </div>
                <div>
                    <span class="block text-[12px] font-semibold text-[#666666]">Subgroup</span>
                    <span class="font-bold text-[#333333]" id="reg-modal-subgroup">—</span>
                </div>
                <div>
                    <span class="block text-[12px] font-semibold text-[#666666]">Skill Level</span>
                    <span class="font-bold text-[#333333]" id="reg-modal-skill">—</span>
                </div>
                <div>
                    <span class="block text-[12px] font-semibold text-[#666666]">Age Group</span>
                    <span class="font-bold text-[#333333]" id="reg-modal-age-group">—</span>
                </div>
                <div>
                    <span class="block text-[12px] font-semibold text-[#666666]">Payment Status</span>
                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-bold text-emerald-800" id="reg-modal-payment">—</span>
                </div>
                <div>
                    <span class="block text-[12px] font-semibold text-[#666666]">Registered On</span>
                    <span class="font-bold text-[#333333]" id="reg-modal-date">—</span>
                </div>
            </div>

            <div id="reg-modal-doubles-box" class="hidden rounded-lg bg-[#F0F7EF] p-4 border border-[#C8E6C0] space-y-2">
                <h5 class="text-[13px] font-bold uppercase text-[#2e7d32]">Doubles & Team Information</h5>
                <div class="grid grid-cols-2 gap-3 text-[13px]">
                    <div class="col-span-2">
                        <span class="block text-[12px] font-semibold text-[#555555]">Team Name</span>
                        <span class="font-bold text-[#333333]" id="reg-modal-team-name">—</span>
                    </div>
                    <div>
                        <span class="block text-[12px] font-semibold text-[#555555]">Partner Name</span>
                        <span class="font-bold text-[#333333]" id="reg-modal-partner-name">—</span>
                    </div>
                    <div>
                        <span class="block text-[12px] font-semibold text-[#555555]">Partner Email</span>
                        <span class="font-medium text-[#333333]" id="reg-modal-partner-email">—</span>
                    </div>
                    @if(!empty($entry['partner_phone']))
                    <div>
                        <span class="block text-[12px] font-semibold text-[#555555]">Partner Phone</span>
                        <span class="font-medium text-[#333333]" id="reg-modal-partner-phone">—</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="flex justify-end pt-5 border-t border-[#EEEEEE] mt-4">
            <button type="button" onclick="closeRegistrationDetailsModal()" class="rounded-lg bg-[#66A157] px-5 py-2 text-[14px] font-semibold text-white hover:bg-[#549648]">
                Close
            </button>
        </div>
    </div>
</div>

@push('profile_scripts')
    <script>
        function openTeamNameModal(registrationId, currentTeamName) {
            document.getElementById('modal-team-name-registration-id').value = registrationId;
            document.getElementById('modal-team-name-input').value = currentTeamName;
            document.getElementById('edit-team-name-modal').classList.remove('hidden');
        }

        function closeTeamNameModal() {
            document.getElementById('edit-team-name-modal').classList.add('hidden');
        }

        function openRegistrationDetailsModal(entry, tournamentName) {
            document.getElementById('reg-modal-tournament-title').innerText = tournamentName + ' - Details';
            document.getElementById('reg-modal-format').innerText = entry.format || '—';
            document.getElementById('reg-modal-category').innerText = entry.category || '—';
            document.getElementById('reg-modal-group').innerText = entry.group || '—';
            document.getElementById('reg-modal-subgroup').innerText = entry.subgroup || '—';
            document.getElementById('reg-modal-skill').innerText = entry.skill_level || '—';
            document.getElementById('reg-modal-age-group').innerText = entry.age_group || 'All Ages';
            document.getElementById('reg-modal-payment').innerText = entry.payment_status || 'Completed';
            document.getElementById('reg-modal-date').innerText = entry.registered_at || '—';

            var doublesBox = document.getElementById('reg-modal-doubles-box');
            if (entry.is_doubles || entry.team_name || entry.partner_name) {
                doublesBox.classList.remove('hidden');
                document.getElementById('reg-modal-team-name').innerText = entry.team_name || '—';
                document.getElementById('reg-modal-partner-name').innerText = entry.partner_name || '—';
                document.getElementById('reg-modal-partner-email').innerText = entry.partner_email || '—';
                var phoneEl = document.getElementById('reg-modal-partner-phone');
                if (phoneEl) phoneEl.innerText = entry.partner_phone || '—';
            } else {
                doublesBox.classList.add('hidden');
            }

            document.getElementById('registration-details-modal').classList.remove('hidden');
        }

        function closeRegistrationDetailsModal() {
            document.getElementById('registration-details-modal').classList.add('hidden');
        }

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
