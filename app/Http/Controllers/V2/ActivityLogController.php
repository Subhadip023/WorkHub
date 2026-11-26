<?php

namespace App\Http\Controllers\V2;

use App\Support\ActivityLogQuery;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    /**
     * Display modern V2 Activity Logs stream.
     */
    public function index(Request $request)
    {
        $authUser = auth()->user();
        if (! $authUser) {
            abort(401);
        }

        $sessionCompanyId = session('current_company_id');
        $activeCompany = is_numeric($sessionCompanyId) ? Company::find($sessionCompanyId) : null;
        $currentWorkspaceName = $activeCompany ? $activeCompany->name : ($sessionCompanyId === 'personal' ? 'Personal Space' : 'All Workspaces');

        [$query, $scope] = ActivityLogQuery::build($request, auth()->user());

        if ($request->filled('event')) {
            $query->where('event', $request->input('event'));
        }

        if ($request->filled('subject_type')) {
            $query->where('subject_type', $request->input('subject_type'));
        }

        $activities = $query->latest('id')->take(60)->get();

        $formattedActivities = $activities->map(function (Activity $act) {
            $causer = $act->causer;
            $subject = $act->subject;

            $subjectTitle = null;
            if ($subject) {
                $subjectTitle = $subject->getAttribute('title') ?? $subject->getAttribute('name');
            }

            return [
                'id' => $act->id,
                'description' => $act->description,
                'event' => $act->event ?: 'action',
                'causer' => ($causer instanceof User) ? [
                    'id' => $causer->id,
                    'name' => $causer->name,
                    'email' => $causer->email,
                    'avatar' => strtoupper(substr($causer->name, 0, 2)),
                    'profile_image' => $causer->profile_image_url,
                ] : [
                    'name' => 'System',
                    'avatar' => 'SYS',
                ],
                'subject_type' => class_basename($act->subject_type ?? 'Resource'),
                'subject_title' => is_string($subjectTitle) ? $subjectTitle : null,
                'properties' => $act->properties,
                'created_at' => $act->created_at ? $act->created_at->format('M d, Y h:i A') : '',
                'created_at_human' => $act->created_at ? $act->created_at->diffForHumans() : '',
            ];
        })->values()->toArray();

        // Calculate KPI stats
        $totalActivities = count($formattedActivities);
        $createdCount = collect($formattedActivities)->where('event', 'created')->count();
        $updatedCount = collect($formattedActivities)->where('event', 'updated')->count();
        $deletedCount = collect($formattedActivities)->where('event', 'deleted')->count();

        $stats = [
            'total' => $totalActivities,
            'created' => $createdCount,
            'updated' => $updatedCount,
            'deleted' => $deletedCount,
            'current_workspace_name' => $currentWorkspaceName,
        ];

        return Inertia::render('New/ActivityLogs', [
            'activities' => $formattedActivities,
            'stats' => $stats,
            'scope' => $scope,
            'can_view_company' => ActivityLogQuery::canViewCompanyScope($authUser),
            'current_workspace_name' => $currentWorkspaceName,
        ]);
    }
}
