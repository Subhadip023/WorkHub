<?php

namespace App\Policies;

use App\Models\CompanyInvitation;
use App\Models\User;

class CompanyInvitationPolicy
{
    /**
     * Determine whether the user can accept or reject the invitation.
     */
    public function handle(User $user, CompanyInvitation $invitation): bool
    {
        return $user->email === $invitation->email;
    }
}
