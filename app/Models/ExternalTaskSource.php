<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExternalTaskSource extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'external_task_api_id',
        'payload',
        'ip_address',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    /**
     * @return BelongsTo<Task, $this>
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * @return BelongsTo<ExternalTaskApi, $this>
     */
    public function externalTaskApi(): BelongsTo
    {
        return $this->belongsTo(ExternalTaskApi::class);
    }
}
