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
            <h1 class="admin-card-title">Booking #{{ $booking->id }}</h1>
            <p class="admin-card-text">
                Manage status, view transaction logs, and process payouts for this booking.
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
    <div class="booking-detail-grid" style="margin-top: 24px; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));">
        
        {{-- Status Handler --}}
        <div class="detail-card">
            <div class="detail-title"><i class="fa-solid fa-toggle-on"></i> Booking Status</div>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 16px;">
                <span class="detail-label">Current Status</span>
                <span class="badge-status {{ $booking->status }}">{{ $booking->statusLabel() }}</span>
            </div>
            <form action="{{ route('admin.bookings.update-status', $booking) }}" method="POST" class="admin-form" style="margin-top:0;">
                @csrf
                @method('PATCH')
                <div style="display:flex; gap:8px;">
                    <select name="status" class="admin-input" style="flex:1; height:46px; padding:8px 12px; font-size:14px; border: 1px solid #d7ead9; border-radius:10px;">
                        @foreach(['pending', 'accepted', 'rejected', 'cancelled'] as $s)
                            <option value="{{ $s }}" {{ $booking->status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="admin-button" style="height:46px; padding:0 20px;">Update</button>
                </div>
            </form>
        </div>

        {{-- Payout Handler --}}
        <div class="detail-card">
            <div class="detail-title"><i class="fa-solid fa-money-bill-transfer"></i> Payout Status</div>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 16px;">
                <span class="detail-label">Payout Status</span>
                <span class="badge-payout {{ $booking->payout_status }}" style="font-size:11px; padding:4px 10px;">{{ ucfirst($booking->payout_status) }}</span>
            </div>
            
            @if($booking->payout_status === 'paid')
                <p style="font-size: 13px; color: #64748b; margin-top: 8px;">
                    <i class="fa-solid fa-circle-check" style="color:#059669;"></i> Paid on {{ $booking->payout_paid_at?->format('M d, Y H:i') }}
                </p>
            @else
                @if($booking->isAccepted())
                    <form action="{{ route('admin.bookings.mark-paid', $booking) }}" method="POST" class="admin-form" style="margin-top:0;">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="admin-button" style="width:100%; height:42px; display:inline-flex; align-items:center; justify-content:center; gap:8px;">
                            <i class="fa-solid fa-check"></i> Mark as Paid
                        </button>
                    </form>
                @else
                    <p style="font-size: 12px; color: #64748b; line-height: 1.4;">
                        Payout is pending and can only be marked as Paid after the booking is <strong>Accepted</strong> by the Mentor/Coach.
                    </p>
                @endif
            @endif
        </div>

    </div>

</section>
@endsection
