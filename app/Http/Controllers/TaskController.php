<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function create()
    {
        $employees = User::where('role', 'employee')->get();
        return view('tasks.create', compact('employees'));
    }

    public function store(Request $request)
    {
        Task::create($request->all());
        return redirect()->back()->with('success', 'Task Assigned');
    }
}

