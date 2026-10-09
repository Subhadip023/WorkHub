<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Http\Request;
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
}
