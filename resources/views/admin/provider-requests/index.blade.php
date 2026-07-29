@extends('layouts.admin')

@section('title', 'Coach & Mentor Requests | '.config('app.name', 'playptl'))
@section('meta_description', 'Manage and review registration applications for Coaches and Mentors.')

@section('content')
    <section class="admin-card">
        <div class="admin-card-header" style="display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; gap:16px; margin-bottom:20px;">
            <div>
                <h1 class="admin-card-title" style="margin:0;">Coach & Mentor Requests</h1>
                <p class="admin-card-text" style="margin-top:4px;">Review pending registrations for Coaches and Mentors. Approve to grant login access or reject applications.</p>
            </div>
            @if ($pendingCount > 0)
                <span style="background:#FFF3CD; color:#856404; font-size:13px; font-weight:600; padding:6px 14px; border-radius:20px; border:1px solid #FFEEBA;">
                    <i class="fa-solid fa-clock" style="margin-right:6px;"></i>{{ $pendingCount }} Pending Application(s)
                </span>
            @endif
        </div>

        @if(session('success'))
            <div class="admin-alert admin-alert--success" style="margin-bottom:20px; padding:12px 16px; background:#D4EDDA; color:#155724; border-radius:8px; border:1px solid #C3E6CB;">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="admin-alert admin-alert--danger" style="margin-bottom:20px; padding:12px 16px; background:#F8D7DA; color:#721C24; border-radius:8px; border:1px solid #F5C6CB;">
                {{ session('error') }}
            </div>
        @endif

        {{-- Filter Form --}}
        <form method="GET" action="{{ route('admin.provider-requests.index') }}" style="margin-bottom:24px; display:flex; flex-wrap:wrap; align-items:flex-end; gap:14px; background:#F8FAFC; padding:18px 20px; border-radius:12px; border:1px solid #E2E8F0;">
            <div style="flex:1; min-width:220px;">
                <label class="admin-label" for="search" style="display:block; margin-bottom:6px; font-size:13px; font-weight:600; color:#334155;">Search Name / Email</label>
                <input type="text" name="search" id="search" value="{{ $search }}" class="admin-input" placeholder="Search applicant..." style="width:100%; height:42px; border-radius:8px; border:1px solid #CBD5E1; padding:0 14px; font-size:14px; background:#ffffff;">
            </div>

            <div style="min-width:180px;">
                <label class="admin-label" for="role" style="display:block; margin-bottom:6px; font-size:13px; font-weight:600; color:#334155;">Filter by Role</label>
                <select name="role" id="role" class="admin-input" style="width:100%; height:42px; border-radius:8px; border:1px solid #CBD5E1; padding:0 14px; font-size:14px; background:#ffffff; cursor:pointer;">
                    <option value="">All Roles (Mentor & Coach)</option>
                    <option value="mentor" {{ $roleFilter === 'mentor' ? 'selected' : '' }}>Mentor</option>
                    <option value="coach" {{ $roleFilter === 'coach' ? 'selected' : '' }}>Coach</option>
                </select>
            </div>

            <div style="min-width:180px;">
                <label class="admin-label" for="status" style="display:block; margin-bottom:6px; font-size:13px; font-weight:600; color:#334155;">Filter by Status</label>
                <select name="status" id="status" class="admin-input" style="width:100%; height:42px; border-radius:8px; border:1px solid #CBD5E1; padding:0 14px; font-size:14px; background:#ffffff; cursor:pointer;">
                    <option value="pending" {{ $statusFilter === 'pending' ? 'selected' : '' }}>Pending Approval</option>
                    <option value="active" {{ $statusFilter === 'active' ? 'selected' : '' }}>Approved (Active)</option>
                    <option value="rejected" {{ $statusFilter === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>All Statuses</option>
                </select>
            </div>

            <div style="display:flex; align-items:center; gap:10px;">
                <button type="submit" class="admin-button" style="height:42px; padding:0 20px; background:#5DA44E; color:#ffffff; font-size:14px; font-weight:600; border:none; border-radius:8px; cursor:pointer; display:inline-flex; align-items:center; gap:8px; transition:background 0.2s;">
                    <i class="fa-solid fa-filter"></i> Filter
                </button>
                <a href="{{ route('admin.provider-requests.index') }}" style="height:42px; padding:0 18px; background:#F1F5F9; color:#475569; font-size:14px; font-weight:600; border:1px solid #CBD5E1; border-radius:8px; text-decoration:none; display:inline-flex; align-items:center; justify-content:center; transition:all 0.2s;">
                    Reset
                </a>
            </div>
        </form>

        {{-- Table --}}
        <div class="admin-table-wrapper" style="overflow-x:auto;">
            <table class="admin-table" style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:#F1F5F9; text-align:left;">
                        <th style="padding:12px;">Applicant</th>
                        <th style="padding:12px;">Role</th>
                        <th style="padding:12px;">Contact</th>
                        <th style="padding:12px;">Location</th>
                        <th style="padding:12px;">Registered Date</th>
                        <th style="padding:12px;">Status</th>
                        <th style="padding:12px; text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $user)
                        @php
                            $isMentorApp = $user->role === \App\Enums\UserRole::Mentor || !is_null($user->mentor_status);
                            $roleLabel = $isMentorApp ? 'Mentor' : 'Coach';
                            $bg = $isMentorApp ? '#E0F2FE' : '#F3E8FF';
                            $fg = $isMentorApp ? '#0369A1' : '#6B21A8';

                            $status = $user->status;
                            if ($isMentorApp && !is_null($user->mentor_status)) {
                                $status = $user->mentor_status;
                            } elseif (!$isMentorApp && !is_null($user->coach_status)) {
                                $status = $user->coach_status;
                            }
                        @endphp
                        <tr style="border-bottom:1px solid #E2E8F0;">
                            <td style="padding:12px;">
                                <div style="display:flex; align-items:center; gap:10px;">
                                    @if($user->avatar_path)
                                        <img src="{{ asset($user->avatar_path) }}" alt="{{ $user->name }}" style="width:40px; height:40px; border-radius:50%; object-fit:cover;">
                                    @else
                                        <div style="width:40px; height:40px; border-radius:50%; background:#5DA44E; color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700;">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <strong style="display:block; color:#1E293B;">{{ $user->name }}</strong>
                                        <span style="font-size:12px; color:#64748B;">{{ $user->username }}</span>
                                    </div>
                                </div>
                            </td>
                            <td style="padding:12px;">
                                <span style="font-size:12px; font-weight:600; padding:4px 10px; border-radius:12px; background:{{ $bg }}; color:{{ $fg }};">
                                    {{ $roleLabel }}
                                </span>
                            </td>
                            <td style="padding:12px;">
                                <div><i class="fa-regular fa-envelope" style="width:14px; color:#94A3B8;"></i> {{ $user->email }}</div>
                                @if($user->phone)
                                    <div style="font-size:12px; color:#64748B;"><i class="fa-solid fa-phone" style="width:14px; color:#94A3B8;"></i> {{ $user->phone }}</div>
                                @endif
                            </td>
                            <td style="padding:12px; font-size:13px; color:#475569;">
                                {{ implode(', ', array_filter([$user->city, $user->state])) ?: 'N/A' }}
                            </td>
                            <td style="padding:12px; font-size:13px; color:#64748B;">
                                {{ $user->created_at ? $user->created_at->format('M d, Y') : 'N/A' }}
                            </td>
                            <td style="padding:12px;">
                                @if($status === 'pending')
                                    <span style="font-size:12px; font-weight:600; padding:4px 10px; border-radius:12px; background:#FEF3C7; color:#92400E;">Pending Approval</span>
                                @elseif($status === 'active')
                                    <span style="font-size:12px; font-weight:600; padding:4px 10px; border-radius:12px; background:#DCFCE7; color:#166534;">Approved</span>
                                @elseif($status === 'rejected')
                                    <span style="font-size:12px; font-weight:600; padding:4px 10px; border-radius:12px; background:#FEE2E2; color:#991B1B;">Rejected</span>
                                @else
                                    <span style="font-size:12px; font-weight:600; padding:4px 10px; border-radius:12px; background:#F1F5F9; color:#475569;">{{ ucfirst($status) }}</span>
                                @endif
                            </td>
                            <td style="padding:12px; text-align:right;">
                                <div style="display:inline-flex; gap:6px;">
                                    @if($status !== 'active')
                                        <form id="approve-form-{{ $user->id }}" method="POST" action="{{ route('admin.provider-requests.approve', $user) }}" style="display:inline;">
                                            @csrf
                                            @method('PATCH')
                                            <button type="button" class="admin-btn btn-confirm-action" 
                                                data-form-id="approve-form-{{ $user->id }}"
                                                data-type="approve"
                                                data-name="{{ $user->name }}"
                                                data-role="{{ $roleLabel }}"
                                                style="background:#5DA44E; color:#fff; padding:6px 12px; font-size:12px; border:none; border-radius:6px; cursor:pointer;">
                                                <i class="fa-solid fa-check"></i> Approve
                                            </button>
                                        </form>
                                    @endif

                                    @if($status !== 'rejected')
                                        <form id="reject-form-{{ $user->id }}" method="POST" action="{{ route('admin.provider-requests.reject', $user) }}" style="display:inline;">
                                            @csrf
                                            @method('PATCH')
                                            <button type="button" class="admin-btn btn-confirm-action" 
                                                data-form-id="reject-form-{{ $user->id }}"
                                                data-type="reject"
                                                data-name="{{ $user->name }}"
                                                data-role="{{ $roleLabel }}"
                                                style="background:#DC2626; color:#fff; padding:6px 12px; font-size:12px; border:none; border-radius:6px; cursor:pointer;">
                                                <i class="fa-solid fa-xmark"></i> Reject
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="padding:30px; text-align:center; color:#94A3B8;">
                                No applications found matching your criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($requests->hasPages())
            <div style="margin-top:20px;">
                {{ $requests->links() }}
            </div>
        @endif
    </section>

    {{-- Modern Confirmation Modal --}}
    <div id="confirm-action-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm opacity-0 pointer-events-none transition-opacity duration-200" style="position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.45); backdrop-filter:blur(3px); z-index:9999; display:none; align-items:center; justify-content:center;">
        <div class="modal-box" style="background:#ffffff; border-radius:14px; width:90%; max-width:440px; padding:24px; box-shadow:0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04); text-align:center; transform:scale(0.95); transition:transform 0.2s ease;">
            <div id="modal-icon-container" style="width:52px; height:52px; border-radius:50%; margin:0 auto 16px auto; display:flex; align-items:center; justify-content:center; font-size:22px;"></div>
            
            <h3 id="modal-title" style="margin:0 0 8px 0; font-size:18px; font-weight:700; color:#1E293B;">Confirm Action</h3>
            <p id="modal-description" style="margin:0 0 24px 0; font-size:14px; color:#64748B; line-height:1.5;"></p>

            <div style="display:flex; gap:12px; justify-content:center;">
                <button type="button" id="modal-btn-cancel" style="flex:1; padding:10px 16px; font-size:14px; font-weight:600; color:#475569; background:#F1F5F9; border:1px solid #E2E8F0; border-radius:8px; cursor:pointer; transition:all 0.2s;">
                    Cancel
                </button>
                <button type="button" id="modal-btn-confirm" style="flex:1; padding:10px 16px; font-size:14px; font-weight:600; color:#ffffff; border:none; border-radius:8px; cursor:pointer; transition:all 0.2s;">
                    Confirm
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var modal = document.getElementById('confirm-action-modal');
            var modalIcon = document.getElementById('modal-icon-container');
            var modalTitle = document.getElementById('modal-title');
            var modalDesc = document.getElementById('modal-description');
            var btnCancel = document.getElementById('modal-btn-cancel');
            var btnConfirm = document.getElementById('modal-btn-confirm');
            var targetFormId = null;

            document.querySelectorAll('.btn-confirm-action').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    targetFormId = this.getAttribute('data-form-id');
                    var type = this.getAttribute('data-type');
                    var name = this.getAttribute('data-name');
                    var role = this.getAttribute('data-role');

                    if (type === 'approve') {
                        modalIcon.style.background = '#DCFCE7';
                        modalIcon.style.color = '#166534';
                        modalIcon.innerHTML = '<i class="fa-solid fa-user-check"></i>';
                        modalTitle.textContent = 'Approve ' + role + ' Application?';
                        modalDesc.textContent = 'Are you sure you want to approve ' + name + '\'s application? An approval email notification will be sent and they will be granted login access.';
                        btnConfirm.style.background = '#5DA44E';
                        btnConfirm.textContent = 'Yes, Approve';
                    } else {
                        modalIcon.style.background = '#FEE2E2';
                        modalIcon.style.color = '#991B1B';
                        modalIcon.innerHTML = '<i class="fa-solid fa-user-xmark"></i>';
                        modalTitle.textContent = 'Reject ' + role + ' Application?';
                        modalDesc.textContent = 'Are you sure you want to reject ' + name + '\'s application? A rejection notification email will be sent and login access will be restricted.';
                        btnConfirm.style.background = '#DC2626';
                        btnConfirm.textContent = 'Yes, Reject';
                    }

                    modal.style.display = 'flex';
                });
            });

            function closeModal() {
                modal.style.display = 'none';
                targetFormId = null;
            }

            btnCancel.addEventListener('click', closeModal);
            modal.addEventListener('click', function (e) {
                if (e.target === modal) closeModal();
            });

            btnConfirm.addEventListener('click', function () {
                if (targetFormId) {
                    var form = document.getElementById(targetFormId);
                    if (form) {
                        btnConfirm.disabled = true;
                        btnConfirm.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';
                        form.submit();
                    }
                }
            });
        });
    </script>
@endsection
