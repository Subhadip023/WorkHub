<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Note extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'note_type',
        'note_type_id',
    ];

    // Note types constants
    const TYPE_PROJECT = 1;

    const TYPE_TASK = 2;

    const TYPE_ORGANIZATION = 3;

    const TYPE_PERSONAL = 4;

    /**
     * Get the author of the note.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent noteable model.
     */
    public function getNoteableAttribute()
    {
        switch ($this->note_type) {
            case self::TYPE_PROJECT:
                return Project::find($this->note_type_id);
            case self::TYPE_TASK:
                return Task::find($this->note_type_id);
            case self::TYPE_ORGANIZATION:
                return Company::find($this->note_type_id);
            case self::TYPE_PERSONAL:
                return User::find($this->note_type_id);
            default:
                return null;
        }
    }
}
