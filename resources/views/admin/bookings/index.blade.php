@extends('layouts.admin')

@section('title', 'Bookings | Admin | ' . config('app.name'))
@section('meta_description', 'Manage all mentor and coach bookings from the admin panel.')

@push('styles')
<style>
    /* Styling stats to match theme design */
    .admin-booking-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 16px;
        margin-top: 20px;
        margin-bottom: 28px;
    }
    .booking-stat-card {
        background-color: #fff;
        border: 1px solid #E0E0E0;
        border-radius: 8px;
        padding: 20px 16px;
        display: flex;
        align-items: center;
        gap: 16px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .booking-stat-icon {
        font-size: 28px;
        width: 48px;
        height: 48px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .booking-stat-info {
        flex: 1;
        text-align: left;
    }
    .booking-stat-number {
        font-size: 24px;
        font-weight: 800;
        color: #333;
        line-height: 1.2;
    }
    .booking-stat-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #6b7280;
        margin-top: 2px;
    }

    /* Card customization theme colors */
    .booking-stat-card.total .booking-stat-icon { background: #f3f4f6; color: #4b5563; }
    .booking-stat-card.pending { border-left: 4px solid #f59e0b; }
    .booking-stat-card.pending .booking-stat-icon { background: #fffbeb; color: #d97706; }
    .booking-stat-card.accepted { border-left: 4px solid #10b981; }
    .booking-stat-card.accepted .booking-stat-icon { background: #ecfdf5; color: #059669; }
    .booking-stat-card.rejected { border-left: 4px solid #ef4444; }
    .booking-stat-card.rejected .booking-stat-icon { background: #fef2f2; color: #dc2626; }
    .booking-stat-card.cancelled { border-left: 4px solid #6b7280; }
    .booking-stat-card.cancelled .booking-stat-icon { background: #f9fafb; color: #4b5563; }

    /* Badge styles matching tables */
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

    .badge-payout {
        display: inline-block;
        padding: 2px 8px;
        font-size: 10px;
        font-weight: 700;
        border-radius: 4px;
        text-transform: uppercase;
    }
    .badge-payout.unpaid { background: #f3f4f6; color: #4b5563; }
    .badge-payout.paid { background: #d1fae5; color: #059669; }
</style>
@endpush

@section('content')
    <section class="admin-card">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-card-title">Booking Management</h1>
                <p class="admin-card-text">View, filter, and manage all student booking requests.</p>
            </div>
        </div>

        {{-- Stats Cards with top margin and icons --}}
        <div class="admin-booking-stats">
            <div class="booking-stat-card total">
                <div class="booking-stat-icon"><i class="fa-solid fa-calendar-check"></i></div>
                <div class="booking-stat-info">
                    <div class="booking-stat-number">{{ $stats['total'] }}</div>
                    <div class="booking-stat-label">Total Bookings</div>
                </div>
            </div>
            <div class="booking-stat-card pending">
                <div class="booking-stat-icon"><i class="fa-solid fa-clock-rotate-left"></i></div>
                <div class="booking-stat-info">
                    <div class="booking-stat-number" style="color: #d97706;">{{ $stats['pending'] }}</div>
                    <div class="booking-stat-label">Pending</div>
                </div>
            </div>
            <div class="booking-stat-card accepted">
                <div class="booking-stat-icon"><i class="fa-solid fa-circle-check"></i></div>
                <div class="booking-stat-info">
                    <div class="booking-stat-number" style="color: #059669;">{{ $stats['accepted'] }}</div>
                    <div class="booking-stat-label">Accepted</div>
                </div>
            </div>
            <div class="booking-stat-card rejected">
                <div class="booking-stat-icon"><i class="fa-solid fa-circle-xmark"></i></div>
                <div class="booking-stat-info">
                    <div class="booking-stat-number" style="color: #dc2626;">{{ $stats['rejected'] }}</div>
                    <div class="booking-stat-label">Rejected</div>
                </div>
            </div>
            <div class="booking-stat-card cancelled">
                <div class="booking-stat-icon"><i class="fa-solid fa-ban"></i></div>
                <div class="booking-stat-info">
                    <div class="booking-stat-number" style="color: #4b5563;">{{ $stats['cancelled'] }}</div>
                    <div class="booking-stat-label">Cancelled</div>
                </div>
            </div>
        </div>

        {{-- Filters block --}}
        <div class="admin-table-wrap" style="margin-bottom: 20px; padding: 16px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
            <form method="GET" action="{{ route('admin.bookings.index') }}" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end;">
                <div style="flex: 1; min-width: 200px;">
                    <label class="admin-label" for="search">Search</label>
                    <input class="admin-input" type="text" name="search" id="search" value="{{ $search }}" placeholder="Search student or provider name...">
                </div>
                <div>
                    <label class="admin-label" for="status">Status</label>
                    <select class="admin-input" name="status" id="status">
                        <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All Statuses</option>
                        @foreach(['pending', 'accepted', 'rejected', 'cancelled'] as $s)
                            <option value="{{ $s }}" {{ $status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="admin-label" for="provider_type">Provider Type</label>
                    <select class="admin-input" name="provider_type" id="provider_type">
                        <option value="all" {{ $providerType === 'all' ? 'selected' : '' }}>All Types</option>
                        <option value="mentor" {{ $providerType === 'mentor' ? 'selected' : '' }}>Mentor</option>
                        <option value="coach" {{ $providerType === 'coach' ? 'selected' : '' }}>Coach</option>
                    </select>
                </div>
                <div style="display: flex; gap: 8px;">
                    <button class="admin-button" type="submit" style="height: 42px;">
                        <i class="fa-solid fa-filter" aria-hidden="true"></i>
                        <span>Filter</span>
                    </button>
                    <a class="admin-button admin-button-secondary" href="{{ route('admin.bookings.index') }}" style="height: 42px; display: inline-flex; align-items: center; justify-content: center; text-decoration: none;">Reset</a>
                </div>
            </form>
        </div>

        {{-- Main Table --}}
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Provider</th>
                        <th>Type</th>
                        <th>Dates</th>
                        <th style="text-align: right;">Amount</th>
                        <th style="text-align: right;">Platform Commission</th>
                        <th style="text-align: center;">Status</th>
                        <th style="text-align: center;">Payout</th>
                        <th style="text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $booking)
                    <tr>
                        <td>
                            <strong>{{ $booking->student->name }}</strong>
                            <span style="display:block;font-size:12px;color:#6b7280;">{{ $booking->student->email }}</span>
                        </td>
                        <td>
                            <strong>{{ $booking->provider->name }}</strong>
                            <span style="display:block;font-size:12px;color:#6b7280;">{{ $booking->provider->email }}</span>
                        </td>
                        <td class="capitalize">{{ $booking->provider_type }}</td>
                        <td>
                            <div style="font-weight: 600;">{{ $booking->from_date->format('M d, Y') }}</div>
                            <div style="font-size: 11px; color: #6b7280;">to {{ $booking->to_date->format('M d, Y') }}</div>
                            @if($booking->booking_time)
                                <div style="font-size: 11px; color: #5DA44E; font-weight: 600; margin-top: 2px;">
                                    <i class="fa-solid fa-clock"></i> {{ Carbon\Carbon::parse($booking->booking_time)->format('g:i A') }}
                                </div>
                            @endif
                        </td>
                        <td style="text-align: right; font-weight: 700; color: #333;">
                            @if($booking->total_amount > 0)
                                {{ $currencySymbol }}{{ number_format($booking->total_amount, 2) }}
                            @else
                                <span style="color: #10b981;">Free</span>
                            @endif
                        </td>
                        <td style="text-align: right; color: #b45309; font-weight: 600;">
                            {{ $currencySymbol }}{{ number_format($booking->commission_amount, 2) }}
                        </td>
                        <td style="text-align: center;">
                            <span class="badge-status {{ $booking->status }}">{{ $booking->statusLabel() }}</span>
                        </td>
                        <td style="text-align: center;">
                            <span class="badge-payout {{ $booking->payout_status }}">{{ ucfirst($booking->payout_status) }}</span>
                        </td>
                        <td style="text-align: center;">
                            <div class="admin-table-actions" style="justify-content: center;">
                                <a href="{{ route('admin.bookings.show', $booking) }}" title="View details">
                                    <i class="fa-solid fa-eye" aria-hidden="true"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="admin-muted" style="text-align: center; padding: 40px 10px;">
                            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px;">
                                <i class="fa-solid fa-calendar-xmark" style="font-size: 32px; color: #9ca3af;"></i>
                                <p>No bookings found.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($bookings->hasPages())
            <div class="admin-pagination">
                @if ($bookings->onFirstPage())
                    <span>Previous</span>
                @else
                    <a href="{{ $bookings->previousPageUrl() }}">Previous</a>
                @endif

                <strong>Page {{ $bookings->currentPage() }} of {{ $bookings->lastPage() }}</strong>

                @if ($bookings->hasMorePages())
                    <a href="{{ $bookings->nextPageUrl() }}">Next</a>
                @else
                    <span>Next</span>
                @endif
            </div>
        @endif
    </section>
@endsection
