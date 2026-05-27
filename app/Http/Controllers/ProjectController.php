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
        $projects = Project::where('company_id', session('current_company_id'))->with('tasks')->paginate(10);
        return view('projects.index')->with('projects', $projects);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProjectRequest $request)
    {
        $data=$request->validated();
        $data['slug'] = str_replace(' ', '-', strtolower($data['name']));
        $data['company_id'] = session('current_company_id');
        Project::create($data);
        return redirect()->route('projects.index')->with('success', 'Project created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        if ($project->company_id != session('current_company_id')) {
            abort(403);
        }

        $project->load('tasks.assignedUser');

        $companyUsers = \App\Models\CompanyUsers::where('company_id', $project->company_id)
            ->with('user')
            ->get()
            ->map(function ($cu) {
                return $cu->user;
            });

        $user_role = \App\Models\CompanyUsers::where('company_id', $project->company_id)
            ->where('user_id', auth()->id())
            ->first()->role ?? 0;

        return view('projects.show', compact('project', 'companyUsers', 'user_role'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProjectRequest $request, Project $project)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        //
    }
}
