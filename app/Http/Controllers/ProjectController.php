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
        $user = auth()->user();
        $companyIds = $user->companies()->pluck('company_id')->toArray();

        $projects = Project::whereIn('company_id', $companyIds)
            ->orWhere(function ($query) use ($user) {
                $query->whereNull('company_id')->where('user_id', $user->id);
            })
            ->with('tasks')
            ->paginate(10);

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
        $user_id = auth()->id();
        
        if ($project->company_id === null) {
            if ($project->user_id !== $user_id) {
                abort(403);
            }
            
            $project->load('tasks.assignedUser');
            $companyUsers = collect([auth()->user()]);
            $user_role = 1; // Admin of their personal space
        } else {
            $membership = \App\Models\CompanyUsers::where('company_id', $project->company_id)
                ->where('user_id', $user_id)
                ->first();

            if (!$membership) {
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

            $user_role = $membership->role;
        }

        return view('projects.show', compact('project', 'companyUsers', 'user_role'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project)
    {
        $user_id = auth()->id();
        if ($project->company_id === null) {
            if ($project->user_id !== $user_id) {
                abort(403);
            }
        } else {
            $membership = \App\Models\CompanyUsers::where('company_id', $project->company_id)
                ->where('user_id', $user_id)
                ->exists();
            if (!$membership) {
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
        $user_id = auth()->id();
        if ($project->company_id === null) {
            if ($project->user_id !== $user_id) {
                abort(403);
            }
        } else {
            $membership = \App\Models\CompanyUsers::where('company_id', $project->company_id)
                ->where('user_id', $user_id)
                ->exists();
            if (!$membership) {
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
        $user_id = auth()->id();
        if ($project->company_id === null) {
            if ($project->user_id !== $user_id) {
                abort(403);
            }
        } else {
            $membership = \App\Models\CompanyUsers::where('company_id', $project->company_id)
                ->where('user_id', $user_id)
                ->first();
            if (!$membership || $membership->role !== 1) {
                abort(403, 'Only organization admins can delete projects.');
            }
        }

        $project->delete();
        return redirect()->route('projects.index')->with('success', 'Project deleted successfully');
    }
}
