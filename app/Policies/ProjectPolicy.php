<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Project;

class ProjectPolicy
{
    public function view(User $user, Project $project): bool
    {
        return $user->company_id === $project->company_id;
    }

    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->role === 'admin'
            && $user->company_id === $project->company_id;
    }
}
