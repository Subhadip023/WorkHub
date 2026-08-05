<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ExternalTaskApi extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'user_id',
        'assigned_user_id',
        'default_status',
        'default_priority',
        'default_type',
        'name',
        'api_key',
        'api_secret',
        'is_active',
    ];

    protected $casts = [
        'api_secret' => 'encrypted',
        'is_active' => 'boolean',
        'project_id' => 'integer',
        'default_status' => 'integer',
        'default_priority' => 'integer',
        'default_type' => 'integer',
    ];

    /**
     * Generate unique API Key and Secret.
     */
    public static function generateCredentials(): array
    {
        return [
            'api_key' => 'wh_pk_'.Str::random(32),
            'api_secret' => 'wh_sk_'.Str::random(48),
        ];
    }

    /**
     * Relationship to Project (0 = personal project).
     *
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    /**
     * Creator / Owner of the API Key.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Member assigned to tasks created via this API Key.
     *
     * @return BelongsTo<User, $this>
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    /**
     * Tasks created using this API Key.
     *
     * @return HasMany<ExternalTaskSource, $this>
     */
    public function sources()
    {
        return $this->hasMany(ExternalTaskSource::class);
    }
}
