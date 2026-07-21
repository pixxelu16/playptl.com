<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminPlayerRegistrationNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $playerName,
        public string $playerEmail,
        public string $playerPhone,
        public string $leagueName,
        public string $registrationType,
        public string $skillLevel,
        public string $amount,
        public string $currency,
        public string $paymentIntentId,
        public ?string $partnerName = null,
        public ?string $partnerEmail = null,
        public ?string $partnerPhone = null,
        public ?string $partnerSkill = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Player Registration - ' . $this->playerName . ' (' . $this->leagueName . ')',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin-player-registration-notification',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
