<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class MakeSuperAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:super-admin 
                            {user? : User ID or Email} 
                            {--revoke : Revoke Super Admin role and demote to regular user}
                            {--role= : Specific role to assign (super_admin, admin, user)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Grant, manage, or revoke Super Admin privileges and feature permissions for a user.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $userInput = $this->argument('user');

        if (! $userInput) {
            $allUsers = User::orderBy('id', 'asc')->get();
            if ($allUsers->isEmpty()) {
                $this->error('No users found in the database.');

                return Command::FAILURE;
            }

            $choices = $allUsers->mapWithKeys(function ($u) {
                $roleName = $u->getRoleNames()->first() ?? User::ROLE_USER;

                return [$u->id => "ID: {$u->id} | {$u->name} ({$u->email}) [{$roleName}]"];
            })->toArray();

            $selectedId = $this->choice('Select a user to update:', $choices);
            preg_match('/ID: (\d+)/', $selectedId, $matches);
            $userInput = $matches[1] ?? key(array_filter($choices, fn ($v) => $v === $selectedId));
        }

        $user = User::where('id', $userInput)
            ->orWhere('email', $userInput)
            ->first();

        if (! $user) {
            $this->error("User not found for identifier '{$userInput}'.");

            return Command::FAILURE;
        }

        Role::findOrCreate(User::ROLE_SUPER_ADMIN);
        Role::findOrCreate(User::ROLE_ADMIN);
        Role::findOrCreate(User::ROLE_USER);

        if ($this->option('revoke')) {
            $user->syncRoles([User::ROLE_USER]);
            if ($user->hasDirectPermission('manage-features')) {
                $user->revokePermissionTo('manage-features');
            }

            $this->info("Revoked: User {$user->name} ({$user->email}) has been demoted to regular User.");

            return Command::SUCCESS;
        }

        $roleOpt = strtolower((string) $this->option('role'));
        if ($roleOpt === 'admin') {
            $roleName = User::ROLE_ADMIN;
        } elseif ($roleOpt === 'user') {
            $roleName = User::ROLE_USER;
        } else {
            $roleName = User::ROLE_SUPER_ADMIN;
        }

        $user->syncRoles([$roleName]);

        if ($user->isSuperAdmin()) {
            $previousSuperAdmins = User::role(User::ROLE_SUPER_ADMIN)
                ->where('id', '!=', $user->id)
                ->get();

            foreach ($previousSuperAdmins as $prevAdmin) {
                $prevAdmin->syncRoles([User::ROLE_USER]);
                if ($prevAdmin->hasDirectPermission('manage-features')) {
                    $prevAdmin->revokePermissionTo('manage-features');
                }
                $this->warn("Demoted previous Super Admin: {$prevAdmin->name} ({$prevAdmin->email}) to regular User.");
            }

            Permission::findOrCreate('manage-features');
            $user->givePermissionTo('manage-features');
        }

        $this->table(
            ['ID', 'Name', 'Email', 'Role'],
            [[
                $user->id,
                $user->name,
                $user->email,
                $roleName,
            ]]
        );

        $this->info("Success! User {$user->name} ({$user->email}) role updated to [{$roleName}].");

        return Command::SUCCESS;
    }
}
