<?php

namespace App\Authorization;

use App\Enums\CompanyRole;
use App\Models\CompanyUsers;
use App\Models\User;

/**
 * Single source of truth for what each company role may do.
 *
 * Policies call allows(); the Permissions page renders matrix(). If a rule
 * changes, change it here and both enforcement and documentation follow.
 */
final class CompanyAbility
{
    public const COMPANY_UPDATE = 'company.update';

    public const COMPANY_DELETE = 'company.delete';

    public const COMPANY_TRANSFER = 'company.transfer';

    public const MEMBERS_INVITE = 'members.invite';

    public const MEMBERS_APPROVE = 'members.approve';

    public const MEMBERS_REMOVE = 'members.remove';

    public const MEMBERS_CHANGE_ROLE = 'members.change_role';

    public const PROJECT_CREATE = 'project.create';

    public const PROJECT_CONTRIBUTE = 'project.contribute';

    public const PROJECT_UPDATE = 'project.update';

    public const PROJECT_DELETE = 'project.delete';

    public const SECRETS_MANAGE = 'secrets.manage';

    public const TASK_UPDATE_ANY = 'task.update_any';

    public const TRASH_MANAGE = 'trash.manage';

    public const ACTIVITY_VIEW_COMPANY = 'activity.view_company';

    /**
     * ability => [minimum role, category, human label]
     *
     * @var array<string, array{0: CompanyRole, 1: string, 2: string}>
     */
    private const DEFINITIONS = [
        self::COMPANY_UPDATE => [CompanyRole::Admin, 'Organization', 'Rename organization'],
        self::COMPANY_DELETE => [CompanyRole::Owner, 'Organization', 'Delete or restore organization'],
        self::COMPANY_TRANSFER => [CompanyRole::Owner, 'Organization', 'Transfer ownership'],
        self::MEMBERS_INVITE => [CompanyRole::Admin, 'Members', 'Invite members'],
        self::MEMBERS_APPROVE => [CompanyRole::Admin, 'Members', 'Approve / reject join requests'],
        self::MEMBERS_REMOVE => [CompanyRole::Admin, 'Members', 'Remove members with a lower role'],
        self::MEMBERS_CHANGE_ROLE => [CompanyRole::Admin, 'Members', 'Change roles below your own'],
        self::PROJECT_CREATE => [CompanyRole::Member, 'Projects', 'Create projects'],
        self::PROJECT_CONTRIBUTE => [CompanyRole::Member, 'Projects', 'Create tasks, notes and issues in any project'],
        self::PROJECT_UPDATE => [CompanyRole::Manager, 'Projects', 'Edit any project (creators can always edit their own)'],
        self::PROJECT_DELETE => [CompanyRole::Admin, 'Projects', 'Delete projects to the recycle bin'],
        self::SECRETS_MANAGE => [CompanyRole::Manager, 'Projects', 'View & manage credentials and external API keys'],
        self::TASK_UPDATE_ANY => [CompanyRole::Manager, 'Tasks', 'Edit, move or delete any task (members: own / assigned only)'],
        self::TRASH_MANAGE => [CompanyRole::Admin, 'Administration', 'Restore / purge recycle bin items'],
        self::ACTIVITY_VIEW_COMPANY => [CompanyRole::Admin, 'Administration', 'View organization-wide activity log'],
    ];

    /** Role of the user in the company (approved, active membership) or null. */
    public static function roleOf(User $user, int $companyId): ?CompanyRole
    {
        $membership = CompanyUsers::where('company_id', $companyId)
            ->where('user_id', $user->id)
            ->where('is_approved', true)
            ->first();

        return $membership?->roleEnum();
    }

    public static function minimumRole(string $ability): CompanyRole
    {
        if (! isset(self::DEFINITIONS[$ability])) {
            throw new \InvalidArgumentException("Unknown company ability [{$ability}].");
        }

        return self::DEFINITIONS[$ability][0];
    }

    public static function roleAllows(?CompanyRole $role, string $ability): bool
    {
        return $role !== null && $role->atLeast(self::minimumRole($ability));
    }

    public static function allows(User $user, int $companyId, string $ability): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return self::roleAllows(self::roleOf($user, $companyId), $ability);
    }

    /**
     * Matrix for the Permissions page, derived from DEFINITIONS.
     *
     * @return list<array{category: string, permissions: list<array<string, mixed>>}>
     */
    public static function matrix(): array
    {
        $categories = [];
        foreach (self::DEFINITIONS as $ability => [$minimum, $category, $label]) {
            $row = ['key' => $ability, 'name' => $label, 'minimum_role' => $minimum->label()];
            foreach (CompanyRole::ordered() as $role) {
                $row[strtolower($role->name)] = $role->atLeast($minimum);
            }
            $categories[$category][] = $row;
        }

        return array_map(
            fn ($category, $permissions) => ['category' => $category, 'permissions' => $permissions],
            array_keys($categories),
            array_values($categories)
        );
    }

    /** @return list<string> */
    public static function all(): array
    {
        return array_keys(self::DEFINITIONS);
    }
}
