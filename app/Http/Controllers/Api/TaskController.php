<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExternalTaskApi;
use App\Models\ExternalTaskSource;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\TaskServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TaskController extends Controller
{
    public function __construct(
        protected readonly NotificationService $notificationService,
        protected readonly TaskServiceInterface $taskService
    ) {}

    /**
     * Display a listing of tasks via API.
     */
    public function index(Request $request): JsonResponse
    {
        $externalApiConfig = $request->attributes->get('externalApiConfig');

        $query = Task::with(['project', 'assignedUser', 'images']);

        if ($externalApiConfig) {
            $query->where('project_id', $externalApiConfig->project_id);
        } elseif (auth()->check()) {
            $user = auth()->user();
            $companyIds = $user->companies()->pluck('company_id')->toArray();
            $projectIds = Project::whereIn('company_id', $companyIds)
                ->orWhere(function ($q) use ($user) {
                    $q->whereNull('company_id')->where('user_id', $user->id);
                })->pluck('id')->toArray();

            $query->where(function ($q) use ($projectIds, $user) {
                $q->whereIn('project_id', $projectIds)
                    ->orWhere(function ($sub) use ($user) {
                        $sub->whereNull('project_id')
                            ->where(function ($inner) use ($user) {
                                $inner->where('user_id', $user->id)
                                    ->orWhere('assigned_to', $user->id);
                            });
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', (int) $request->input('status'));
        }

        if ($request->filled('priority')) {
            $query->where('priority', (int) $request->input('priority'));
        }

        if ($request->filled('type')) {
            $query->where('type', (int) $request->input('type'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $perPage = min((int) $request->input('per_page', 15), 100);
        $tasks = $query->latest()->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Tasks retrieved successfully',
            'data' => $tasks->items(),
            'pagination' => [
                'total' => $tasks->total(),
                'per_page' => $tasks->perPage(),
                'current_page' => $tasks->currentPage(),
                'last_page' => $tasks->lastPage(),
            ],
        ]);
    }

    /**
     * Store a newly created task via API.
     */
    public function store(Request $request): JsonResponse
    {
        $externalApiConfig = $request->attributes->get('externalApiConfig');

        $validated = $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
            'status' => 'nullable|integer|in:1,2,3,4',
            'priority' => 'nullable|integer|in:1,2,3,4',
            'type' => 'nullable|integer|in:1,2,3,4',
            'image' => 'nullable|file|image|max:10240',
            'images.*' => 'nullable|file|image|max:10240',
            'image_base64' => 'nullable|string',
            'images_base64' => 'nullable|array',
            'images_base64.*' => 'nullable|string',
            'image_url' => 'nullable|url',
            'images_url' => 'nullable|array',
            'images_url.*' => 'nullable|url',
        ]);

        $targetProjectId = $validated['project_id'] ?? ($externalApiConfig ? $externalApiConfig->project_id : null);
        $project = null;

        if ($targetProjectId && $targetProjectId != 0) {
            $project = Project::findOrFail($targetProjectId);
            if (! $externalApiConfig) {
                Gate::authorize('update', $project);
            }
            $validated['project_id'] = $project->id;
        }

        $user_id = $externalApiConfig ? $externalApiConfig->user_id : auth()->id();
        $validated['user_id'] = $user_id;

        if (! $externalApiConfig && $project) {
            $externalApiConfig = ExternalTaskApi::where('project_id', $project->id)
                ->where('is_active', true)
                ->latest()
                ->first();
        }

        if (empty($validated['assigned_to'])) {
            if ($externalApiConfig && $externalApiConfig->assigned_user_id) {
                $validated['assigned_to'] = $externalApiConfig->assigned_user_id;
            } elseif (! $project) {
                $validated['assigned_to'] = $user_id;
            }
        }

        if (empty($validated['status']) && $externalApiConfig?->default_status) {
            $validated['status'] = $externalApiConfig->default_status;
        }

        if (empty($validated['priority']) && $externalApiConfig?->default_priority) {
            $validated['priority'] = $externalApiConfig->default_priority;
        }

        if (empty($validated['type']) && $externalApiConfig?->default_type) {
            $validated['type'] = $externalApiConfig->default_type;
        }

        $task = Task::create($validated);

        // Process any attached images using TaskService
        $this->taskService->processTaskImages($request, $task);

        if ($externalApiConfig) {
            ExternalTaskSource::create([
                'task_id' => $task->id,
                'external_task_api_id' => $externalApiConfig->id,
                'payload' => $request->except(['image', 'images', 'image_base64', 'images_base64']),
                'ip_address' => $request->ip(),
            ]);
        }

        $assignee = $task->assignedUser ?? ($task->assigned_to ? User::find($task->assigned_to) : null);
        if ($assignee) {
            $message = $project
                ? "You have been assigned the task '{$task->title}' in project '{$project->name}'."
                : "You have been assigned the task '{$task->title}'.";

            $this->notificationService->send(
                $assignee,
                'task_created',
                'Task Assigned via API',
                $message,
                $project?->company_id,
                ['task_id' => $task->id, 'project_id' => $project?->id, 'url' => route('tasks.show', $task->id)]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Task created successfully',
            'data' => $task->fresh(['project', 'assignedUser', 'images', 'externalSource.externalTaskApi']),
        ], 201);
    }

    /**
     * Upload image(s) to an existing task via API.
     */
    public function uploadImage(Request $request, Task $task): JsonResponse
    {
        $externalApiConfig = $request->attributes->get('externalApiConfig');

        if ($externalApiConfig) {
            if ($task->project_id !== $externalApiConfig->project_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: This API key cannot modify tasks outside its project.',
                ], 403);
            }
        } else {
            Gate::authorize('update', $task);
        }

        $request->validate([
            'image' => 'nullable|file|image|max:10240',
            'images.*' => 'nullable|file|image|max:10240',
            'image_base64' => 'nullable|string',
            'images_base64' => 'nullable|array',
            'images_base64.*' => 'nullable|string',
            'image_url' => 'nullable|url',
            'images_url' => 'nullable|array',
            'images_url.*' => 'nullable|url',
        ]);

        $createdImages = $this->taskService->processTaskImages($request, $task);

        if (empty($createdImages)) {
            return response()->json([
                'success' => false,
                'message' => 'No valid image file, base64 payload, or image URL was provided.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => count($createdImages).' image(s) uploaded successfully',
            'data' => $task->fresh(['images']),
        ], 200);
    }
}
