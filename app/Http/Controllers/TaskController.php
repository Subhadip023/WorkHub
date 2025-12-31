<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller // ✅ must extend App\Http\Controllers\Controller
{
    public function index(Project $project)
    {
        $this->authorize('viewAny', [Task::class, $project]);
        $tasks = $project->tasks()->latest()->get();
        return view('tasks.index', compact('project', 'tasks'));
    }

    public function store(Request $request, Project $project)
    {
        $this->authorize('create', [Task::class, $project]);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'status' => 'todo',
            'project_id' => $project->id,
            'user_id' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Task created successfully!');
    }
}
