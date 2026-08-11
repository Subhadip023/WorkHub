<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class GithubIssue extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['task_id', 'project_github_repo_id', 'github_issue_id', 'issue_number', 'issue_url', 'title', 'state'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected $fillable = [
        'task_id',
        'project_github_repo_id',
        'github_issue_id',
        'issue_number',
        'issue_url',
        'title',
        'state',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
        'github_issue_id' => 'integer',
        'issue_number' => 'integer',
    ];

    /**
     * @return BelongsTo<Task, $this>
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * @return BelongsTo<ProjectGithubRepo, $this>
     */
    public function projectGithubRepo(): BelongsTo
    {
        return $this->belongsTo(ProjectGithubRepo::class);
    }
}
