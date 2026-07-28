<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Pennant\Concerns\HasFeatures;

/**
 * @property bool|null $beta_access
 * @property bool|null $issues_access
 */
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasFeatures, Notifiable;

    public const ROLE_SUPER_ADMIN = 0;

    public const ROLE_ADMIN = 1;

    public const ROLE_USER = 2;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_image',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => 'integer',
        ];
    }

    /**
     * @return HasMany<CompanyUsers, $this>
     */
    public function companies(): HasMany
    {
        return $this->hasMany(CompanyUsers::class, 'user_id')->where('is_approved', true);
    }

    /**
     * @return HasMany<CompanyUsers, $this>
     */
    public function allCompanies(): HasMany
    {
        return $this->hasMany(CompanyUsers::class, 'user_id');
    }

    /**
     * @return HasMany<Comment, $this>
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * @return HasMany<Notification, $this>
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    /**
     * Count tasks that are not yet completed (status 1=To Do, 2=In Progress, 4=On Hold).
     */
    public function pendingTasksCount(): int
    {
        return $this->tasks()->whereIn('status', [1, 2, 4])->count();
    }

    /**
     * Count tasks that are completed (status 3=Completed).
     */
    public function completedTasksCount(): int
    {
        return $this->tasks()->where('status', 3)->count();
    }

    /**
     * Check if user is a Super Admin.
     */
    public function isSuperAdmin(): bool
    {
        return (int) $this->role === self::ROLE_SUPER_ADMIN;
    }

    /**
     * Check if user is an Admin (or Super Admin).
     */
    public function isAdmin(): bool
    {
        return (int) $this->role === self::ROLE_ADMIN || $this->isSuperAdmin();
    }
}
