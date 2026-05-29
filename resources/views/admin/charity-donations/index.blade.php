@extends('layouts.admin')

@section('title', 'Charity Donations | '.config('app.name', 'playptl'))
@section('meta_description', 'View all charity donations.')

@section('content')
    <section class="admin-card">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-card-title">Charity Donations</h1>
                <p class="admin-card-text">Donations received from the charity page.</p>
            </div>
            <div class="admin-page-header-actions">
                <span class="admin-badge admin-badge-success">
                    Total raised: ${{ number_format((float) $totalCompleted, 2) }}
                </span>
            </div>
        </div>

        <div class="admin-table-wrap" style="margin-bottom: 14px;">
            <form method="GET" action="{{ route('admin.charity-donations.index') }}" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
                <div>
                    <label class="admin-label" for="status">Status</label>
                    <select class="admin-input" name="status" id="status">
                        <option value="" @selected($status === '')>All</option>
                        <option value="completed" @selected($status === 'completed')>Completed</option>
                        <option value="pending" @selected($status === 'pending')>Pending</option>
                        <option value="failed" @selected($status === 'failed')>Failed</option>
                    </select>
                </div>
                <div>
                    <button class="admin-button admin-button-link" type="submit">
                        <i class="fa-solid fa-filter" aria-hidden="true"></i>
                        <span>Filter</span>
                    </button>
                    <a class="admin-button admin-button-secondary" href="{{ route('admin.charity-donations.index') }}">Reset</a>
                </div>
            </form>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Donor</th>
                        <th>Address</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Transaction</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($donations as $d)
                        <tr>
                            <td>{{ $d->id }}</td>
                            <td>{{ $d->created_at?->format('M d, Y H:i') ?? '-' }}</td>
                            <td>
                                <strong>{{ $d->donor_name }}</strong>
                                @if ($d->email)
                                    <span style="display:block;font-size:12px;color:#6b7280;">{{ $d->email }}</span>
                                @endif
                                @if ($d->user)
                                    <span style="display:block;font-size:11px;color:#9ca3af;">Registered user #{{ $d->user_id }}</span>
                                @endif
                            </td>
                            <td style="max-width:220px;">
                                {{ $d->address }}<br>
                                <span style="font-size:12px;color:#6b7280;">{{ $d->city }}, {{ $d->state }} {{ $d->zip }}</span>
                            </td>
                            <td>${{ number_format((float) $d->amount, 2) }} {{ $d->currency }}</td>
                            <td>{{ $d->status }}</td>
                            <td style="max-width:240px;word-break:break-all;">{{ $d->transaction_id ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="admin-empty-state">
                                    <i class="fa-solid fa-hand-holding-heart" aria-hidden="true"></i>
                                    <p>No charity donations found.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($donations->hasPages())
            <div class="admin-pagination">
                @if ($donations->onFirstPage())
                    <span>Previous</span>
                @else
                    <a href="{{ $donations->previousPageUrl() }}">Previous</a>
                @endif

                <strong>Page {{ $donations->currentPage() }} of {{ $donations->lastPage() }}</strong>

                @if ($donations->hasMorePages())
                    <a href="{{ $donations->nextPageUrl() }}">Next</a>
                @else
                    <span>Next</span>
                @endif
            </div>
        @endif
    </section>
@endsection
