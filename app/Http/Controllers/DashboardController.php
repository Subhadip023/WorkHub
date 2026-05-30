<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use App\Models\CompanyUsers;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $auth_user = auth()->user();
        $companyIds = $auth_user->companies()->pluck('company_id')->toArray();

        // Fetch all projects (both personal and organizational)
        $projects = \App\Models\Project::whereIn('company_id', $companyIds)
            ->orWhere(function ($query) use ($auth_user) {
                $query->whereNull('company_id')->where('user_id', $auth_user->id);
            })
            ->with(['tasks', 'company'])
            ->get();

        // Fetch all team members from all companies they belong to
        $teamMembers = \App\Models\CompanyUsers::whereIn('company_id', $companyIds)
            ->with(['user', 'company'])
            ->get()
            ->unique('user_id');

        $projectsCount = $projects->count();

        $totalTasks = 0;
        $completedTasks = 0;
        foreach ($projects as $project) {
            $totalTasks += $project->tasks->count();
            $completedTasks += $project->tasks->where('is_completed', true)->count();
        }

        return view('welcome', compact('projects', 'projectsCount', 'totalTasks', 'completedTasks', 'teamMembers'));
    }
}
