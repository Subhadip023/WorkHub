<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;

uses(RefreshDatabase::class);

function createSuperAdmin(): User
{
    $user = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
    Feature::for($user)->activate('manage-features');
    Feature::for($user)->activate('access-beta');

    return $user;
}

test('non-super admin cannot access feature management index or toggle endpoints', function () {
    $regularUser = User::factory()->create(['role' => User::ROLE_USER]);

    $this->actingAs($regularUser)
        ->get(route('admin.features.index'))
        ->assertStatus(403);
});

test('super admin can view feature management dashboard', function () {
    $superAdmin = createSuperAdmin();

    $this->actingAs($superAdmin)
        ->get(route('admin.features.index'))
        ->assertStatus(200)
        ->assertSee('Feature Access');
});

test('super admin can toggle feature access for any user', function () {
    $superAdmin = createSuperAdmin();
    $targetUser = User::factory()->create(['role' => User::ROLE_USER]);

    expect(Feature::for($targetUser)->active('access-beta'))->toBeFalse();

    // Activate feature for target user
    $this->actingAs($superAdmin)
        ->postJson(route('admin.features.toggle-feature', $targetUser), [
            'feature' => 'access-beta',
        ])
        ->assertOk()
        ->assertJson([
            'success' => true,
            'active' => true,
        ]);

    expect(Feature::for($targetUser)->active('access-beta'))->toBeTrue();

    // Deactivate feature for target user
    $this->actingAs($superAdmin)
        ->postJson(route('admin.features.toggle-feature', $targetUser), [
            'feature' => 'access-beta',
        ])
        ->assertOk()
        ->assertJson([
            'success' => true,
            'active' => false,
        ]);

    expect(Feature::for($targetUser)->active('access-beta'))->toBeFalse();
});

test('super admin can change user role between admin and user', function () {
    $superAdmin = createSuperAdmin();
    $targetUser = User::factory()->create(['role' => User::ROLE_USER]);

    $this->actingAs($superAdmin)
        ->postJson(route('admin.features.toggle-role', $targetUser), [
            'role' => User::ROLE_ADMIN,
        ])
        ->assertOk()
        ->assertJson([
            'success' => true,
            'role' => User::ROLE_ADMIN,
        ]);

    expect($targetUser->fresh()->role)->toBe(User::ROLE_ADMIN);
});

test('web UI rejects assigning super admin role', function () {
    $superAdmin = createSuperAdmin();
    $targetUser = User::factory()->create(['role' => User::ROLE_USER]);

    $this->actingAs($superAdmin)
        ->postJson(route('admin.features.toggle-role', $targetUser), [
            'role' => User::ROLE_SUPER_ADMIN,
        ])
        ->assertStatus(422)
        ->assertJson([
            'success' => false,
        ]);

    expect($targetUser->fresh()->role)->toBe(User::ROLE_USER);
});

test('console command promotes user to super admin and demotes previous super admin', function () {
    $prevSuperAdmin = createSuperAdmin();
    $newSuperAdmin = User::factory()->create(['role' => User::ROLE_USER]);

    $this->artisan("user:super-admin {$newSuperAdmin->id}")
        ->assertExitCode(0);

    expect($newSuperAdmin->fresh()->isSuperAdmin())->toBeTrue();
    expect($prevSuperAdmin->fresh()->isSuperAdmin())->toBeFalse();
    expect(Feature::for($newSuperAdmin->fresh())->active('manage-features'))->toBeTrue();
    expect(Feature::for($prevSuperAdmin->fresh())->active('manage-features'))->toBeFalse();
    expect(User::where('role', User::ROLE_SUPER_ADMIN)->count())->toBe(1);
});
