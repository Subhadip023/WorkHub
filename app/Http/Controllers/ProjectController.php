<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::where('company_id', Auth::user()->company_id)->get();
        return view('projects.index', compact('projects'));
    }

    public function create()
    {
        return view('projects.create');
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required']);

        Project::create([
            'name' => $request->name,
            'company_id' => Auth::user()->company_id,
            'created_by' => Auth::id(),
        ]);

        return redirect('/projects');
    }
}
