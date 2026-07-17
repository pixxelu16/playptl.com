<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Booking $booking,
        public string $recipientRole = 'student'
    ) {}

    public function envelope(): Envelope
    {
        $subject = match ($this->recipientRole) {
            'provider' => 'Booking Declined (You Declined) — ' . $this->booking->student->name,
            'admin' => 'Booking Declined — ' . $this->booking->student->name . ' with ' . $this->booking->provider->name,
            default => 'Booking Declined — ' . $this->booking->provider->name,
        };

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.booking-rejected',
        );
    }
}
