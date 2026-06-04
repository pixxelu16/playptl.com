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

        @if (session('status'))
            <div class="admin-alert admin-alert-success">{{ session('status') }}</div>
        @endif
        @if (session('error'))
            <div class="admin-alert admin-alert-error">{{ session('error') }}</div>
        @endif

        <div class="admin-table-wrap" style="margin-bottom: 14px;">
            <form method="GET" action="{{ route('admin.charity-donations.index') }}" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
                <div>
                    <label class="admin-label" for="status">Status</label>
                    <select class="admin-input" name="status" id="status">
                        <option value="" @selected($status === '')>All</option>
                        <option value="completed" @selected($status === 'completed')>Completed</option>
                        <option value="submitted" @selected($status === 'submitted')>Submitted</option>
                        <option value="pending" @selected($status === 'pending')>Pending</option>
                        <option value="failed" @selected($status === 'failed')>Failed</option>
                    </select>
                </div>
                <div>
                    <label class="admin-label" for="type">Type</label>
                    <select class="admin-input" name="type" id="type">
                        <option value="" @selected($type === '')>All</option>
                        <option value="money" @selected($type === 'money')>Money</option>
                        <option value="material" @selected($type === 'material')>Material</option>
                        <option value="person" @selected($type === 'person')>Person</option>
                    </select>
                </div>
                <div>
                    <button class="admin-button admin-button-link" type="submit">
                        <i class="fa-solid fa-filter" aria-hidden="true"></i>
                        <span>Filter</span>
                    </button>
                    <a class="admin-button admin-button-secondary" href="{{ route('admin.charity-donations.index') }}">Reset</a>
                    <button
                        type="button"
                        class="admin-button admin-button-link"
                        id="charity-donations-email-open"
                    >
                        <i class="fa-solid fa-envelope" aria-hidden="true"></i>
                        <span>Send Email</span>
                    </button>
                </div>
            </form>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Cause</th>
                        <th>Donor</th>
                        <th>Details</th>
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
                            <td>{{ ucfirst((string) ($d->donation_type ?? 'money')) }}</td>
                            <td>{{ $d->charityCause?->title ?? ($d->meta['charity_cause_title'] ?? '-') }}</td>
                            <td>
                                <strong>{{ $d->donor_name }}</strong>
                                @if ($d->email)
                                    <span style="display:block;font-size:12px;color:#6b7280;">{{ $d->email }}</span>
                                @endif
                                @if ($d->phone)
                                    <span style="display:block;font-size:12px;color:#6b7280;">{{ $d->phone }}</span>
                                @endif
                                @if ($d->user)
                                    <span style="display:block;font-size:11px;color:#9ca3af;">Registered user #{{ $d->user_id }}</span>
                                @endif
                            </td>
                            <td style="max-width:240px;">
                                @if ($d->donation_type === 'material')
                                    <span>{{ $d->material_detail ?: 'Material' }}</span><br>
                                    <span style="font-size:12px;color:#6b7280;">Qty: {{ number_format((float) ($d->quantity ?? 0), 2) }}</span>
                                @elseif ($d->donation_type === 'person')
                                    <span style="font-size:12px;color:#6b7280;">Volunteers: {{ number_format((float) ($d->quantity ?? 0), 0) }}</span>
                                @else
                                    {{ $d->address }}<br>
                                    <span style="font-size:12px;color:#6b7280;">{{ $d->city }}, {{ $d->state }} {{ $d->zip }}</span>
                                @endif
                            </td>
                            <td>
                                @if ($d->donation_type === 'money')
                                    ${{ number_format((float) $d->amount, 2) }} {{ $d->currency }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $d->status }}</td>
                            <td style="max-width:240px;word-break:break-all;">{{ $d->transaction_id ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
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

    <div id="charity-donations-email-modal" class="admin-modal" hidden aria-hidden="true">
        <button type="button" class="admin-modal-backdrop" data-charity-email-modal-close aria-label="Close"></button>
        <div class="admin-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="charity-donations-email-title">
            <h2 id="charity-donations-email-title" class="admin-modal-title">Send Email to Donors</h2>
            <p id="charity-donations-email-summary" class="admin-modal-footer-note" style="margin-top: -8px;">
                {{ $filterSummary }} — <strong id="charity-donations-email-count">{{ $emailRecipientCount }}</strong>
                <span id="charity-donations-email-count-label">{{ Str::plural('recipient', $emailRecipientCount) }}</span> with email (all matching donations, not only this page).
            </p>

            <form method="POST" action="{{ route('admin.charity-donations.send-email') }}" class="admin-form" style="margin-top: 16px;">
                @csrf
                <input type="hidden" name="status" value="{{ $status }}">
                <input type="hidden" name="type" value="{{ $type }}">

                <div class="admin-form-group">
                    <label class="admin-label" for="charity-email-subject">Subject</label>
                    <input
                        class="admin-input"
                        id="charity-email-subject"
                        type="text"
                        name="subject"
                        value="{{ old('subject', 'Message from '.config('app.name', 'Premier Tennis League')) }}"
                        maxlength="255"
                    >
                </div>

                <div class="admin-form-group">
                    <label class="admin-label" for="charity-email-message">Message <span style="color:#dc2626;">*</span></label>
                    <textarea
                        class="admin-input admin-textarea"
                        id="charity-email-message"
                        name="message"
                        rows="8"
                        required
                        minlength="10"
                        maxlength="5000"
                        placeholder="Write your message to donors..."
                    >{{ old('message') }}</textarea>
                </div>

                <div class="admin-modal-actions">
                    <button type="button" class="admin-modal-btn-cancel" data-charity-email-modal-close>Cancel</button>
                    <button type="submit" class="admin-modal-btn-primary" id="charity-donations-email-submit">
                        <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
                        <span id="charity-donations-email-submit-label">Send to {{ $emailRecipientCount }} {{ Str::plural('donor', $emailRecipientCount) }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            (function () {
                var openBtn = document.getElementById('charity-donations-email-open');
                var modal = document.getElementById('charity-donations-email-modal');
                var statusSelect = document.getElementById('status');
                var typeSelect = document.getElementById('type');
                var countUrl = @json(route('admin.charity-donations.email-recipient-count'));
                if (!openBtn || !modal) return;

                function syncFilterInputs() {
                    var statusInput = modal.querySelector('input[name="status"]');
                    var typeInput = modal.querySelector('input[name="type"]');
                    if (statusSelect && statusInput) statusInput.value = statusSelect.value;
                    if (typeSelect && typeInput) typeInput.value = typeSelect.value;
                }

                function updateRecipientPreview() {
                    syncFilterInputs();
                    var statusInput = modal.querySelector('input[name="status"]');
                    var typeInput = modal.querySelector('input[name="type"]');
                    var params = new URLSearchParams();
                    if (statusInput && statusInput.value) params.set('status', statusInput.value);
                    if (typeInput && typeInput.value) params.set('type', typeInput.value);

                    fetch(countUrl + '?' + params.toString(), {
                        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    })
                        .then(function (r) { return r.json(); })
                        .then(function (data) {
                            var count = data.count || 0;
                            var countEl = document.getElementById('charity-donations-email-count');
                            var countLabel = document.getElementById('charity-donations-email-count-label');
                            var submitBtn = document.getElementById('charity-donations-email-submit');
                            var submitLabel = document.getElementById('charity-donations-email-submit-label');

                            if (countEl) countEl.textContent = String(count);
                            if (countLabel) countLabel.textContent = count === 1 ? 'recipient' : 'recipients';
                            if (submitLabel) submitLabel.textContent = 'Send to ' + count + ' ' + (count === 1 ? 'donor' : 'donors');
                            if (submitBtn) submitBtn.disabled = count === 0;
                        })
                        .catch(function () {});
                }

                function openModal() {
                    updateRecipientPreview();
                    modal.hidden = false;
                    modal.setAttribute('aria-hidden', 'false');
                    modal.classList.add('is-open');
                    document.body.classList.add('admin-modal-open');
                    var message = document.getElementById('charity-email-message');
                    if (message) message.focus();
                }

                function closeModal() {
                    modal.hidden = true;
                    modal.setAttribute('aria-hidden', 'true');
                    modal.classList.remove('is-open');
                    document.body.classList.remove('admin-modal-open');
                }

                openBtn.addEventListener('click', openModal);
                if (statusSelect) statusSelect.addEventListener('change', function () {
                    if (modal.classList.contains('is-open')) updateRecipientPreview();
                });
                if (typeSelect) typeSelect.addEventListener('change', function () {
                    if (modal.classList.contains('is-open')) updateRecipientPreview();
                });
                document.querySelectorAll('[data-charity-email-modal-close]').forEach(function (btn) {
                    btn.addEventListener('click', closeModal);
                });
                document.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape' && modal.classList.contains('is-open')) closeModal();
                });
            })();
        </script>
    @endpush
@endsection
