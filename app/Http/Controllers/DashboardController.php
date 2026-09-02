<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\CompanyUsers;
use App\Models\Project;
use App\Models\Task;
use App\Services\TaskServiceInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Activitylog\Models\Activity;

class DashboardController extends Controller
{
    public function __construct(
        protected readonly TaskServiceInterface $taskService
    ) {}

    /**
     * Handle the incoming request (New Inertia React Dashboard with Actual Database Data).
     */
    public function __invoke(Request $request, ?Company $company = null)
    {
        $auth_user = auth()->user();
        $companyIds = $auth_user ? $auth_user->companies()->pluck('company_id')->toArray() : [];

        if ($company) {
            if (! in_array($company->id, $companyIds)) {
                abort(403, 'You are not a member of this organization.');
            }

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
            $projects = Project::select('id', 'name', 'theme', 'company_id', 'user_id')
                ->whereIn('company_id', $companyIds)
                ->orWhere(function ($query) use ($auth_user) {
                    if ($auth_user) {
                        $query->whereNull('company_id')->where('user_id', $auth_user->id);
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

            $teamMembers = CompanyUsers::whereIn('company_id', $companyIds)
                ->with(['user', 'company'])
                ->get()
                ->unique('user_id');

            $currentWorkspaceName = 'All Workspaces';
        }

        $projectsCount = $projects->count();

        $totalTasks = 0;
        $completedTasks = 0;
        $formattedProjects = [];
        foreach ($projects as $project) {
            $pTotal = $project->tasks->count();
            $pCompleted = $project->tasks->where('status', 3)->count();
            $totalTasks += $pTotal;
            $completedTasks += $pCompleted;
            $progress = $pTotal > 0 ? (int) round(($pCompleted / $pTotal) * 100) : 0;

            $formattedProjects[] = [
                'id' => $project->id,
                'name' => $project->name,
                'progress' => $progress,
                'status' => $progress === 100 ? 'Completed' : ($progress > 75 ? 'Near Completion' : 'In Progress'),
                'tag' => $project->company ? $project->company->name : 'Personal',
            ];
        }

        $counts = $auth_user ? $this->taskService->getTodayTaskCounts($auth_user, $company) : [
            'todayCount' => 0, 'overdueCount' => 0, 'todayPastCount' => 0, 'allPendingCount' => 0, 'today' => now()->toDateString(),
        ];

        // Fetch actual tasks from DB
        $projectIds = $projects->pluck('id')->toArray();
        $dbTasksQuery = Task::with(['project', 'assignedUser']);

        if ($company) {
            $dbTasksQuery->whereIn('project_id', $projectIds);
        } elseif ($auth_user) {
            $dbTasksQuery->where(function ($q) use ($projectIds, $auth_user) {
                $q->whereIn('project_id', $projectIds)
                  ->orWhere('user_id', $auth_user->id)
                  ->orWhere('assigned_to', $auth_user->id);
            });
        }

        $dbTasks = $dbTasksQuery->latest()->take(10)->get();

        $initialTasks = $dbTasks->map(function ($task) {
            $priorityName = match ((int) $task->priority) {
                4 => 'Urgent',
                3 => 'High',
                2 => 'Medium',
                default => 'Low',
            };
            $typeName = $task->getTypeName();

            return [
                'id' => 'WH-' . str_pad($task->id, 3, '0', STR_PAD_LEFT),
                'title' => $task->title,
                'completed' => (int) $task->status === 3,
                'tags' => [$typeName],
                'project' => $task->project ? $task->project->name : 'Personal',
                'branch' => 'main',
                'priority' => $priorityName,
                'assignee' => [
                    'name' => $task->assignedUser ? $task->assignedUser->name : 'Unassigned',
                    'avatar' => strtoupper(substr($task->assignedUser ? $task->assignedUser->name : 'UN', 0, 2)),
                ],
                'updatedAt' => $task->updated_at ? $task->updated_at->diffForHumans() : 'Recently',
            ];
        })->toArray();

        // Fetch actual activity log stream
        $activityStream = Activity::with('causer')->latest()->take(6)->get()->map(function ($log) {
            return [
                'id' => $log->id,
                'time' => $log->created_at ? $log->created_at->format('H:i:s') : now()->format('H:i:s'),
                'source' => $log->causer ? $log->causer->name : 'System',
                'event' => $log->description,
                'relTime' => $log->created_at ? $log->created_at->diffForHumans() : 'Just now',
            ];
        })->toArray();

        // Calculate velocity throughput chart data from actual DB records
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dayName = $date->format('D');
            $createdCount = Task::whereDate('created_at', $date->toDateString())->count();
            $completedCount = Task::whereDate('updated_at', $date->toDateString())->where('status', 3)->count();

            $chartData[] = [
                'name' => $dayName,
                'throughput' => $createdCount,
                'completed' => $completedCount,
            ];
        }

        log_activity(
            description: "Viewed dashboard ({$currentWorkspaceName})",
            event: 'viewed_dashboard',
            properties: [
                'workspace' => $currentWorkspaceName,
                'company_id' => $company?->id,
            ]
        );

        return Inertia::render('Dashboard', [
            'user' => $auth_user ? [
                'name' => $auth_user->name,
                'email' => $auth_user->email,
            ] : null,
            'stats' => [
                'total_projects' => $projectsCount,
                'active_tasks' => $totalTasks - $completedTasks,
                'completed_tasks' => $completedTasks,
                'team_members' => $teamMembers->count(),
                'overdue_tasks' => $counts['overdueCount'] ?? 0,
                'today_tasks' => $counts['todayCount'] ?? 0,
            ],
            'recent_activity' => $activityStream,
            'activity_stream' => $activityStream,
            'chart_data' => $chartData,
            'projects' => $formattedProjects,
            'initial_tasks' => $initialTasks,
        ]);
    }

    /**
     * Legacy/Old Blade Dashboard View
     */
    public function oldDashboard(Request $request, ?Company $company = null)
    {
        $auth_user = auth()->user();
        $companyIds = $auth_user->companies()->pluck('company_id')->toArray();

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
                ->orWhere(function ($query) use ($auth_user) {
                    $query->whereNull('company_id')->where('user_id', $auth_user->id);
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
        $counts = $this->taskService->getTodayTaskCounts($auth_user, $company);
        $todayCount = $counts['todayCount'];
        $overdueCount = $counts['overdueCount'];
        $todayPastCount = $counts['todayPastCount'];
        $allPendingCount = $counts['allPendingCount'];
        $today = $counts['today'];

        $activeTaskFilter = $request->get('task_filter', 'today_past');
        $perPage = (int) $request->get('per_page', 5);

        $todayTasks = $this->taskService->getTodayTasks($auth_user, $company, $activeTaskFilter, $perPage);

        log_activity(
            description: "Viewed old dashboard ({$currentWorkspaceName})",
            event: 'viewed_old_dashboard',
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
}

