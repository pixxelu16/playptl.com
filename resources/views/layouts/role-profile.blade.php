@extends('layouts.website')

@section('title')
    {{ $pageTitle ?? $roleName . ' Panel' }} | {{ config('app.name', 'playptl') }}
@endsection

@section('page_bg', '#E8F7E9')
@section('header_class', 'absolute inset-x-0 top-0 z-[100] bg-transparent px-5 pb-4 pt-6 sm:px-8 lg:px-14')
@section('suppress_global_status', true)

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css" />
    <style>
        .cropper-view-box,
        .cropper-face {
            border-radius: 50%;
        }
        .animate-fade-in {
            animation: fadeIn 0.2s ease-out forwards;
        }
        .animate-scale-in {
            animation: scaleIn 0.2s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes scaleIn {
            from { transform: scale(0.95); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
    </style>
@endpush

@php
    $profileNavActive = 'block rounded-lg bg-[#66A157] px-4 py-3 text-center text-[14px] font-semibold leading-snug text-white shadow-[0_1px_5px_rgba(102,161,87,0.3)] sm:text-[15px]';
    $profileNavInactive = 'block rounded-lg border border-[#E0E0E0] bg-white px-4 py-3 text-center text-[14px] font-semibold leading-snug text-[#333333] transition-colors hover:bg-gray-50 sm:text-[15px]';
    $navClass = fn (string $section) => $activeSection === $section ? $profileNavActive : $profileNavInactive;
@endphp

@section('content')
    <main class="bg-[#E8F7E9] font-['Montserrat',ui-sans-serif,system-ui,sans-serif] text-[#333333] antialiased">
        <section class="site-hero relative flex flex-col overflow-hidden">
            <video class="absolute inset-0 z-0 h-full min-h-full w-full object-cover" autoplay muted loop playsinline preload="auto" aria-hidden="true">
                <source src="{{ asset('frontend/videos/hero-section-video.mp4') }}" type="type/mp4">
            </video>
            <div class="pointer-events-none absolute inset-0 z-[1] bg-gradient-to-b from-[rgba(8,15,28,0.88)] via-[rgba(8,15,28,0.35)] via-40% to-[rgba(8,15,28,0.55)]" aria-hidden="true"></div>

            <div class="relative z-10 mx-auto flex w-full max-w-[1400px] flex-1 flex-col justify-center px-5 pb-16 pt-28 sm:px-8 sm:pb-24 sm:pt-36 lg:px-14 lg:pb-32 lg:pt-44">
                <header class="max-w-5xl">
                    <nav class="mb-6 flex flex-wrap items-center gap-x-1 gap-y-2 text-[14px] font-semibold uppercase tracking-[0.28em] text-[#B4F000] sm:mb-8" aria-label="Breadcrumb">
                        <a href="{{ url('/') }}" class="text-[#B4F000] transition-opacity hover:opacity-90">Home</a>
                        <span class="mx-1 sm:mx-2">&gt;&gt;</span>
                        <span class="text-[#B4F000]">{{ $roleName }} Profile</span>
                    </nav>

                    <h1 class="league-1 text-[clamp(2.75rem,11vw,5rem)] font-normal uppercase leading-[0.95] tracking-[0.02em]">
                        <span class="text-white">MY</span><span class="text-[#B4F000]"> PANEL</span>
                    </h1>
                </header>
            </div>
        </section>

        <section class="mx-auto max-w-[1400px] px-5 py-10 sm:px-8 sm:py-12 lg:px-14 lg:py-16">
            <div class="flex min-w-0 flex-col gap-6 pb-1 lg:flex-row lg:items-start lg:gap-6">
                <aside class="w-full shrink-0 lg:w-[450px] lg:min-w-[450px] lg:max-w-[450px]">
                    <div class="overflow-hidden rounded-[12px] bg-white p-[5px] shadow-[0_1px_8px_rgba(0,0,0,0.06)] ring-1 ring-[#E0E0E0]">
                        <div class="rounded-[5px] bg-[#E8F5E9] px-5 py-6 text-center">
                            @php
                                $initials = '';
                                if ($user->first_name && $user->last_name) {
                                    $initials = strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1));
                                } else {
                                    $names = explode(' ', $user->name);
                                    if (count($names) >= 2) {
                                        $initials = strtoupper(substr($names[0], 0, 1) . substr($names[count($names)-1], 0, 1));
                                    } else {
                                        $initials = strtoupper(substr($user->name, 0, 2));
                                    }
                                }
                            @endphp
                            <div class="relative mx-auto h-[100px] w-[100px]" id="profile-avatar-container">
                                @if ($user->avatar_path)
                                    <img
                                        id="profile-avatar-img"
                                        src="{{ asset($user->avatar_path) }}"
                                        alt="Avatar"
                                        class="h-full w-full rounded-full object-cover ring-2 ring-white"
                                        width="100"
                                        height="100"
                                    />
                                @else
                                    <div id="profile-avatar-initials" class="flex h-full w-full items-center justify-center rounded-full bg-[#66A157] text-[36px] font-bold text-white ring-2 ring-white uppercase select-none">
                                        {{ $initials }}
                                    </div>
                                @endif
                                
                                {{-- Camera Icon Overlay --}}
                                <label for="profile-photo-input" class="absolute bottom-0 right-0 flex h-8 w-8 cursor-pointer items-center justify-center rounded-full bg-white text-[#333333] shadow-[0_2px_5px_rgba(0,0,0,0.15)] hover:bg-gray-100 transition-colors border border-gray-200" title="Change Profile Picture">
                                    <i class="fa-solid fa-camera text-[13px]"></i>
                                </label>
                                <input type="file" id="profile-photo-input" class="hidden" accept="image/jpeg,image/jpg,image/png,image/webp" />
                            </div>
                            <h2 class="mt-4 text-[18px] font-bold leading-tight text-[#333333]">{{ $user->name }}</h2>
                            <p class="mt-1 text-[14px] font-medium text-[#666666]">{{ $roleName }}</p>
                        </div>
                        
                        <nav class="space-y-2 p-4">
                            <a href="{{ route(strtolower($roleName) . '.dashboard') }}" class="{{ $navClass('dashboard') }}">Dashboard</a>
                            <a href="{{ route(strtolower($roleName) . '.profile') }}" class="{{ $navClass('profile') }}">Edit Profile</a>
                            @if(in_array(strtolower($roleName), ['student']))
                                <a href="{{ route('student.bookings') }}" class="{{ $navClass('bookings') }}">My Bookings</a>
                            @elseif(in_array(strtolower($roleName), ['mentor', 'coach']))
                                <a href="{{ route('provider.bookings') }}" class="{{ $navClass('bookings') }}">Booking Requests</a>
                            @endif
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full rounded-lg border border-red-200 bg-white px-4 py-3 text-center text-[14px] font-semibold leading-snug text-red-600 transition-colors hover:bg-red-50 sm:text-[15px]">
                                    Logout
                                </button>
                            </form>
                        </nav>
                    </div>
                </aside>

                <div class="min-w-0 w-full space-y-6 lg:w-[810px] lg:min-w-[810px] lg:max-w-[810px] lg:shrink-0">
                    @if (session('status'))
                        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-[14px] font-semibold text-emerald-700">
                            {{ session('status') }}
                        </div>
                    @endif

                    @yield('profile_panel')
                </div>
            </div>
        </section>
    </main>

    {{-- Interactive Cropping Modal --}}
    <div id="cropping-modal" class="fixed inset-0 z-[999] hidden flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 animate-fade-in">
        <div class="relative w-full max-w-lg overflow-hidden rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-black/5 flex flex-col gap-4 animate-scale-in">
            <div class="flex items-center justify-between border-b pb-3 border-gray-100">
                <h3 class="text-lg font-bold text-gray-900">Crop Profile Photo</h3>
                <button type="button" class="close-modal-btn text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            {{-- Screen 1: Cropper --}}
            <div id="cropper-screen" class="flex flex-col gap-4">
                <div class="relative w-full overflow-hidden rounded-lg bg-gray-100 flex items-center justify-center" style="height: 300px;">
                    <img id="cropper-image" src="" alt="Source Image" class="max-w-full max-h-full" />
                </div>
                <div class="flex flex-wrap items-center justify-between gap-2 border-t pt-3 border-gray-100">
                    <div class="flex gap-2">
                        <button type="button" id="zoom-in" class="flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 transition-colors" title="Zoom In">
                            <i class="fa-solid fa-magnifying-glass-plus"></i>
                        </button>
                        <button type="button" id="zoom-out" class="flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 transition-colors" title="Zoom Out">
                            <i class="fa-solid fa-magnifying-glass-minus"></i>
                        </button>
                        <button type="button" id="rotate-left" class="flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 transition-colors" title="Rotate Left">
                            <i class="fa-solid fa-rotate-left"></i>
                        </button>
                        <button type="button" id="rotate-right" class="flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 transition-colors" title="Rotate Right">
                            <i class="fa-solid fa-rotate-right"></i>
                        </button>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" class="close-modal-btn rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors">Cancel</button>
                        <button type="button" id="go-to-preview" class="rounded-lg bg-[#66A157] px-4 py-2 text-sm font-semibold text-white hover:bg-[#5a9048] transition-colors">Done</button>
                    </div>
                </div>
            </div>

            {{-- Screen 2: Preview --}}
            <div id="preview-screen" class="hidden flex flex-col items-center gap-4">
                <p class="text-sm text-gray-600 text-center font-medium">Please review the preview below before confirming the upload:</p>
                <div class="relative h-[150px] w-[150px] overflow-hidden rounded-full ring-4 ring-[#E8F7E9] shadow-md">
                    <img id="cropped-preview-img" src="" alt="Cropped Preview" class="h-full w-full object-cover" />
                </div>
                <div class="flex w-full justify-center gap-3 border-t pt-4 border-gray-100">
                    <button type="button" class="close-modal-btn rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors">Cancel</button>
                    <button type="button" id="back-to-crop" class="rounded-lg border border-[#66A157] bg-white px-4 py-2 text-sm font-semibold text-[#66A157] hover:bg-emerald-50 transition-colors">Re-crop</button>
                    <button type="button" id="confirm-upload" class="rounded-lg bg-[#66A157] px-4 py-2 text-sm font-semibold text-white hover:bg-[#5a9048] transition-colors flex items-center gap-2">
                        <span id="upload-btn-text">Confirm & Upload</span>
                        <i id="upload-spinner" class="fa-solid fa-circle-notch fa-spin hidden"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>
    <script>
        function escapeHtml(str) {
            return String(str || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function showToast(message, type) {
            type = type || 'success';
            var bg = type === 'success' ? 'bg-emerald-500' : 'bg-red-500';
            var icon = type === 'success' ? '<i class="fa-solid fa-circle-check mr-2"></i>' : '<i class="fa-solid fa-circle-exclamation mr-2"></i>';
            var $toast = $(
                '<div class="fixed top-5 right-5 z-[9999] flex items-center rounded-lg px-4 py-3 text-white shadow-lg transition-all duration-300 transform translate-y-[-20px] opacity-0 ' + bg + '">' +
                    icon +
                    '<span class="font-medium text-sm">' + escapeHtml(message) + '</span>' +
                '</div>'
            );
            $('body').append($toast);
            
            $toast.get(0).offsetHeight;
            $toast.removeClass('translate-y-[-20px] opacity-0').addClass('translate-y-0 opacity-100');
            
            setTimeout(function () {
                $toast.removeClass('translate-y-0 opacity-100').addClass('translate-y-[-20px] opacity-0');
                setTimeout(function () {
                    $toast.remove();
                }, 300);
            }, 4000);
        }

        $(function () {
            var cropper = null;
            var selectedFile = null;
            var $modal = $('#cropping-modal');
            var $cropperImg = $('#cropper-image');
            var $previewImg = $('#cropped-preview-img');
            var $cropperScreen = $('#cropper-screen');
            var $previewScreen = $('#preview-screen');
            var $uploadSpinner = $('#upload-spinner');
            var $uploadBtnText = $('#upload-btn-text');
            var $confirmBtn = $('#confirm-upload');
            
            // Trigger file picker on camera overlay click
            $('#profile-photo-input').on('change', function (e) {
                var files = e.target.files;
                if (!files || !files.length) return;
                
                selectedFile = files[0];
                
                // Limit validation to 8MB
                if (selectedFile.size > 8 * 1024 * 1024) {
                    showToast('The selected image exceeds the 8MB size limit.', 'error');
                    $(this).val('');
                    return;
                }
                
                var reader = new FileReader();
                reader.onload = function (event) {
                    $cropperImg.attr('src', event.target.result);
                    
                    // Reset screen states
                    $cropperScreen.removeClass('hidden');
                    $previewScreen.addClass('hidden');
                    $modal.removeClass('hidden');
                    
                    // Initialize cropper
                    if (cropper) {
                        cropper.destroy();
                    }
                    
                    setTimeout(function () {
                        cropper = new Cropper($cropperImg[0], {
                            aspectRatio: 1,
                            viewMode: 1,
                            dragMode: 'crop',
                            autoCropArea: 0.8,
                            cropBoxMovable: true,
                            cropBoxResizable: true,
                            toggleDragModeOnDblclick: false,
                            responsive: true
                        });
                    }, 50);
                };
                reader.readAsDataURL(selectedFile);
            });
            
            // Done button clicked - switch to preview
            $('#go-to-preview').on('click', function () {
                if (!cropper) return;
                
                var canvas = cropper.getCroppedCanvas({
                    width: 400,
                    height: 400
                });
                
                $previewImg.attr('src', canvas.toDataURL('image/jpeg', 0.9));
                $cropperScreen.addClass('hidden');
                $previewScreen.removeClass('hidden');
            });
            
            // Re-crop button clicked - switch back to cropper
            $('#back-to-crop').on('click', function () {
                $previewScreen.addClass('hidden');
                $cropperScreen.removeClass('hidden');
            });
            
            // Close modal actions
            $('.close-modal-btn').on('click', function () {
                closeCropModal();
            });
            
            function closeCropModal() {
                $modal.addClass('hidden');
                $('#profile-photo-input').val('');
                if (cropper) {
                    cropper.destroy();
                    cropper = null;
                }
            }
            
            // Cropper Controls
            $('#zoom-in').on('click', function () {
                if (cropper) cropper.zoom(0.1);
            });
            $('#zoom-out').on('click', function () {
                if (cropper) cropper.zoom(-0.1);
            });
            $('#rotate-left').on('click', function () {
                if (cropper) cropper.rotate(-45);
            });
            $('#rotate-right').on('click', function () {
                if (cropper) cropper.rotate(45);
            });
            
            // Confirm & Upload cropped image
            $confirmBtn.on('click', function () {
                if (!cropper) return;
                
                $confirmBtn.prop('disabled', true);
                $uploadSpinner.removeClass('hidden');
                $uploadBtnText.text('Uploading...');
                
                var canvas = cropper.getCroppedCanvas({
                    width: 400,
                    height: 400
                });
                
                canvas.toBlob(function (blob) {
                    var formData = new FormData();
                    var fileExtension = selectedFile.name.split('.').pop() || 'jpg';
                    formData.append('avatar', blob, 'avatar.' + fileExtension);
                    
                    $.ajax({
                        url: "{{ route('profile.avatar.update') }}",
                        method: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || ''
                        },
                        success: function (response) {
                            showToast(response.message || 'Profile photo uploaded successfully!', 'success');
                            
                            // Swap initials circular element or existing image with new optimized image
                            var $avatarImg = $('#profile-avatar-img');
                            if ($avatarImg.length) {
                                $avatarImg.attr('src', response.avatar_url);
                            } else {
                                var newImgHtml = '<img id="profile-avatar-img" src="' + response.avatar_url + '" alt="Avatar" class="h-full w-full rounded-full object-cover ring-2 ring-white" width="100" height="100" />';
                                $('#profile-avatar-initials').replaceWith(newImgHtml);
                            }
                            
                            closeCropModal();
                        },
                        error: function (xhr) {
                            var errMsg = 'Failed to upload profile photo.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errMsg = xhr.responseJSON.message;
                            }
                            showToast(errMsg, 'error');
                        },
                        complete: function () {
                            $confirmBtn.prop('disabled', false);
                            $uploadSpinner.addClass('hidden');
                            $uploadBtnText.text('Confirm & Upload');
                        }
                    });
                }, 'image/jpeg', 0.9);
            });
        });
    </script>
@endpush
