<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InviteMember extends Mailable
{
    use Queueable, SerializesModels;

    public $companyName;

    public $adminName;

    public $expiry;

    public $joinLink;

    public $customMessage;

    /**
     * Create a new message instance.
     */
    public function __construct(string $companyName, string $adminName, string $expiry, string $joinLink, ?string $customMessage)
    {
        $this->companyName = $companyName;
        $this->adminName = $adminName;
        $this->expiry = $expiry;
        $this->joinLink = $joinLink;
        $this->customMessage = $customMessage;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Invitation to join {$this->companyName} on WorkHub",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.invite',
        );
    }
}
