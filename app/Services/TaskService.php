<?php

namespace App\Services;

use App\Models\Company;
use App\Models\CompanyUsers;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskHistory;
use App\Models\TaskImage;
use App\Models\User;
use App\Repositories\TaskRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TaskService implements TaskServiceInterface
{
    public function __construct(
        protected readonly TaskRepositoryInterface $taskRepository,
        protected readonly NotificationService $notificationService
    ) {}

    public function createTask(array $validated, ?Project $project = null, ?User $creator = null): Task
    {
        $user_id = $creator ? $creator->id : auth()->id();
        $validated['user_id'] = $user_id;

        if (array_key_exists('description', $validated)) {
            $validated['description'] = $this->processDescriptionEmbeddedImages($validated['description']);
        }

        if ($project) {
            if (empty($validated['assigned_to'])) {
                $validated['assigned_to'] = $user_id;
            }
            $task = $this->taskRepository->createProjectTask($project, $validated);

            $assignee = $task->assignedUser ?? User::find($task->assigned_to);
            if ($assignee) {
                $this->notificationService->send(
                    $assignee,
                    'task_created',
                    'Task Assigned',
                    "You have been assigned the task '{$task->title}' in project '{$project->name}'.",
                    $project->company_id,
                    ['task_id' => $task->id, 'project_id' => $project->id, 'url' => route('tasks.show', $task->id)]
                );
            }
        } else {
            if (empty($validated['assigned_to'])) {
                $validated['assigned_to'] = $user_id;
            }

            $task = $this->taskRepository->createTask($validated);

            $assignee = $task->assignedUser ?? User::find($task->assigned_to);
            if ($assignee) {
                $this->notificationService->send(
                    $assignee,
                    'task_created',
                    'Task Assigned',
                    "You have been assigned the task '{$task->title}'.",
                    null,
                    ['task_id' => $task->id, 'url' => route('tasks.show', $task->id)]
                );
            }
        }

        return $task;
    }

    public function updateTask(Task $task, array $validated, ?User $authUser = null): Task
    {
        $authId = $authUser ? $authUser->id : auth()->id();

        if (array_key_exists('description', $validated)) {
            $validated['description'] = $this->processDescriptionEmbeddedImages($validated['description']);
        }

        $oldDueDate = $task->due_date;
        $oldAssigneeId = $task->assigned_to;
        $oldStatus = $task->status;
        $oldPriority = $task->priority;

        $task = $this->taskRepository->updateTask($task, $validated);

        if ($task->due_date !== $oldDueDate) {
            $assignee = $task->assignedUser ?? User::find($task->assigned_to);
            if ($assignee) {
                $message = $task->due_date
                    ? "The deadline for task '{$task->title}' has been set/updated to {$task->due_date}."
                    : "The deadline for task '{$task->title}' has been removed.";

                $this->notificationService->send(
                    $assignee,
                    'task_deadline_updated',
                    'Task Deadline Updated',
                    $message,
                    $task->project ? $task->project->company_id : null,
                    ['task_id' => $task->id, 'project_id' => $task->project_id, 'due_date' => $task->due_date, 'url' => route('tasks.show', $task->id)]
                );
            }
        }

        if ($task->assigned_to !== $oldAssigneeId) {
            $newAssignee = User::find($task->assigned_to);
            if ($newAssignee && $task->assigned_to !== $authId) {
                $projectName = $task->project ? " in project '{$task->project->name}'" : '';
                $this->notificationService->send(
                    $newAssignee,
                    'task_assigned',
                    'Task Assigned',
                    "You have been assigned the task '{$task->title}'{$projectName}.",
                    $task->project ? $task->project->company_id : null,
                    ['task_id' => $task->id, 'project_id' => $task->project_id, 'url' => route('tasks.show', $task->id)]
                );
            }
        }

        if ($task->status !== $oldStatus) {
            $assignee = $task->assignedUser ?? User::find($task->assigned_to);
            if ($assignee && $task->assigned_to !== $authId) {
                $statusNames = [1 => 'To Do', 2 => 'In Progress', 3 => 'Completed', 4 => 'On Hold'];
                $statusStr = $statusNames[$task->status] ?? 'Unknown';
                $this->notificationService->send(
                    $assignee,
                    'task_status_updated',
                    'Task Status Updated',
                    "The status of task '{$task->title}' has been updated to '{$statusStr}'.",
                    $task->project ? $task->project->company_id : null,
                    ['task_id' => $task->id, 'project_id' => $task->project_id, 'status' => $task->status, 'url' => route('tasks.show', $task->id)]
                );
            }
        }

        if ($task->priority !== $oldPriority) {
            $assignee = $task->assignedUser ?? User::find($task->assigned_to);
            if ($assignee && $task->assigned_to !== $authId) {
                $priorityNames = [1 => 'Low', 2 => 'Medium', 3 => 'High', 4 => 'Urgent'];
                $priorityStr = $priorityNames[$task->priority] ?? 'Unknown';
                $this->notificationService->send(
                    $assignee,
                    'task_priority_updated',
                    'Task Priority Updated',
                    "The priority of task '{$task->title}' has been set to '{$priorityStr}'.",
                    $task->project ? $task->project->company_id : null,
                    ['task_id' => $task->id, 'project_id' => $task->project_id, 'priority' => $task->priority, 'url' => route('tasks.show', $task->id)]
                );
            }
        }

        return $task;
    }

    public function toggleTaskStatus(Task $task): Task
    {
        return $this->taskRepository->toggleTaskStatus($task);
    }

    public function deleteTask(Task $task, ?User $authUser = null): bool
    {
        $authId = $authUser ? $authUser->id : auth()->id();

        $assignee = $task->assignedUser ?? User::find($task->assigned_to);
        if ($assignee && $task->assigned_to !== $authId) {
            $company_id = $task->project ? $task->project->company_id : null;
            $projectName = $task->project ? " in project '{$task->project->name}'" : '';
            $redirectUrl = $task->project ? route('projects.show', $task->project_id) : route('tasks.index');

            $this->notificationService->send(
                $assignee,
                'task_deleted',
                'Task Deleted',
                "The task '{$task->title}'{$projectName} has been deleted.",
                $company_id,
                ['project_id' => $task->project_id, 'url' => $redirectUrl]
            );
        }

        return $this->taskRepository->deleteTask($task);
    }

    public function copyTask(Task $task, ?User $creator = null): Task
    {
        $creatorUser = $creator ?? auth()->user();

        $copyData = [
            'title' => $task->title.' (Copy)',
            'description' => $task->description,
            'status' => 1,
            'priority' => $task->priority,
            'type' => $task->type,
            'points' => $task->points,
            'due_date' => $task->due_date,
            'assigned_to' => $task->assigned_to,
            'parent_id' => $task->parent_id,
            'user_id' => $creatorUser ? $creatorUser->id : $task->user_id,
        ];

        $newTask = $task->project
            ? $this->taskRepository->createProjectTask($task->project, $copyData)
            : $this->taskRepository->createTask($copyData);

        TaskHistory::create([
            'task_id' => $newTask->id,
            'user_id' => $creatorUser ? $creatorUser->id : auth()->id(),
            'field' => 'copied_from',
            'old_value' => (string) $task->id,
            'new_value' => $task->title,
        ]);

        return $newTask;
    }

    public function importTasks(array $data, Project $project, User $creator): array
    {
        $count = 0;
        $subtasksCount = 0;
        $skipped = 0;

        foreach ($data as $item) {
            if (! is_array($item) || empty($item['title'])) {
                $skipped++;

                continue;
            }

            $taskData = $this->normalizeTaskImportData($item, $project, $creator);
            $task = $this->taskRepository->createProjectTask($project, $taskData);
            $count++;

            // Support nested subtasks if present
            if (! empty($item['subtasks']) && is_array($item['subtasks'])) {
                foreach ($item['subtasks'] as $subItem) {
                    if (is_string($subItem) && ! empty(trim($subItem))) {
                        $this->createSubtask($task, [
                            'title' => trim($subItem),
                            'status' => 1,
                            'priority' => 2,
                            'type' => 1,
                        ], $creator);
                        $subtasksCount++;
                    } elseif (is_array($subItem) && ! empty($subItem['title'])) {
                        $subtaskData = $this->normalizeTaskImportData($subItem, $project, $creator);
                        $this->createSubtask($task, $subtaskData, $creator);
                        $subtasksCount++;
                    }
                }
            }
        }

        return ['imported' => $count, 'subtasks' => $subtasksCount, 'skipped' => $skipped];
    }

    /**
     * Normalize flexible task import input fields into standard database values.
     */
    private function normalizeTaskImportData(array $item, Project $project, User $creator): array
    {
        // Status resolution
        $status = 1;
        if (isset($item['status'])) {
            $statusVal = strtolower(trim((string) $item['status']));
            if (in_array($statusVal, ['1', 'todo', 'to do', 'pending'])) {
                $status = 1;
            } elseif (in_array($statusVal, ['2', 'in progress', 'inprogress', 'doing'])) {
                $status = 2;
            } elseif (in_array($statusVal, ['3', 'completed', 'complete', 'done', 'finished'])) {
                $status = 3;
            } elseif (in_array($statusVal, ['4', 'on hold', 'onhold', 'hold', 'paused'])) {
                $status = 4;
            } elseif (is_numeric($item['status']) && in_array((int) $item['status'], [1, 2, 3, 4])) {
                $status = (int) $item['status'];
            }
        } elseif (! empty($item['is_completed'])) {
            $status = filter_var($item['is_completed'], FILTER_VALIDATE_BOOLEAN) ? 3 : 1;
        }

        // Priority resolution
        $priority = 2;
        if (isset($item['priority'])) {
            $priorityVal = strtolower(trim((string) $item['priority']));
            if (in_array($priorityVal, ['1', 'low'])) {
                $priority = 1;
            } elseif (in_array($priorityVal, ['2', 'medium', 'normal'])) {
                $priority = 2;
            } elseif (in_array($priorityVal, ['3', 'high'])) {
                $priority = 3;
            } elseif (in_array($priorityVal, ['4', 'urgent', 'critical'])) {
                $priority = 4;
            } elseif (is_numeric($item['priority']) && in_array((int) $item['priority'], [1, 2, 3, 4])) {
                $priority = (int) $item['priority'];
            }
        }

        // Type resolution
        $type = 1;
        if (isset($item['type'])) {
            $typeVal = strtolower(trim((string) $item['type']));
            if (in_array($typeVal, ['1', 'task'])) {
                $type = 1;
            } elseif (in_array($typeVal, ['2', 'bug', 'issue', 'defect'])) {
                $type = 2;
            } elseif (in_array($typeVal, ['3', 'feature', 'story'])) {
                $type = 3;
            } elseif (in_array($typeVal, ['4', 'improvement', 'enhancement'])) {
                $type = 4;
            } elseif (is_numeric($item['type']) && in_array((int) $item['type'], [1, 2, 3, 4])) {
                $type = (int) $item['type'];
            }
        }

        // Assignee resolution (ID, Email, or Name) — only among users allowed in
        // this project's workspace; never search the whole platform.
        $assignedTo = $creator->id;
        $assigneeInput = $item['assigned_to'] ?? $item['assignee'] ?? $item['assignee_email'] ?? $item['assigned_user'] ?? null;
        if (! empty($assigneeInput)) {
            $candidates = User::query();
            if ($project->company_id !== null) {
                $candidates->whereIn('id', CompanyUsers::where('company_id', $project->company_id)
                    ->where('is_approved', true)
                    ->pluck('user_id'));
            } else {
                $candidates->whereIn('id', array_unique([$creator->id, (int) $project->user_id]));
            }

            $match = null;
            if (is_numeric($assigneeInput)) {
                $match = (clone $candidates)->whereKey((int) $assigneeInput)->first();
            } else {
                $assigneeStr = trim((string) $assigneeInput);
                $match = (clone $candidates)->where('email', $assigneeStr)->first()
                    ?? (clone $candidates)->where('name', 'LIKE', '%'.addcslashes($assigneeStr, '%_\\').'%')->first();
            }

            if ($match) {
                $assignedTo = $match->id;
            }
        }

        // Due date resolution
        $dueDate = null;
        if (! empty($item['due_date'])) {
            $timestamp = strtotime((string) $item['due_date']);
            if ($timestamp !== false) {
                $dueDate = date('Y-m-d', $timestamp);
            }
        }

        // Points
        $points = isset($item['points']) && is_numeric($item['points']) ? (int) $item['points'] : null;

        return [
            'title' => trim($item['title']),
            'description' => $item['description'] ?? null,
            'due_date' => $dueDate,
            'assigned_to' => $assignedTo,
            'user_id' => $creator->id,
            'status' => $status,
            'priority' => $priority,
            'type' => $type,
            'points' => $points,
        ];
    }

    public function processTaskImages(Request $request, Task $task): array
    {
        $createdImages = [];

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('task_images', 'public');
            $createdImages[] = $this->taskRepository->createTaskImage($task, $path);
        }

        if ($request->hasFile('images')) {
            $files = is_array($request->file('images')) ? $request->file('images') : [$request->file('images')];
            foreach ($files as $file) {
                if ($file->isValid()) {
                    $path = $file->store('task_images', 'public');
                    $createdImages[] = $this->taskRepository->createTaskImage($task, $path);
                }
            }
        }

        $base64List = [];
        if ($request->filled('image_base64')) {
            $base64List[] = $request->input('image_base64');
        }
        if ($request->filled('images_base64') && is_array($request->input('images_base64'))) {
            $base64List = array_merge($base64List, $request->input('images_base64'));
        }

        foreach ($base64List as $b64) {
            if (is_string($b64)) {
                $extension = 'jpg';
                if (preg_match('/^data:image\/(\w+);base64,/', $b64, $type)) {
                    $b64Data = substr($b64, strpos($b64, ',') + 1);
                    $ext = strtolower($type[1]);
                    if (in_array($ext, ['jpeg', 'jpg', 'png', 'gif', 'webp'])) {
                        $extension = $ext === 'jpeg' ? 'jpg' : $ext;
                    }
                } else {
                    $b64Data = $b64;
                }

                $decoded = base64_decode($b64Data, true);
                $detected = $decoded !== false ? $this->detectImageExtension($decoded) : null;
                if ($detected !== null) {
                    $extension = $detected;
                    $fileName = 'task_images/'.Str::random(40).'.'.$extension;
                    Storage::disk('public')->put($fileName, $decoded);
                    $createdImages[] = $this->taskRepository->createTaskImage($task, $fileName);
                }
            }
        }

        $urlList = [];
        if ($request->filled('image_url')) {
            $urlList[] = $request->input('image_url');
        }
        if ($request->filled('images_url') && is_array($request->input('images_url'))) {
            $urlList = array_merge($urlList, $request->input('images_url'));
        }

        foreach ($urlList as $url) {
            if (! is_string($url)) {
                continue;
            }

            $image = $this->fetchRemoteImage($url);
            if ($image !== null) {
                $fileName = 'task_images/'.Str::random(40).'.'.$image['extension'];
                Storage::disk('public')->put($fileName, $image['bytes']);
                $createdImages[] = $this->taskRepository->createTaskImage($task, $fileName);
            }
        }

        return $createdImages;
    }

    public function uploadImage(Task $task, $file): TaskImage
    {
        $path = $file->store('task_images', 'public');

        return $this->taskRepository->createTaskImage($task, $path);
    }

    public function deleteImage(TaskImage $image): bool
    {
        if (Storage::disk('public')->exists($image->image_path)) {
            Storage::disk('public')->delete($image->image_path);
        }

        return $this->taskRepository->deleteTaskImage($image);
    }

    public function getTodayTasks(User $user, ?Company $company = null, string $filter = 'today_past', int $perPage = 5): LengthAwarePaginator
    {
        $allowedFilters = ['today_past', 'due_today', 'overdue', 'all_pending'];
        if (! in_array($filter, $allowedFilters)) {
            $filter = 'today_past';
        }

        $allowedPerPage = [5, 10, 25, 50, 100];
        if (! in_array($perPage, $allowedPerPage)) {
            $perPage = 5;
        }

        return $this->taskRepository->getTodayTasks($user, $company, $filter, $perPage);
    }

    public function getTodayTaskCounts(User $user, ?Company $company = null): array
    {
        return $this->taskRepository->getTodayTaskCounts($user, $company);
    }

    public function createSubtask(Task $parentTask, array $validated, ?User $creator = null): Task
    {
        $validated['parent_id'] = $parentTask->id;

        if (! isset($validated['project_id']) && $parentTask->project_id) {
            $validated['project_id'] = $parentTask->project_id;
        }

        return $this->createTask($validated, $parentTask->project, $creator);
    }

    public function getSubtasks(Task $parentTask): Collection
    {
        return $parentTask->subtasks()->with(['assignedUser', 'project'])->get();
    }

    public function getSubtaskProgress(Task $parentTask): array
    {
        $subtasks = $parentTask->subtasks;
        $total = $subtasks->count();

        if ($total === 0) {
            return [
                'total' => 0,
                'completed' => 0,
                'percentage' => 0.0,
            ];
        }

        $completed = $subtasks->where('status', 3)->count();
        $percentage = round(($completed / $total) * 100, 2);

        return [
            'total' => $total,
            'completed' => $completed,
            'percentage' => $percentage,
        ];
    }

    public function processDescriptionEmbeddedImages(?string $description): ?string
    {
        if (empty($description)) {
            return $description;
        }

        return preg_replace_callback(
            '/data:image\/(\w+);base64,([A-Za-z0-9+\/=\s]+)/',
            function ($matches) {
                $b64Data = preg_replace('/\s+/', '', $matches[2]);
                $decoded = base64_decode($b64Data, true);

                // SVG and non-image payloads are rejected: they could carry script
                // and are served from the app's own origin.
                $extension = $decoded !== false ? $this->detectImageExtension($decoded) : null;

                if ($extension !== null) {
                    $fileName = 'task_images/'.Str::random(40).'.'.$extension;
                    Storage::disk('public')->put($fileName, $decoded);

                    return asset('storage/'.$fileName);
                }

                return $matches[0];
            },
            $description
        );
    }

    /** Maximum size for an image fetched from a remote URL (bytes). */
    private const MAX_REMOTE_IMAGE_BYTES = 10 * 1024 * 1024;

    /**
     * Return a safe file extension if the bytes are a supported raster image, else null.
     */
    private function detectImageExtension(string $bytes): ?string
    {
        if ($bytes === '' || strlen($bytes) > self::MAX_REMOTE_IMAGE_BYTES) {
            return null;
        }

        $info = @getimagesizefromstring($bytes);
        if ($info === false) {
            return null;
        }

        return match ($info[2]) {
            IMAGETYPE_JPEG => 'jpg',
            IMAGETYPE_PNG => 'png',
            IMAGETYPE_GIF => 'gif',
            IMAGETYPE_WEBP => 'webp',
            default => null,
        };
    }

    /**
     * Fetch an image from a user-supplied URL with SSRF protections:
     * http(s) only, public IPs only (pinned to defeat DNS rebinding),
     * no redirects, size cap, and content must be a real raster image.
     *
     * @return array{bytes: string, extension: string}|null
     */
    private function fetchRemoteImage(string $url): ?array
    {
        $parts = parse_url($url);
        $scheme = strtolower($parts['scheme'] ?? '');
        $host = $parts['host'] ?? null;

        if (! in_array($scheme, ['http', 'https'], true) || ! $host || isset($parts['user']) || isset($parts['pass'])) {
            return null;
        }

        $host = trim($host, '[]');
        $port = $parts['port'] ?? ($scheme === 'https' ? 443 : 80);

        $ips = filter_var($host, FILTER_VALIDATE_IP) ? [$host] : (gethostbynamel($host) ?: []);
        if ($ips === []) {
            return null;
        }

        foreach ($ips as $ip) {
            if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return null;
            }
        }

        try {
            $response = Http::timeout(10)
                ->withOptions([
                    'allow_redirects' => false,
                    'curl' => [CURLOPT_RESOLVE => ["{$host}:{$port}:{$ips[0]}"]],
                ])
                ->get($url);
        } catch (\Throwable $e) {
            report($e);

            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $length = (int) $response->header('Content-Length');
        if ($length > self::MAX_REMOTE_IMAGE_BYTES) {
            return null;
        }

        $bytes = $response->body();
        $extension = $this->detectImageExtension($bytes);

        return $extension === null ? null : ['bytes' => $bytes, 'extension' => $extension];
    }
}
