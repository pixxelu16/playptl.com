@extends('layouts.admin')

@section('title', 'Payment History | '.config('app.name', 'playptl'))
@section('meta_description', 'View all payment transactions.')

@section('content')
    <section class="admin-card">
        <div class="admin-page-header">
            <div>
                <h1 class="admin-card-title">Payment History</h1>
                <p class="admin-card-text">Track payments made by players.</p>
            </div>
        </div>

        <div class="admin-table-wrap" style="margin-bottom: 14px;">
            <form method="GET" action="{{ route('admin.payment-histories.index') }}" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
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
                    <label class="admin-label" for="league_id">Tournament</label>
                    <select class="admin-input" name="league_id" id="league_id">
                        <option value="" @selected($leagueId === null)>All</option>
                        @foreach ($leagues as $l)
                            <option value="{{ $l->id }}" @selected($leagueId === $l->id)>{{ $l->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <button class="admin-button admin-button-link" type="submit">
                        <i class="fa-solid fa-filter" aria-hidden="true"></i>
                        <span>Filter</span>
                    </button>
                    <a class="admin-button admin-button-secondary" href="{{ route('admin.payment-histories.index') }}">Reset</a>
                </div>
            </form>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Player</th>
                        <th>Tournament</th>
                        <th>Amount</th>
                        <th>Currency</th>
                        <th>Status</th>
                        <th>Transaction</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payments as $p)
                        <tr>
                            <td>{{ $p->id }}</td>
                            <td>{{ $p->created_at?->format('M d, Y H:i') ?? '-' }}</td>
                            <td>
                                @php
                                    $rawName = (string) ($p->user?->name ?? '');
                                    $displayName = trim(preg_split('/\s*&\s*/', $rawName)[0] ?? $rawName);
                                @endphp
                                <strong>{{ $displayName !== '' ? $displayName : '-' }}</strong>
                                <span style="display:block;font-size:12px;color:#6b7280;">{{ $p->user?->email ?? '' }}</span>
                            </td>
                            <td>{{ $p->league?->name ?? ('#'.$p->league_id) }}</td>
                            <td>{{ $p->amount }}</td>
                            <td>{{ $p->currency }}</td>
                            <td>{{ $p->status }}</td>
                            <td style="max-width:240px;word-break:break-all;">{{ $p->transaction_id }}</td>
                            <td>{{ $p->description ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <div class="admin-empty-state">
                                    <i class="fa-solid fa-receipt" aria-hidden="true"></i>
                                    <p>No payment history found.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($payments->hasPages())
            <div class="admin-pagination">
                @if ($payments->onFirstPage())
                    <span>Previous</span>
                @else
                    <a href="{{ $payments->previousPageUrl() }}">Previous</a>
                @endif

                <strong>Page {{ $payments->currentPage() }} of {{ $payments->lastPage() }}</strong>

                @if ($payments->hasMorePages())
                    <a href="{{ $payments->nextPageUrl() }}">Next</a>
                @else
                    <span>Next</span>
                @endif
            </div>
        @endif
    </section>
@endsection

