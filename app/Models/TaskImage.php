<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskImage extends Model
{
    protected $fillable = [
        'task_id',
        'image_path',
    ];

    protected $appends = [
        'url',
    ];

    public function getUrlAttribute(): string
    {
        return asset('storage/'.$this->image_path);
    }

    /**
     * Get the task that owns the image.
     *
     * @return BelongsTo<Task, $this>
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}
