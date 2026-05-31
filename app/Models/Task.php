<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected static function booted()
    {
        static::created(function ($task) {
            TaskHistory::create([
                'task_id' => $task->id,
                'user_id' => auth()->id(),
                'old_status' => null,
                'new_status' => $task->status ?? 1,
            ]);
        });

        static::updating(function ($task) {
            if ($task->isDirty('status')) {
                $oldStatus = $task->getOriginal('status');
                $newStatus = $task->status;

                if ($oldStatus != $newStatus) {
                    TaskHistory::create([
                        'task_id' => $task->id,
                        'user_id' => auth()->id(),
                        'old_status' => $oldStatus,
                        'new_status' => $newStatus,
                    ]);
                }
            }
        });
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
}
