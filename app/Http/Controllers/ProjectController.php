<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    /**
     * List projects of logged-in user's company
     */
    public function index()
    {
        $projects = Project::where('company_id', Auth::user()->company_id)->get();

        return view('projects.index', compact('projects'));
    }

    /**
     * Show create project form
     */
    public function create()
    {
        return view('projects.create');
    }

    /**
     * Store new project
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        Project::create([
            'name' => $request->name,
            'description' => $request->description,
            'company_id' => Auth::user()->company_id,
        ]);

        return redirect()->route('projects.index')
            ->with('success', 'Project created successfully');
    }
}
