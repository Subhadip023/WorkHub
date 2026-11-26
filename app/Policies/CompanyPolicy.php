<?php

namespace App\Policies;

use App\Authorization\CompanyAbility;
use App\Enums\CompanyRole;
use App\Models\Company;
use App\Models\CompanyUsers;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Organization-level rules. Minimum roles live in App\Authorization\CompanyAbility.
 */
class CompanyPolicy
{
    /**
     * Determine whether the user can view the company.
     */
    public function view(User $user, Company $company): bool
    {
        return $user->companies->contains('company_id', $company->id);
    }

    /**
     * Rename / update settings, invite, approve or reject join requests.
     */
    public function update(User $user, Company $company): bool
    {
        return CompanyAbility::allows($user, $company->id, CompanyAbility::COMPANY_UPDATE);
    }

    /**
     * Delete the organization (Owner only).
     */
    public function delete(User $user, Company $company): bool
    {
        return CompanyAbility::allows($user, $company->id, CompanyAbility::COMPANY_DELETE);
    }

    /**
     * Transfer ownership (Owner only).
     */
    public function transferOwnership(User $user, Company $company): bool
    {
        return CompanyAbility::allows($user, $company->id, CompanyAbility::COMPANY_TRANSFER);
    }

    /**
     * Remove a member or change their role: the actor must be Admin+ and must
     * strictly outrank the target. The Owner can never be removed or demoted here.
     */
    public function manageMember(User $user, Company $company, CompanyUsers $target): Response
    {
        if ((int) $target->user_id === (int) $user->id) {
            return Response::deny('You cannot change your own membership. Use Leave or Transfer ownership.');
        }

        if ($target->roleEnum() === CompanyRole::Owner) {
            return Response::deny('The owner cannot be removed or demoted. The owner must transfer ownership first.');
        }

        if ($user->isSuperAdmin()) {
            return Response::allow();
        }

        $actorRole = CompanyAbility::roleOf($user, $company->id);

        if (! CompanyAbility::roleAllows($actorRole, CompanyAbility::MEMBERS_CHANGE_ROLE)) {
            return Response::deny('Only administrators can manage members.');
        }

        if (! $actorRole->outranks($target->roleEnum())) {
            return Response::deny('You can only manage members with a lower role than yours.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can leave the company.
     */
    public function leave(User $user, Company $company): Response
    {
        $membership = $user->companies()->where('company_id', $company->id)->first();

        if (! $membership || ! $membership->is_approved) {
            return Response::deny('You are not a member of this organization.');
        }

        if ($membership->roleEnum() === CompanyRole::Owner) {
            return Response::deny('The owner cannot leave. Transfer ownership to another member first.');
        }

        return Response::allow();
    }

    /**
     * Restore a trashed company (Owner at the time of deletion).
     */
    public function restore(User $user, Company $company): bool
    {
        $membership = CompanyUsers::effectiveMembership($user->id, $company->id);

        return CompanyAbility::roleAllows($membership?->roleEnum(), CompanyAbility::COMPANY_DELETE);
    }

    /**
     * Determine whether the user can permanently delete the company.
     */
    public function forceDelete(User $user, Company $company): bool
    {
        return $this->restore($user, $company);
    }
}
