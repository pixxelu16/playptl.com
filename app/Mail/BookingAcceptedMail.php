<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingAcceptedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Booking $booking,
        public string $recipientRole = 'student'
    ) {}

    public function envelope(): Envelope
    {
        $subject = match ($this->recipientRole) {
            'provider' => 'Booking Confirmed (You Accepted) — ' . $this->booking->student->name,
            'admin' => 'Booking Confirmed (Accepted) — ' . $this->booking->student->name . ' with ' . $this->booking->provider->name,
            default => 'Booking Accepted — ' . $this->booking->provider->name,
        };

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.booking-accepted',
        );
    }
}
