<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserRegisteredMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public bool $isAdminNotification = false
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->isAdminNotification
            ? 'New User Registered — ' . $this->user->name
            : 'Welcome to ' . config('app.name', 'Premier Tennis League') . ' — Account Confirmed';

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.user-registered',
        );
    }
}
