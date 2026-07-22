@extends('layouts.admin')

@section('title', 'Booking #' . $booking->id . ' | Admin | ' . config('app.name'))

@push('styles')
<style>
    /* Status Badge styling */
    .badge-status {
        display: inline-block;
        padding: 4px 10px;
        font-size: 11px;
        font-weight: 700;
        border-radius: 9999px;
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }
    .badge-status.pending { background: #fef3c7; color: #d97706; }
    .badge-status.accepted { background: #d1fae5; color: #059669; }
    .badge-status.rejected { background: #fee2e2; color: #dc2626; }
    .badge-status.cancelled { background: #f3f4f6; color: #4b5563; }
    .badge-status.completed { background: #dbeafe; color: #2563eb; }

    /* Custom form elements matching overall admin style */
    .booking-detail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 24px;
        margin-top: 24px;
    }
    .detail-card {
        background-color: #fff;
        border: 1px solid #E0E0E0;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .detail-title {
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
        color: #64748b;
        letter-spacing: 0.05em;
        margin-bottom: 16px;
        border-bottom: 1px solid #e2e8f0;
        padding-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .detail-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px dashed #f1f5f9;
        font-size: 14px;
    }
    .detail-row:last-child {
        border-bottom: none;
    }
    .detail-label {
        color: #64748b;
    }
    .detail-value {
        font-weight: 600;
        color: #1e293b;
        text-align: right;
    }
</style>
@endpush

@section('content')
<section class="admin-card">
    <div class="admin-page-header" style="margin-bottom: 20px;">
        <div>
            <h1 class="admin-card-title" style="display:inline-flex; align-items:center; gap:10px;">
                Booking #{{ $booking->id }}
                <span class="badge-status {{ $booking->status }}">{{ $booking->statusLabel() }}</span>
            </h1>
            <p class="admin-card-text">
                View session transaction logs and process payouts for this booking.
            </p>
        </div>
        <a class="admin-button admin-button-secondary" href="{{ route('admin.bookings.index') }}">
            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
            <span>Back to Bookings</span>
        </a>
    </div>

    @if(session('success'))
        <div class="admin-alert admin-alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="admin-alert admin-alert-error">{{ session('error') }}</div>
    @endif

    {{-- Layout Grid --}}
    <div class="booking-detail-grid">

        {{-- Panel 1: Customer & Provider --}}
        <div class="detail-card">
            <div class="detail-title"><i class="fa-solid fa-user-group"></i> Parties involved</div>
            <div class="detail-row">
                <span class="detail-label">Student</span>
                <span class="detail-value">
                    {{ $booking->student->name }}
                    <small style="display:block;font-weight:normal;color:#64748b;">{{ $booking->student->email }}</small>
                </span>
            </div>
            @if($booking->student_phone)
            <div class="detail-row">
                <span class="detail-label">Student Phone</span>
                <span class="detail-value">{{ $booking->student_phone }}</span>
            </div>
            @endif
            @if($booking->student_location)
            <div class="detail-row">
                <span class="detail-label">Location</span>
                <span class="detail-value">{{ $booking->student_location }}</span>
            </div>
            @endif
            <div class="detail-row" style="margin-top: 10px; border-top: 1px solid #e2e8f0; padding-top: 14px;">
                <span class="detail-label">{{ ucfirst($booking->provider_type) }}</span>
                <span class="detail-value">
                    {{ $booking->provider->name }}
                    <small style="display:block;font-weight:normal;color:#64748b;">{{ $booking->provider->email }}</small>
                </span>
            </div>
            @if($booking->message)
            <div style="margin-top: 16px; padding: 12px; background: #f8fafc; border-radius: 6px; border-left: 3px solid #5DA44E; font-size: 13px;">
                <strong style="color: #475569; display:block; margin-bottom: 4px;">Student Message:</strong>
                <span style="color: #64748b; font-style: italic;">"{{ $booking->message }}"</span>
            </div>
            @endif
        </div>

        {{-- Panel 2: Session Details --}}
        <div class="detail-card">
            <div class="detail-title"><i class="fa-solid fa-calendar-days"></i> Session Details</div>
            <div class="detail-row">
                <span class="detail-label">From Date</span>
                <span class="detail-value">{{ $booking->from_date->format('M d, Y') }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">To Date</span>
                <span class="detail-value">{{ $booking->to_date->format('M d, Y') }}</span>
            </div>
            @if($booking->booking_time)
            <div class="detail-row">
                <span class="detail-label">Preferred Time</span>
                <span class="detail-value">{{ Carbon\Carbon::parse($booking->booking_time)->format('g:i A') }}</span>
            </div>
            @endif
            <div class="detail-row">
                <span class="detail-label">Total Days</span>
                <span class="detail-value">{{ $booking->total_days }} days</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Hours / Day</span>
                <span class="detail-value">{{ $booking->hours_per_day }} hrs</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Total Hours</span>
                <span class="detail-value">{{ $booking->total_hours }} hrs</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Created At</span>
                <span class="detail-value">{{ $booking->created_at->format('M d, Y H:i') }}</span>
            </div>
        </div>

        {{-- Panel 3: Financial Summary --}}
        <div class="detail-card">
            <div class="detail-title"><i class="fa-solid fa-file-invoice-dollar"></i> Financials &amp; Actions</div>
            <div class="detail-row">
                <span class="detail-label">Hourly Rate</span>
                <span class="detail-value">
                    @if($booking->hourly_rate > 0)
                        {{ $currencySymbol }}{{ number_format($booking->hourly_rate, 2) }}/hr
                    @else
                        Free
                    @endif
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Total Charge</span>
                <span class="detail-value" style="font-weight:700;">
                    @if($booking->total_amount > 0)
                        {{ $currencySymbol }}{{ number_format($booking->total_amount, 2) }}
                    @else
                        Free
                    @endif
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Platform Share ({{ number_format(100 - $booking->commission_rate, 2) }}%)</span>
                <span class="detail-value" style="color: #b45309;">
                    {{ $currencySymbol }}{{ number_format($booking->commission_amount, 2) }}
                </span>
            </div>
            <div class="detail-row" style="border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 12px;">
                <span class="detail-label" style="font-weight:700;">{{ ucfirst($booking->provider_type) }} Earnings ({{ $booking->commission_rate }}%)</span>
                <span class="detail-value" style="color: #059669; font-size: 16px; font-weight: 800;">
                    {{ $currencySymbol }}{{ number_format($booking->provider_amount, 2) }}
                </span>
            </div>

            @if($booking->stripe_charge_id)
            <div class="detail-row" style="font-size: 12px;">
                <span class="detail-label">Charge ID</span>
                <span class="detail-value" style="font-family: monospace; font-size:11px;">{{ $booking->stripe_charge_id }}</span>
            </div>
            @endif
            @if($booking->stripe_refund_id)
            <div class="detail-row" style="font-size: 12px; color: #dc2626;">
                <span class="detail-label">Refund ID</span>
                <span class="detail-value" style="font-family: monospace; font-size:11px;">{{ $booking->stripe_refund_id }}</span>
            </div>
            @endif
        </div>

    </div>

    {{-- Bottom Action Row --}}
    <div style="margin-top: 24px; max-width: 500px;">
        
        {{-- Payout Handler --}}
        <div class="detail-card">
            <div class="detail-title"><i class="fa-solid fa-money-bill-transfer"></i> Payout Status</div>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 16px;">
                <span class="detail-label">Payout Status</span>
                <span class="badge-payout {{ $booking->payout_status }}" style="font-size:11px; padding:4px 10px;">{{ ucfirst($booking->payout_status) }}</span>
            </div>
            
            @if($booking->payout_status === 'paid')
                <div style="margin-top: 12px; padding: 12px; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 8px; color: #065f46; font-size: 13px; font-weight: 500;">
                    <i class="fa-solid fa-circle-check"></i> Paid {{ $currencySymbol }}{{ number_format($booking->provider_amount, 2) }} to {{ ucfirst($booking->provider_type) }} <strong>{{ $booking->provider->name }}</strong> manually.
                </div>
                <p style="font-size: 12px; color: #64748b; margin-top: 8px; margin-bottom: 0;">
                    Paid on {{ $booking->payout_paid_at?->format('M d, Y H:i') }}
                </p>
            @else
                <div style="margin-top: 12px; margin-bottom: 16px; padding: 12px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; color: #92400e; font-size: 13px; font-weight: 500;">
                    <i class="fa-solid fa-circle-info"></i> Please pay {{ $currencySymbol }}{{ number_format($booking->provider_amount, 2) }} to {{ ucfirst($booking->provider_type) }} <strong>{{ $booking->provider->name }}</strong> manually.
                </div>
                @if($booking->isAccepted())
                    <form action="{{ route('admin.bookings.mark-paid', $booking) }}" method="POST" class="admin-form confirm-form" style="margin-top:0;"
                          data-title="Mark Payout as Paid" data-text="Are you sure you want to mark this payout as Paid to the Mentor/Coach?" data-confirm-text="Yes, mark paid" data-confirm-color="#059669">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="admin-button" style="width:100%; height:42px; display:inline-flex; align-items:center; justify-content:center; gap:8px;">
                            <i class="fa-solid fa-check"></i> Mark as Paid
                        </button>
                    </form>
                @else
                    <p style="font-size: 12px; color: #64748b; line-height: 1.4; margin-top: 8px;">
                        Payout is pending and can only be marked as Paid after the booking is <strong>Accepted</strong> by the Mentor/Coach.
                    </p>
                @endif
            @endif
        </div>

    </div>

</section>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.confirm-form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const title = form.getAttribute('data-title') || 'Are you sure?';
            const text = form.getAttribute('data-text') || 'Do you want to proceed?';
            const confirmBtnText = form.getAttribute('data-confirm-text') || 'Yes, proceed';
            const confirmColor = form.getAttribute('data-confirm-color') || '#5DA44E';
            
            Swal.fire({
                title: title,
                text: text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: confirmColor,
                cancelButtonColor: '#6b7280',
                confirmButtonText: confirmBtnText,
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
