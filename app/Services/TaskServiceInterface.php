<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskImage;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

interface TaskServiceInterface
{
    public function createTask(array $validated, ?Project $project = null, ?User $creator = null): Task;

    public function updateTask(Task $task, array $validated, ?User $authUser = null): Task;

    public function toggleTaskStatus(Task $task): Task;

    public function deleteTask(Task $task, ?User $authUser = null): bool;

    public function importTasks(array $data, Project $project, User $creator): array;

    public function processTaskImages(Request $request, Task $task): array;

    public function uploadImage(Task $task, $file): TaskImage;

    public function deleteImage(TaskImage $image): bool;

    public function getTodayTasks(User $user, ?Company $company = null, string $filter = 'today_past', int $perPage = 5): LengthAwarePaginator;

    public function getTodayTaskCounts(User $user, ?Company $company = null): array;
}
