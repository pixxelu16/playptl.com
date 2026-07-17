@extends('layouts.admin')

@section('title', 'Manage Gallery | ' . config('app.name', 'playptl'))
@section('meta_description', 'Admin interface to upload, edit, and delete photos in the gallery.')

@section('content')
    <section class="admin-card">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-card-title">Manage Gallery Photos</h1>
                <p class="admin-card-text">View, edit details of, or delete existing photos from the platform gallery.</p>
            </div>
            <button type="button" class="admin-button" onclick="openUploadModal()">
                <i class="fa-solid fa-plus" aria-hidden="true" style="margin-right: 6px;"></i>
                <span>Upload Photo</span>
            </button>
        </div>

        @if (session('status'))
            <div class="admin-alert admin-alert-success" style="margin-bottom: 20px;">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="admin-alert admin-alert-error" style="margin-bottom: 20px;">{{ $errors->first() }}</div>
        @endif

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 120px;">Photo</th>
                        <th style="width: 200px;">Uploaded By & Date</th>
                        <th>Notes / Caption</th>
                        <th style="width: 120px; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($uploads as $upload)
                        <tr style="vertical-align: top;">
                            <td>
                                <a href="{{ asset($upload->image_path) }}" target="_blank">
                                    <img src="{{ asset($upload->image_path) }}" alt="Gallery Image" style="width: 100px; height: 75px; object-fit: cover; border-radius: 6px; border: 1px solid #ddd; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                                </a>
                            </td>
                            <td>
                                <div style="font-size: 14px; font-weight: 500;">
                                    {{ $upload->uploadedBy?->name ?? 'Administrator' }}
                                </div>
                                <div style="font-size: 12px; color: #6b7280; margin-top: 4px;">
                                    {{ $upload->upload_date ? $upload->upload_date->format('M d, Y') : ($upload->created_at ? $upload->created_at->format('M d, Y') : '') }}
                                </div>
                            </td>
                            <td>
                                <div style="font-size: 14px; color: #374151; white-space: pre-line;">
                                    {{ $upload->notes ?: '—' }}
                                </div>
                            </td>
                            <td style="text-align: right;">
                                <div class="admin-table-actions" style="justify-content: flex-end;">
                                    <button type="button" title="Edit Photo Details" onclick="openEditModal({{ $upload->id }}, '{{ addslashes($upload->notes ?? '') }}', '{{ asset($upload->image_path) }}')" style="background: #eef2f6; color: #4b5563;">
                                        <i class="fa-solid fa-pen" aria-hidden="true"></i>
                                    </button>
                                    <form class="delete-photo-form" method="POST" action="{{ route('admin.gallery.destroy', $upload) }}" style="margin: 0; display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="admin-button admin-button-danger" style="padding: 6px 12px; font-size: 13px; background: #dc2626; color: white; display: inline-flex; align-items: center; gap: 4px;" title="Delete Photo">
                                            <i class="fa-solid fa-trash" aria-hidden="true"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="admin-muted" style="text-align: center; padding: 30px;">No gallery photos found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($uploads->hasPages())
            <div class="admin-pagination-container" style="margin-top: 20px;">
                {{ $uploads->links() }}
            </div>
        @endif
    </section>

    {{-- Modal: Upload New Photo --}}
    <div id="upload-photo-modal" class="admin-modal" hidden aria-hidden="true">
        <button type="button" class="admin-modal-backdrop" onclick="closeUploadModal()" aria-label="Close"></button>
        <div class="admin-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="upload-modal-title">
            <h2 id="upload-modal-title" class="admin-modal-title">Upload New Gallery Photo</h2>
            
            <form action="{{ route('admin.gallery.store') }}" method="POST" enctype="multipart/form-data" class="admin-form">
                @csrf
                <div class="admin-form-group" style="margin-bottom: 16px;">
                    <label for="image" class="admin-form-label" style="font-weight: 600; display: block; margin-bottom: 6px;">Select Photo <span style="color: red;">*</span></label>
                    <input type="file" name="image" id="image" class="admin-form-input" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px; width: 100%;" required>
                    <span style="font-size: 12px; color: #6b7280; display: block; margin-top: 4px;">Max size: 2MB. Format: JPG, JPEG, PNG, WEBP.</span>
                </div>

                <div class="admin-form-group" style="margin-bottom: 20px;">
                    <label for="notes" class="admin-form-label" style="font-weight: 600; display: block; margin-bottom: 6px;">Notes / Caption</label>
                    <textarea name="notes" id="notes" class="admin-form-input" rows="3" placeholder="Enter notes or caption for the photo..." style="padding: 8px; border: 1px solid #ddd; border-radius: 4px; width: 100%; resize: vertical;"></textarea>
                </div>

                <div class="admin-modal-actions" style="margin-top: 24px;">
                    <button type="button" class="admin-modal-btn-cancel" onclick="closeUploadModal()">Cancel</button>
                    <button type="submit" class="admin-modal-btn-primary">
                        <i class="fa-solid fa-cloud-arrow-up" aria-hidden="true" style="margin-right: 6px;"></i>
                        Upload Photo
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal: Edit Photo --}}
    <div id="edit-photo-modal" class="admin-modal" hidden aria-hidden="true">
        <button type="button" class="admin-modal-backdrop" onclick="closeEditModal()" aria-label="Close"></button>
        <div class="admin-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="edit-modal-title">
            <h2 id="edit-modal-title" class="admin-modal-title">Edit Photo Details</h2>
            
            <form id="edit-photo-form" method="POST" enctype="multipart/form-data" class="admin-form">
                @csrf
                @method('PUT')
                
                <div class="admin-form-group" style="margin-bottom: 16px; text-align: center;">
                    <label class="admin-form-label" style="font-weight: 600; display: block; margin-bottom: 6px; text-align: left;">Current Photo</label>
                    <img id="edit_image_preview" src="" alt="Current Photo" style="max-width: 100%; max-height: 160px; object-fit: contain; border-radius: 6px; border: 1px solid #ddd; display: block; margin: 0 auto;">
                </div>

                <div class="admin-form-group" style="margin-bottom: 16px;">
                    <label for="edit_image" class="admin-form-label" style="font-weight: 600; display: block; margin-bottom: 6px;">Replace Photo (Optional)</label>
                    <input type="file" name="image" id="edit_image" class="admin-form-input" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px; width: 100%;">
                    <span style="font-size: 12px; color: #6b7280; display: block; margin-top: 4px;">Leave empty to keep current photo.</span>
                </div>

                <div class="admin-form-group" style="margin-bottom: 20px;">
                    <label for="edit_notes" class="admin-form-label" style="font-weight: 600; display: block; margin-bottom: 6px;">Notes / Caption</label>
                    <textarea name="notes" id="edit_notes" class="admin-form-input" rows="4" placeholder="Enter notes or caption for the photo..." style="padding: 8px; border: 1px solid #ddd; border-radius: 4px; width: 100%; resize: vertical;"></textarea>
                </div>

                <div class="admin-modal-actions" style="margin-top: 24px;">
                    <button type="button" class="admin-modal-btn-cancel" onclick="closeEditModal()">Cancel</button>
                    <button type="submit" class="admin-modal-btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function openUploadModal() {
            const modal = document.getElementById('upload-photo-modal');
            modal.classList.add('is-open');
            modal.removeAttribute('hidden');
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('admin-modal-open');
        }

        function closeUploadModal() {
            const modal = document.getElementById('upload-photo-modal');
            modal.classList.remove('is-open');
            modal.setAttribute('hidden', 'true');
            modal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('admin-modal-open');
        }

        function openEditModal(id, notes, imagePath) {
            const modal = document.getElementById('edit-photo-modal');
            const form = document.getElementById('edit-photo-form');
            const textarea = document.getElementById('edit_notes');
            const preview = document.getElementById('edit_image_preview');
            
            form.action = `/admin/gallery/${id}`;
            textarea.value = notes;
            preview.src = imagePath;
            
            modal.classList.add('is-open');
            modal.removeAttribute('hidden');
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('admin-modal-open');
        }

        function closeEditModal() {
            const modal = document.getElementById('edit-photo-modal');
            modal.classList.remove('is-open');
            modal.setAttribute('hidden', 'true');
            modal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('admin-modal-open');
        }

        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.delete-photo-form').forEach(function (form) {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "Do you want to delete this photo from the gallery? This cannot be undone.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc2626',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, delete it!',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>
@endpush
