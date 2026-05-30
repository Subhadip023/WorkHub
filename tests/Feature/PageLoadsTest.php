<?php

use App\Models\User;
use App\Models\Company;
use App\Models\CompanyUsers;

it('loads public pages successfully', function () {
    $this->get('/')->assertStatus(200);
    $this->get('/login')->assertStatus(200);
    $this->get('/register')->assertStatus(200);
});

it('loads dashboard and main pages for authenticated user in personal space', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $this->actingAs($user);

    // Verify dashboard loads
    $this->get('/dashboard')->assertStatus(200);

    // Verify projects page loads
    $this->get('/projects')->assertStatus(200);

    // Verify tasks page loads
    $this->get('/tasks')->assertStatus(200);

    // Verify organizations page loads
    $this->get('/companies')->assertStatus(200);
});

it('loads dashboard and main pages for authenticated user in an organization', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $company = Company::create([
        'name' => 'Test Organization',
        'code' => 'T1ST',
    ]);

    CompanyUsers::create([
        'company_id' => $company->id,
        'user_id' => $user->id,
        'role' => 1, // Admin
    ]);

    $this->actingAs($user);

    // Verify dashboard loads
    $this->get('/dashboard')->assertStatus(200);

    // Verify projects page loads
    $this->get('/projects')->assertStatus(200);

    // Verify tasks page loads
    $this->get('/tasks')->assertStatus(200);

    // Verify organizations page loads
    $this->get('/companies')->assertStatus(200);

    // Verify notes page loads
    $this->get('/notes')->assertStatus(200);
});
