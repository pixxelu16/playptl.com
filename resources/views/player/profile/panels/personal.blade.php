<div
    id="profile-section-personal"
    class="overflow-hidden rounded-[12px] bg-white p-6 shadow-[0_1px_8px_rgba(0,0,0,0.06)] ring-1 ring-[#E0E0E0] sm:p-8"
>
    <h3 class="mb-6 text-[18px] font-bold leading-tight text-[#333333] sm:text-[20px]">Personal Information</h3>
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
