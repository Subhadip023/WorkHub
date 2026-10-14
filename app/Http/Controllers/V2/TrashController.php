<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanyUsers;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class TrashController extends Controller
{
    /**
     * Display the V2 Recycle Bin / Trash interface.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        if (! $user) {
            abort(401);
        }

        $allCompanyIds = CompanyUsers::withTrashed()
            ->where('user_id', $user->id)
            ->pluck('company_id')
            ->toArray();

        // 1. Trashed Tasks
        $projectIdsForTasks = Project::withTrashed()
            ->where(function ($q) use ($allCompanyIds, $user) {
                $q->whereIn('company_id', $allCompanyIds)
                    ->orWhere(function ($sub) use ($user) {
                        $sub->whereNull('company_id')->where('user_id', $user->id);
                    });
            })
            ->pluck('id')
            ->toArray();

        $tasks = Task::onlyTrashed()
            ->where(function ($q) use ($projectIdsForTasks, $user) {
                $q->whereIn('project_id', $projectIdsForTasks)
                    ->orWhere(function ($sub) use ($user) {
                        $sub->whereNull('project_id')
                            ->where(function ($ownerCheck) use ($user) {
                                $ownerCheck->where('user_id', $user->id)
                                    ->orWhere('assigned_to', $user->id);
                            });
                    });
            })
            ->with(['project', 'assignedUser'])
            ->latest('deleted_at')
            ->get()
            ->map(function (Task $t) {
                return [
                    'id' => $t->id,
                    'title' => $t->title,
                    'code' => 'WH-'.str_pad((string) $t->id, 3, '0', STR_PAD_LEFT),
                    'project_name' => $t->project ? $t->project->name : 'Personal Task',
                    'project_theme' => $t->project?->theme ?: '#10b981',
                    'assigned_to_name' => $t->assignedUser?->name,
                    'deleted_at' => $t->deleted_at ? $t->deleted_at->format('M d, Y h:i A') : '',
                    'deleted_at_human' => $t->deleted_at ? $t->deleted_at->diffForHumans() : '',
                ];
            })->values()->toArray();

        // 2. Trashed Projects
        $adminCompanyIds = CompanyUsers::withTrashed()
            ->where('user_id', $user->id)
            ->where('role', 1)
            ->pluck('company_id')
            ->toArray();

        $projects = Project::onlyTrashed()
            ->where(function ($q) use ($adminCompanyIds, $user) {
                $q->whereIn('company_id', $adminCompanyIds)
                    ->orWhere(function ($sub) use ($user) {
                        $sub->whereNull('company_id')->where('user_id', $user->id);
                    });
            })
            ->with('company')
            ->latest('deleted_at')
            ->get()
            ->map(function (Project $p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'theme' => $p->theme ?: '#10b981',
                    'company_name' => $p->company ? $p->company->name : 'Personal Space',
                    'deleted_at' => $p->deleted_at ? $p->deleted_at->format('M d, Y h:i A') : '',
                    'deleted_at_human' => $p->deleted_at ? $p->deleted_at->diffForHumans() : '',
                ];
            })->values()->toArray();

        // 3. Trashed Companies
        $companies = Company::onlyTrashed()
            ->whereIn('id', $adminCompanyIds)
            ->latest('deleted_at')
            ->get()
            ->map(function (Company $c) {
                return [
                    'id' => $c->id,
                    'name' => $c->name,
                    'code' => $c->code,
                    'deleted_at' => $c->deleted_at ? $c->deleted_at->format('M d, Y h:i A') : '',
                    'deleted_at_human' => $c->deleted_at ? $c->deleted_at->diffForHumans() : '',
                ];
            })->values()->toArray();

        $stats = [
            'total_trashed' => count($tasks) + count($projects) + count($companies),
            'tasks_count' => count($tasks),
            'projects_count' => count($projects),
            'companies_count' => count($companies),
        ];

        return Inertia::render('New/Trash', [
            'trashed_tasks' => $tasks,
            'trashed_projects' => $projects,
            'trashed_companies' => $companies,
            'stats' => $stats,
        ]);
    }

    public function restoreTask($id)
    {
        $task = Task::onlyTrashed()->findOrFail($id);
        Gate::authorize('restore', $task);

        if ($task->project_id) {
            $parentProject = Project::withTrashed()->find($task->project_id);
            if ($parentProject && $parentProject->trashed()) {
                return redirect()->back()->with('error', "Cannot restore task because parent project '{$parentProject->name}' is in the Recycle Bin. Please restore the project first.");
            }
        }

        $task->restore();

        return redirect()->back()->with('success', "Task '{$task->title}' restored successfully.");
    }

    public function forceDeleteTask($id)
    {
        $task = Task::onlyTrashed()->findOrFail($id);
        Gate::authorize('forceDelete', $task);

        $task->forceDelete();

        return redirect()->back()->with('success', 'Task permanently deleted.');
    }

    public function restoreProject($id)
    {
        $project = Project::onlyTrashed()->findOrFail($id);
        Gate::authorize('restore', $project);

        $project->restore();

        return redirect()->back()->with('success', "Project '{$project->name}' restored successfully.");
    }

    public function forceDeleteProject($id)
    {
        $project = Project::onlyTrashed()->findOrFail($id);
        Gate::authorize('forceDelete', $project);

        $project->tasks()->forceDelete();
        $project->forceDelete();

        return redirect()->back()->with('success', 'Project permanently deleted.');
    }

    public function restoreCompany($id)
    {
        $company = Company::onlyTrashed()->findOrFail($id);
        Gate::authorize('restore', $company);

        $company->restore();
        CompanyUsers::onlyTrashed()->where('company_id', $company->id)->restore();

        return redirect()->back()->with('success', "Organization '{$company->name}' restored successfully.");
    }

    public function forceDeleteCompany($id)
    {
        $company = Company::onlyTrashed()->findOrFail($id);
        Gate::authorize('forceDelete', $company);

        $projectIds = Project::withTrashed()->where('company_id', $company->id)->pluck('id')->toArray();
        Task::withTrashed()->whereIn('project_id', $projectIds)->forceDelete();
        Project::withTrashed()->where('company_id', $company->id)->forceDelete();
        CompanyUsers::withTrashed()->where('company_id', $company->id)->forceDelete();
        $company->forceDelete();

        return redirect()->back()->with('success', 'Organization permanently deleted.');
    }
}
