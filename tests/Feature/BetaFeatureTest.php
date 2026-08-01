<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

test('spatie permission can explicitly activate and deactivate beta access for a user in database', function () {
    $user = User::factory()->create();
    Permission::findOrCreate('access-beta');

    expect($user->hasPermissionTo('access-beta'))->toBeFalse();

    // Activate permission using Spatie
    $user->givePermissionTo('access-beta');

    expect($user->hasPermissionTo('access-beta'))->toBeTrue();

    // Deactivate permission using Spatie
    $user->revokePermissionTo('access-beta');

    expect($user->hasPermissionTo('access-beta'))->toBeFalse();
});
