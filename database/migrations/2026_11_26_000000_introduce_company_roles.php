<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Introduces Owner / Admin / Manager / Member company roles and removes the
 * global "Admin" Spatie role.
 *
 * company_users.role values: 0 = Member, 1 = Admin, 2 = Manager, 3 = Owner.
 * Before this migration, anything other than 1 meant "member" (seeders wrote 2),
 * so those rows are normalized to 0 first.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Normalize legacy member values (e.g. seeded 2) to Member = 0.
        //    Only on the first run: once owners exist, 2 and 3 are real roles.
        if (! DB::table('company_users')->where('role', 3)->exists()) {
            DB::table('company_users')->whereNotIn('role', [0, 1])->update(['role' => 0]);
        }

        // 2. Exactly one Owner per company: the earliest admin membership
        //    (the creator is inserted first), else the earliest approved member.
        $companies = DB::table('companies')->select('id', 'deleted_at')->get();

        foreach ($companies as $company) {
            // Idempotent: never create a second owner.
            if (DB::table('company_users')->where('company_id', $company->id)->where('role', 3)->exists()) {
                continue;
            }

            $base = DB::table('company_users')->where('company_id', $company->id)->where('is_approved', true);

            if ($company->deleted_at === null) {
                $base->whereNull('deleted_at');
            }

            $ownerId = (clone $base)->where('role', 1)->orderBy('id')->value('id')
                ?? (clone $base)->orderBy('id')->value('id');

            if ($ownerId) {
                DB::table('company_users')->where('id', $ownerId)->update(['role' => 3]);
            }
        }

        // 3. Remove the global "Admin" role: those users become regular Users.
        //    Workspace power now comes only from company roles.
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $adminRole = Role::where('name', 'Admin')->first();
        if ($adminRole) {
            $userRole = Role::findOrCreate(User::ROLE_USER);
            User::role('Admin')->each(function (User $user) use ($userRole) {
                $user->removeRole('Admin');
                if ($user->roles()->count() === 0) {
                    $user->assignRole($userRole);
                }
            });
            $adminRole->delete();
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        DB::table('company_users')->where('role', 3)->update(['role' => 1]);
        DB::table('company_users')->where('role', 2)->update(['role' => 0]);

        Role::findOrCreate('Admin');
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
