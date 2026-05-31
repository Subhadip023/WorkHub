<?php

namespace App\Providers;

use App\Models\Company;
use App\Models\Note;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFour();

        Relation::morphMap([
            'task' => Task::class,
            'project' => Project::class,
            'company' => Company::class,
            'note' => Note::class,
        ]);

        VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
            return (new MailMessage)
                ->subject('Verify Email Address - WorkHub')
                ->view('emails.verify-email', [
                    'url' => $url,
                    'name' => $notifiable->name ?? 'User',
                ]);
        });
    }
}
