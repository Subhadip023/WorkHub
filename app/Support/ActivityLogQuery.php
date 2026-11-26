<?php

namespace App\Support;

use App\Enums\CompanyRole;
use App\Models\CompanyUsers;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

/**
 * Builds the activity-log query shared by the V1 and V2 activity pages.
 *
 * - scope=mine (default): the viewer's own activity.
 * - scope=company (legacy alias: all): activity on records of companies where the
 *   viewer is Admin or Owner. Super Admins see everything.
 * - user_id=N: N's activity, only on records of companies where the viewer is
 *   Admin/Owner and N is an approved member (403 otherwise). Super Admins: unrestricted.
 */
final class ActivityLogQuery
{
    /**
     * @return array{0: Builder, 1: string} [query, effective scope]
     */
    public static function build(Request $request, User $viewer): array
    {
        $scope = $request->query('scope', 'mine');
        if ($scope === 'all') {
            $scope = 'company';
        }

        $query = Activity::with(['causer', 'subject']);
        $adminCompanyIds = self::adminCompanyIds($viewer);
        $targetUserId = $request->filled('user_id') ? (int) $request->input('user_id') : null;

        if ($targetUserId !== null && $targetUserId !== $viewer->id) {
            if (! $viewer->isSuperAdmin()) {
                $sharedCompanyIds = CompanyUsers::where('user_id', $targetUserId)
                    ->where('is_approved', true)
                    ->whereIn('company_id', $adminCompanyIds)
                    ->pluck('company_id')
                    ->all();

                abort_if($sharedCompanyIds === [], 403, 'You can only view activity of members in organizations you administer.');

                CompanyActivityScope::apply($query, $sharedCompanyIds);
            }

            self::forUser($query, $targetUserId);

            return [$query, 'member'];
        }

        if ($scope === 'company' && ($viewer->isSuperAdmin() || $adminCompanyIds !== [])) {
            if (! $viewer->isSuperAdmin()) {
                CompanyActivityScope::apply($query, $adminCompanyIds);
            }

            return [$query, 'company'];
        }

        self::forUser($query, $viewer->id);

        return [$query, 'mine'];
    }

    /** Whether the viewer may use the organization-wide view at all. */
    public static function canViewCompanyScope(User $viewer): bool
    {
        return $viewer->isSuperAdmin() || self::adminCompanyIds($viewer) !== [];
    }

    /** @return array<int, int> */
    public static function adminCompanyIds(User $viewer): array
    {
        return CompanyUsers::where('user_id', $viewer->id)
            ->where('is_approved', true)
            ->whereIn('role', CompanyRole::adminValues())
            ->pluck('company_id')
            ->all();
    }

    private static function forUser(Builder $query, int $userId): void
    {
        $query->where(function ($q) use ($userId) {
            $q->where(fn ($s) => $s->where('causer_type', 'user')->where('causer_id', $userId))
                ->orWhere(fn ($s) => $s->where('subject_type', 'user')->where('subject_id', $userId));
        });
    }
}
