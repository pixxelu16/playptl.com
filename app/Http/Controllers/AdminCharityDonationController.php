<?php

namespace App\Http\Controllers;

use App\Mail\CharityDonationDonorMessageMail;
use App\Models\CharityDonation;
use App\Support\CharityFundraisingStats;
use App\Support\MatchScheduleMailQueue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminCharityDonationController extends Controller
{
    public function index(Request $request): View
    {
        $status = (string) $request->query('status', '');
        $type = (string) $request->query('type', '');

        $donations = CharityDonation::query()
            ->with(['user', 'charityCause'])
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->when($type !== '', fn ($q) => $q->where('donation_type', $type))
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        $totalCompleted = CharityFundraisingStats::current()['total_raised'];
        $emailRecipients = $this->resolveRecipients($status, $type);

        return view('admin.charity-donations.index', [
            'donations' => $donations,
            'status' => $status,
            'type' => $type,
            'totalCompleted' => $totalCompleted,
            'emailRecipientCount' => count($emailRecipients),
            'filterSummary' => $this->filterSummary($status, $type),
        ]);
    }

    public function recipientCount(Request $request): JsonResponse
    {
        $status = (string) $request->query('status', '');
        $type = (string) $request->query('type', '');

        if ($status !== '' && ! in_array($status, ['completed', 'submitted', 'pending', 'failed'], true)) {
            $status = '';
        }

        if ($type !== '' && ! in_array($type, ['money', 'material', 'person'], true)) {
            $type = '';
        }

        $count = count($this->resolveRecipients($status, $type));

        return response()->json([
            'count' => $count,
            'summary' => $this->filterSummary($status, $type),
        ]);
    }

    public function sendEmail(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['nullable', 'string', 'in:completed,submitted,pending,failed'],
            'type' => ['nullable', 'string', 'in:money,material,person'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
        ]);

        $status = (string) ($validated['status'] ?? '');
        $type = (string) ($validated['type'] ?? '');
        $recipients = $this->resolveRecipients($status, $type);

        if ($recipients === []) {
            return redirect()
                ->route('admin.charity-donations.index', $this->filterQuery($status, $type))
                ->with('error', 'No donors with a valid email address match the current filters.');
        }

        $subject = trim((string) ($validated['subject'] ?? ''));
        if ($subject === '') {
            $subject = 'Message from '.config('app.name', 'Premier Tennis League');
        }

        $message = (string) $validated['message'];
        $adminName = (string) ($request->user()?->name ?? 'Admin');

        MatchScheduleMailQueue::beginBulkScheduling();

        foreach ($recipients as $recipient) {
            MatchScheduleMailQueue::queue($recipient['email'], new CharityDonationDonorMessageMail(
                donorName: $recipient['name'],
                adminMessage: $message,
                emailSubject: $subject,
                adminName: $adminName,
            ));
        }

        $count = count($recipients);

        return redirect()
            ->route('admin.charity-donations.index', $this->filterQuery($status, $type))
            ->with('status', "Queued email for {$count} donor(s). Messages will send in the background.");
    }

    /**
     * @return array<int, array{email: string, name: string}>
     */
    private function resolveRecipients(string $status, string $type): array
    {
        $donations = CharityDonation::query()
            ->with('user')
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->when($type !== '', fn ($q) => $q->where('donation_type', $type))
            ->orderBy('id')
            ->get();

        $recipients = [];

        foreach ($donations as $donation) {
            $email = trim((string) ($donation->email ?: $donation->user?->email ?? ''));

            if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            $key = strtolower($email);

            if (! isset($recipients[$key])) {
                $recipients[$key] = [
                    'email' => $email,
                    'name' => (string) ($donation->donor_name !== '' ? $donation->donor_name : 'Donor'),
                ];
            }
        }

        return array_values($recipients);
    }

    private function filterSummary(string $status, string $type): string
    {
        $parts = [];

        $parts[] = 'Type: '.($type !== '' ? ucfirst($type) : 'All');
        $parts[] = 'Status: '.($status !== '' ? ucfirst($status) : 'All');

        return implode(' · ', $parts);
    }

    /**
     * @return array<string, string>
     */
    private function filterQuery(string $status, string $type): array
    {
        return array_filter([
            'status' => $status,
            'type' => $type,
        ]);
    }
}
