<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class CommunityInvitationMail extends Mailable
{
    // Deliberately synchronous inside the ID-only job, never serialized to a queue.
    public function __construct(public string $communityName, public string $acceptUrl) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your private community invitation');
    }

    public function content(): Content
    {
        return new Content(view: 'mail.community-invitation');
    }
}
