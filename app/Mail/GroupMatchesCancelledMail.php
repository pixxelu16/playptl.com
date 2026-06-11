<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GroupMatchesCancelledMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 60;

    public function __construct(
        public string $recipientDisplayName,
        public string $leagueName,
        public string $divisionName,
        public string $groupName,
        public int $cancelledMatchCount,
        public bool $divisionWideCancel = false,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Group matches cancelled — '.$this->leagueName,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.group-matches-cancelled',
        );
    }
}
