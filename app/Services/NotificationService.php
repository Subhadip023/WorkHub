<?php

namespace App\Services;

use App\Mail\WorkHubNotificationMail;
use App\Models\Company;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /**
     * Send/create a new notification for a user.
     *
     * @param  array<string, mixed>  $data
     */
    public function send(
        User $user,
        string $type,
        string $title,
        string $message,
        ?int $organizationId = null,
        array $data = []
    ): Notification {
        $notification = Notification::create([
            'user_id' => $user->id,
            'organization_id' => $organizationId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'read_at' => null,
        ]);

        $this->dispatchEmailNotification($user, $notification, $organizationId);

        return $notification;
    }

    /**
     * Dispatch an email notification to the user if enabled.
     */
    protected function dispatchEmailNotification(User $user, Notification $notification, ?int $organizationId = null): void
    {
        // Respect user's email notification preferences (default true)
        if (isset($user->email_notifications) && ! $user->email_notifications) {
            return;
        }

        if (empty($user->email)) {
            return;
        }

        try {
            $workspaceName = null;
            if ($organizationId !== null) {
                $company = Company::find($organizationId);
                $workspaceName = $company?->name;
            }

            $actionUrl = $notification->data['url'] ?? url('/dashboard');

            Mail::to($user->email)->send(new WorkHubNotificationMail(
                notification: $notification,
                actionUrl: $actionUrl,
                workspaceName: $workspaceName,
                recipient: $user,
            ));
        } catch (\Throwable $e) {
            Log::warning('Failed to dispatch notification email: '.$e->getMessage(), [
                'user_id' => $user->id,
                'notification_id' => $notification->id,
            ]);
        }
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead(Notification $notification): bool
    {
        return $notification->update([
            'read_at' => now(),
        ]);
    }

    /**
     * Mark all unread notifications for a user as read.
     */
    public function markAllAsRead(User $user, ?int $organizationId = null): int
    {
        $query = Notification::where('user_id', $user->id)
            ->whereNull('read_at');

        if ($organizationId !== null) {
            $query->where('organization_id', $organizationId);
        }

        return $query->update([
            'read_at' => now(),
        ]);
    }

    /**
     * Get all notifications for a user, optionally filtered by organization.
     *
     * @return Collection<int, Notification>
     */
    public function getAllForUser(User $user, ?int $organizationId = null): Collection
    {
        $query = Notification::where('user_id', $user->id);

        if ($organizationId !== null) {
            $query->where('organization_id', $organizationId);
        }

        return $query->latest()->get();
    }

    /**
     * Get all unread notifications for a user, optionally filtered by organization.
     *
     * @return Collection<int, Notification>
     */
    public function getUnreadForUser(User $user, ?int $organizationId = null): Collection
    {
        $query = Notification::where('user_id', $user->id)
            ->whereNull('read_at');

        if ($organizationId !== null) {
            $query->where('organization_id', $organizationId);
        }

        return $query->latest()->get();
    }
}
