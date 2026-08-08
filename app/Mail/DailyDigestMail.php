<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class DailyDigestMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $emailSubject;

    public string $emailBody;

    public ?User $recipient;

    public LengthAwarePaginator|Collection|array $todayTasks;

    public array $counts;

    public string $dashboardUrl;

    public function __construct(
        string $subject,
        string $body,
        ?User $recipient = null,
        mixed $todayTasks = null,
        array $counts = [],
        ?string $dashboardUrl = null
    ) {
        $this->emailSubject = $subject;
        $this->emailBody = $body;
        $this->recipient = $recipient;
        $this->todayTasks = $todayTasks ?? collect();
        $this->counts = $counts;
        $this->dashboardUrl = $dashboardUrl ?? url('/dashboard?task_filter=today_past&per_page=5');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->emailSubject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.daily-digest',
        );
    }
}
