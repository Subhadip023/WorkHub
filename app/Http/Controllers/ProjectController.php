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
    $user = auth()->user();

    // Admin safety check (extra, middleware already does this)
    if ($user->role !== 'admin') {
        abort(403);
    }

    $company = $user->company;

    // Fetch company members
    $members = \App\Models\User::where('company_id', $company->id)->get();

    return view('projects.create', [
        'company' => $company,
        'members' => $members
    ]);
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
