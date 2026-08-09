<?php

namespace App\Providers;

use App\Models\Company;
use App\Models\CompanyInvitation;
use App\Models\Note;
use App\Models\Project;
use App\Models\Task;
use App\Repositories\TaskRepository;
use App\Repositories\TaskRepositoryInterface;
use App\Services\TaskService;
use App\Services\TaskServiceInterface;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
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

        $this->app->bind(TaskRepositoryInterface::class, TaskRepository::class);
        $this->app->bind(TaskServiceInterface::class, TaskService::class);
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

        view()->composer('layouts.admin', function ($view) {
            if (auth()->check()) {
                $user = auth()->user();
                $cacheKey = 'pending_invitations_'.$user->id;

                $pendingInvitations = cache()->remember($cacheKey, 300, function () use ($user) {
                    return CompanyInvitation::where('email', $user->email)
                        ->with('company')
                        ->get();
                });

                $view->with('pendingInvitations', $pendingInvitations);
            } else {
                $view->with('pendingInvitations', collect());
            }
        });

        Gate::before(function ($user, $ability) {
            return $user->isSuperAdmin() ? true : null;
        });

        // Custom API Rate Limiter: 24 requests per minute (1 request per 2.5 seconds)
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(24)->by(
                $request->header('X-Api-Key') ?: ($request->user()?->id ?: $request->ip())
            );
        });
    }
}
