<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $current_company = session('current_company_id');
        if (!$current_company) {
            return redirect()->route('companies.create')->with('error', 'Please create a company first');
        }

        // Fetch projects for filtering and association
        $projects = Project::where('company_id', $current_company)->get();

        // Base query for tasks in this company
        $tasksQuery = Task::whereHas('project', function ($query) use ($current_company) {
            $query->where('company_id', $current_company);
        });

        // Compute company-wide stats before pagination
        $totalCount = (clone $tasksQuery)->count();
        $completedCount = (clone $tasksQuery)->where('is_completed', true)->count();
        $pendingCount = $totalCount - $completedCount;
        $overdueCount = (clone $tasksQuery)->where('is_completed', false)
            ->whereNotNull('due_date')
            ->where('due_date', '<', now()->toDateString())
            ->count();

        // Fetch paginated tasks
        $tasks = $tasksQuery->with(['project', 'assignedUser'])->paginate(15);

        // Fetch team members of the company for dropdowns
        $companyUsers = \App\Models\CompanyUsers::where('company_id', $current_company)
            ->with('user')
            ->get()
            ->map(function ($cu) {
                return $cu->user;
            })
            ->filter()
            ->values();

        $user_role = \App\Models\CompanyUsers::where('company_id', $current_company)
            ->where('user_id', auth()->id())
            ->first()->role ?? 0;

        return view('tasks.index', compact(
            'tasks', 
            'projects', 
            'companyUsers', 
            'user_role', 
            'totalCount', 
            'completedCount', 
            'pendingCount', 
            'overdueCount'
        ));
    }

    /**
     * Store a newly created task from the general tasks page.
     */
    public function storeGeneral(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $project = Project::findOrFail($validated['project_id']);
        if ($project->company_id != session('current_company_id')) {
            abort(403);
        }

        if (empty($validated['assigned_to'])) {
            $validated['assigned_to'] = auth()->id();
        }

        $project->tasks()->create($validated);

        return redirect()->route('tasks.index')->with('success', 'Task created successfully');
    }

    /**
     * Store a newly created task in storage.
     */
    public function store(Request $request, Project $project)
    {
        if ($project->company_id != session('current_company_id')) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        if (empty($validated['assigned_to'])) {
            $validated['assigned_to'] = auth()->id();
        }

        $project->tasks()->create($validated);

        return redirect()->route('projects.show', $project)->with('success', 'Task created successfully');
    }

    /**
     * Check if the authenticated user has permission to mutate the given task.
     */
    protected function checkTaskOwnership(Task $task)
    {
        if ($task->project->company_id != session('current_company_id')) {
            abort(403);
        }

        $user_role = \App\Models\CompanyUsers::where('company_id', session('current_company_id'))
            ->where('user_id', auth()->id())
            ->first()->role ?? 0;

        if ($user_role == 0 && $task->assigned_to !== auth()->id()) {
            abort(403, 'You can only modify tasks assigned to you.');
        }
    }

    /**
     * Toggle the status of the task.
     */
    public function toggle(Task $task)
    {
        $this->checkTaskOwnership($task);

        $task->update([
            'is_completed' => !$task->is_completed,
        ]);

        return redirect()->back()->with('success', 'Task status updated');
    }

    /**
     * Update the specified task.
     */
    public function update(Request $request, Task $task)
    {
        $this->checkTaskOwnership($task);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $task->update($validated);

        return redirect()->back()->with('success', 'Task updated successfully');
    }

    /**
     * Remove the specified task from storage.
     */
    public function destroy(Task $task)
    {
        $this->checkTaskOwnership($task);

        $task->delete();

        return redirect()->back()->with('success', 'Task deleted successfully');
    }

    /**
     * Import multiple tasks via a JSON array.
     */
    public function import(Request $request, Project $project)
    {
        if ($project->company_id != session('current_company_id')) {
            abort(403);
        }

        $request->validate([
            'json_data' => 'required|string',
        ]);

        $data = json_decode($request->input('json_data'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return redirect()->back()->with('error', 'Invalid JSON format: ' . json_last_error_msg());
        }

        // Normalize to array of objects if a single object was passed
        if (is_array($data) && isset($data['title'])) {
            $data = [$data];
        }

        if (!is_array($data)) {
            return redirect()->back()->with('error', 'JSON must be an array of tasks or a single task object.');
        }

        $count = 0;
        foreach ($data as $item) {
            if (!empty($item['title'])) {
                $project->tasks()->create([
                    'title' => $item['title'],
                    'description' => $item['description'] ?? null,
                    'due_date' => $item['due_date'] ?? null,
                    'assigned_to' => $item['assigned_to'] ?? auth()->id(),
                    'is_completed' => $item['is_completed'] ?? false,
                ]);
                $count++;
            }
        }

        return redirect()->route('projects.show', $project)->with('success', "$count tasks imported successfully");
    }

    /**
     * Display the specified task details.
     */
    public function show(Task $task)
    {
        if ($task->project->company_id != session('current_company_id')) {
            abort(403);
        }

        $task->load(['project', 'assignedUser', 'images']);

        $current_company = session('current_company_id');
        $companyUsers = \App\Models\CompanyUsers::where('company_id', $current_company)
            ->with('user')
            ->get()
            ->map(function ($cu) {
                return $cu->user;
            })
            ->filter()
            ->values();

        $user_role = \App\Models\CompanyUsers::where('company_id', $current_company)
            ->where('user_id', auth()->id())
            ->first()->role ?? 0;

        return view('tasks.show', compact('task', 'companyUsers', 'user_role'));
    }

    /**
     * Upload an image for the task.
     */
    public function uploadImage(Request $request, Task $task)
    {
        if ($task->project->company_id != session('current_company_id')) {
            abort(403);
        }

        $this->checkTaskOwnership($task);

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('task_images', 'public');

            $task->images()->create([
                'image_path' => $path,
            ]);

            return redirect()->back()->with('success', 'Image uploaded successfully');
        }

        return redirect()->back()->with('error', 'Failed to upload image');
    }

    /**
     * Delete a task image.
     */
    public function deleteImage(Request $request, \App\Models\TaskImage $image)
    {
        $task = $image->task;

        if ($task->project->company_id != session('current_company_id')) {
            abort(403);
        }

        $this->checkTaskOwnership($task);

        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($image->image_path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($image->image_path);
        }

        $image->delete();

        return redirect()->back()->with('success', 'Image deleted successfully');
    }
}
