<?php

namespace App\Repositories;

use App\Models\Company;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskImage;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface TaskRepositoryInterface
{
    public function getFilteredTasksForUser(User $user, array $filters = [], int $perPage = 5): LengthAwarePaginator;

    public function getTaskStatsForUser(User $user): array;

    public function getAccessibleCompanyUsers(User $user): Collection;

    public function getTodayTasks(User $user, ?Company $company = null, string $filter = 'today_past', int $perPage = 5): LengthAwarePaginator;

    public function getTodayTaskCounts(User $user, ?Company $company = null): array;

    public function findOrFail(int|string $id): Task;

    public function createTask(array $data): Task;

    public function createProjectTask(Project $project, array $data): Task;

    public function updateTask(Task $task, array $data): Task;

    public function deleteTask(Task $task): bool;

    public function toggleTaskStatus(Task $task): Task;

    public function createTaskImage(Task $task, string $imagePath): TaskImage;

    public function deleteTaskImage(TaskImage $image): bool;
}
