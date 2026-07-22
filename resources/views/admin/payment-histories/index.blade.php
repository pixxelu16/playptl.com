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
                        <th>Player / Student</th>
                        <th>Type / Tournament</th>
                        <th>Amount</th>
                        <th>Currency</th>
                        <th>Status</th>
                        <th>Transaction</th>
                        <th>Description</th>
                        <th style="text-align:right;">Action</th>
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
                            <td>
                                @if($p->league_id)
                                    <span style="font-weight:600;color:#1e40af;">Tournament Entry</span>
                                    <span style="display:block;font-size:11px;color:#4b5563;">{{ $p->league?->name ?? ('#'.$p->league_id) }}</span>
                                @else
                                    <span style="font-weight:600;color:#047857;">Session Booking</span>
                                    <span style="display:block;font-size:11px;color:#6b7280;">{{ $p->meta['provider_type'] ?? 'Provider' }} Session</span>
                                @endif
                            </td>
                            <td><strong>{{ $p->amount }}</strong></td>
                            <td>{{ $p->currency }}</td>
                            <td>
                                <span class="admin-badge {{ $p->status === 'completed' ? 'admin-badge-success' : 'admin-badge-warning' }}">
                                    {{ $p->status }}
                                </span>
                            </td>
                            <td style="max-width:180px;word-break:break-all;font-family:monospace;font-size:12px;">{{ $p->transaction_id }}</td>
                            <td>{{ $p->description ?? '-' }}</td>
                            <td style="text-align:right;">
                                <button type="button" class="admin-button admin-button-secondary" onclick="showDetails(this)" data-payment='@json($p)'>
                                    Details
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10">
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

    {{-- Payment Details Modal --}}
    <div id="payment-details-modal" class="admin-modal" hidden aria-hidden="true">
        <button type="button" class="admin-modal-backdrop" onclick="closeDetailsModal()" aria-label="Close"></button>
        <div class="admin-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="payment-details-title" style="max-width: 580px;">
            <h2 id="payment-details-title" class="admin-modal-title">Payment Transaction Details</h2>
            
            <div id="modal-details-body" class="admin-form" style="margin-top: 16px; font-size:14px; line-height:1.6;">
                {{-- Dynamic Details will be populated here --}}
            </div>

            <div class="admin-modal-actions" style="margin-top: 24px;">
                <button type="button" class="admin-button admin-button-secondary" onclick="closeDetailsModal()">Close</button>
            </div>
        </div>
    </div>

    <script>
        function showDetails(btn) {
            const p = JSON.parse(btn.getAttribute('data-payment'));
            const body = document.getElementById('modal-details-body');
            
            let metaHtml = '';
            if (p.meta) {
                metaHtml += `<h3 style="font-weight:700;margin-top:16px;margin-bottom:8px;font-size:13px;text-transform:uppercase;color:#4b5563;letter-spacing:0.05em;border-bottom:1px solid #e5e7eb;padding-bottom:4px;">Metadata & Breakdown</h3>`;
                metaHtml += `<div style="display:grid;grid-template-cols:1fr 1fr;gap:8px;background:#f9fafb;padding:12px;border-radius:8px;border:1px solid #f3f4f6;">`;
                for (const [key, val] of Object.entries(p.meta)) {
                    const cleanKey = key.replace(/_/g, ' ');
                    metaHtml += `<div style="text-transform:capitalize;color:#6b7280;font-size:12px;">${cleanKey}:</div>`;
                    metaHtml += `<div style="font-weight:600;color:#111827;font-size:12px;text-align:right;">${val}</div>`;
                }
                metaHtml += `</div>`;
            }

            body.innerHTML = `
                <div style="display:grid;grid-template-columns:1fr 1.5fr;gap:10px;border-bottom:1px solid #f3f4f6;padding-bottom:12px;margin-bottom:12px;">
                    <div style="color:#6b7280;">Transaction ID:</div>
                    <div style="font-weight:700;font-family:monospace;word-break:break-all;">${p.transaction_id || '-'}</div>
                    <div style="color:#6b7280;">Description:</div>
                    <div style="font-weight:600;">${p.description || '-'}</div>
                    <div style="color:#6b7280;">Amount / Currency:</div>
                    <div style="font-weight:700;color:#047857;font-size:16px;">${p.amount} ${p.currency}</div>
                    <div style="color:#6b7280;">Payment Status:</div>
                    <div><span class="admin-badge ${p.status === 'completed' ? 'admin-badge-success' : 'admin-badge-warning'}" style="text-transform:uppercase;">${p.status}</span></div>
                    <div style="color:#6b7280;">Processed At:</div>
                    <div style="font-weight:600;">${new Date(p.created_at).toLocaleString('en-US', {dateStyle:'medium', timeStyle:'short'})}</div>
                </div>
                ${metaHtml}
            `;
            
            const modal = document.getElementById('payment-details-modal');
            modal.removeAttribute('hidden');
            modal.removeAttribute('aria-hidden');
        }

        function closeDetailsModal() {
            const modal = document.getElementById('payment-details-modal');
            modal.setAttribute('hidden', 'true');
            modal.setAttribute('aria-hidden', 'true');
        }
    </script>
@endsection

