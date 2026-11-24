<?php

namespace App\Models;

use Database\Factories\CompanyUsersFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class CompanyUsers extends Model
{
    /** @use HasFactory<CompanyUsersFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = ['role', 'company_id', 'user_id', 'is_approved'];

    protected static function booted(): void
    {
        static::saved(function (CompanyUsers $companyUser) {
            Cache::forget("accessible_company_users_{$companyUser->user_id}_company_{$companyUser->company_id}");
            Cache::forget("accessible_company_users_{$companyUser->user_id}_all_companies");
        });

        static::deleted(function (CompanyUsers $companyUser) {
            Cache::forget("accessible_company_users_{$companyUser->user_id}_company_{$companyUser->company_id}");
            Cache::forget("accessible_company_users_{$companyUser->user_id}_all_companies");
        });
    }

    /**
     * Resolve the membership that still grants rights in a company for restore /
     * force-delete decisions.
     *
     * - Active company: only an active, approved membership counts. A member who
     *   was removed (soft-deleted row) gets nothing.
     * - Trashed company: every membership is soft-deleted together with the
     *   company, so a trashed membership counts ONLY if it was deleted as part of
     *   the company deletion (not earlier by an admin removing that member).
     */
    public static function effectiveMembership(int $userId, int $companyId): ?self
    {
        $company = Company::withTrashed()->find($companyId);
        if (! $company) {
            return null;
        }

        $active = static::where('company_id', $companyId)
            ->where('user_id', $userId)
            ->where('is_approved', true)
            ->first();

        if ($active || ! $company->trashed()) {
            return $active;
        }

        return static::onlyTrashed()
            ->where('company_id', $companyId)
            ->where('user_id', $userId)
            ->where('is_approved', true)
            ->where('deleted_at', '>=', $company->deleted_at->copy()->subSeconds(5))
            ->first();
    }

    /**
     * Company IDs where the user still holds an effective membership
     * (see effectiveMembership()), optionally only where they are admin.
     *
     * @return array<int, int>
     */
    public static function effectiveCompanyIds(int $userId, bool $adminOnly = false): array
    {
        return static::withTrashed()
            ->where('user_id', $userId)
            ->pluck('company_id')
            ->unique()
            ->filter(function ($companyId) use ($userId, $adminOnly) {
                $membership = static::effectiveMembership($userId, (int) $companyId);

                return $membership && (! $adminOnly || (int) $membership->role === 1);
            })
            ->values()
            ->all();
    }

    /**
     * Restore only the memberships that were soft-deleted together with the
     * company, so members removed earlier are not silently re-added.
     */
    public static function restoreWithCompany(Company $company): void
    {
        if (! $company->deleted_at) {
            return;
        }

        static::onlyTrashed()
            ->where('company_id', $company->id)
            ->where('deleted_at', '>=', $company->deleted_at->copy()->subSeconds(5))
            ->restore();
    }

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
