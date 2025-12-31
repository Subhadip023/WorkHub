<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function index(Project $project)
    {
        if ($project->company_id !== Auth::user()->company_id) {
            abort(403);
        }

        $tasks = $project->tasks()->latest()->get();

        return view('tasks.index', compact('tasks', 'project'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'project_id' => 'required'
        ]);

        $project = Project::where('id', $request->project_id)
            ->where('company_id', Auth::user()->company_id)
            ->firstOrFail();

        Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'project_id' => $project->id,
            'user_id' => Auth::id(),
            'status' => 'pending',
        ]);

        return back();
    }
}
