<?php

namespace App\Models;

use Database\Factories\CompanyUsersFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyUsers extends Model
{
    /** @use HasFactory<CompanyUsersFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = ['role', 'company_id', 'user_id', 'is_approved'];

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<Task, $this>
     */
    public function totalTasks()
    {
        return $this->hasMany(Task::class, 'assigned_to', 'user_id')
            ->whereHas('project', function ($query) {
                $query->whereColumn('projects.company_id', 'company_users.company_id');
            });
    }

    /**
     * @return HasMany<Task, $this>
     */
    public function pendingTasks()
    {
        return $this->totalTasks()->where('status', '!=', 3);
    }

    /**
     * @return HasMany<Task, $this>
     */
    public function completedTasks()
    {
        return $this->totalTasks()->where('status', 3);
    }
}
