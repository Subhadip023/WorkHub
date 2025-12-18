<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;

class TaskController extends Controller
{
    // Show all tasks
    public function index()
    {
        $tasks = Task::latest()->get();
        return view('tasks.index', compact('tasks'));
    }

    // Store new task
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|min:3',
            'description' => 'nullable'
        ]);

        Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'status' => 'pending'
        ]);

        return redirect('/tasks')->with('success', 'Task added successfully!');
    }

    // Delete task
    public function destroy($id)
    {
        Task::findOrFail($id)->delete();
        return redirect('/tasks')->with('success', 'Task deleted!');
    }
}
