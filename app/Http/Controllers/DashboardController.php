<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\CompanyUsers;
use App\Models\Project;
use App\Services\TaskServiceInterface;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        protected readonly TaskServiceInterface $taskService
    ) {}

    /**
     * Handle the incoming request (Classic Blade Dashboard View).
     */
    public function __invoke(Request $request, ?Company $company = null)
    {
        $authUser = auth()->user();
        $companyIds = $authUser ? $authUser->companies()->pluck('company_id')->toArray() : [];

        if ($company) {
            // Verify membership
            if (! in_array($company->id, $companyIds)) {
                abort(403, 'You are not a member of this organization.');
            }

            // Filter to selected company
            $projects = Project::select('id', 'name', 'theme', 'company_id')
                ->where('company_id', $company->id)
                ->with([
                    'tasks' => function ($query) {
                        $query->select('id', 'project_id', 'status');
                    },
                    'company' => function ($query) {
                        $query->select('id', 'name');
                    },
                ])
                ->get();

            $teamMembers = CompanyUsers::where('company_id', $company->id)
                ->with(['user', 'company'])
                ->get()
                ->unique('user_id');

            $currentWorkspaceName = $company->name;
        } else {
            // Fetch all projects (both personal and organizational)
            $projects = Project::select('id', 'name', 'theme', 'company_id', 'user_id')
                ->whereIn('company_id', $companyIds)
                ->orWhere(function ($query) use ($authUser) {
                    if ($authUser) {
                        $query->whereNull('company_id')->where('user_id', $authUser->id);
                    }
                })
                ->with([
                    'tasks' => function ($query) {
                        $query->select('id', 'project_id', 'status');
                    },
                    'company' => function ($query) {
                        $query->select('id', 'name');
                    },
                ])
                ->get();

            // Fetch all team members from all companies they belong to
            $teamMembers = CompanyUsers::whereIn('company_id', $companyIds)
                ->with(['user', 'company'])
                ->get()
                ->unique('user_id');

            $currentWorkspaceName = 'All Workspaces';
        }

        $projectsCount = $projects->count();

        $totalTasks = 0;
        $completedTasks = 0;
        foreach ($projects as $project) {
            $totalTasks += $project->tasks->count();
            $completedTasks += $project->tasks->where('status', 3)->count();
        }

        // Fetch Today's tasks & stats using TaskService
        $counts = $authUser ? $this->taskService->getTodayTaskCounts($authUser, $company) : [
            'todayCount' => 0, 'overdueCount' => 0, 'todayPastCount' => 0, 'allPendingCount' => 0, 'today' => now()->toDateString(),
        ];
        $todayCount = $counts['todayCount'];
        $overdueCount = $counts['overdueCount'];
        $todayPastCount = $counts['todayPastCount'];
        $allPendingCount = $counts['allPendingCount'];
        $today = $counts['today'];

        $activeTaskFilter = $request->get('task_filter', 'today_past');
        $perPage = (int) $request->get('per_page', 5);

        $todayTasks = $authUser ? $this->taskService->getTodayTasks($authUser, $company, $activeTaskFilter, $perPage) : collect();

        log_activity(
            description: "Viewed classic dashboard ({$currentWorkspaceName})",
            event: 'viewed_dashboard',
            properties: [
                'workspace' => $currentWorkspaceName,
                'company_id' => $company?->id,
            ]
        );

        return view('welcome', compact(
            'projects',
            'projectsCount',
            'totalTasks',
            'completedTasks',
            'teamMembers',
            'currentWorkspaceName',
            'company',
            'todayTasks',
            'activeTaskFilter',
            'todayCount',
            'overdueCount',
            'todayPastCount',
            'allPendingCount',
            'today',
            'perPage'
        ));
    }

    /**
     * Alias for backward compatibility.
     */
    public function oldDashboard(Request $request, ?Company $company = null)
    {
        return $this->__invoke($request, $company);
    }
}
