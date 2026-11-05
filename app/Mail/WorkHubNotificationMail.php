<?php

namespace App\Mail;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WorkHubNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public Notification $notification;

    public string $actionUrl;

    public ?string $workspaceName;

    public ?User $recipient;

    /**
     * Create a new message instance.
     */
    public function __construct(
        Notification $notification,
        ?string $actionUrl = null,
        ?string $workspaceName = null,
        ?User $recipient = null
    ) {
        $this->notification = $notification;
        $this->actionUrl = $actionUrl ?? ($notification->data['url'] ?? url('/dashboard'));
        $this->workspaceName = $workspaceName;
        $this->recipient = $recipient ?? $notification->user;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subjectPrefix = $this->workspaceName ? "[{$this->workspaceName}] " : '[WorkHub] ';

        return new Envelope(
            subject: $subjectPrefix.$this->notification->title,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.notification',
        );
    }
}
