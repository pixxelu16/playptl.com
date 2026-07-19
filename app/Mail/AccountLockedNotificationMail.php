<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountLockedNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $lockedUser,
        public readonly string $unblockUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '⚠️ Account Locked: ' . $this->lockedUser->name . ' (' . $this->lockedUser->email . ')',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.account-locked-notification',
        );
    }
}
