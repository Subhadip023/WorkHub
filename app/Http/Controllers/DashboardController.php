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
        $companies = $auth_user->companies;
        
        $current_company = session('current_company_id');
        if (!$current_company || !$companies->contains('company_id', $current_company)) {
            $current_company = $companies->first()->company_id ?? null;
        }

        if (!isset($current_company)) {
            return redirect()->route('companies.create')->with('error', 'Please create a company first');
        }

        $user_role = CompanyUsers::where('company_id', $current_company)->where('user_id', $auth_user->id)->first()->role ?? null;
        $current_company_data = Company::where('id', $current_company)->first();

        session([
            'current_company_id' => $current_company,
            'current_role' => $user_role,
            'current_company_data' => $current_company_data,
            'code' => $current_company_data->code
        ]);

        $projects = \App\Models\Project::where('company_id', $current_company)->with('tasks')->get();
        $projectsCount = $projects->count();

        $totalTasks = 0;
        $completedTasks = 0;
        foreach ($projects as $project) {
            $totalTasks += $project->tasks->count();
            $completedTasks += $project->tasks->where('is_completed', true)->count();
        }

        $teamMembers = CompanyUsers::where('company_id', $current_company)
            ->with('user')
            ->get();

        return view('welcome', compact('projects', 'projectsCount', 'totalTasks', 'completedTasks', 'teamMembers'));
    }
}
