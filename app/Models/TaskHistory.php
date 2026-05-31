<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $task_id
 * @property int|null $user_id
 * @property string|null $field
 * @property string|null $old_value
 * @property string|null $new_value
 * @property int|null $old_status
 * @property int|null $new_status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Task $task
 * @property-read User|null $user
 */
class TaskHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'user_id',
        'field',
        'old_value',
        'new_value',
        'old_status',
        'new_status',
    ];

    /**
     * @return BelongsTo<Task, $this>
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
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

    /**
     * Get human-readable priority name.
     */
    public static function getPriorityName(?int $priority): string
    {
        switch ($priority) {
            case 1: return 'Low';
            case 2: return 'Medium';
            case 3: return 'High';
            case 4: return 'Urgent';
            default: return 'Medium';
        }
    }

    /**
     * Get human-readable type name.
     */
    public static function getTypeName(?int $type): string
    {
        switch ($type) {
            case 1: return 'Task';
            case 2: return 'Bug';
            case 3: return 'Feature';
            case 4: return 'Improvement';
            default: return 'Task';
        }
    }

    /**
     * Get human-readable description for the history event.
     */
    public function getDescription(): string
    {
        $field = $this->field ?? 'status';
        $old = $this->old_value !== null ? $this->old_value : ($this->old_status !== null ? (string) $this->old_status : null);
        $new = $this->new_value !== null ? $this->new_value : ($this->new_status !== null ? (string) $this->new_status : null);

        if ($field === 'status') {
            if ($old === null) {
                return 'Task created with status '.self::getStatusName((int) $new);
            }

            return 'Status changed to '.self::getStatusName((int) $new);
        }

        if ($field === 'priority') {
            if ($old === null) {
                return 'Priority set to '.self::getPriorityName((int) $new);
            }

            return 'Priority changed to '.self::getPriorityName((int) $new);
        }

        if ($field === 'type') {
            if ($old === null) {
                return 'Type set to '.self::getTypeName((int) $new);
            }

            return 'Type changed to '.self::getTypeName((int) $new);
        }

        if ($field === 'title') {
            return 'Title changed to "'.e($new).'"';
        }

        if ($field === 'description') {
            return 'Description was updated';
        }

        if ($field === 'due_date') {
            if ($new === null || $new === '') {
                return 'Due date was removed';
            }
            try {
                return 'Due date set to '.Carbon::parse($new)->format('M d, Y');
            } catch (\Exception $e) {
                return 'Due date changed to '.e($new);
            }
        }

        if ($field === 'assigned_to') {
            if ($new === null || $new === '' || $new === '0') {
                return 'Task was unassigned';
            }
            $user = User::find($new);
            $userName = $user ? $user->name : 'Unknown User';

            return 'Assigned to '.$userName;
        }

        return 'Updated '.$field;
    }

    /**
     * Get human-readable old value details.
     */
    public function getOldValueDetails(): ?string
    {
        $field = $this->field ?? 'status';
        $old = $this->old_value !== null ? $this->old_value : ($this->old_status !== null ? (string) $this->old_status : null);

        if ($old === null || $old === '') {
            return null;
        }

        if ($field === 'status') {
            return 'From: '.self::getStatusName((int) $old);
        }

        if ($field === 'priority') {
            return 'From: '.self::getPriorityName((int) $old);
        }

        if ($field === 'type') {
            return 'From: '.self::getTypeName((int) $old);
        }

        if ($field === 'title') {
            return 'From: "'.e($old).'"';
        }

        if ($field === 'due_date') {
            try {
                return 'From: '.Carbon::parse($old)->format('M d, Y');
            } catch (\Exception $e) {
                return 'From: '.e($old);
            }
        }

        if ($field === 'assigned_to') {
            if ($old === '0') {
                return 'From: Unassigned';
            }
            $user = User::find($old);
            $userName = $user ? $user->name : 'Unknown User';

            return 'From: '.$userName;
        }

        return null;
    }
}
