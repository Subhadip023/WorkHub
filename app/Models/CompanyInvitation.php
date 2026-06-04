<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyInvitation extends Model
{
    use HasFactory;

    protected $fillable = ['company_id', 'email'];

    /**
     * Get the company associated with the invitation.
     *
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Process any pending invitations for the given user.
     *
     * @param  User  $user
     */
    public static function processPendingInvitations($user): void
    {
        if (! $user) {
            return;
        }

        $invitations = self::where('email', $user->email)->get();

        if ($invitations->isEmpty()) {
            return;
        }

        foreach ($invitations as $invitation) {
            // Join the company
            CompanyUsers::firstOrCreate([
                'company_id' => $invitation->company_id,
                'user_id' => $user->id,
            ], [
                'role' => 0, // Member
            ]);

            // Set the active company session
            session(['current_company_id' => $invitation->company_id]);

            // Delete the invitation
            $invitation->delete();
        }
    }
}
