<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $superAdminRole = Role::findOrCreate(User::ROLE_SUPER_ADMIN);
        $adminRole = Role::findOrCreate(User::ROLE_ADMIN);
        $userRole = Role::findOrCreate(User::ROLE_USER);

        if (Schema::hasColumn('users', 'role')) {
            $users = DB::table('users')->select('id', 'role')->get();

            foreach ($users as $user) {
                $u = User::find($user->id);
                if (! $u) {
                    continue;
                }

                if ((int) $user->role === 0) {
                    $u->assignRole($superAdminRole);
                } elseif ((int) $user->role === 1) {
                    $u->assignRole($adminRole);
                } else {
                    $u->assignRole($userRole);
                }
            }

            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('role');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->integer('role')->default(2)->after('password');
            });

            $users = User::all();
            foreach ($users as $user) {
                if ($user->hasRole(User::ROLE_SUPER_ADMIN)) {
                    DB::table('users')->where('id', $user->id)->update(['role' => 0]);
                } elseif ($user->hasRole(User::ROLE_ADMIN)) {
                    DB::table('users')->where('id', $user->id)->update(['role' => 1]);
                } else {
                    DB::table('users')->where('id', $user->id)->update(['role' => 2]);
                }
            }
        }
    }
};
