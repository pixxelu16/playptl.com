<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminProviderApplicationNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user
    ) {}

    public function envelope(): Envelope
    {
        $roleName = ucfirst($this->user->role->value);
        return new Envelope(
            subject: "Action Required: New {$roleName} Registration - {$this->user->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin-provider-application-notification',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
