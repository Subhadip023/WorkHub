<?php

namespace App\Policies;

use App\Authorization\CompanyAbility;
use App\Models\CompanyUsers;
use App\Models\Project;
use App\Models\User;

/**
 * Personal projects (company_id null): the owner can do everything.
 * Company projects: rules come from App\Authorization\CompanyAbility.
 */
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
     * Create tasks, notes, issues and imports inside the project.
     */
    public function contribute(User $user, Project $project): bool
    {
        if ($project->company_id === null) {
            return $project->user_id === $user->id;
        }

        return CompanyAbility::allows($user, $project->company_id, CompanyAbility::PROJECT_CONTRIBUTE);
    }

    /**
     * Edit the project itself (name, description, status...). Managers and above,
     * or the member who created the project.
     */
    public function update(User $user, Project $project): bool
    {
        if ($project->company_id === null) {
            return $project->user_id === $user->id;
        }

        if ($project->user_id === $user->id && $this->view($user, $project)) {
            return true;
        }

        return CompanyAbility::allows($user, $project->company_id, CompanyAbility::PROJECT_UPDATE);
    }

    /**
     * View or manage project secrets (credentials vault, external API keys).
     */
    public function manageSecrets(User $user, Project $project): bool
    {
        if ($project->company_id === null) {
            return $project->user_id === $user->id;
        }

        return CompanyAbility::allows($user, $project->company_id, CompanyAbility::SECRETS_MANAGE);
    }

    /**
     * Determine whether the user can delete the project.
     */
    public function delete(User $user, Project $project): bool
    {
        if ($project->company_id === null) {
            return $project->user_id === $user->id;
        }

        return CompanyAbility::allows($user, $project->company_id, CompanyAbility::PROJECT_DELETE);
    }

    /**
     * Determine whether the user can restore the project.
     */
    public function restore(User $user, Project $project): bool
    {
        if ($project->company_id === null) {
            return $project->user_id === $user->id;
        }

        // Membership may be soft-deleted together with a trashed company.
        $membership = CompanyUsers::effectiveMembership($user->id, $project->company_id);

        return CompanyAbility::roleAllows($membership?->roleEnum(), CompanyAbility::TRASH_MANAGE);
    }

    /**
     * Determine whether the user can permanently delete the project.
     */
    public function forceDelete(User $user, Project $project): bool
    {
        return $this->restore($user, $project);
    }
}
