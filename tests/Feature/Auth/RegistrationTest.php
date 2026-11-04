<?php

use App\Models\User;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
    expect(User::where('email', 'test@example.com')->first()->ui_version)->toBe('v2');
});

test('new user can select classic v1 mode during registration', function () {
    $response = $this->post('/register', [
        'name' => 'Classic User',
        'email' => 'classic@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'ui_version' => 'v1',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
    expect(User::where('email', 'classic@example.com')->first()->ui_version)->toBe('v1');
});
