<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class NotificationService
{
    /**
     * Send/create a new notification for a user.
     *
     * @param array<string, mixed> $data
     */
    public function send(
        User $user,
        string $type,
        string $title,
        string $message,
        ?int $organizationId = null,
        array $data = []
    ): Notification {
        return Notification::create([
            'user_id' => $user->id,
            'organization_id' => $organizationId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'read_at' => null,
        ]);
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
