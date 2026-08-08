<?php

namespace App\Http\Controllers;

use App\Models\CompanyUsers;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskImage;
use App\Repositories\TaskRepositoryInterface;
use App\Services\TaskServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TaskController extends Controller
{
    public function __construct(
        protected readonly TaskRepositoryInterface $taskRepository,
        protected readonly TaskServiceInterface $taskService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Query accessible projects for filter dropdown
        $companyIds = $user->companies()->pluck('company_id')->toArray();
        $projects = Project::select('id', 'name')->whereIn('company_id', $companyIds)
            ->orWhere(function ($query) use ($user) {
                $query->whereNull('company_id')->where('user_id', $user->id);
            })
            ->get();

        $companyUsers = $this->taskRepository->getAccessibleCompanyUsers($user);
        $stats = $this->taskRepository->getTaskStatsForUser($user);

        $filters = $request->only(['project', 'status', 'assignee', 'type', 'show_completed']);
        $tasks = $this->taskRepository->getFilteredTasksForUser($user, $filters, 5);

        $user_role = 1;

        return view('tasks.index', array_merge([
            'tasks' => $tasks,
            'projects' => $projects,
            'companyUsers' => $companyUsers,
            'user_role' => $user_role,
        ], $stats));
    }

    /**
     * Store a newly created task from the general tasks page.
     */
    public function storeGeneral(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
            'status' => 'nullable|integer|in:1,2,3,4',
            'priority' => 'nullable|integer|in:1,2,3,4',
            'type' => 'nullable|integer|in:1,2,3,4',
        ]);

        $project = ! empty($validated['project_id']) ? Project::findOrFail($validated['project_id']) : null;

        if ($project) {
            Gate::authorize('update', $project);
        }

        $this->taskService->createTask($validated, $project, auth()->user());

        return redirect()->route('tasks.index')->with('success', 'Task created successfully');
    }

    /**
     * Store a newly created task in storage.
     */
    public function store(Request $request, Project $project)
    {
        Gate::authorize('update', $project);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
            'status' => 'nullable|integer|in:1,2,3,4',
            'priority' => 'nullable|integer|in:1,2,3,4',
            'type' => 'nullable|integer|in:1,2,3,4',
        ]);

        if (empty($validated['assigned_to'])) {
            $validated['assigned_to'] = null;
        }

        $this->taskService->createTask($validated, $project, auth()->user());

        return redirect()->route('projects.show', $project)->with('success', 'Task created successfully');
    }

    /**
     * Check if the authenticated user has permission to mutate the given task.
     */
    protected function checkTaskOwnership(Task $task)
    {
        Gate::authorize('update', $task);
    }

    /**
     * Toggle the status of the task.
     */
    public function toggle(Task $task)
    {
        $this->checkTaskOwnership($task);

        $task = $this->taskService->toggleTaskStatus($task);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Task status updated',
                'status' => $task->status,
                'task' => $task->fresh(['project', 'assignedUser']),
            ]);
        }

        return redirect()->back()->with('success', 'Task status updated');
    }

    /**
     * Update the specified task.
     */
    public function update(Request $request, Task $task)
    {
        $this->checkTaskOwnership($task);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'project_id' => 'nullable|exists:projects,id',
            'assigned_to' => 'nullable|exists:users,id',
            'status' => 'nullable|integer|in:1,2,3,4',
            'priority' => 'nullable|integer|in:1,2,3,4',
            'type' => 'nullable|integer|in:1,2,3,4',
        ]);

        $task = $this->taskService->updateTask($task, $validated, auth()->user());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Task updated successfully',
                'task' => $task->fresh(['project', 'assignedUser']),
            ]);
        }

        return redirect()->back()->with('success', 'Task updated successfully');
    }

    /**
     * Remove the specified task from storage.
     */
    public function destroy(Task $task)
    {
        $this->checkTaskOwnership($task);

        $previousUrl = url()->previous();
        $taskShowUrl = route('tasks.show', $task);
        $projectId = $task->project_id;

        $this->taskService->deleteTask($task, auth()->user());

        if ($previousUrl === $taskShowUrl || str_contains($previousUrl, "/tasks/{$task->id}")) {
            if ($projectId) {
                return redirect()->route('projects.show', $projectId)->with('success', 'Task deleted successfully.');
            }

            return redirect()->route('tasks.index')->with('success', 'Task deleted successfully.');
        }

        return redirect()->back()->with('success', 'Task deleted successfully.');
    }

    /**
     * Import multiple tasks via a JSON array.
     */
    public function import(Request $request, Project $project)
    {
        Gate::authorize('update', $project);

        $request->validate([
            'json_data' => 'required|string',
        ]);

        $data = json_decode($request->input('json_data'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return redirect()->back()->with('error', 'Invalid JSON format: '.json_last_error_msg());
        }

        if (is_array($data) && isset($data['title'])) {
            $data = [$data];
        }

        if (! is_array($data)) {
            return redirect()->back()->with('error', 'JSON must be an array of tasks or a single task object.');
        }

        $res = $this->taskService->importTasks($data, $project, auth()->user());

        $message = "{$res['imported']} task(s) imported successfully.";
        if ($res['skipped'] > 0) {
            $message .= " {$res['skipped']} item(s) skipped (missing title).";
        }

        return redirect()->route('projects.show', $project)->with('success', $message);
    }

    /**
     * Display the specified task details.
     */
    public function show(Task $task)
    {
        $project = $task->project;
        $user_id = auth()->id();

        Gate::authorize('view', $task);

        if ($project === null) {
            $companyUsers = $this->taskRepository->getAccessibleCompanyUsers(auth()->user());
            $user_role = 1;
        } elseif ($project->company_id === null) {
            $companyUsers = collect([auth()->user()]);
            $user_role = 1;
        } else {
            $membership = CompanyUsers::where('company_id', $project->company_id)
                ->where('user_id', $user_id)
                ->first();

            $companyUsers = CompanyUsers::where('company_id', $project->company_id)
                ->with('user')
                ->get()
                ->map(function ($cu) {
                    return $cu->user;
                })
                ->filter()
                ->values();

            $user_role = $membership ? $membership->role : 1;
        }

        $task->load(['project', 'assignedUser', 'images', 'histories.user']);

        $comments = $task->comments()->with('user')->latest()->get();

        return view('tasks.show', compact('task', 'companyUsers', 'user_role', 'comments'));
    }

    /**
     * Upload an image for the task.
     */
    public function uploadImage(Request $request, Task $task)
    {
        $this->checkTaskOwnership($task);

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        if ($request->hasFile('image')) {
            $this->taskService->uploadImage($task, $request->file('image'));

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

        $this->checkTaskOwnership($task);

        $this->taskService->deleteImage($image);

        return redirect()->back()->with('success', 'Image deleted successfully');
    }
}
