<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingRequestedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Booking $booking,
        public string $recipientRole = 'provider'
    ) {}

    public function envelope(): Envelope
    {
        $subject = match ($this->recipientRole) {
            'student' => 'Booking Request Submitted — ' . $this->booking->provider->name,
            'admin' => 'New Booking Request — ' . $this->booking->student->name . ' with ' . $this->booking->provider->name,
            default => 'New Booking Request — ' . $this->booking->student->name,
        };

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.booking-requested',
        );
    }
}
