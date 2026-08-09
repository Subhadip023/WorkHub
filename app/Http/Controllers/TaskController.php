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
        $projects = Project::select('id', 'name', 'theme')->whereIn('company_id', $companyIds)
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
            'points' => 'nullable|integer|min:0|max:99999',
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
            'points' => 'nullable|integer|min:0|max:99999',
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
            'points' => 'nullable|integer|min:0|max:99999',
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
     * Copy / Duplicate the specified task.
     */
    public function copy(Task $task)
    {
        $this->checkTaskOwnership($task);

        $newTask = $this->taskService->copyTask($task, auth()->user());

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Task copied successfully',
                'task' => $newTask,
                'redirect' => route('tasks.show', $newTask),
            ]);
        }

        return redirect()->route('tasks.show', $newTask)->with('success', 'Task copied successfully');
    }

    /**
     * Import multiple tasks via a JSON array, JSON file, or CSV file.
     */
    public function import(Request $request, Project $project)
    {
        Gate::authorize('update', $project);

        $request->validate([
            'json_data' => 'nullable|string',
            'import_file' => 'nullable|file|mimes:json,csv,txt|max:5120',
        ]);

        $data = [];

        if ($request->hasFile('import_file')) {
            $file = $request->file('import_file');
            $extension = strtolower($file->getClientOriginalExtension());
            $content = file_get_contents($file->getRealPath());

            if ($extension === 'json') {
                $data = json_decode($content, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return redirect()->back()->with('error', 'Invalid JSON file: '.json_last_error_msg());
                }
            } elseif (in_array($extension, ['csv', 'txt'])) {
                $data = $this->parseCsvContent($content);
            }
        } elseif ($request->filled('json_data')) {
            $rawContent = trim($request->input('json_data'));
            if (str_starts_with($rawContent, '[') || str_starts_with($rawContent, '{')) {
                $data = json_decode($rawContent, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return redirect()->back()->with('error', 'Invalid JSON format: '.json_last_error_msg());
                }
            } else {
                $data = $this->parseCsvContent($rawContent);
            }
        } else {
            return redirect()->back()->with('error', 'Please paste JSON/CSV data or upload a file.');
        }

        if (is_array($data) && isset($data['title'])) {
            $data = [$data];
        }

        if (! is_array($data) || empty($data)) {
            return redirect()->back()->with('error', 'No valid tasks found to import.');
        }

        $res = $this->taskService->importTasks($data, $project, auth()->user());

        $message = "{$res['imported']} task(s) imported successfully.";
        if (isset($res['subtasks']) && $res['subtasks'] > 0) {
            $message .= " (Includes {$res['subtasks']} subtasks).";
        }
        if ($res['skipped'] > 0) {
            $message .= " {$res['skipped']} item(s) skipped (missing title).";
        }

        return redirect()->route('projects.show', $project)->with('success', $message);
    }

    /**
     * Parse raw CSV content into array of associative rows based on headers.
     */
    private function parseCsvContent(string $content): array
    {
        $trimmed = trim($content);
        if ($trimmed === '') {
            return [];
        }

        $lines = explode("\n", str_replace("\r\n", "\n", $trimmed));
        $headerLine = array_shift($lines);
        if ($headerLine === '') {
            return [];
        }

        $headers = str_getcsv($headerLine);
        $headers = array_map(function ($h) {
            return strtolower(trim((string) preg_replace('/[^a-zA-Z0-9_]/', '', str_replace(' ', '_', (string) $h))));
        }, $headers);

        $rows = [];
        foreach ($lines as $line) {
            if (empty(trim($line))) {
                continue;
            }
            $rowValues = str_getcsv($line);
            if (count($rowValues) === count($headers)) {
                $rows[] = array_combine($headers, $rowValues);
            } else {
                $row = [];
                foreach ($headers as $index => $header) {
                    $row[$header] = $rowValues[$index] ?? null;
                }
                $rows[] = $row;
            }
        }

        return $rows;
    }

    /**
     * Display the specified task details.
     */
    public function show(Task $task)
    {
        $project = $task->project;
        $user_id = auth()->id();

        Gate::authorize('view', $task);

        log_activity(
            description: "Viewed task '{$task->title}'",
            event: 'viewed',
            subject: $task
        );

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

        $task->load(['project', 'parent', 'subtasks.assignedUser', 'subtasks.project', 'images', 'histories.user']);

        $comments = $task->comments()->with('user')->latest()->get();

        $user = auth()->user();
        $companyIds = $user->companies()->pluck('company_id')->toArray();
        $projects = Project::select('id', 'name', 'theme')->whereIn('company_id', $companyIds)
            ->orWhere(function ($query) use ($user) {
                $query->whereNull('company_id')->where('user_id', $user->id);
            })
            ->get();

        $subtaskProgress = $this->taskService->getSubtaskProgress($task);

        return view('tasks.show', compact('task', 'companyUsers', 'user_role', 'comments', 'subtaskProgress', 'projects'));
    }

    /**
     * Store a newly created subtask under a parent task.
     */
    public function storeSubtask(Request $request, Task $task)
    {
        $this->checkTaskOwnership($task);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
            'status' => 'nullable|integer|in:1,2,3,4',
            'priority' => 'nullable|integer|in:1,2,3,4',
            'type' => 'nullable|integer|in:1,2,3,4',
            'points' => 'nullable|integer|min:0|max:99999',
        ]);

        $this->taskService->createSubtask($task, $validated, auth()->user());

        return redirect()->back()->with('success', 'Subtask created successfully.');
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
