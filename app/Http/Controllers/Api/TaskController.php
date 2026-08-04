<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExternalTaskApi;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TaskController extends Controller
{
    public function __construct(private readonly NotificationService $notificationService) {}

    /**
     * Store a newly created task in a project via API.
     */
    public function store(Request $request, Project $project): JsonResponse
    {
        $apiKey = $request->header('X-Api-Key') ?? $request->input('api_key');
        $externalApiConfig = null;

        if ($apiKey) {
            $externalApiConfig = ExternalTaskApi::where('api_key', $apiKey)->where('is_active', true)->first();
            if (! $externalApiConfig) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or inactive API key provided.',
                ], 403);
            }

            $signature = $request->header('X-Api-Signature') ?? $request->header('X-Signature');
            if (! $signature) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing required HMAC signature header (X-Api-Signature).',
                ], 403);
            }

            $expectedSignature = hash_hmac('sha256', $request->getContent(), $externalApiConfig->api_secret);
            if (! hash_equals($expectedSignature, $signature)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid HMAC signature.',
                ], 403);
            }
        } else {
            if (! auth()->check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                ], 403);
            }
            Gate::authorize('update', $project);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
            'status' => 'nullable|integer|in:1,2,3,4',
            'priority' => 'nullable|integer|in:1,2,3,4',
            'type' => 'nullable|integer|in:1,2,3,4',
        ]);

        $user_id = $externalApiConfig ? $externalApiConfig->user_id : auth()->id();
        $validated['user_id'] = $user_id;

        // If no explicit assignee, status, or priority is passed, check ExternalTaskApi configuration
        if (! $externalApiConfig) {
            $externalApiConfig = ExternalTaskApi::where('project_id', $project->id)
                ->where('is_active', true)
                ->latest()
                ->first();
        }

        if (empty($validated['assigned_to'])) {
            $validated['assigned_to'] = $externalApiConfig->assigned_user_id ?? null;
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

        $task = $project->tasks()->create($validated);

        $assignee = $task->assignedUser ?? ($task->assigned_to ? User::find($task->assigned_to) : null);
        if ($assignee) {
            $this->notificationService->send(
                $assignee,
                'task_created',
                'Task Assigned via API',
                "You have been assigned the task '{$task->title}' in project '{$project->name}'.",
                $project->company_id,
                ['task_id' => $task->id, 'project_id' => $project->id, 'url' => route('tasks.show', $task->id)]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Task created successfully',
            'data' => $task->fresh(['project', 'assignedUser']),
        ], 201);
    }

    /**
     * Store a general task (or task with optional project_id in body) via API.
     */
    public function storeGeneral(Request $request): JsonResponse
    {
        $apiKey = $request->header('X-Api-Key') ?? $request->input('api_key');
        $externalApiConfig = null;

        if ($apiKey) {
            $externalApiConfig = ExternalTaskApi::where('api_key', $apiKey)->where('is_active', true)->first();
            if (! $externalApiConfig) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or inactive API key provided.',
                ], 403);
            }

            $signature = $request->header('X-Api-Signature') ?? $request->header('X-Signature');
            if (! $signature) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing required HMAC signature header (X-Api-Signature).',
                ], 403);
            }

            $expectedSignature = hash_hmac('sha256', $request->getContent(), $externalApiConfig->api_secret);
            if (! hash_equals($expectedSignature, $signature)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid HMAC signature.',
                ], 403);
            }
        } else {
            if (! auth()->check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                ], 403);
            }
        }

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

        $targetProjectId = $validated['project_id'] ?? ($externalApiConfig ? $externalApiConfig->project_id : null);

        if ($targetProjectId && $targetProjectId != 0) {
            $project = Project::findOrFail($targetProjectId);

            return $this->store($request, $project);
        }

        $user_id = $externalApiConfig ? $externalApiConfig->user_id : auth()->id();
        $validated['user_id'] = $user_id;

        if (empty($validated['assigned_to'])) {
            if ($externalApiConfig && $externalApiConfig->assigned_user_id) {
                $validated['assigned_to'] = $externalApiConfig->assigned_user_id;
            } else {
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

        $assignee = $task->assignedUser ?? User::find($task->assigned_to);
        if ($assignee) {
            $this->notificationService->send(
                $assignee,
                'task_created',
                'Task Assigned via API',
                "You have been assigned the task '{$task->title}'.",
                null,
                ['task_id' => $task->id, 'url' => route('tasks.show', $task->id)]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Task created successfully',
            'data' => $task->fresh(['project', 'assignedUser']),
        ], 201);
    }
}
