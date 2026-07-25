<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    /**
     * Determine whether the user can view the task.
     */
    public function view(User $user, Task $task): bool
    {
        $project = $task->project;

        // 1. Personal Task Scope (No Project associated)
        if ($project === null) {
            return ($task->user_id === $user->id) || ($task->assigned_to === $user->id);
        }

        // 2. Personal Project Scope
        if ($project->company_id === null) {
            return ($project->user_id === $user->id) || ($task->assigned_to === $user->id);
        }

        // 3. Organization Project Scope (Must be a company member)
        return $user->companies->contains('company_id', $project->company_id);
    }

    /**
     * Determine whether the user can update (mutate, toggle, upload/delete images, delete) the task.
     */
    public function update(User $user, Task $task): bool
    {
        $project = $task->project;

        // 1. Personal Task Scope (No Project associated)
        if ($project === null) {
            return ($task->user_id === $user->id) || ($task->assigned_to === $user->id);
        }

        // 2. Personal Project Scope (Project is not associated with any company)
        if ($project->company_id === null) {
            return ($project->user_id === $user->id) || ($task->assigned_to === $user->id);
        }

        // 3. Organization Project Scope
        $membership = $user->companies()->where('company_id', $project->company_id)->first();

        if (! $membership) {
            return false;
        }

        // Admins can modify anything; Members can only modify tasks explicitly assigned to them
        return ($membership->role == 1) || ($task->assigned_to === $user->id) || ($task->user_id === $user->id);
    }

    /**
     * Determine whether the user can restore the task.
     */
    public function restore(User $user, Task $task): bool
    {
        $project = $task->project()->withTrashed()->first();

        // 1. Personal Task Scope (No Project associated)
        if ($project === null) {
            return ($task->user_id === $user->id) || ($task->assigned_to === $user->id);
        }

        // 2. Personal Project Scope (Project is not associated with any company)
        if ($project->company_id === null) {
            return ($project->user_id === $user->id) || ($task->assigned_to === $user->id);
        }

        // 3. Organization Project Scope
        $membership = $user->companies()->withTrashed()->where('company_id', $project->company_id)->first();

        if (! $membership) {
            return false;
        }

        // Admins can restore anything; Members can only restore tasks explicitly assigned to them or created by them
        return ($membership->role == 1) || ($task->assigned_to === $user->id) || ($task->user_id === $user->id);
    }

    /**
     * Determine whether the user can permanently delete the task.
     */
    public function forceDelete(User $user, Task $task): bool
    {
        return $this->restore($user, $task);
    }
}
