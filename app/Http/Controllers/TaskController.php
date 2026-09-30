<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\CompanyUsers;
use App\Models\Note;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskImage;
use App\Models\User;
use App\Repositories\TaskRepositoryInterface;
use App\Services\TaskServiceInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Inertia\Inertia;

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
        /** @var User|null $user */
        $user = auth()->user();
        $companyId = session('current_company_id');
        $company = is_numeric($companyId) ? Company::find($companyId) : null;
        $companyIds = $user ? $user->companies()->pluck('company_id')->toArray() : [];

        if ($company) {
            $projectsQuery = Project::select('id', 'name', 'theme', 'company_id')->where('company_id', $company->id);
            $currentWorkspaceName = $company->name;
        } else {
            $projectsQuery = Project::select('id', 'name', 'theme', 'company_id')->where(function ($q) use ($user, $companyIds) {
                $q->whereIn('company_id', $companyIds);
                if ($user) {
                    $q->orWhere(function ($sub) use ($user) {
                        $sub->whereNull('company_id')->where('user_id', $user->id);
                    });
                }
            });
            $currentWorkspaceName = 'All Workspaces';
        }

        $projects = $projectsQuery->get();
        $companyUsers = $user ? $this->taskRepository->getAccessibleCompanyUsers($user, $company?->id) : collect();
        $statsData = $user ? $this->taskRepository->getTaskStatsForUser($user) : [
            'totalCount' => 0,
            'completedCount' => 0,
            'pendingCount' => 0,
            'overdueCount' => 0,
        ];

        $filters = $request->only(['project', 'status', 'assignee', 'type', 'show_completed']);

        // Support legacy blade view if explicitly requested
        if ($request->has('legacy')) {
            $tasks = $user ? $this->taskRepository->getFilteredTasksForUser($user, $filters, 5) : collect();
            $user_role = 1;

            return view('tasks.index', array_merge([
                'tasks' => $tasks,
                'projects' => $projects,
                'companyUsers' => $companyUsers,
                'user_role' => $user_role,
            ], $statsData));
        }

        if (! $request->filled('status') && ! $request->has('show_completed')) {
            $filters['show_completed'] = true;
        }

        $perPage = (int) $request->input('per_page', 100);
        $paginatedTasks = $user ? $this->taskRepository->getFilteredTasksForUser($user, $filters, $perPage) : null;

        $urgentCount = 0;
        $tasksCollection = collect($paginatedTasks ? $paginatedTasks->items() : []);

        $formattedTasks = $tasksCollection->map(function (Task $t) use ($user, &$urgentCount) {
            $statusName = match ((int) $t->status) {
                2 => 'In Progress',
                3 => 'Done',
                4 => 'Review',
                default => 'To Do',
            };

            $priorityName = match ((int) $t->priority) {
                4 => 'Urgent',
                3 => 'High',
                2 => 'Medium',
                default => 'Low',
            };

            if ((int) $t->priority === 4 && (int) $t->status !== 3) {
                $urgentCount++;
            }

            $formattedDueDate = 'No deadline';
            $isOverdue = false;
            if ($t->due_date) {
                $dueCarbon = Carbon::parse($t->due_date);
                if ($dueCarbon->isToday()) {
                    $formattedDueDate = 'Today';
                } elseif ($dueCarbon->isTomorrow()) {
                    $formattedDueDate = 'Tomorrow';
                } elseif ($dueCarbon->isYesterday()) {
                    $formattedDueDate = 'Yesterday';
                } else {
                    $formattedDueDate = $dueCarbon->format('M d, Y');
                }

                if ((int) $t->status !== 3 && $dueCarbon->endOfDay()->isPast()) {
                    $isOverdue = true;
                }
            }

            $assigneeData = $t->assignedUser ? [
                'id' => $t->assignedUser->id,
                'name' => $t->assignedUser->name,
                'avatar' => strtoupper(substr($t->assignedUser->name, 0, 2)),
                'profile_image' => $t->assignedUser->profile_image_url ?? null,
            ] : [
                'id' => null,
                'name' => 'Unassigned',
                'avatar' => 'UN',
                'profile_image' => null,
            ];

            $subtasksCount = $t->subtasks->count();
            $completedSubtasksCount = $t->subtasks->where('status', 3)->count();
            $subtasksLabel = $subtasksCount > 0 ? "{$completedSubtasksCount}/{$subtasksCount}" : '0/0';

            return [
                'id' => 'WH-'.str_pad((string) $t->id, 3, '0', STR_PAD_LEFT),
                'db_id' => $t->id,
                'title' => $t->title,
                'description' => trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($t->description ?: '')))) ?: '',
                'raw_description' => $t->description ?: '',
                'status' => $statusName,
                'status_id' => (int) $t->status,
                'priority' => $priorityName,
                'priority_id' => (int) $t->priority,
                'type' => $t->getTypeName(),
                'type_id' => (int) ($t->type ?: 1),
                'points' => $t->points,
                'dueDate' => $formattedDueDate,
                'due_date_raw' => $t->due_date ? Carbon::parse($t->due_date)->format('Y-m-d') : '',
                'is_overdue' => $isOverdue,
                'project' => $t->project ? $t->project->name : 'Personal Space',
                'project_id' => $t->project_id,
                'project_theme' => $t->project?->theme ?: '#10b981',
                'branch' => $t->project ? 'feat/'.Str::slug(Str::limit($t->title, 20, '')) : 'main',
                'assignee' => $assigneeData,
                'category' => $t->getTypeName(),
                'completed' => (int) $t->status === 3,
                'subtasks' => $subtasksLabel,
                'subtasks_count' => $subtasksCount,
                'completed_subtasks_count' => $completedSubtasksCount,
                'can_edit' => $user ? Gate::allows('update', $t) : false,
                'can_delete' => $user ? Gate::allows('delete', $t) : false,
                'created_at' => $t->created_at?->format('M d, Y'),
            ];
        })->values()->toArray();

        $stats = [
            'total' => $statsData['totalCount'] ?? count($formattedTasks),
            'pending' => $statsData['pendingCount'] ?? 0,
            'completed' => $statsData['completedCount'] ?? 0,
            'overdue' => $statsData['overdueCount'] ?? 0,
            'urgent' => $urgentCount,
        ];

        $projectsFormatted = $projects->map(function (Project $p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'theme' => $p->theme ?: '#10b981',
            ];
        })->values()->toArray();

        $companyUsersFormatted = $companyUsers->map(function ($u) {
            return [
                'id' => $u->id,
                'name' => $u->name,
                'avatar' => strtoupper(substr($u->name, 0, 2)),
                'profile_image' => $u->profile_image_url ?? null,
            ];
        })->values()->toArray();

        return Inertia::render('New/Tasks', [
            'initial_tasks' => $formattedTasks,
            'projects' => $projectsFormatted,
            'company_users' => $companyUsersFormatted,
            'stats' => $stats,
            'filters' => $filters,
            'current_workspace_name' => $currentWorkspaceName,
        ]);
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

        /** @var User|null $authUser */
        $authUser = auth()->user();

        if ($project === null) {
            $companyUsers = $authUser ? $this->taskRepository->getAccessibleCompanyUsers($authUser) : collect();
            $user_role = 1;
        } elseif ($project->company_id === null) {
            $companyUsers = $authUser ? collect([$authUser]) : collect();
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

        $task->load([
            'project',
            'parent',
            'subtasks.assignedUser',
            'subtasks.project',
            'images',
            'histories.user',
            'assignedUser',
            'user',
        ]);

        $comments = $task->comments()->with('user')->latest()->get();

        $companyIds = $authUser ? $authUser->companies()->pluck('company_id')->toArray() : [];
        $projects = Project::select('id', 'name', 'theme')->whereIn('company_id', $companyIds)
            ->orWhere(function ($query) use ($authUser) {
                if ($authUser) {
                    $query->whereNull('company_id')->where('user_id', $authUser->id);
                }
            })
            ->get();

        $subtaskProgress = $this->taskService->getSubtaskProgress($task);

        // Support legacy blade view if explicitly requested
        if (request()->has('legacy')) {
            return view('tasks.show', compact('task', 'companyUsers', 'user_role', 'comments', 'subtaskProgress', 'projects'));
        }

        $statusName = match ((int) $task->status) {
            2 => 'In Progress',
            3 => 'Done',
            4 => 'Review',
            default => 'To Do',
        };

        $priorityName = match ((int) $task->priority) {
            4 => 'Urgent',
            3 => 'High',
            2 => 'Medium',
            default => 'Low',
        };

        $formattedDueDate = 'No deadline';
        $isOverdue = false;
        if ($task->due_date) {
            $dueCarbon = Carbon::parse($task->due_date);
            if ($dueCarbon->isToday()) {
                $formattedDueDate = 'Today';
            } elseif ($dueCarbon->isTomorrow()) {
                $formattedDueDate = 'Tomorrow';
            } elseif ($dueCarbon->isYesterday()) {
                $formattedDueDate = 'Yesterday';
            } else {
                $formattedDueDate = $dueCarbon->format('M d, Y');
            }

            if ((int) $task->status !== 3 && $dueCarbon->endOfDay()->isPast()) {
                $isOverdue = true;
            }
        }

        $taskData = [
            'id' => 'WH-'.str_pad((string) $task->id, 3, '0', STR_PAD_LEFT),
            'db_id' => $task->id,
            'title' => $task->title,
            'description' => $task->description ?: '',
            'raw_description' => $task->description ?: '',
            'status' => $statusName,
            'status_id' => (int) $task->status,
            'priority' => $priorityName,
            'priority_id' => (int) $task->priority,
            'type' => $task->getTypeName(),
            'type_id' => (int) ($task->type ?: 1),
            'points' => $task->points,
            'dueDate' => $formattedDueDate,
            'due_date_raw' => $task->due_date ? Carbon::parse($task->due_date)->format('Y-m-d') : '',
            'is_overdue' => $isOverdue,
            'project' => $task->project ? [
                'id' => $task->project->id,
                'name' => $task->project->name,
                'theme' => $task->project->theme ?: '#10b981',
            ] : [
                'id' => null,
                'name' => 'Personal Space',
                'theme' => '#6b7280',
            ],
            'project_id' => $task->project_id,
            'branch' => $task->project ? 'feat/'.Str::slug(Str::limit($task->title, 20, '')) : 'main',
            'created' => $task->created_at ? $task->created_at->diffForHumans().' by '.($task->user ? $task->user->name : 'System') : '',
            'created_at' => $task->created_at?->format('M d, Y'),
            'assignee' => $task->assignedUser ? [
                'id' => $task->assignedUser->id,
                'name' => $task->assignedUser->name,
                'avatar' => strtoupper(substr($task->assignedUser->name, 0, 2)),
                'email' => $task->assignedUser->email,
                'profile_image' => $task->assignedUser->profile_image_url ?? null,
            ] : null,
            'reporter' => $task->user ? [
                'id' => $task->user->id,
                'name' => $task->user->name,
                'avatar' => strtoupper(substr($task->user->name, 0, 2)),
                'email' => $task->user->email,
                'profile_image' => $task->user->profile_image_url ?? null,
            ] : null,
            'completed' => (int) $task->status === 3,
            'parentTask' => $task->parent ? [
                'id' => 'WH-'.str_pad((string) $task->parent->id, 3, '0', STR_PAD_LEFT),
                'db_id' => $task->parent->id,
                'title' => $task->parent->title,
            ] : null,
            'can_edit' => $authUser ? Gate::allows('update', $task) : false,
            'can_delete' => $authUser ? Gate::allows('delete', $task) : false,
        ];

        $subtasksFormatted = $task->subtasks->map(function (Task $sub) {
            return [
                'id' => $sub->id,
                'db_id' => $sub->id,
                'text' => $sub->title,
                'title' => $sub->title,
                'done' => (int) $sub->status === 3,
                'completed' => (int) $sub->status === 3,
                'status' => match ((int) $sub->status) {
                    2 => 'In Progress',
                    3 => 'Done',
                    4 => 'Review',
                    default => 'To Do',
                },
                'priority' => match ((int) $sub->priority) {
                    4 => 'Urgent',
                    3 => 'High',
                    2 => 'Medium',
                    default => 'Low',
                },
                'assignee' => $sub->assignedUser ? [
                    'id' => $sub->assignedUser->id,
                    'name' => $sub->assignedUser->name,
                    'avatar' => strtoupper(substr($sub->assignedUser->name, 0, 2)),
                    'profile_image' => $sub->assignedUser->profile_image_url ?? null,
                ] : null,
            ];
        })->values()->toArray();

        $commentsFormatted = $comments->map(function ($c) use ($authUser) {
            return [
                'id' => $c->id,
                'user' => [
                    'id' => $c->user->id,
                    'name' => $c->user->name,
                    'avatar' => strtoupper(substr($c->user->name, 0, 2)),
                    'profile_image' => $c->user->profile_image_url,
                    'role' => 'Member',
                ],
                'time' => $c->created_at ? $c->created_at->diffForHumans() : '',
                'created_at' => $c->created_at?->format('M d, Y H:i'),
                'text' => $c->content,
                'can_delete' => $authUser ? Gate::allows('delete', $c) : false,
            ];
        })->values()->toArray();

        $imagesFormatted = $task->images->map(function (TaskImage $img) {
            return [
                'id' => $img->id,
                'name' => basename($img->image_path),
                'url' => asset('storage/'.$img->image_path),
                'size' => '',
                'created_at' => $img->created_at?->diffForHumans(),
            ];
        })->values()->toArray();

        $taskNotes = Note::where('note_type', Note::TYPE_TASK)
            ->where('note_type_id', $task->id)
            ->latest()
            ->get()
            ->map(function (Note $n) {
                return [
                    'id' => $n->id,
                    'title' => $n->title,
                    'content' => $n->description,
                    'time' => $n->created_at ? $n->created_at->diffForHumans() : '',
                    'created_at' => $n->created_at?->format('M d, Y H:i'),
                ];
            })->values()->toArray();

        $historyLogs = $task->histories->map(function ($h) {
            return [
                'id' => $h->id,
                'user' => [
                    'name' => $h->user ? $h->user->name : 'System',
                    'avatar' => $h->user ? strtoupper(substr($h->user->name, 0, 2)) : 'SY',
                ],
                'description' => $h->getDescription(),
                'action' => $h->getDescription(),
                'field' => $h->field,
                'old_value' => $h->old_value,
                'new_value' => $h->new_value,
                'time' => $h->created_at ? $h->created_at->diffForHumans() : '',
                'created_at' => $h->created_at?->format('M d, Y H:i'),
            ];
        })->values()->toArray();

        $projectsFormatted = $projects->map(function (Project $p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'theme' => $p->theme ?: '#10b981',
            ];
        })->values()->toArray();

        $companyUsersFormatted = $companyUsers->map(function ($u) {
            return [
                'id' => $u->id,
                'name' => $u->name,
                'avatar' => strtoupper(substr($u->name, 0, 2)),
                'profile_image' => $u->profile_image_url ?? null,
            ];
        })->values()->toArray();

        return Inertia::render('New/TaskView', [
            'task' => $taskData,
            'initial_subtasks' => $subtasksFormatted,
            'initial_comments' => $commentsFormatted,
            'initial_images' => $imagesFormatted,
            'initial_notes' => $taskNotes,
            'history_logs' => $historyLogs,
            'projects' => $projectsFormatted,
            'company_users' => $companyUsersFormatted,
            'subtask_progress' => $subtaskProgress,
            'ui' => [
                'subtasks_title' => 'Subtasks',
                'add_subtask_button' => 'Add Subtask',
                'parent_task_label' => 'Parent Task',
            ],
        ]);
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
