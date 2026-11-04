<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanyUsers;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class ProjectController extends Controller
{
    /**
     * Display the V2 Projects list using Inertia React.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $companyId = session('current_company_id');
        $company = is_numeric($companyId) ? Company::find($companyId) : null;
        $companyIds = $user ? $user->companies()->pluck('company_id')->toArray() : [];

        if ($company) {
            $projectsQuery = Project::where('company_id', $company->id);
            $currentWorkspaceName = $company->name;
        } else {
            $projectsQuery = Project::where(function ($q) use ($user, $companyIds) {
                $q->whereIn('company_id', $companyIds);
                if ($user) {
                    $q->orWhere(function ($sub) use ($user) {
                        $sub->whereNull('company_id')->where('user_id', $user->id);
                    });
                }
            });
            $currentWorkspaceName = 'All Workspaces';
        }

        $projects = $projectsQuery->with([
            'company:id,name',
            'user:id,name,profile_image',
            'tasks' => function ($query) {
                $query->select('id', 'title', 'project_id', 'status', 'priority', 'due_date', 'assigned_to')
                    ->with('assignedUser:id,name,profile_image');
            },
        ])->latest()->get();

        $userCompanies = $user ? $user->companies()->with('company')->get()->map(function ($cu) {
            return [
                'id' => $cu->company?->id,
                'name' => $cu->company->name ?? '',
            ];
        })->filter(fn ($item) => ! empty($item['id']))->values()->toArray() : [];

        $totalTasksCount = 0;
        $completedTasksCount = 0;
        $inProgressProjectsCount = 0;
        $completedProjectsCount = 0;
        $onHoldProjectsCount = 0;

        $formattedProjects = $projects->map(function (Project $p) use ($user, &$totalTasksCount, &$completedTasksCount, &$inProgressProjectsCount, &$completedProjectsCount, &$onHoldProjectsCount) {
            $tasks = $p->tasks;
            $pTotal = $tasks->count();
            $pCompleted = $tasks->where('status', 3)->count();
            $totalTasksCount += $pTotal;
            $completedTasksCount += $pCompleted;

            $progress = $pTotal > 0 ? (int) round(($pCompleted / $pTotal) * 100) : 0;

            $statusName = match ((int) $p->status) {
                3 => 'Completed',
                2 => 'In Progress',
                4 => 'On Hold',
                default => 'To Do',
            };

            if ((int) $p->status === 3) {
                $completedProjectsCount++;
            } elseif ((int) $p->status === 4) {
                $onHoldProjectsCount++;
            } else {
                $inProgressProjectsCount++;
            }

            $priorityName = match ((int) $p->priority) {
                4 => 'Urgent',
                3 => 'High',
                2 => 'Medium',
                default => 'Low',
            };

            $assignedUsers = $tasks->pluck('assignedUser')->filter()->unique('id');
            $teamMembers = $assignedUsers->map(function ($u) {
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'avatar' => strtoupper(substr($u->name, 0, 2)),
                    'profile_image' => $u->profile_image_url,
                ];
            })->values()->toArray();

            $earliestDueDate = $tasks->where('status', '!=', 3)
                ->whereNotNull('due_date')
                ->sortBy('due_date')
                ->first()?->due_date;

            $formattedDueDate = $earliestDueDate
                ? Carbon::parse($earliestDueDate)->format('M d, Y')
                : ($p->created_at ? $p->created_at->format('M d, Y') : 'No deadline');

            $canEdit = $user && ($p->company_id === null ? $p->user_id === $user->id : $user->companies->contains('company_id', $p->company_id));
            $canDelete = false;
            if ($user) {
                if ($p->company_id === null) {
                    $canDelete = $p->user_id === $user->id;
                } else {
                    $membership = $user->companies()->where('company_id', $p->company_id)->first();
                    $canDelete = $membership && (int) $membership->role === 1;
                }
            }

            return [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'description' => trim(strip_tags($p->description ?: 'No description provided.')),
                'raw_description' => $p->description ?: '',
                'theme' => $p->theme ?: '#10b981',
                'status' => $statusName,
                'status_id' => (int) $p->status,
                'priority' => $priorityName,
                'priority_id' => (int) $p->priority,
                'progress' => $progress,
                'dueDate' => $formattedDueDate,
                'completedTasks' => $pCompleted,
                'totalTasks' => $pTotal,
                'tag' => $p->theme ?: ($p->company ? $p->company->name : 'Personal'),
                'category' => $p->company ? $p->company->name : 'Personal Space',
                'company_id' => $p->company_id,
                'company_name' => $p->company?->name,
                'teamMembers' => $teamMembers,
                'can_edit' => $canEdit,
                'can_delete' => $canDelete,
                'created_at' => $p->created_at?->format('M d, Y'),
            ];
        })->toArray();

        $stats = [
            'total_projects' => count($projects),
            'in_progress' => $inProgressProjectsCount,
            'completed' => $completedProjectsCount,
            'on_hold' => $onHoldProjectsCount,
            'total_tasks' => $totalTasksCount,
            'completed_tasks' => $completedTasksCount,
            'completion_rate' => $totalTasksCount > 0 ? (int) round(($completedTasksCount / $totalTasksCount) * 100) : 100,
        ];

        return Inertia::render('New/Projects', [
            'initial_projects' => $formattedProjects,
            'companies' => $userCompanies,
            'stats' => $stats,
            'current_workspace_name' => $currentWorkspaceName,
        ]);
    }

    /**
     * Display the specified project details using Inertia React.
     */
    public function show(Request $request, Project $project)
    {
        Gate::authorize('view', $project);

        log_activity(
            description: "Viewed project '{$project->name}'",
            event: 'viewed',
            subject: $project
        );

        $user = auth()->user();

        // Check user edit and delete permissions
        $canEdit = $user && ($project->company_id === null ? $project->user_id === $user->id : $user->companies->contains('company_id', $project->company_id));
        $canDelete = false;
        if ($user) {
            if ($project->company_id === null) {
                $canDelete = $project->user_id === $user->id;
            } else {
                $membership = $user->companies()->where('company_id', $project->company_id)->first();
                $canDelete = $membership && (int) $membership->role === 1;
            }
        }

        // Available team members for this project
        if ($project->company_id === null) {
            $teamMembers = $user ? [[
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => strtoupper(substr($user->name, 0, 2)),
                'profile_image' => $user->profile_image_url,
            ]] : [];
        } else {
            $companyUsers = CompanyUsers::where('company_id', $project->company_id)
                ->with('user')
                ->where('is_approved', true)
                ->get();

            $teamMembers = $companyUsers->map(function ($cu) {
                $u = $cu->user;
                if (! $u) return null;
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'avatar' => strtoupper(substr($u->name, 0, 2)),
                    'profile_image' => $u->profile_image_url,
                    'role' => (int) $cu->role === 1 ? 'Admin' : 'Member',
                ];
            })->filter()->values()->toArray();
        }

        // Tasks query with status, priority, and assignees
        $tasks = $project->tasks()
            ->with(['assignedUser:id,name,email,profile_image'])
            ->orderByRaw('due_date IS NULL, due_date ASC')
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($task) {
                return [
                    'id' => $task->id,
                    'db_id' => $task->id,
                    'title' => $task->title,
                    'description' => $task->description,
                    'status' => (int) $task->status,
                    'status_label' => match ((int) $task->status) {
                        3 => 'Completed',
                        2 => 'In Progress',
                        4 => 'On Hold',
                        default => 'To Do',
                    },
                    'priority' => (int) $task->priority,
                    'priority_label' => match ((int) $task->priority) {
                        4 => 'Urgent',
                        3 => 'High',
                        2 => 'Medium',
                        default => 'Low',
                    },
                    'type' => (int) $task->type,
                    'type_label' => match ((int) $task->type) {
                        2 => 'Bug',
                        3 => 'Task',
                        default => 'Feature',
                    },
                    'points' => $task->points,
                    'due_date' => $task->due_date ? Carbon::parse($task->due_date)->format('M d, Y') : null,
                    'raw_due_date' => $task->due_date ? Carbon::parse($task->due_date)->format('Y-m-d') : '',
                    'assigned_to' => $task->assigned_to,
                    'assignee' => $task->assignedUser ? [
                        'id' => $task->assignedUser->id,
                        'name' => $task->assignedUser->name,
                        'email' => $task->assignedUser->email,
                        'avatar' => strtoupper(substr($task->assignedUser->name, 0, 2)),
                        'profile_image' => $task->assignedUser->profile_image_url,
                    ] : null,
                    'completed' => (int) $task->status === 3,
                    'created_at' => $task->created_at ? $task->created_at->format('M d, Y') : '',
                ];
            });

        // Project Notes
        $notes = $project->notes()
            ->with('user:id,name,profile_image')
            ->latest()
            ->get()
            ->map(function ($note) {
                return [
                    'id' => $note->id,
                    'title' => $note->title,
                    'description' => $note->description,
                    'created_at' => $note->created_at ? $note->created_at->format('M d, Y') : '',
                    'user' => $note->user ? [
                        'id' => $note->user->id,
                        'name' => $note->user->name,
                        'avatar' => strtoupper(substr($note->user->name, 0, 2)),
                        'profile_image' => $note->user->profile_image_url,
                    ] : null,
                ];
            });

        // Project Credentials
        $credentials = $project->credentials()
            ->latest()
            ->get()
            ->map(function ($cred) {
                return [
                    'id' => $cred->id,
                    'type' => $cred->type,
                    'type_name' => $cred->type_slug ?? 'Development',
                    'name' => $cred->name,
                    'host_or_identifier' => $cred->host_or_identifier,
                    'password_or_secret' => $cred->password_or_secret,
                    'created_at' => $cred->created_at ? $cred->created_at->format('M d, Y') : '',
                ];
            });

        // Comments / Discussion thread
        $comments = $project->getCachedComments()->map(function ($comm) {
            return [
                'id' => $comm->id,
                'content' => $comm->content,
                'created_at' => $comm->created_at ? $comm->created_at->diffForHumans() : '',
                'user' => [
                    'id' => $comm->user?->id,
                    'name' => $comm->user?->name ?? 'User',
                    'avatar' => strtoupper(substr($comm->user?->name ?? 'U', 0, 2)),
                    'profile_image' => $comm->user?->profile_image_url,
                ],
                'can_delete' => auth()->id() === $comm->user_id,
            ];
        });

        // Statistics
        $totalTasks = $tasks->count();
        $completedTasks = $tasks->where('status', 3)->count();
        $inProgressTasks = $tasks->where('status', 2)->count();
        $todoTasks = $tasks->where('status', 1)->count();
        $progress = $totalTasks > 0 ? (int) round(($completedTasks / $totalTasks) * 100) : 0;

        $stats = [
            'total_tasks' => $totalTasks,
            'completed_tasks' => $completedTasks,
            'in_progress_tasks' => $inProgressTasks,
            'todo_tasks' => $todoTasks,
            'progress' => $progress,
            'total_notes' => $notes->count(),
            'total_credentials' => $credentials->count(),
            'total_comments' => $comments->count(),
        ];

        $formattedProject = [
            'id' => $project->id,
            'name' => $project->name,
            'slug' => $project->slug,
            'description' => $project->description ?: '',
            'theme' => $project->theme ?: '#10b981',
            'status' => (int) $project->status,
            'status_label' => match ((int) $project->status) {
                3 => 'Completed',
                2 => 'In Progress',
                4 => 'On Hold',
                default => 'To Do',
            },
            'priority' => (int) $project->priority,
            'priority_label' => match ((int) $project->priority) {
                4 => 'Urgent',
                3 => 'High',
                2 => 'Medium',
                default => 'Low',
            },
            'company_id' => $project->company_id,
            'company_name' => $project->company?->name,
            'owner' => $project->user ? [
                'id' => $project->user->id,
                'name' => $project->user->name,
                'profile_image' => $project->user->profile_image_url,
            ] : null,
            'can_edit' => $canEdit,
            'can_delete' => $canDelete,
            'created_at' => $project->created_at ? $project->created_at->format('M d, Y') : '',
        ];

        return Inertia::render('New/ProjectView', [
            'project' => $formattedProject,
            'tasks' => $tasks->values()->all(),
            'notes' => $notes->values()->all(),
            'credentials' => $credentials->values()->all(),
            'comments' => $comments->values()->all(),
            'team_members' => $teamMembers,
            'stats' => $stats,
        ]);
    }
}
