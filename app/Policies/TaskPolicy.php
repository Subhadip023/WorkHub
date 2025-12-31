<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Task;

class TaskPolicy
{
    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function update(User $user, Task $task): bool
    {
        return $user->company_id === $task->project->company_id;
    }
}
