<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function view(User $user, Project $project): bool
    {
        return $user->company_id === $project->company_id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->isAdmin() &&
               $user->company_id === $project->company_id;
    }
}
