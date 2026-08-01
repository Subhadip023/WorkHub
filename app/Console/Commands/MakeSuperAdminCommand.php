<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;

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
                $roleLabel = match ((int) $u->role) {
                    User::ROLE_SUPER_ADMIN => '[Super Admin]',
                    User::ROLE_ADMIN => '[Admin]',
                    default => '[User]',
                };

                return [$u->id => "ID: {$u->id} | {$u->name} ({$u->email}) {$roleLabel}"];
            })->toArray();

            $selectedId = $this->choice('Select a user to update:', $choices);
            // Extract numeric ID from standard format or key
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

        if ($this->option('revoke')) {
            $user->role = User::ROLE_USER;
            $user->save();
            if ($user->hasPermissionTo('manage-features')) {
                $user->revokePermissionTo('manage-features');
            }
            if ($user->hasPermissionTo('access-beta')) {
                $user->revokePermissionTo('access-beta');
            }

            $this->info("Revoked: User {$user->name} ({$user->email}) has been demoted to regular User.");

            return Command::SUCCESS;
        }

        $roleOpt = strtolower((string) $this->option('role'));
        if ($roleOpt === 'admin') {
            $user->role = User::ROLE_ADMIN;
            $roleName = 'Admin';
        } elseif ($roleOpt === 'user') {
            $user->role = User::ROLE_USER;
            $roleName = 'User';
        } else {
            $user->role = User::ROLE_SUPER_ADMIN;
            $roleName = 'Super Admin';
        }

        $user->save();

        if ($user->isSuperAdmin()) {
            $previousSuperAdmins = User::where('role', User::ROLE_SUPER_ADMIN)
                ->where('id', '!=', $user->id)
                ->get();

            foreach ($previousSuperAdmins as $prevAdmin) {
                $prevAdmin->role = User::ROLE_USER;
                $prevAdmin->save();
                if ($prevAdmin->hasPermissionTo('manage-features')) {
                    $prevAdmin->revokePermissionTo('manage-features');
                }
                $this->warn("Demoted previous Super Admin: {$prevAdmin->name} ({$prevAdmin->email}) to regular User.");
            }

            Permission::findOrCreate('manage-features');
            Permission::findOrCreate('access-beta');
            Permission::findOrCreate('access-issues');

            $user->givePermissionTo(['manage-features', 'access-beta', 'access-issues']);
        }

        $this->table(
            ['ID', 'Name', 'Email', 'Role', 'access-beta Feature'],
            [[
                $user->id,
                $user->name,
                $user->email,
                $roleName,
                $user->hasPermissionTo('access-beta') ? 'Active (True)' : 'Inactive (False)',
            ]]
        );

        $this->info("Success! User {$user->name} ({$user->email}) role updated to [{$roleName}].");

        return Command::SUCCESS;
    }
}
