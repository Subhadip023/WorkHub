<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\User;
use Illuminate\Auth\Access\Response;

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
     * Determine whether the user can update the company.
     */
    public function update(User $user, Company $company): bool
    {
        $membership = $user->companies()->where('company_id', $company->id)->first();

        return $membership && $membership->role === 1;
    }

    /**
     * Determine whether the user can delete the company.
     */
    public function delete(User $user, Company $company): bool
    {
        return $this->update($user, $company);
    }

    /**
     * Determine whether the user can leave the company.
     */
    public function leave(User $user, Company $company)
    {
        $membership = $user->companies()->where('company_id', $company->id)->first();

        if (! $membership || ! $membership->is_approved) {
            return Response::deny('You are not a member of this organization.');
        }

        if ($membership->role === 1) {
            return Response::deny('Administrators cannot leave the organization.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can restore the company.
     */
    public function restore(User $user, Company $company): bool
    {
        $membership = $user->companies()->withTrashed()->where('company_id', $company->id)->first();

        return $membership && $membership->role === 1;
    }

    /**
     * Determine whether the user can permanently delete the company.
     */
    public function forceDelete(User $user, Company $company): bool
    {
        return $this->restore($user, $company);
    }
}
