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
    $user = auth()->user();
    $company = $user->company;

    // Default empty collection
    $members = collect();

    // Only admin can see members
    if ($user->isCompanyAdmin()) {
        $members = $company->users()->get();
    }

    return view('projects.create', compact('company', 'members'));
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
        // dd(auth()->user()->id);
        Project::create([
            'name' => $request->name,
            'description' => $request->description,
            'company_id' => Auth::user()->company_id,
            'created_by' => auth()->user()->id,
        ]);

        return redirect()->route('projects.index')
            ->with('success', 'Project created successfully');
    }
}
