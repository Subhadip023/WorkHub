<?php

namespace App\Rules;

use App\Models\CompanyUsers;
use App\Models\Project;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Ensures a task assignee belongs to the same workspace as the task.
 *
 * - Company project: assignee must be an approved member of that company.
 * - Personal project / personal task: assignee must be the actor (or project owner)
 *   or share at least one approved company with the actor.
 */
class AssignableToProject implements ValidationRule
{
    public function __construct(
        private readonly ?Project $project,
        private readonly User $actor
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        if (! is_numeric($value) || ! self::isAllowed((int) $value, $this->project, $this->actor)) {
            $fail('The selected assignee is not a member of this workspace.');
        }
    }

    public static function isAllowed(int $assigneeId, ?Project $project, User $actor): bool
    {
        if ($project && $project->company_id !== null) {
            return CompanyUsers::where('company_id', $project->company_id)
                ->where('user_id', $assigneeId)
                ->where('is_approved', true)
                ->exists();
        }

        if ($assigneeId === $actor->id || ($project && $assigneeId === (int) $project->user_id)) {
            return true;
        }

        $actorCompanyIds = CompanyUsers::where('user_id', $actor->id)
            ->where('is_approved', true)
            ->pluck('company_id');

        return CompanyUsers::whereIn('company_id', $actorCompanyIds)
            ->where('user_id', $assigneeId)
            ->where('is_approved', true)
            ->exists();
    }
}
