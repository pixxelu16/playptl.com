<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProviderApplicationReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user
    ) {}

    public function envelope(): Envelope
    {
        $roleName = ucfirst($this->user->role instanceof \App\Enums\UserRole ? $this->user->role->value : (string) $this->user->role);
        return new Envelope(
            subject: "Application Received - {$roleName} Registration under Review",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.provider-application-received',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
