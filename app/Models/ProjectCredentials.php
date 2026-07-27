<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectCredentials extends Model
{
    use HasFactory;

    protected $table = 'project_credentials';

    protected $fillable = [
        'project_id',
        'type',
        'name',
        'host_or_identifier',
        'password_or_secret',
    ];

    protected $casts = [
        'password_or_secret' => 'encrypted',
    ];

    const TYPE_PRODUCTION = 0;

    const TYPE_STAGING = 1;

    const TYPE_API_KEY = 2;

    const TYPE_DEVELOPMENT = 3;

    public static array $typeMap = [
        'production' => self::TYPE_PRODUCTION,
        'staging' => self::TYPE_STAGING,
        'api_key' => self::TYPE_API_KEY,
        'development' => self::TYPE_DEVELOPMENT,
    ];

    public static array $typeLabels = [
        self::TYPE_PRODUCTION => 'production',
        self::TYPE_STAGING => 'staging',
        self::TYPE_API_KEY => 'api_key',
        self::TYPE_DEVELOPMENT => 'development',
    ];

    public function getTypeSlugAttribute(): string
    {
        return self::$typeLabels[$this->type] ?? 'development';
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
