<?php

namespace App\Repositories;

use App\Models\Company;
use App\Models\CompanyUsers;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskImage;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class TaskRepository implements TaskRepositoryInterface
{
    protected function getAccessibleTasksQuery(User $user)
    {
        $companyIds = $user->companies()->pluck('company_id')->toArray();

        $projectIds = Project::select('id', 'name')->whereIn('company_id', $companyIds)
            ->orWhere(function ($query) use ($user) {
                $query->whereNull('company_id')->where('user_id', $user->id);
            })
            ->pluck('id')->toArray();

        return Task::select('id', 'title', 'status', 'priority', 'type', 'points', 'due_date', 'project_id', 'assigned_to', 'user_id', 'created_at', 'updated_at')
            ->where(function ($query) use ($projectIds, $user) {
                $query->whereIn('project_id', $projectIds)
                    ->orWhere(function ($q) use ($user) {
                        $q->whereNull('project_id')
                            ->where(function ($sub) use ($user) {
                                $sub->where('user_id', $user->id)
                                    ->orWhere('assigned_to', $user->id);
                            });
                    });
            });
    }

    protected function getDashboardTaskQuery(User $user, ?Company $company = null)
    {
        if ($company) {
            return Task::select('id', 'title', 'status', 'priority', 'type', 'points', 'due_date', 'project_id', 'assigned_to', 'user_id', 'created_at', 'updated_at')
                ->where('assigned_to', $user->id)
                ->where('status', '!=', 4)
                ->where(function ($query) use ($company) {
                    $query->whereHas('project', function ($pQuery) use ($company) {
                        $pQuery->where('company_id', $company->id)
                            ->where('status', '!=', 4);
                    })->orWhereNull('project_id');
                });
        }

        $companyIds = $user->companies()->pluck('company_id')->toArray();

        return Task::select('id', 'title', 'status', 'priority', 'type', 'points', 'due_date', 'project_id', 'assigned_to', 'user_id', 'created_at', 'updated_at')
            ->where('assigned_to', $user->id)
            ->where('status', '!=', 4)
            ->where(function ($query) use ($companyIds, $user) {
                $query->whereHas('project', function ($pQuery) use ($companyIds, $user) {
                    $pQuery->where(function ($q) use ($companyIds, $user) {
                        $q->whereIn('company_id', $companyIds)
                            ->orWhere(function ($sub) use ($user) {
                                $sub->whereNull('company_id')->where('user_id', $user->id);
                            });
                    })->where('status', '!=', 4);
                })->orWhereNull('project_id');
            });
    }

    public function getFilteredTasksForUser(User $user, array $filters = [], int $perPage = 5): LengthAwarePaginator
    {
        $tasksQuery = $this->getAccessibleTasksQuery($user);

        if (! empty($filters['project']) && $filters['project'] !== 'all') {
            if ($filters['project'] === 'none') {
                $tasksQuery->whereNull('project_id');
            } else {
                $tasksQuery->where('project_id', $filters['project']);
            }
        }

        if (! empty($filters['status']) && $filters['status'] !== 'all') {
            if ($filters['status'] === 'completed') {
                $tasksQuery->where('status', 3);
            } elseif ($filters['status'] === 'pending') {
                $tasksQuery->where('status', '!=', 3);
            }
        } else {
            $showCompleted = isset($filters['show_completed']) && ($filters['show_completed'] === 'true' || $filters['show_completed'] === true);
            if (! $showCompleted) {
                $tasksQuery->where('status', '!=', 3);
            }
        }

        if (! empty($filters['assignee']) && $filters['assignee'] !== 'all') {
            if ($filters['assignee'] === 'unassigned') {
                $tasksQuery->whereNull('assigned_to');
            } else {
                $tasksQuery->where('assigned_to', $filters['assignee']);
            }
        }

        if (! empty($filters['type']) && $filters['type'] !== 'all') {
            $tasksQuery->where('type', $filters['type']);
        }

        return $tasksQuery->with(['project', 'assignedUser'])->paginate($perPage);
    }

    public function getTaskStatsForUser(User $user): array
    {
        $tasksQuery = $this->getAccessibleTasksQuery($user);

        $totalCount = (clone $tasksQuery)->count();
        $completedCount = (clone $tasksQuery)->where('status', 3)->count();
        $pendingCount = $totalCount - $completedCount;
        $overdueCount = (clone $tasksQuery)->where('status', '!=', 3)
            ->whereNotNull('due_date')
            ->where('due_date', '<', now()->toDateString())
            ->count();

        return [
            'totalCount' => $totalCount,
            'completedCount' => $completedCount,
            'pendingCount' => $pendingCount,
            'overdueCount' => $overdueCount,
        ];
    }

    public function getAccessibleCompanyUsers(User $user, ?int $companyId = null): Collection
    {
        $targetCompanyId = $companyId ?? session('active_company_id');
        $cacheKeySuffix = $targetCompanyId ? "company_{$targetCompanyId}" : 'all_companies';
        $cacheKey = "accessible_company_users_{$user->id}_{$cacheKeySuffix}";

        return Cache::remember($cacheKey, 600, function () use ($user, $targetCompanyId) {
            if ($targetCompanyId) {
                $companyIds = [$targetCompanyId];
            } else {
                $companyIds = $user->companies()->pluck('company_id')->toArray();
            }

            $companyUsers = CompanyUsers::whereIn('company_id', $companyIds)
                ->with('user')
                ->get()
                ->map(function ($cu) {
                    return $cu->user;
                })
                ->filter()
                ->unique('id')
                ->values();

            if (! $companyUsers->contains('id', $user->id)) {
                $companyUsers->push($user);
            }

            return $companyUsers;
        });
    }

    public function getTodayTasks(User $user, ?Company $company = null, string $filter = 'today_past', int $perPage = 5): LengthAwarePaginator
    {
        $today = now()->toDateString();
        $query = $this->getDashboardTaskQuery($user, $company)->where('status', '!=', 3);

        if ($filter === 'due_today') {
            $query->where('due_date', '=', $today);
        } elseif ($filter === 'overdue') {
            $query->whereNotNull('due_date')->where('due_date', '<', $today);
        } elseif ($filter === 'all_pending') {
            // No due_date filter
        } else {
            // Default 'today_past'
            $query->where(function ($q) use ($today) {
                $q->whereNull('due_date')->orWhere('due_date', '<=', $today);
            });
        }

        return $query->with(['project'])
            ->orderByRaw('priority DESC')
            ->orderByRaw('CASE WHEN due_date IS NULL THEN 1 ELSE 0 END, due_date ASC')
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getTodayTaskCounts(User $user, ?Company $company = null): array
    {
        $today = now()->toDateString();
        $allPendingTaskBase = $this->getDashboardTaskQuery($user, $company)->where('status', '!=', 3);

        $todayCount = (clone $allPendingTaskBase)->where('due_date', '=', $today)->count();
        $overdueCount = (clone $allPendingTaskBase)->whereNotNull('due_date')->where('due_date', '<', $today)->count();
        $todayPastCount = (clone $allPendingTaskBase)->where(function ($q) use ($today) {
            $q->whereNull('due_date')->orWhere('due_date', '<=', $today);
        })->count();
        $allPendingCount = (clone $allPendingTaskBase)->count();

        return [
            'todayCount' => $todayCount,
            'overdueCount' => $overdueCount,
            'todayPastCount' => $todayPastCount,
            'allPendingCount' => $allPendingCount,
            'today' => $today,
        ];
    }

    public function findOrFail(int|string $id): Task
    {
        return Task::findOrFail($id);
    }

    public function createTask(array $data): Task
    {
        return Task::create($data);
    }

    public function createProjectTask(Project $project, array $data): Task
    {
        return $project->tasks()->create($data);
    }

    public function updateTask(Task $task, array $data): Task
    {
        $task->update($data);

        return $task;
    }

    public function deleteTask(Task $task): bool
    {
        return (bool) $task->delete();
    }

    public function toggleTaskStatus(Task $task): Task
    {
        $newStatus = ($task->status == 3) ? 1 : 3;
        $task->update(['status' => $newStatus]);

        return $task;
    }

    public function createTaskImage(Task $task, string $imagePath): TaskImage
    {
        return $task->images()->create(['image_path' => $imagePath]);
    }

    public function deleteTaskImage(TaskImage $image): bool
    {
        return (bool) $image->delete();
    }
}
