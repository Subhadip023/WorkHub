<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'user_id',
        'old_status',
        'new_status',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Task, $this>
     */
    public function task(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, $this>
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get human-readable status name.
     */
    public static function getStatusName(?int $status): string
    {
        switch ($status) {
            case 1: return 'To Do';
            case 2: return 'In Progress';
            case 3: return 'Completed';
            case 4: return 'On Hold';
            default: return 'Unknown';
        }
    }
}
