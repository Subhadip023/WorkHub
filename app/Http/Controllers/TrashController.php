<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\CompanyUsers;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Support\Facades\Gate;

class TrashController extends Controller
{
    /**
     * Display a listing of trashed resources.
     */
    public function index()
    {
        $user = auth()->user();
        if (! $user) {
            abort(401);
        }

        $allCompanyIds = CompanyUsers::withTrashed()
            ->where('user_id', $user->id)
            ->pluck('company_id')
            ->toArray();

        // 1. Fetch Trashed Tasks
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
            ->get();

        // 2. Fetch Trashed Projects
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
            ->get();

        // 3. Fetch Trashed Companies
        $deletedCompanyIds = CompanyUsers::onlyTrashed()
            ->where('user_id', $user->id)
            ->where('role', 1)
            ->pluck('company_id')
            ->toArray();

        $companies = Company::onlyTrashed()
            ->whereIn('id', $deletedCompanyIds)
            ->latest('deleted_at')
            ->get();

        // 4. Fetch Trashed Members (where user is admin in the target company)
        $currentUserAdminCompanyIds = CompanyUsers::withTrashed()
            ->where('user_id', $user->id)
            ->where('role', 1)
            ->pluck('company_id')
            ->toArray();

        $trashedMembers = CompanyUsers::onlyTrashed()
            ->whereIn('company_id', $currentUserAdminCompanyIds)
            ->with(['user', 'company'])
            ->latest('deleted_at')
            ->get();

        return view('trash.index', compact('tasks', 'projects', 'companies', 'trashedMembers'));
    }

    /**
     * Restore a deleted task.
     */
    public function restoreTask($id)
    {
        $task = Task::onlyTrashed()->findOrFail($id);

        if ($task->project_id) {
            $project = Project::withTrashed()->find($task->project_id);
            if (! $project) {
                abort(404);
            }
            if ($project->trashed()) {
                return redirect()->back()->with('error', 'Cannot restore task because its project is in the trash. Please restore the project first.');
            }
        }

        Gate::authorize('restore', $task);

        $task->restore();

        return redirect()->back()->with('success', 'Task restored successfully.');
    }

    /**
     * Permanently delete a task.
     */
    public function forceDeleteTask($id)
    {
        $task = Task::onlyTrashed()->findOrFail($id);

        Gate::authorize('forceDelete', $task);

        $task->forceDelete();

        return redirect()->back()->with('success', 'Task permanently deleted.');
    }

    /**
     * Restore a deleted project.
     */
    public function restoreProject($id)
    {
        $project = Project::onlyTrashed()->findOrFail($id);

        Gate::authorize('restore', $project);

        $project->restore();

        return redirect()->back()->with('success', 'Project restored successfully.');
    }

    /**
     * Permanently delete a project.
     */
    public function forceDeleteProject($id)
    {
        $project = Project::onlyTrashed()->findOrFail($id);

        Gate::authorize('forceDelete', $project);

        // Clean up tasks associated with this project permanently
        $project->tasks()->forceDelete();
        $project->forceDelete();

        return redirect()->back()->with('success', 'Project and its tasks permanently deleted.');
    }

    /**
     * Restore a deleted company.
     */
    public function restoreCompany($id)
    {
        $company = Company::onlyTrashed()->findOrFail($id);

        Gate::authorize('restore', $company);

        // Restore Company
        $company->restore();

        // Restore all membership records
        CompanyUsers::onlyTrashed()->where('company_id', $company->id)->restore();

        return redirect()->back()->with('success', 'Organization and members restored successfully.');
    }

    /**
     * Permanently delete a company.
     */
    public function forceDeleteCompany($id)
    {
        $company = Company::onlyTrashed()->findOrFail($id);

        Gate::authorize('forceDelete', $company);

        // 1. Force delete all tasks and projects in this company
        $projectIds = Project::withTrashed()->where('company_id', $company->id)->pluck('id')->toArray();
        Task::withTrashed()->whereIn('project_id', $projectIds)->forceDelete();
        Project::withTrashed()->where('company_id', $company->id)->forceDelete();

        // 2. Force delete membership records
        CompanyUsers::withTrashed()->where('company_id', $company->id)->forceDelete();

        // 3. Force delete the company
        $company->forceDelete();

        return redirect()->back()->with('success', 'Organization and all associated data permanently deleted.');
    }

    /**
     * Restore a deleted company member.
     */
    public function restoreMember($id)
    {
        $membership = CompanyUsers::onlyTrashed()->findOrFail($id);

        Gate::authorize('restore', $membership);

        $membership->restore();

        return redirect()->back()->with('success', 'Organization member restored successfully.');
    }

    /**
     * Permanently delete a company member.
     */
    public function forceDeleteMember($id)
    {
        $membership = CompanyUsers::onlyTrashed()->findOrFail($id);

        Gate::authorize('forceDelete', $membership);

        $membership->forceDelete();

        return redirect()->back()->with('success', 'Member record permanently deleted.');
    }
}
