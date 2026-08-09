<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Cache;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property int $id
 * @property int $user_id
 * @property string $content
 * @property string $commentable_type
 * @property int $commentable_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Model $commentable
 * @property-read User $user
 */
class Comment extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected $fillable = [
        'user_id',
        'content',
        'commentable_type',
        'commentable_id',
    ];

    protected static function booted(): void
    {
        static::saved(function (Comment $comment) {
            static::clearDiscussionCache($comment);
        });

        static::deleted(function (Comment $comment) {
            static::clearDiscussionCache($comment);
        });
    }

    protected static function clearDiscussionCache(Comment $comment): void
    {
        if ($comment->commentable_type === 'project' || $comment->commentable_type === Project::class) {
            Cache::forget("project_{$comment->commentable_id}_comments");
        }
    }

    /**
     * Get the owning commentable model.
     *
     * @return MorphTo<Model, $this>
     */
    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user that authored the comment.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
