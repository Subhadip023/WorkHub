<?php

namespace App\Support;

use App\Models\Comment;
use App\Models\ExternalTaskApi;
use App\Models\Note;
use App\Models\Project;
use App\Models\ProjectCredentials;
use App\Models\Task;
use Illuminate\Database\Eloquent\Builder;

/**
 * Restricts an activity-log query to records that belong to given companies,
 * so organization-level views never leak another tenant's activity.
 *
 * Subjects are matched by morph alias (see AppServiceProvider morphMap).
 */
final class CompanyActivityScope
{
    /**
     * @param  array<int, int>  $companyIds
     */
    public static function apply(Builder $query, array $companyIds): Builder
    {
        if ($companyIds === []) {
            return $query->whereRaw('1 = 0');
        }

        $projectIds = Project::withTrashed()->whereIn('company_id', $companyIds)->pluck('id')->all();
        $taskIds = $projectIds === [] ? [] : Task::withTrashed()->whereIn('project_id', $projectIds)->pluck('id')->all();

        $noteIds = Note::withTrashed()->where(function ($q) use ($companyIds, $projectIds, $taskIds) {
            $q->where(fn ($s) => $s->where('note_type', Note::TYPE_ORGANIZATION)->whereIn('note_type_id', $companyIds))
                ->orWhere(fn ($s) => $s->where('note_type', Note::TYPE_PROJECT)->whereIn('note_type_id', $projectIds ?: [0]))
                ->orWhere(fn ($s) => $s->where('note_type', Note::TYPE_TASK)->whereIn('note_type_id', $taskIds ?: [0]));
        })->pluck('id')->all();

        $credentialIds = $projectIds === [] ? [] : ProjectCredentials::whereIn('project_id', $projectIds)->pluck('id')->all();
        $apiIds = $projectIds === [] ? [] : ExternalTaskApi::withTrashed()->whereIn('project_id', $projectIds)->pluck('id')->all();

        $commentIds = Comment::where(function ($q) use ($companyIds, $projectIds, $taskIds, $noteIds) {
            $q->where(fn ($s) => $s->where('commentable_type', 'company')->whereIn('commentable_id', $companyIds))
                ->orWhere(fn ($s) => $s->where('commentable_type', 'project')->whereIn('commentable_id', $projectIds ?: [0]))
                ->orWhere(fn ($s) => $s->where('commentable_type', 'task')->whereIn('commentable_id', $taskIds ?: [0]))
                ->orWhere(fn ($s) => $s->where('commentable_type', 'note')->whereIn('commentable_id', $noteIds ?: [0]));
        })->pluck('id')->all();

        $subjects = [
            'company' => $companyIds,
            'project' => $projectIds,
            'task' => $taskIds,
            'note' => $noteIds,
            'comment' => $commentIds,
            'project_credentials' => $credentialIds,
            'external_task_api' => $apiIds,
        ];

        return $query->where(function ($q) use ($subjects) {
            foreach ($subjects as $type => $ids) {
                if ($ids !== []) {
                    $q->orWhere(fn ($s) => $s->where('subject_type', $type)->whereIn('subject_id', $ids));
                }
            }
            // Guarantee the group is never empty (would otherwise match everything).
            $q->orWhereRaw('1 = 0');
        });
    }
}
