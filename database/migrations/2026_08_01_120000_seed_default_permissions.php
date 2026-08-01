<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::findOrCreate('manage-features');
        Permission::findOrCreate('access-beta');
        Permission::findOrCreate('access-issues');
    }

    public function down(): void
    {
        Permission::whereIn('name', ['manage-features', 'access-beta', 'access-issues'])->delete();
    }
};
