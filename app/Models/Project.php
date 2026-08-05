<?php

namespace App\Models;

use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'theme',
        'status',
        'priority',
        'user_id',
        'company_id',
    ];

    /**
     * @return HasMany<Task, $this>
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * @return HasMany<Note, $this>
     */
    public function notes(): HasMany
    {
        return $this->hasMany(Note::class, 'note_type_id')->where('note_type', Note::TYPE_PROJECT);
    }

    /**
     * @return MorphMany<Comment, $this>
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * Get cached discussion comments for the project.
     */
    public function getCachedComments()
    {
        return Cache::remember(
            "project_{$this->id}_comments",
            3600,
            fn () => $this->comments()->with('user')->latest()->get()
        );
    }

    /**
     * Clear cached discussion comments.
     */
    public function clearCachedComments(): void
    {
        Cache::forget("project_{$this->id}_comments");
    }

    /**
     * @return HasMany<ProjectCredentials, $this>
     */
    public function credentials(): HasMany
    {
        return $this->hasMany(ProjectCredentials::class, 'project_id');
    }

    /**
     * @return HasMany<ExternalTaskApi, $this>
     */
    public function externalApis(): HasMany
    {
        return $this->hasMany(ExternalTaskApi::class, 'project_id');
    }
}
