<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;


class TaskController extends Controller
{
public function index()
{
$tasks = Task::latest()->get();
return view('tasks.index', compact('tasks'));
}


public function store(Request $request)
{
$request->validate([
'title' => 'required|min:3'
]);


Task::create([
'title' => $request->title,
'description' => $request->description
]);


return redirect('/tasks')->with('success', 'Task Added');
}


public function destroy($id)
{
Task::findOrFail($id)->delete();
return redirect('/tasks')->with('success', 'Task Deleted');
}
}
