<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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

    public function pendingTasksCount(): int
    {
        return $this->tasks()->where('status', 'pending')->count();
    }

    public function completedTasksCount(): int
    {
        return $this->tasks()->where('status', 'completed')->count();
    }
}
