<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Services\TaskServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IssueController extends Controller
{
    /**
     * Display a listing of existing issues.
     */
    public function index(Request $request)
    {
        $state = $request->query('state', 'all');
        if (! in_array($state, ['open', 'closed', 'all'])) {
            $state = 'all';
        }

        $apiUrl = config('services.task_api.url') ?: url('/api/tasks');

        $headers = ['Accept' => 'application/json'];
        if ($apiKey = config('services.task_api.key')) {
            $headers['X-Api-Key'] = $apiKey;
            if ($apiSecret = config('services.task_api.secret')) {
                $headers['X-Api-Signature'] = hash_hmac('sha256', '', $apiSecret);
            }
        } elseif (request()->hasHeader('Cookie')) {
            $headers['Cookie'] = request()->header('Cookie');
        }

        $queryParams = [
            'per_page' => 100,
            'only_external' => 1,
        ];
        if ($state === 'closed') {
            $queryParams['status'] = 3;
        }

        $taskList = [];
        try {
            $response = Http::withHeaders($headers)->get($apiUrl, $queryParams);

            if ($response->successful() && is_array($response->json('data'))) {
                $taskList = $response->json('data');
            }
        } catch (\Throwable $e) {
            Log::error('Failed to fetch tasks from API in IssueController: '.$e->getMessage());
        }

        $issues = collect($taskList)->map(function ($task) {
            return $this->formatTaskAsIssue($task);
        });

        if ($state === 'open') {
            $issues = $issues->filter(fn ($i) => $i['state'] === 'open')->values();
        } elseif ($state === 'closed') {
            $issues = $issues->filter(fn ($i) => $i['state'] === 'closed')->values();
        }

        return view('issues.index', [
            'issues' => $issues,
            'error' => null,
            'state' => $state,
        ]);
    }

    /**
     * Submit an issue report to WorkHub Task API.
     */
    public function store(Request $request, TaskServiceInterface $taskService)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'priority' => 'required|string|in:low,medium,high,critical',
            'category' => 'required|string|in:bug,feature,improvement,security,other',
            'description' => 'required|string',
            'attachment' => 'nullable|file|max:10240', // 10MB limit
            'image' => 'nullable|file|image|max:10240',
            'images.*' => 'nullable|file|image|max:10240',
            'image_base64' => 'nullable|string',
            'images_base64' => 'nullable|array',
            'images_base64.*' => 'nullable|string',
            'image_url' => 'nullable|url',
            'images_url' => 'nullable|array',
            'images_url.*' => 'nullable|url',
        ]);

        $user = auth()->user();

        // Handle attachment if uploaded
        $attachmentUrl = null;
        $attachmentName = null;
        if ($request->hasFile('attachment') && $request->file('attachment')->isValid()) {
            $file = $request->file('attachment');
            $attachmentName = $file->getClientOriginalName();
            $path = $file->store('issues', 'public');
            $attachmentUrl = asset('storage/'.$path);
        }

        $categoryMap = [
            'bug' => Task::TYPE_BUG,
            'feature' => Task::TYPE_FEATURE,
            'improvement' => Task::TYPE_IMPROVEMENT,
            'security' => Task::TYPE_BUG,
            'other' => Task::TYPE_TASK,
        ];
        $priorityMap = [
            'low' => 1,
            'medium' => 2,
            'high' => 3,
            'critical' => 4,
        ];

        $type = $categoryMap[$request->input('category')] ?? Task::TYPE_BUG;
        $priority = $priorityMap[$request->input('priority')] ?? 2;

        $rawDescription = $request->input('description');
        $rawDescription = $taskService->processDescriptionEmbeddedImages($rawDescription);
        $description = '<div>'.$rawDescription.'</div>';

        if ($attachmentUrl) {
            $extension = strtolower(pathinfo($attachmentName, PATHINFO_EXTENSION));
            $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];

            if (in_array($extension, $imageExtensions)) {
                $description .= '<div class="mt-3"><strong>Attachment:</strong><br><img src="'.$attachmentUrl.'" alt="'.e($attachmentName).'" class="img-fluid rounded mt-1" style="max-height: 350px;"></div>';
            } else {
                $description .= '<div class="mt-3"><strong>Attachment:</strong> <a href="'.$attachmentUrl.'" target="_blank" class="font-weight-bold"><i class="fas fa-paperclip mr-1"></i>'.e($attachmentName).'</a></div>';
            }
        }

        if ($user) {
            $description .= '<hr><div class="text-muted small"><strong>Reported by:</strong> '.e($user->name).' ('.e($user->email).')</div>';
        }

        $apiUrl = config('services.task_api.url') ?: url('/api/tasks');

        $payload = [
            'title' => $request->input('title'),
            'description' => $description,
            'type' => $type,
            'priority' => $priority,
            'status' => 1,
        ];

        if ($attachmentUrl) {
            $payload['image_url'] = $attachmentUrl;
        }

        if ($request->filled('image_url')) {
            $payload['image_url'] = $request->input('image_url');
        }
        if ($request->filled('images_url')) {
            $payload['images_url'] = $request->input('images_url');
        }
        if ($request->filled('image_base64')) {
            $payload['image_base64'] = $request->input('image_base64');
        }
        if ($request->filled('images_base64')) {
            $payload['images_base64'] = $request->input('images_base64');
        }

        $headers = [
            'Accept' => 'application/json',
        ];

        if ($apiKey = config('services.task_api.key')) {
            $headers['X-Api-Key'] = $apiKey;
            if ($apiSecret = config('services.task_api.secret')) {
                $headers['X-Api-Signature'] = hash_hmac('sha256', json_encode($payload), $apiSecret);
            }
        } elseif (request()->hasHeader('Cookie')) {
            $headers['Cookie'] = request()->header('Cookie');
        }

        try {
            $response = Http::withHeaders($headers)->post($apiUrl, $payload);

            if ($response->successful()) {
                $taskData = $response->json('data') ?? [];
                $taskId = $taskData['id'] ?? null;
                $taskUrl = $taskId ? route('tasks.show', $taskId) : route('tasks.index');

                return response()->json([
                    'success' => true,
                    'message' => 'Issue successfully created on Task API.',
                    'url' => $taskUrl,
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Issue submission to Task API failed, attempting fallback task creation: '.$e->getMessage());
        }

        // Fallback to internal task creation if HTTP POST fails
        try {
            $task = Task::create([
                'title' => $request->input('title'),
                'description' => $description,
                'type' => $type,
                'priority' => $priority,
                'status' => 1,
                'user_id' => $user?->id,
                'assigned_to' => $user?->id,
            ]);

            $taskService->processTaskImages($request, $task);

            return response()->json([
                'success' => true,
                'message' => 'Issue successfully created on Task API.',
                'url' => route('tasks.show', $task->id),
            ]);
        } catch (\Throwable $e) {
            Log::error('Issue submission task creation failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to submit the issue to Task API. Please check server logs.',
            ], 502);
        }
    }

    /**
     * Map Task model / API response array into issue view format.
     */
    private function formatTaskAsIssue($task): array
    {
        $isModel = $task instanceof Task;
        $id = $isModel ? $task->id : ($task['id'] ?? 1);
        $title = $isModel ? $task->title : ($task['title'] ?? '');
        $status = $isModel ? $task->status : ($task['status'] ?? 1);
        $priorityVal = $isModel ? $task->priority : ($task['priority'] ?? 2);
        $typeVal = $isModel ? $task->type : ($task['type'] ?? 2);
        $description = $isModel ? $task->description : ($task['description'] ?? '');
        $createdAt = $isModel ? $task->created_at : ($task['created_at'] ?? now());

        $userName = 'User';
        if ($isModel) {
            $userName = $task->assignedUser->name ?? $task->user->name ?? 'User';
        } else {
            $userName = $task['assigned_user']['name'] ?? $task['user']['name'] ?? 'User';
        }

        $priorityMap = [1 => 'low', 2 => 'medium', 3 => 'high', 4 => 'critical'];
        $categoryMap = [1 => 'other', 2 => 'bug', 3 => 'feature', 4 => 'improvement'];

        $priorityStr = $priorityMap[$priorityVal] ?? 'medium';
        $categoryStr = $categoryMap[$typeVal] ?? 'bug';

        return [
            'number' => $id,
            'title' => $title,
            'state' => $status == 3 ? 'closed' : 'open',
            'body' => $description,
            'html_url' => route('tasks.show', $id),
            'created_at' => is_string($createdAt) ? $createdAt : ($createdAt ? $createdAt->toIso8601String() : now()->toIso8601String()),
            'labels' => [
                ['name' => $categoryStr],
                ['name' => $priorityStr],
            ],
            'user' => [
                'login' => $userName,
            ],
        ];
    }
}
