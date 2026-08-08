<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskImage;
use App\Models\User;
use App\Repositories\TaskRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
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
            if (! isset($validated['assigned_to']) || $validated['assigned_to'] === null) {
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

    public function importTasks(array $data, Project $project, User $creator): array
    {
        $count = 0;
        $skipped = 0;

        foreach ($data as $item) {
            if (! empty($item['title'])) {
                $status = isset($item['status']) && in_array((int) $item['status'], [1, 2, 3, 4]) ? (int) $item['status'] : (($item['is_completed'] ?? false) ? 3 : 1);
                $priority = isset($item['priority']) && in_array((int) $item['priority'], [1, 2, 3, 4]) ? (int) $item['priority'] : 2;
                $type = isset($item['type']) && in_array((int) $item['type'], [1, 2, 3, 4]) ? (int) $item['type'] : 1;

                $assignedTo = $creator->id;
                if (! empty($item['assigned_to'])) {
                    $assignedExists = User::where('id', (int) $item['assigned_to'])->exists();
                    if ($assignedExists) {
                        $assignedTo = (int) $item['assigned_to'];
                    }
                }

                $this->taskRepository->createProjectTask($project, [
                    'title' => $item['title'],
                    'description' => $item['description'] ?? null,
                    'due_date' => isset($item['due_date']) ? date('Y-m-d', strtotime($item['due_date'])) : null,
                    'assigned_to' => $assignedTo,
                    'user_id' => $creator->id,
                    'status' => $status,
                    'priority' => $priority,
                    'type' => $type,
                ]);
                $count++;
            } else {
                $skipped++;
            }
        }

        return ['imported' => $count, 'skipped' => $skipped];
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
                if ($decoded !== false) {
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
            if (filter_var($url, FILTER_VALIDATE_URL)) {
                try {
                    $response = Http::timeout(10)->get($url);
                    if ($response->successful()) {
                        $contentType = $response->header('Content-Type');
                        $extension = 'jpg';
                        if ($contentType) {
                            if (str_contains($contentType, 'png')) {
                                $extension = 'png';
                            } elseif (str_contains($contentType, 'gif')) {
                                $extension = 'gif';
                            } elseif (str_contains($contentType, 'webp')) {
                                $extension = 'webp';
                            }
                        }
                        $fileName = 'task_images/'.Str::random(40).'.'.$extension;
                        Storage::disk('public')->put($fileName, $response->body());
                        $createdImages[] = $this->taskRepository->createTaskImage($task, $fileName);
                    }
                } catch (\Throwable $e) {
                }
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
}
