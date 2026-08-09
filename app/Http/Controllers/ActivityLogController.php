<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $scope = $request->query('scope', 'mine');

        $query = Activity::with(['causer', 'subject']);

        if ($request->filled('user_id')) {
            $targetUserId = (int) $request->input('user_id');
            $query->where(function ($q) use ($targetUserId) {
                $q->where(function ($sub) use ($targetUserId) {
                    $sub->where('causer_type', 'user')
                        ->where('causer_id', $targetUserId);
                })->orWhere(function ($sub) use ($targetUserId) {
                    $sub->where('subject_type', 'user')
                        ->where('subject_id', $targetUserId);
                });
            });
        } elseif ($scope === 'all' && ($user->isAdmin() || $user->isSuperAdmin())) {
            // Admin viewing all system activity logs
        } else {
            // User viewing their own activity logs
            $query->where(function ($q) use ($user) {
                $q->where(function ($sub) use ($user) {
                    $sub->where('causer_type', 'user')
                        ->where('causer_id', $user->id);
                })->orWhere(function ($sub) use ($user) {
                    $sub->where('subject_type', 'user')
                        ->where('subject_id', $user->id);
                });
            });
        }

        if ($request->filled('event')) {
            $query->where('event', $request->input('event'));
        }

        if ($request->filled('subject_type')) {
            $query->where('subject_type', $request->input('subject_type'));
        }

        $activities = $query->latest('id')->paginate(20);

        return view('activity_logs.index', compact('activities', 'scope'));
    }
}
