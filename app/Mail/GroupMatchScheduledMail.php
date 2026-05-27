<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GroupMatchScheduledMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $recipientDisplayName,
        public string $leagueName,
        public string $divisionName,
        public string $groupName,
        public string $matchDateDisplay,
        public string $startTime,
        public string $venueDisplay,
        public string $formatLabel,
        public string $opponentSummary,
        public bool $updatedByOpponent = false,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->updatedByOpponent
                ? 'Match schedule updated — '.$this->leagueName
                : 'Your match is scheduled — '.$this->leagueName,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.group-match-scheduled',
        );
    }
}
