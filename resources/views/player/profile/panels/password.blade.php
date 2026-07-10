<div id="profile-section-password" class="overflow-hidden rounded-[12px] bg-white p-6 shadow-[0_1px_8px_rgba(0,0,0,0.06)] ring-1 ring-[#E0E0E0] sm:p-8">
    <h3 class="mb-6 text-[18px] font-bold leading-tight text-[#333333] sm:text-[20px]">Password &amp; Security</h3>

    @if ($errors->has('current_password') || $errors->has('password') || $errors->has('password_confirmation'))
        <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-[14px] font-semibold text-red-700">
            {{ $errors->first('current_password') ?: $errors->first('password') ?: $errors->first('password_confirmation') }}
        </div>
    @endif

    <form class="space-y-5" method="POST" action="{{ route('player.password.update') }}">
        @csrf
        @method('PUT')
        <div>
            <label for="mp-current-password" class="{{ $pwdLabelClass }}">Current Password</label>
            <div class="relative">
                <input
                    id="mp-current-password"
                    name="current_password"
                    type="password"
                    required
                    autocomplete="current-password"
                    class="{{ $pwdInputClass }} @error('current_password') border-red-400 focus:border-red-400 focus:ring-red-400 @enderror"
                />
                <button type="button" class="{{ $pwdEyeBtn }}" aria-label="Show password" data-password-toggle>
                    <svg class="h-5 w-5" data-password-eye fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <svg class="hidden h-5 w-5" data-password-eye-off fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                    </svg>
                </button>
            </div>
        </div>
        <div>
            <label for="mp-new-password" class="{{ $pwdLabelClass }}">New Password</label>
            <div class="relative">
                <input
                    id="mp-new-password"
                    name="password"
                    type="password"
                    required
                    autocomplete="new-password"
                    class="{{ $pwdInputClass }} @error('password') border-red-400 focus:border-red-400 focus:ring-red-400 @enderror"
                />
                <button type="button" class="{{ $pwdEyeBtn }}" aria-label="Show password" data-password-toggle>
                    <svg class="h-5 w-5" data-password-eye fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <svg class="hidden h-5 w-5" data-password-eye-off fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                    </svg>
                </button>
            </div>
        </div>
        <div>
            <label for="mp-confirm-password" class="{{ $pwdLabelClass }}">Confirm New Password</label>
            <div class="relative">
                <input
                    id="mp-confirm-password"
                    name="password_confirmation"
                    type="password"
                    required
                    autocomplete="new-password"
                    class="{{ $pwdInputClass }}"
                />
                <button type="button" class="{{ $pwdEyeBtn }}" aria-label="Show password" data-password-toggle>
                    <svg class="h-5 w-5" data-password-eye fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <svg class="hidden h-5 w-5" data-password-eye-off fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                    </svg>
                </button>
            </div>
        </div>
        <div class="flex flex-wrap gap-3 pt-2">
            <a href="{{ route('player.my-profile') }}" class="rounded-lg border border-[#E0E0E0] bg-[#F3F4F6] px-6 py-2.5 text-[14px] font-semibold text-[#333333] transition hover:bg-[#E8EAED] sm:text-[15px]">
                Cancel
            </a>
            <button type="submit" class="rounded-lg bg-[#66A157] px-6 py-2.5 text-[14px] font-semibold text-white shadow-sm transition hover:bg-[#5a9048] sm:text-[15px]">
                Update Password
            </button>
        </div>
    </form>
</div>

@push('profile_scripts')
    <script>
        (function () {
            document.querySelectorAll('[data-password-toggle]').forEach(function (toggleBtn) {
                toggleBtn.addEventListener('click', function () {
                    var wrap = toggleBtn.closest('.relative');
                    var input = wrap ? wrap.querySelector('input') : null;
                    var eye = toggleBtn.querySelector('[data-password-eye]');
                    var eyeOff = toggleBtn.querySelector('[data-password-eye-off]');
                    if (!input || !eye || !eyeOff) return;
                    if (input.type === 'password') {
                        input.type = 'text';
                        eye.classList.add('hidden');
                        eyeOff.classList.remove('hidden');
                        toggleBtn.setAttribute('aria-label', 'Hide password');
                    } else {
                        input.type = 'password';
                        eye.classList.remove('hidden');
                        eyeOff.classList.add('hidden');
                        toggleBtn.setAttribute('aria-label', 'Show password');
                    }
                });
            });
        })();
    </script>
@endpush
