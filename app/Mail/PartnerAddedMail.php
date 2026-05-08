<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PartnerAddedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $inviterName,
        public string $leagueName,
        public string $setupUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You were added to a doubles registration',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.partner-added',
        );
    }
}

