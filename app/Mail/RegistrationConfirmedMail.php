<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RegistrationConfirmedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $userName,
        public string $leagueName,
        public string $registrationType,
        public string $skillLevel,
        public string $amount,
        public string $currency,
        public string $paymentIntentId,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tournament registration confirmed',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.registration-confirmed',
        );
    }
}

