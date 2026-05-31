<?php

namespace App\Http\Controllers;

use App\Models\CompanyUsers;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $companyIds = $user->companies()->pluck('company_id')->toArray();

        // Fetch all projects (both personal and organizational)
        $projects = Project::whereIn('company_id', $companyIds)
            ->orWhere(function ($query) use ($user) {
                $query->whereNull('company_id')->where('user_id', $user->id);
            })
            ->get();

        $projectIds = $projects->pluck('id')->toArray();

        // Base query for all accessible tasks
        $tasksQuery = Task::whereIn('project_id', $projectIds);

        // Fetch all unique team members from all companies the user belongs to
        $companyUsers = CompanyUsers::whereIn('company_id', $companyIds)
            ->with('user')
            ->get()
            ->map(function ($cu) {
                return $cu->user;
            })
            ->filter()
            ->unique('id')
            ->values();

        if (! $companyUsers->contains('id', $user->id)) {
            $companyUsers->push($user);
        }

        // Compute stats before pagination
        $totalCount = (clone $tasksQuery)->count();
        $completedCount = (clone $tasksQuery)->where('status', 3)->count();
        $pendingCount = $totalCount - $completedCount;
        $overdueCount = (clone $tasksQuery)->where('status', '!=', 3)
            ->whereNotNull('due_date')
            ->where('due_date', '<', now()->toDateString())
            ->count();

        // Apply filters from query parameters
        if ($request->filled('project') && $request->project !== 'all') {
            $tasksQuery->where('project_id', $request->project);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            if ($request->status === 'completed') {
                $tasksQuery->where('status', 3);
            } elseif ($request->status === 'pending') {
                $tasksQuery->where('status', '!=', 3);
            }
        }

        if ($request->filled('assignee') && $request->assignee !== 'all') {
            if ($request->assignee === 'unassigned') {
                $tasksQuery->whereNull('assigned_to');
            } else {
                $tasksQuery->where('assigned_to', $request->assignee);
            }
        }

        // Fetch paginated tasks
        $tasks = $tasksQuery->with(['project', 'assignedUser'])->paginate(5);

        // Pass 1 as default role, since role is checked dynamically per task in the view now
        $user_role = 1;

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
            'status' => 'nullable|integer|in:1,2,3,4',
            'priority' => 'nullable|integer|in:1,2,3,4',
        ]);

        $project = Project::findOrFail($validated['project_id']);
        $user_id = auth()->id();

        if ($project->company_id === null) {
            if ($project->user_id !== $user_id) {
                abort(403);
            }
        } else {
            $membership = CompanyUsers::where('company_id', $project->company_id)
                ->where('user_id', $user_id)
                ->exists();
            if (! $membership) {
                abort(403);
            }
        }

        if (empty($validated['assigned_to'])) {
            $validated['assigned_to'] = $user_id;
        }

        $project->tasks()->create($validated);

        return redirect()->route('tasks.index')->with('success', 'Task created successfully');
    }

    /**
     * Store a newly created task in storage.
     */
    public function store(Request $request, Project $project)
    {
        $user_id = auth()->id();
        if ($project->company_id === null) {
            if ($project->user_id !== $user_id) {
                abort(403);
            }
        } else {
            $belongs = CompanyUsers::where('company_id', $project->company_id)
                ->where('user_id', $user_id)
                ->exists();
            if (! $belongs) {
                abort(403);
            }
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
            'status' => 'nullable|integer|in:1,2,3,4',
            'priority' => 'nullable|integer|in:1,2,3,4',
        ]);

        if (empty($validated['assigned_to'])) {
            $validated['assigned_to'] = $user_id;
        }

        $project->tasks()->create($validated);

        return redirect()->route('projects.show', $project)->with('success', 'Task created successfully');
    }

    /**
     * Check if the authenticated user has permission to mutate the given task.
     */
    protected function checkTaskOwnership(Task $task)
    {
        $project = $task->project;
        $user_id = auth()->id();

        if ($project->company_id === null) {
            if ($project->user_id !== $user_id) {
                abort(403);
            }
        } else {
            $membership = CompanyUsers::where('company_id', $project->company_id)
                ->where('user_id', $user_id)
                ->first();

            if (! $membership) {
                abort(403, 'You are not a member of this organization.');
            }

            if ($membership->role == 0 && $task->assigned_to !== $user_id) {
                abort(403, 'You can only modify tasks assigned to you.');
            }
        }
    }

    /**
     * Toggle the status of the task.
     */
    public function toggle(Task $task)
    {
        $this->checkTaskOwnership($task);

        $newStatus = ($task->status == 3) ? 1 : 3;
        $task->update([
            'status' => $newStatus,
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
            'status' => 'nullable|integer|in:1,2,3,4',
            'priority' => 'nullable|integer|in:1,2,3,4',
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
        $user_id = auth()->id();
        if ($project->company_id === null) {
            if ($project->user_id !== $user_id) {
                abort(403);
            }
        } else {
            $belongs = CompanyUsers::where('company_id', $project->company_id)
                ->where('user_id', $user_id)
                ->exists();
            if (! $belongs) {
                abort(403);
            }
        }

        $request->validate([
            'json_data' => 'required|string',
        ]);

        $data = json_decode($request->input('json_data'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return redirect()->back()->with('error', 'Invalid JSON format: '.json_last_error_msg());
        }

        // Normalize to array of objects if a single object was passed
        if (is_array($data) && isset($data['title'])) {
            $data = [$data];
        }

        if (! is_array($data)) {
            return redirect()->back()->with('error', 'JSON must be an array of tasks or a single task object.');
        }

        $count = 0;
        foreach ($data as $item) {
            if (! empty($item['title'])) {
                $status = $item['status'] ?? (($item['is_completed'] ?? false) ? 3 : 1);
                $priority = $item['priority'] ?? 2;
                $project->tasks()->create([
                    'title' => $item['title'],
                    'description' => $item['description'] ?? null,
                    'due_date' => $item['due_date'] ?? null,
                    'assigned_to' => $item['assigned_to'] ?? auth()->id(),
                    'status' => $status,
                    'priority' => $priority,
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
        $project = $task->project;
        $user_id = auth()->id();

        if ($project->company_id === null) {
            if ($project->user_id !== $user_id) {
                abort(403);
            }

            $companyUsers = collect([auth()->user()]);
            $user_role = 1;
        } else {
            $membership = CompanyUsers::where('company_id', $project->company_id)
                ->where('user_id', $user_id)
                ->first();

            if (! $membership) {
                abort(403);
            }

            $companyUsers = CompanyUsers::where('company_id', $project->company_id)
                ->with('user')
                ->get()
                ->map(function ($cu) {
                    return $cu->user;
                })
                ->filter()
                ->values();

            $user_role = $membership->role;
        }

        $task->load(['project', 'assignedUser', 'images', 'histories.user']);

        return view('tasks.show', compact('task', 'companyUsers', 'user_role'));
    }

    /**
     * Upload an image for the task.
     */
    public function uploadImage(Request $request, Task $task)
    {
        $current_company = session('current_company_id');
        if ($current_company === 'personal') {
            if ($task->project->company_id !== null || $task->project->user_id !== auth()->id()) {
                abort(403);
            }
        } else {
            if ($task->project->company_id != $current_company) {
                abort(403);
            }
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
    public function deleteImage(Request $request, TaskImage $image)
    {
        $task = $image->task;
        $current_company = session('current_company_id');
        if ($current_company === 'personal') {
            if ($task->project->company_id !== null || $task->project->user_id !== auth()->id()) {
                abort(403);
            }
        } else {
            if ($task->project->company_id != $current_company) {
                abort(403);
            }
        }

        $this->checkTaskOwnership($task);

        if (Storage::disk('public')->exists($image->image_path)) {
            Storage::disk('public')->delete($image->image_path);
        }

        $image->delete();

        return redirect()->back()->with('success', 'Image deleted successfully');
    }
}
