<?php

use App\Models\User;

it('renders landing page with dual-engine interactive mode controls and theme button', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertSee('WorkHub');
    $response->assertSee('landingThemeBtn', false);
    $response->assertSee('themeSunIcon', false);
    $response->assertSee('themeMoonIcon', false);
    $response->assertSee('hero-btn-v2', false);
    $response->assertSee('hero-btn-v1', false);
    $response->assertSee('Developer Mode (V2 React)');
    $response->assertSee('Classic Mode (V1 Blade)');
    $response->assertSee('hero-mockup-v2', false);
    $response->assertSee('hero-mockup-v1', false);
});

it('renders classic mode v1 topbar with theme button', function () {
    $user = User::factory()->create([
        'ui_version' => 'v1',
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertStatus(200);
    $response->assertSee('classicThemeToggleBtn', false);
    $response->assertSee('classicThemeIcon', false);
});

it('renders developer mode v2 dashboard with inertia component and user data', function () {
    $user = User::factory()->create([
        'ui_version' => 'v2',
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('New/Dashboard'));
});
