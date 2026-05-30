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

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function images()
    {
        return $this->hasMany(TaskImage::class);
    }

    public function notes()
    {
        return $this->hasMany(Note::class, 'note_type_id')->where('note_type', Note::TYPE_TASK);
    }

    public function histories()
    {
        return $this->hasMany(TaskHistory::class)->latest();
    }
}
