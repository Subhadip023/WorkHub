<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(private readonly NotificationService $notificationService) {}

    /**
     * Display a listing of unread notifications for the authenticated user.
     */
    public function index(Request $request)
    {
        $companyId = session('current_company_id');
        $orgId = is_numeric($companyId) ? (int) $companyId : null;

        $notifications = $this->notificationService->getUnreadForUser(auth()->user(), $orgId);

        return response()->json([
            'notifications' => $notifications,
        ]);
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead(Notification $notification)
    {
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }

        $this->notificationService->markAsRead($notification);

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Mark all unread notifications for the authenticated user as read.
     */
    public function markAllAsRead(Request $request)
    {
        $companyId = session('current_company_id');
        $orgId = is_numeric($companyId) ? (int) $companyId : null;

        $this->notificationService->markAllAsRead(auth()->user(), $orgId);

        return response()->json([
            'success' => true,
        ]);
    }
}
