<?php

namespace App\Policies;

use App\Authorization\CompanyAbility;
use App\Models\CompanyUsers;
use App\Models\User;

class CompanyUsersPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CompanyUsers $companyUsers): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CompanyUsers $companyUsers): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CompanyUsers $companyUsers): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CompanyUsers $companyUsers): bool
    {
        // A removed user must never be able to restore their own membership.
        if ((int) $companyUsers->user_id === (int) $user->id) {
            return false;
        }

        $membership = CompanyUsers::effectiveMembership($user->id, $companyUsers->company_id);

        return $membership && ! $membership->trashed()
            && CompanyAbility::roleAllows($membership->roleEnum(), CompanyAbility::MEMBERS_REMOVE)
            && $membership->roleEnum()->outranks($companyUsers->roleEnum());
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, CompanyUsers $companyUsers): bool
    {
        return $this->restore($user, $companyUsers);
    }
}
