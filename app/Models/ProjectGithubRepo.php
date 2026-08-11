<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ProjectGithubRepo extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['project_id', 'user_id', 'repo_owner', 'repo_name', 'auto_sync_issues', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected $fillable = [
        'project_id',
        'user_id',
        'repo_owner',
        'repo_name',
        'access_token',
        'webhook_secret',
        'auto_sync_issues',
        'is_active',
        'last_synced_at',
    ];

    protected $casts = [
        'access_token' => 'encrypted',
        'webhook_secret' => 'encrypted',
        'auto_sync_issues' => 'boolean',
        'is_active' => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    /**
     * Get full repository name (e.g., owner/repo).
     */
    public function getFullRepoNameAttribute(): string
    {
        return "{$this->repo_owner}/{$this->repo_name}";
    }

    /**
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<GithubIssue, $this>
     */
    public function githubIssues()
    {
        return $this->hasMany(GithubIssue::class);
    }
}
