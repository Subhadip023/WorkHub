<?php

namespace App\Console\Commands;

use App\Mail\DailyDigestMail;
use App\Models\User;
use App\Services\TaskServiceInterface;
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
                            {--to= : The recipient email address (overrides MAIL_DAILY_DIGEST_TO in .env)}
                            {--subject= : The email subject line}
                            {--body= : The email body text}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily task digest emails with today & past tasks to users';

    /**
     * Execute the console command.
     */
    public function handle(TaskServiceInterface $taskService): int
    {
        /** @var string|null $toOption */
        $toOption = $this->option('to');
        $customSubject = $this->option('subject');
        $customBody = $this->option('body');

        if ($toOption) {
            $users = User::where('email', $toOption)->get();
            if ($users->isEmpty()) {
                $user = new User(['name' => 'Subscriber', 'email' => $toOption]);
                $this->sendDigestToUser($user, $taskService, $customSubject, $customBody);

                return self::SUCCESS;
            }
        } else {
            $defaultTo = config('mail.daily_digest.to');
            if (! empty($defaultTo)) {
                $users = User::where('email', $defaultTo)->get();
                if ($users->isEmpty()) {
                    $user = new User(['name' => 'Subscriber', 'email' => $defaultTo]);
                    $users = collect([$user]);
                }
            } else {
                $users = User::all();
            }
        }

        if ($users->isEmpty()) {
            $this->warn('No recipients found to send daily digest.');

            return self::SUCCESS;
        }

        foreach ($users as $user) {
            $this->sendDigestToUser($user, $taskService, $customSubject, $customBody);
        }

        $this->info('Daily digest email(s) sent successfully.');

        return self::SUCCESS;
    }

    protected function sendDigestToUser(User $user, TaskServiceInterface $taskService, ?string $customSubject = null, ?string $customBody = null): void
    {
        $todayTasks = $user->id ? $taskService->getTodayTasks($user, null, 'today_past', 5) : collect();
        $counts = $user->id ? $taskService->getTodayTaskCounts($user, null) : [
            'todayCount' => 0,
            'overdueCount' => 0,
            'todayPastCount' => 0,
            'allPendingCount' => 0,
            'today' => now()->toDateString(),
        ];

        $subject = $customSubject ?: config('mail.daily_digest.subject', 'WorkHub — Today\'s Task Digest');
        $body = $customBody ?: config('mail.daily_digest.body', 'Here is your daily task digest for today.');
        $dashboardUrl = url('/dashboard?task_filter=today_past&per_page=5');

        $this->info("Sending daily digest to: {$user->email}");
        Mail::to($user->email)->send(new DailyDigestMail($subject, $body, $user, $todayTasks, $counts, $dashboardUrl));
    }
}
