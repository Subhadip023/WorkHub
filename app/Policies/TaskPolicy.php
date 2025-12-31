<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    /**
     * Determine if the user can view tasks of a project
     */
    public function viewAny(User $user, Project $project): bool
    {
        return $user->company_id === $project->company_id;
    }

    /**
     * Determine if the user can create a task under a project
     */
    public function create(User $user, Project $project): bool
    {
        return $user->company_id === $project->company_id;
    }

    /**
     * Determine if the user can update a task
     */
    public function update(User $user, Task $task): bool
    {
        return $user->company_id === $task->project->company_id;
    }

    /**
     * Determine if the user can delete a task
     */
    public function delete(User $user, Task $task): bool
    {
        return $user->company_id === $task->project->company_id;
    }
}
