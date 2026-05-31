<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected static function booted()
    {
        static::created(function ($task) {
            \App\Models\TaskHistory::create([
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
                    \App\Models\TaskHistory::create([
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Project, $this>
     */
    public function project(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, $this>
     */
    public function assignedUser(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<TaskImage, $this>
     */
    public function images(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TaskImage::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Note, $this>
     */
    public function notes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Note::class, 'note_type_id')->where('note_type', Note::TYPE_TASK);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<TaskHistory, $this>
     */
    public function histories(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TaskHistory::class)->latest();
    }
}
