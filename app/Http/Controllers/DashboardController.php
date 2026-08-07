<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\CompanyUsers;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, ?Company $company = null)
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

            // Tasks query for selected company (Assigned to current user only)
            $dashboardTaskQuery = Task::where('assigned_to', $auth_user->id)
                ->where(function ($query) use ($company) {
                    $query->whereHas('project', function ($pQuery) use ($company) {
                        $pQuery->where('company_id', $company->id);
                    })->orWhereNull('project_id');
                });
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

            // Tasks query for all workspaces (Assigned to current user only)
            $dashboardTaskQuery = Task::where('assigned_to', $auth_user->id)
                ->where(function ($query) use ($companyIds, $auth_user) {
                    $query->whereHas('project', function ($pQuery) use ($companyIds, $auth_user) {
                        $pQuery->whereIn('company_id', $companyIds)
                            ->orWhere(function ($sub) use ($auth_user) {
                                $sub->whereNull('company_id')->where('user_id', $auth_user->id);
                            });
                    })->orWhereNull('project_id');
                });
        }

        $projectsCount = $projects->count();

        $totalTasks = 0;
        $completedTasks = 0;
        foreach ($projects as $project) {
            $totalTasks += $project->tasks->count();
            $completedTasks += $project->tasks->where('status', 3)->count();
        }

        $today = now()->toDateString();
        $allPendingTaskBase = (clone $dashboardTaskQuery)->where('status', '!=', 3);

        $todayCount = (clone $allPendingTaskBase)->where('due_date', '=', $today)->count();
        $overdueCount = (clone $allPendingTaskBase)->whereNotNull('due_date')->where('due_date', '<', $today)->count();
        $todayPastCount = (clone $allPendingTaskBase)->where(function ($q) use ($today) {
            $q->whereNull('due_date')->orWhere('due_date', '<=', $today);
        })->count();
        $allPendingCount = (clone $allPendingTaskBase)->count();

        $activeTaskFilter = $request->get('task_filter', 'today_past');
        $todayTasksQuery = (clone $dashboardTaskQuery)->where('status', '!=', 3);

        if ($activeTaskFilter === 'due_today') {
            $todayTasksQuery->where('due_date', '=', $today);
        } elseif ($activeTaskFilter === 'overdue') {
            $todayTasksQuery->whereNotNull('due_date')->where('due_date', '<', $today);
        } elseif ($activeTaskFilter === 'all_pending') {
            // No due_date filter, show all pending assigned to user
        } else {
            // Default 'today_past'
            $activeTaskFilter = 'today_past';
            $todayTasksQuery->where(function ($q) use ($today) {
                $q->whereNull('due_date')->orWhere('due_date', '<=', $today);
            });
        }

        $perPage = (int) $request->get('per_page', 5);
        if (! in_array($perPage, [5, 10, 25, 50, 100])) {
            $perPage = 5;
        }

        // Priority-wise ordering: 4 (Urgent) -> 3 (High) -> 2 (Medium) -> 1 (Low)
        $todayTasks = $todayTasksQuery
            ->with(['project'])
            ->orderByRaw('priority DESC')
            ->orderByRaw('CASE WHEN due_date IS NULL THEN 1 ELSE 0 END, due_date ASC')
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->withQueryString();

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
