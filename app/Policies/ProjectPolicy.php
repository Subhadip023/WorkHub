<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    /**
     * Determine whether the user can view the project.
     */
    public function view(User $user, Project $project): bool
    {
        if ($project->company_id === null) {
            return $project->user_id === $user->id;
        }

        return $user->companies->contains('company_id', $project->company_id);
    }

    /**
     * Determine whether the user can update the project.
     */
    public function update(User $user, Project $project): bool
    {
        return $this->view($user, $project);
    }

    /**
     * Determine whether the user can delete the project.
     */
    public function delete(User $user, Project $project): bool
    {
        if ($project->company_id === null) {
            return $project->user_id === $user->id;
        }

        // Only company administrators can delete company projects
        $membership = $user->companies()->where('company_id', $project->company_id)->first();

        return $membership && $membership->role === 1;
    }

    /**
     * Determine whether the user can restore the project.
     */
    public function restore(User $user, Project $project): bool
    {
        // Use withTrashed() because membership or project components might be soft-deleted
        if ($project->company_id === null) {
            return $project->user_id === $user->id;
        }

        $membership = $user->companies()->withTrashed()->where('company_id', $project->company_id)->first();

        return $membership && $membership->role === 1;
    }

    /**
     * Determine whether the user can permanently delete the project.
     */
    public function forceDelete(User $user, Project $project): bool
    {
        return $this->restore($user, $project);
    }
}
