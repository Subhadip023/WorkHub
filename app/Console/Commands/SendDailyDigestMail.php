<?php

namespace App\Console\Commands;

use App\Mail\DailyDigestMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendDailyDigestMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:daily-digest
                            {--to= : The recipient email address (defaults to MAIL_DAILY_DIGEST_TO in .env)}
                            {--subject= : The email subject line}
                            {--body= : The email body text}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send the daily digest email to a configured recipient';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        /** @var string|null $toOption */
        $toOption = $this->option('to');

        /** @var string|null $configTo */
        $configTo = config('mail.daily_digest.to');

        $to = $toOption ?: $configTo;

        if ($to === null || $to === '') {
            $this->error('No recipient email address configured. Set MAIL_DAILY_DIGEST_TO in your .env or pass --to=email@example.com');

            return self::FAILURE;
        }

        /** @var string $subjectOption */
        $subjectOption = $this->option('subject') ?: config('mail.daily_digest.subject', 'WorkHub — Daily Digest');

        /** @var string $bodyOption */
        $bodyOption = $this->option('body') ?: config('mail.daily_digest.body', 'This is your daily digest from WorkHub.');

        $this->info("Sending daily digest to: {$to}");

        Mail::to($to)->send(new DailyDigestMail($subjectOption, $bodyOption));

        $this->info('Daily digest email sent successfully.');

        return self::SUCCESS;
    }
}
