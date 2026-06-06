<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    const TYPE_TASK = 1;

    const TYPE_BUG = 2;

    const TYPE_FEATURE = 3;

    const TYPE_IMPROVEMENT = 4;

    protected $guarded = [];

    protected static function booted()
    {
        static::created(function ($task) {
            TaskHistory::create([
                'task_id' => $task->id,
                'user_id' => auth()->id(),
                'field' => 'status',
                'old_value' => null,
                'new_value' => (string) ($task->status ?? 1),
                'old_status' => null,
                'new_status' => $task->status ?? 1,
            ]);

            if ($task->priority) {
                TaskHistory::create([
                    'task_id' => $task->id,
                    'user_id' => auth()->id(),
                    'field' => 'priority',
                    'old_value' => null,
                    'new_value' => (string) $task->priority,
                ]);
            }

            if ($task->type) {
                TaskHistory::create([
                    'task_id' => $task->id,
                    'user_id' => auth()->id(),
                    'field' => 'type',
                    'old_value' => null,
                    'new_value' => (string) $task->type,
                ]);
            }
        });

        static::updating(function ($task) {
            $trackedFields = ['status', 'priority', 'type', 'title', 'description', 'due_date', 'assigned_to'];
            foreach ($trackedFields as $field) {
                if ($task->isDirty($field)) {
                    $oldVal = $task->getOriginal($field);
                    $newVal = $task->getAttribute($field);
                    if ($oldVal != $newVal) {
                        $data = [
                            'task_id' => $task->id,
                            'user_id' => auth()->id(),
                            'field' => $field,
                            'old_value' => $oldVal === null ? null : (string) $oldVal,
                            'new_value' => $newVal === null ? null : (string) $newVal,
                        ];

                        if ($field === 'status') {
                            $data['old_status'] = $oldVal !== null ? (int) $oldVal : null;
                            $data['new_status'] = $newVal !== null ? (int) $newVal : null;
                        }

                        TaskHistory::create($data);
                    }
                }
            }
        });
    }

    /**
     * Get human-readable type name.
     */
    public function getTypeName(): string
    {
        switch ($this->type) {
            case self::TYPE_TASK: return 'Task';
            case self::TYPE_BUG: return 'Bug';
            case self::TYPE_FEATURE: return 'Feature';
            case self::TYPE_IMPROVEMENT: return 'Improvement';
            default: return 'Task';
        }
    }

    /**
     * Get CSS badge class for styling the task type.
     */
    public function getTypeBadgeClass(): string
    {
        switch ($this->type) {
            case self::TYPE_BUG: return 'badge-danger';
            case self::TYPE_FEATURE: return 'badge-success';
            case self::TYPE_IMPROVEMENT: return 'badge-info';
            case self::TYPE_TASK:
            default:
                return 'badge-light border text-gray-800';
        }
    }

    /**
     * Get FontAwesome icon class.
     */
    public function getTypeIcon(): string
    {
        switch ($this->type) {
            case self::TYPE_BUG: return 'fa-bug text-danger';
            case self::TYPE_FEATURE: return 'fa-rocket text-success';
            case self::TYPE_IMPROVEMENT: return 'fa-chart-line text-info';
            case self::TYPE_TASK:
            default:
                return 'fa-tasks text-secondary';
        }
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
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * @return HasMany<TaskImage, $this>
     */
    public function images(): HasMany
    {
        return $this->hasMany(TaskImage::class);
    }

    /**
     * @return HasMany<Note, $this>
     */
    public function notes(): HasMany
    {
        return $this->hasMany(Note::class, 'note_type_id')->where('note_type', Note::TYPE_TASK);
    }

    /**
     * @return HasMany<TaskHistory, $this>
     */
    public function histories(): HasMany
    {
        return $this->hasMany(TaskHistory::class)->latest();
    }

    /**
     * @return MorphMany<Comment, $this>
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
