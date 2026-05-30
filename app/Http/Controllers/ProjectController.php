<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $current_company = session('current_company_id');
        if ($current_company === 'personal') {
            $projects = Project::whereNull('company_id')
                ->where('user_id', auth()->id())
                ->with('tasks')
                ->paginate(10);
        } else {
            $projects = Project::where('company_id', $current_company)
                ->with('tasks')
                ->paginate(10);
        }
        return view('projects.index')->with('projects', $projects);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('projects.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProjectRequest $request)
    {
        $data = $request->validated();
        $data['slug'] = str_replace(' ', '-', strtolower($data['name']));
        
        $current_company = session('current_company_id');
        if ($current_company === 'personal') {
            $data['company_id'] = null;
        } else {
            $data['company_id'] = $current_company;
        }
        $data['user_id'] = auth()->id();

        Project::create($data);
        return redirect()->route('projects.index')->with('success', 'Project created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        $current_company = session('current_company_id');
        
        if ($current_company === 'personal') {
            if ($project->company_id !== null || $project->user_id !== auth()->id()) {
                abort(403);
            }
            
            $project->load('tasks.assignedUser');
            $companyUsers = collect([auth()->user()]);
            $user_role = 1; // Admin of their personal space
        } else {
            if ($project->company_id != $current_company) {
                abort(403);
            }

            $project->load('tasks.assignedUser');

            $companyUsers = \App\Models\CompanyUsers::where('company_id', $project->company_id)
                ->with('user')
                ->get()
                ->map(function ($cu) {
                    return $cu->user;
                })
                ->filter()
                ->values();

            $user_role = \App\Models\CompanyUsers::where('company_id', $project->company_id)
                ->where('user_id', auth()->id())
                ->first()->role ?? 0;
        }

        return view('projects.show', compact('project', 'companyUsers', 'user_role'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project)
    {
        $current_company = session('current_company_id');
        if ($current_company === 'personal') {
            if ($project->company_id !== null || $project->user_id !== auth()->id()) {
                abort(403);
            }
        } else {
            if ($project->company_id != $current_company) {
                abort(403);
            }
        }
        return view('projects.edit', compact('project'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProjectRequest $request, Project $project)
    {
        $current_company = session('current_company_id');
        if ($current_company === 'personal') {
            if ($project->company_id !== null || $project->user_id !== auth()->id()) {
                abort(403);
            }
        } else {
            if ($project->company_id != $current_company) {
                abort(403);
            }
        }

        $data = $request->validated();
        $data['slug'] = str_replace(' ', '-', strtolower($data['name']));
        $project->update($data);

        return redirect()->route('projects.index')->with('success', 'Project updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        $current_company = session('current_company_id');
        if ($current_company === 'personal') {
            if ($project->company_id !== null || $project->user_id !== auth()->id()) {
                abort(403);
            }
        } else {
            if ($project->company_id != $current_company) {
                abort(403);
            }
            
            // Check if user is admin of the company to delete
            $is_admin = \App\Models\CompanyUsers::where('company_id', $current_company)
                ->where('user_id', auth()->id())
                ->where('role', 1)
                ->exists();
            if (!$is_admin) {
                abort(403, 'Only organization admins can delete projects.');
            }
        }

        $project->delete();
        return redirect()->route('projects.index')->with('success', 'Project deleted successfully');
    }
}
