<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanyUsers;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Activitylog\Models\Activity;

class DashboardController extends Controller
{
    public function __construct(
        protected readonly TaskServiceInterface $taskService
    ) {}

    /**
     * Display the Modern Inertia V2 Dashboard.
     */
    public function index(Request $request, ?Company $company = null)
    {
        $authUser = auth()->user();
        $companyIds = $authUser ? $authUser->companies()->pluck('company_id')->toArray() : [];

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

        $counts = $authUser ? $this->taskService->getTodayTaskCounts($authUser, $company) : [
            'todayCount' => 0, 'overdueCount' => 0, 'todayPastCount' => 0, 'allPendingCount' => 0, 'today' => now()->toDateString(),
        ];

        // Fetch actual tasks from DB
        $projectIds = $projects->pluck('id')->toArray();
        $dbTasksQuery = Task::with(['project', 'assignedUser']);

        if ($company) {
            $dbTasksQuery->whereIn('project_id', $projectIds);
        } elseif ($authUser) {
            $dbTasksQuery->where(function ($q) use ($projectIds, $authUser) {
                $q->whereIn('project_id', $projectIds)
                    ->orWhere('user_id', $authUser->id)
                    ->orWhere('assigned_to', $authUser->id);
            });
        }

        $dbTasks = $dbTasksQuery->latest()->take(10)->get();

        $initialTasks = $dbTasks->map(function (Task $task) {
            $priorityName = match ((int) $task->priority) {
                4 => 'Urgent',
                3 => 'High',
                2 => 'Medium',
                default => 'Low',
            };
            $typeName = $task->getTypeName();

            return [
                'id' => 'WH-'.str_pad((string) $task->id, 3, '0', STR_PAD_LEFT),
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
        /** @var Collection<int, Activity> $activities */
        $activities = Activity::with('causer')->latest()->take(6)->get();

        $activityStream = $activities->map(function (Activity $log) {
            $causer = $log->causer;
            $causerName = $causer instanceof User ? $causer->name : 'System';

            return [
                'id' => $log->id,
                'time' => $log->created_at ? $log->created_at->format('H:i:s') : now()->format('H:i:s'),
                'source' => $causerName,
                'event' => $log->description,
                'relTime' => $log->created_at ? $log->created_at->diffForHumans() : 'Just now',
            ];
        })->toArray();

        // Calculate velocity throughput chart data
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

        // Formatted team members list
        $formattedTeamMembers = $teamMembers->map(function ($member) {
            $u = $member->user;
            $c = $member->company;

            return [
                'id' => $member->id,
                'name' => $u ? $u->name : 'User',
                'email' => $u ? $u->email : '',
                'company_name' => $c ? $c->name : null,
                'role' => (int) $member->role === 1 ? 'Admin' : 'Member',
                'profile_image' => $u?->profile_image_url,
            ];
        })->values()->toArray();

        // User companies for workspace filter
        $userCompanies = $authUser ? $authUser->companies()->with('company')->get()->map(function ($cUser) {
            return [
                'id' => $cUser->company ? $cUser->company->id : null,
                'name' => $cUser->company ? $cUser->company->name : '',
            ];
        })->filter(fn ($item) => ! empty($item['id']))->values()->toArray() : [];

        // Fetch today & pending tasks with active filter and pagination
        $activeTaskFilter = (string) $request->get('task_filter', 'today_past');
        $perPage = (int) $request->get('per_page', 5);
        $todayTasksPaginator = $authUser ? $this->taskService->getTodayTasks($authUser, $company, $activeTaskFilter, $perPage) : null;

        $formattedTodayTasks = [];
        $paginationMeta = null;
        if ($todayTasksPaginator) {
            $formattedTodayTasks = collect($todayTasksPaginator->items())->map(function (Task $task) use ($counts) {
                $todayDate = $counts['today'] ?? now()->toDateString();
                $isOverdue = $task->due_date && $task->due_date < $todayDate;
                $isDueToday = $task->due_date && $task->due_date == $todayDate;

                return [
                    'id' => $task->id,
                    'display_id' => 'WH-'.str_pad((string) $task->id, 3, '0', STR_PAD_LEFT),
                    'title' => $task->title,
                    'priority' => (int) $task->priority,
                    'priority_name' => match ((int) $task->priority) {
                        4 => 'Urgent',
                        3 => 'High',
                        2 => 'Medium',
                        default => 'Low',
                    },
                    'status' => (int) $task->status,
                    'completed' => (int) $task->status === 3,
                    'due_date' => $task->due_date,
                    'is_overdue' => $isOverdue,
                    'is_due_today' => $isDueToday,
                    'project' => $task->project ? [
                        'id' => $task->project->id,
                        'name' => $task->project->name,
                        'theme' => $task->project->theme,
                    ] : null,
                    'external_source' => (bool) $task->externalSource,
                ];
            })->toArray();

            $paginationMeta = [
                'current_page' => $todayTasksPaginator->currentPage(),
                'last_page' => $todayTasksPaginator->lastPage(),
                'per_page' => $todayTasksPaginator->perPage(),
                'total' => $todayTasksPaginator->total(),
            ];
        }

        // Projects detailed progress list
        $formattedProjectsDetail = $projects->map(function ($project) {
            $pTotal = $project->tasks->count();
            $pCompleted = $project->tasks->where('status', 3)->count();
            $pPercentage = $pTotal > 0 ? (int) round(($pCompleted / $pTotal) * 100) : 0;

            return [
                'id' => $project->id,
                'name' => $project->name,
                'theme' => $project->theme ?? '#3b82f6',
                'total_tasks' => $pTotal,
                'completed_tasks' => $pCompleted,
                'percentage' => $pPercentage,
            ];
        })->toArray();

        log_activity(
            description: "Viewed v2 dashboard ({$currentWorkspaceName})",
            event: 'viewed_v2_dashboard',
            properties: [
                'workspace' => $currentWorkspaceName,
                'company_id' => $company?->id,
            ]
        );

        return Inertia::render('New/Dashboard', [
            'user' => $authUser ? [
                'id' => $authUser->id,
                'name' => $authUser->name,
                'email' => $authUser->email,
                'profile_image' => $authUser->profile_image_url,
                'avatar' => $authUser->profile_image_url,
            ] : null,
            'current_workspace_name' => $currentWorkspaceName,
            'company' => $company ? [
                'id' => $company->id,
                'name' => $company->name,
            ] : null,
            'user_companies' => $userCompanies,
            'stats' => [
                'total_projects' => $projectsCount,
                'total_tasks' => $totalTasks,
                'active_tasks' => $totalTasks - $completedTasks,
                'completed_tasks' => $completedTasks,
                'team_members' => $teamMembers->count(),
                'overdue_tasks' => $counts['overdueCount'] ?? 0,
                'today_tasks' => $counts['todayCount'] ?? 0,
                'today_past_tasks' => $counts['todayPastCount'] ?? 0,
                'all_pending_tasks' => $counts['allPendingCount'] ?? 0,
            ],
            'counts' => $counts,
            'active_task_filter' => $activeTaskFilter,
            'per_page' => $perPage,
            'today_tasks_list' => $formattedTodayTasks,
            'pagination_meta' => $paginationMeta,
            'projects_detail' => $formattedProjectsDetail,
            'team_members_list' => $formattedTeamMembers,
            'recent_activity' => $activityStream,
            'activity_stream' => $activityStream,
            'chart_data' => $chartData,
            'projects' => $formattedProjects,
            'initial_tasks' => $initialTasks,
        ]);
    }
}
