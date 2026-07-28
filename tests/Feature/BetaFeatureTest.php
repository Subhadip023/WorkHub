<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;

uses(RefreshDatabase::class);

test('pennant can explicitly activate and deactivate beta access for a user in database', function () {
    $user = User::factory()->create();

    expect(Feature::for($user)->active('access-beta'))->toBeFalse();

    // Activate feature using Pennant (persisted in DB)
    Feature::for($user)->activate('access-beta');

    expect(Feature::for($user)->active('access-beta'))->toBeTrue();

    // Deactivate feature using Pennant (persisted in DB)
    Feature::for($user)->deactivate('access-beta');

    expect(Feature::for($user)->active('access-beta'))->toBeFalse();
});
