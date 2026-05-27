@php
    $uploadWizardStart = $uploadWizardStart ?? ($errors->any() ? 'files' : 'gallery');
@endphp

<div
    id="profile-section-upload"
    class="overflow-hidden rounded-[10px] bg-white p-6 shadow-[0_4px_12px_rgba(0,0,0,0.05)] ring-1 ring-[#E0E0E0] sm:p-8"
>
    <div class="mb-6 flex flex-wrap items-start justify-between gap-3">
        <h3 class="text-[18px] font-bold leading-tight text-[#333333] sm:text-[20px]">Upload Match Images</h3>
        @if (! empty($uploadSelectedMatchId))
            <a
                href="{{ route('player.profile.location', ['match' => $uploadSelectedMatchId, 'kind' => $uploadSelectedMatchKind ?? 'group']) }}"
                class="text-[13px] font-semibold text-[#66A157] hover:underline"
            >
                ← Back to My Matches
            </a>
        @endif
    </div>

    @if (! empty($uploadMatchOptions))
        <div class="mb-6">
            <label for="mp-upload-match-select" class="{{ $uploadMatchLabelClass }}">Select match</label>
            <select
                id="mp-upload-match-select"
                class="{{ $uploadMatchSelectClass }}"
                onchange="if (this.value) { var o = this.options[this.selectedIndex]; var k = o.getAttribute('data-kind') || 'group'; window.location = '{{ route('player.profile.upload') }}?match=' + encodeURIComponent(this.value) + '&kind=' + encodeURIComponent(k); }"
            >
                @foreach ($uploadMatchOptions as $opt)
                    <option
                        value="{{ $opt['id'] }}"
                        data-kind="{{ $opt['kind'] ?? 'group' }}"
                        @selected((int) ($uploadSelectedMatchId ?? 0) === (int) $opt['id'] && ($uploadSelectedMatchKind ?? 'group') === ($opt['kind'] ?? 'group'))
                    >{{ $opt['label'] }}</option>
                @endforeach
            </select>
        </div>
    @endif

    @if (session('status'))
        <div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-[14px] font-semibold text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-[14px] font-semibold text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    {{-- Step 1: gallery preview + "Upload Image" CTA (same flow as before) --}}
    <div id="upload-step-gallery" @class(['hidden' => $uploadWizardStart === 'files'])>
        <div class="mb-6">
            <label for="mp-upload-match-display" class="{{ $uploadMatchLabelClass }}">Match Players</label>
            <input
                id="mp-upload-match-display"
                type="text"
                readonly
                value="{{ $uploadMatchPlayersLabel ?? '' }}"
                placeholder="No matches in your division"
                class="{{ $uploadMatchSelectClass }} @if (! empty($uploadSelectedMatchId)) bg-[#F9FAFB] cursor-default pr-3.5 text-[#424242] @endif"
                @if (empty($uploadSelectedMatchId)) disabled @endif
                autocomplete="off"
            />
        </div>

        @if (! empty($uploadScheduledDateLabel))
            <div class="mb-6 rounded-lg border border-[#E8E8E8] bg-[#FAFAFA] px-4 py-3 text-[14px] text-[#424242]">
                <p class="font-semibold text-[#333333]">Match date</p>
                <p class="mt-0.5">{{ $uploadScheduledDateLabel }}</p>
            </div>
        @endif

        <div class="mb-6">
            <p class="{{ $uploadMatchLabelClass }}">Match photos</p>
            @if (empty($uploadSelectedMatchId))
                <div class="rounded-lg border border-[#E8E8E8] bg-[#FAFAFA] px-4 py-8 text-center text-[14px] text-[#6B7280]">
                    No matches in your division right now.
                </div>
            @elseif (! empty($uploadExistingImages))
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 sm:gap-[14px]">
                    @foreach ($uploadExistingImages as $img)
                        <figure
                            class="overflow-hidden rounded-lg ring-1 ring-[#E8E8E8] cursor-pointer transition hover:ring-2 hover:ring-[#5DA051]/45"
                            data-mp-upload-remove
                            data-delete-url="{{ ($img['kind'] ?? 'group') === 'playoff' ? route('player.profile.playoff-upload.destroy', $img['id']) : route('player.profile.upload.destroy', $img['id']) }}"
                            tabindex="0"
                            aria-haspopup="dialog"
                            aria-label="Remove this match photo"
                        >
                            <img
                                src="{{ $img['url'] }}"
                                alt=""
                                class="aspect-square w-full object-cover select-none pointer-events-none"
                                loading="lazy"
                                decoding="async"
                                width="320"
                                height="320"
                            />
                            <figcaption class="border-t border-[#EEEEEE] bg-white px-2 py-1.5 text-[11px] leading-snug text-[#666666] sm:text-[12px]">
                                <span class="font-medium text-[#6B7280]">{{ $img['upload_date'] }}</span>
                                @if (! empty($img['notes']))
                                    <span class="mt-0.5 block line-clamp-3 text-[#333333]">{{ $img['notes'] }}</span>
                                @endif
                            </figcaption>
                        </figure>
                    @endforeach
                </div>
            @else
                <div
                    class="flex min-h-[220px] flex-col items-center justify-center rounded-lg border-2 border-dashed border-[#D1D5DB] bg-[#F9FAFB] px-6 py-12 text-center sm:min-h-[260px]"
                    role="status"
                >
                    <svg class="mb-3 h-12 w-12 text-[#C4C4C4]" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3A1.5 1.5 0 001.5 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                    </svg>
                    <p class="text-[15px] font-semibold text-[#6B7280] sm:text-[16px]">No images for this match yet</p>
                    <p class="mt-2 max-w-sm text-[13px] leading-relaxed text-[#9CA3AF] sm:text-[14px]">
                        Is match ke liye abhi koi photo upload nahi hui. Neeche <span class="font-semibold text-[#5DA051]">Upload Image</span> dabakar photos add karo.
                    </p>
                </div>
            @endif
        </div>

        <div class="mt-8 flex justify-center sm:mt-10">
            <button
                type="button"
                data-upload-go-step2
                @if (empty($uploadSelectedMatchId)) disabled @endif
                class="inline-flex min-w-[200px] items-center justify-center gap-2.5 rounded-lg border-2 border-dashed border-[#C8C8C8] bg-[#EEEEEE] px-10 py-3.5 text-[14px] font-semibold text-[#666666] transition hover:border-[#5DA051] hover:bg-[#E8F5E4] hover:text-[#2d6b24] disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:border-[#C8C8C8] disabled:hover:bg-[#EEEEEE] disabled:hover:text-[#666666] sm:min-w-[220px] sm:text-[15px]"
            >
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                </svg>
                Upload Image
            </button>
        </div>
    </div>

    {{-- Step 2: choose files + notes + save --}}
    <div id="upload-step-files" @class(['hidden' => $uploadWizardStart === 'gallery'])>
        <form
            class="space-y-6"
            method="post"
            action="{{ route('player.profile.upload.store') }}"
            enctype="multipart/form-data"
        >
            @csrf
            @if (! empty($uploadSelectedMatchId))
                <input type="hidden" name="match" value="{{ $uploadSelectedMatchId }}" />
                <input type="hidden" name="match_kind" value="{{ $uploadSelectedMatchKind ?? 'group' }}" />
            @endif
            <div>
                <label for="mp-upload-match-step2-display" class="{{ $uploadMatchLabelClass }}">Match Players</label>
                <input
                    id="mp-upload-match-step2-display"
                    type="text"
                    readonly
                    value="{{ $uploadMatchPlayersLabel ?? '' }}"
                    class="{{ $uploadMatchSelectClass }} bg-[#F9FAFB] cursor-default pr-3.5 text-[#424242]"
                    autocomplete="off"
                />
            </div>

            @if (! empty($uploadScheduledDateLabel))
                <div class="rounded-lg border border-[#E8E8E8] bg-[#FAFAFA] px-4 py-3 text-[14px] text-[#424242]">
                    <p class="font-semibold text-[#333333]">Match date</p>
                    <p class="mt-0.5">{{ $uploadScheduledDateLabel }}</p>
                </div>
            @endif

            <div>
                <p class="{{ $uploadMatchLabelClass }} mb-2">Add images <span class="font-bold text-red-600">*</span></p>
                <label for="mp-upload-files" class="{{ $uploadDropzoneClass }}">
                    <svg class="mb-3 h-10 w-10 text-[#999999]" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                    </svg>
                    <span class="text-[15px] font-bold text-[#333333] sm:text-[16px]">
                        Tap to choose files,
                        <span class="font-bold text-[#5DA051]">or browse</span>
                    </span>
                    <span class="mt-2 block text-[12px] font-normal text-[#999999] sm:text-[13px]">JPG, PNG, WEBP — max 10 MB each (up to 12 files)</span>
                    <input
                        id="mp-upload-files"
                        name="images[]"
                        type="file"
                        accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp"
                        multiple
                        required
                        class="absolute inset-0 h-full w-full cursor-pointer opacity-0"
                    />
                </label>
                <div id="mp-upload-selected-files" class="mt-3 hidden rounded-lg border border-[#D7EAD9] bg-[#F6FBF5] px-4 py-3" aria-live="polite">
                    <p id="mp-upload-selected-heading" class="mb-2 text-[12px] font-bold text-[#2f7a2a]"></p>
                    <ul id="mp-upload-selected-list" class="space-y-2"></ul>
                </div>
            </div>

            <div>
                <label for="mp-upload-notes" class="{{ $uploadNotesLabelClass }}">Match notes</label>
                <textarea
                    id="mp-upload-notes"
                    name="notes"
                    rows="5"
                    placeholder="Optional notes saved with this upload (same text for each image in this batch)."
                    class="{{ $uploadNotesClass }}"
                >{{ old('notes') }}</textarea>
            </div>

            <div class="flex flex-wrap gap-3 pt-1">
                <button
                    type="button"
                    data-upload-back-gallery
                    class="rounded-lg border border-[#DDDDDD] bg-[#F3F4F6] px-6 py-2.5 text-[14px] font-semibold text-[#333333] transition hover:bg-[#E8EAED] sm:text-[15px]"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    class="rounded-lg bg-[#5DA051] px-6 py-2.5 text-[14px] font-semibold text-white shadow-sm transition hover:bg-[#539547] sm:text-[15px]"
                >
                    Save uploads
                </button>
            </div>
        </form>
    </div>

    {{-- Remove image confirmation (matches league admin / Voyagers-style dialog) --}}
    <div
        id="mp-remove-upload-modal"
        class="fixed inset-0 z-[250] flex items-center justify-center p-4 sm:p-6"
        style="display: none;"
        role="dialog"
        aria-modal="true"
        aria-labelledby="mp-remove-upload-title"
        aria-hidden="true"
    >
        <div class="absolute inset-0 bg-[rgba(0,0,0,0.55)]" data-mp-remove-cancel aria-hidden="true"></div>
        <div class="relative z-10 w-full max-w-[420px] rounded-xl bg-white px-6 py-6 shadow-[0_12px_40px_rgba(0,0,0,0.22)] sm:px-8 sm:py-7">
            <h2 id="mp-remove-upload-title" class="text-[17px] font-bold leading-snug text-[#212121] sm:text-[18px]">Remove Image</h2>
            <p class="mt-3 text-[13px] leading-relaxed text-[#757575] sm:text-[14px]">
                Are you sure you wish to remove this image? This action cannot be undone.
            </p>
            <div class="mt-8 flex flex-wrap items-center justify-end gap-6 sm:gap-8">
                <button
                    type="button"
                    class="text-[12px] font-semibold uppercase tracking-[0.12em] text-[#757575] transition hover:text-[#424242] sm:text-[13px]"
                    data-mp-remove-cancel
                >
                    CANCEL
                </button>
                <form id="mp-remove-upload-form" method="post" class="m-0 inline p-0">
                    @csrf
                    @method('DELETE')
                    <button
                        type="submit"
                        class="text-[12px] font-semibold uppercase tracking-[0.12em] text-[#E53935] transition hover:text-[#C62828] sm:text-[13px]"
                    >
                        CONFIRM
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('profile_scripts')
    <script>
        (function () {
            var start = @json($uploadWizardStart);
            var uploadStepGallery = document.getElementById('upload-step-gallery');
            var uploadStepFiles = document.getElementById('upload-step-files');

            function setUploadWizardStep(step) {
                if (!uploadStepGallery || !uploadStepFiles) return;
                var showGallery = step === 'gallery';
                uploadStepGallery.classList.toggle('hidden', !showGallery);
                uploadStepFiles.classList.toggle('hidden', showGallery);
            }

            document.querySelectorAll('[data-upload-go-step2]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    setUploadWizardStep('files');
                });
            });

            document.querySelectorAll('[data-upload-back-gallery]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    setUploadWizardStep('gallery');
                });
            });

            setUploadWizardStep(start === 'files' ? 'files' : 'gallery');
        })();
    </script>
    <script>
        (function () {
            var fileInput = document.getElementById('mp-upload-files');
            var selectedWrap = document.getElementById('mp-upload-selected-files');
            var selectedHeading = document.getElementById('mp-upload-selected-heading');
            var selectedList = document.getElementById('mp-upload-selected-list');

            function formatFileSize(bytes) {
                if (bytes < 1024) {
                    return bytes + ' B';
                }
                if (bytes < 1024 * 1024) {
                    return (bytes / 1024).toFixed(1) + ' KB';
                }

                return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
            }

            function renderSelectedFiles() {
                if (!fileInput || !selectedWrap || !selectedList || !selectedHeading) {
                    return;
                }

                selectedList.innerHTML = '';
                var files = fileInput.files;

                if (!files || files.length === 0) {
                    selectedWrap.classList.add('hidden');
                    return;
                }

                selectedWrap.classList.remove('hidden');
                selectedHeading.textContent = files.length === 1
                    ? '1 file selected'
                    : files.length + ' files selected';

                for (var i = 0; i < files.length; i++) {
                    var file = files[i];
                    var li = document.createElement('li');
                    li.className = 'flex items-center gap-2 rounded-md border border-[#E8E8E8] bg-white px-3 py-2 text-[13px] text-[#333333]';

                    var icon = document.createElement('span');
                    icon.className = 'shrink-0 text-[#5DA051]';
                    icon.setAttribute('aria-hidden', 'true');
                    icon.innerHTML = '<svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M2.25 21h19.5a2.25 2.25 0 0 0 2.25-2.25V6a2.25 2.25 0 0 0-2.25-2.25H2.25A2.25 2.25 0 0 0 0 6v12.75A2.25 2.25 0 0 0 2.25 21Z"/></svg>';

                    var nameWrap = document.createElement('span');
                    nameWrap.className = 'min-w-0 flex-1';

                    var nameEl = document.createElement('span');
                    nameEl.className = 'block truncate font-semibold';
                    nameEl.textContent = file.name;
                    nameEl.title = file.name;

                    var sizeEl = document.createElement('span');
                    sizeEl.className = 'block text-[11px] text-[#757575]';
                    sizeEl.textContent = formatFileSize(file.size);

                    nameWrap.appendChild(nameEl);
                    nameWrap.appendChild(sizeEl);
                    li.appendChild(icon);
                    li.appendChild(nameWrap);
                    selectedList.appendChild(li);
                }
            }

            if (fileInput) {
                fileInput.addEventListener('change', renderSelectedFiles);
                renderSelectedFiles();
            }
        })();
    </script>
    <script>
        (function () {
            var modal = document.getElementById('mp-remove-upload-modal');
            var form = document.getElementById('mp-remove-upload-form');
            if (!modal || !form) {
                return;
            }

            function openModal(actionUrl) {
                if (!actionUrl) {
                    return;
                }
                form.setAttribute('action', actionUrl);
                modal.style.display = 'flex';
                modal.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
            }

            function closeModal() {
                modal.style.display = 'none';
                modal.setAttribute('aria-hidden', 'true');
                form.removeAttribute('action');
                document.body.style.overflow = '';
            }

            document.querySelectorAll('[data-mp-upload-remove]').forEach(function (el) {
                el.addEventListener('click', function () {
                    var url = el.getAttribute('data-delete-url');
                    if (url) {
                        openModal(url);
                    }
                });
                el.addEventListener('keydown', function (e) {
                    if (e.key !== 'Enter' && e.key !== ' ') {
                        return;
                    }
                    e.preventDefault();
                    var url = el.getAttribute('data-delete-url');
                    if (url) {
                        openModal(url);
                    }
                });
            });

            modal.querySelectorAll('[data-mp-remove-cancel]').forEach(function (node) {
                node.addEventListener('click', function () {
                    closeModal();
                });
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && modal.style.display === 'flex') {
                    closeModal();
                }
            });
        })();
    </script>
@endpush
