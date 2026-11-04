<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;

test('classic user with ui_version v1 receives blade view on clean dashboard url', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'ui_version' => 'v1',
    ]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertStatus(200);
    $response->assertViewIs('welcome');
});

test('modern user with ui_version v2 receives inertia dashboard on clean dashboard url', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'ui_version' => 'v2',
    ]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('New/Dashboard')
        ->has('stats')
        ->has('projects')
    );
});

test('modern user receives inertia tasks on clean tasks url', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'ui_version' => 'v2',
    ]);

    $response = $this->actingAs($user)->get('/tasks');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('New/Tasks')
        ->has('initial_tasks')
        ->has('stats')
    );
});

test('modern user receives inertia projects on clean projects url', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'ui_version' => 'v2',
    ]);

    $response = $this->actingAs($user)->get('/projects');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('New/Projects')
        ->has('initial_projects')
        ->has('stats')
    );
});

test('ui switcher switches user to modern v2 and classic v1 seamlessly', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'ui_version' => 'v1',
    ]);

    // Switch to V2
    $switchResponse = $this->actingAs($user)->get(route('ui.switch', 'v2'));
    $switchResponse->assertRedirect(route('dashboard'));

    expect($user->fresh()->ui_version)->toBe('v2');
    expect(session('ui_version'))->toBe('v2');

    // Subsequent clean dashboard request renders V2 Inertia
    $v2Response = $this->actingAs($user)->get('/dashboard');
    $v2Response->assertStatus(200);
    $v2Response->assertInertia(fn ($page) => $page->component('New/Dashboard'));

    // Switch back to V1
    $switchV1Response = $this->actingAs($user)->get(route('ui.switch', 'v1'));
    $switchV1Response->assertRedirect(route('dashboard'));

    expect($user->fresh()->ui_version)->toBe('v1');
    expect(session('ui_version'))->toBe('v1');

    // Subsequent clean dashboard request renders Classic Blade
    $v1Response = $this->actingAs($user)->get('/dashboard');
    $v1Response->assertStatus(200);
    $v1Response->assertViewIs('welcome');
});

test('user can update ui preference via post settings endpoint', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'ui_version' => 'v1',
    ]);

    $response = $this->actingAs($user)->post('/settings/ui-preference', [
        'ui_version' => 'v2',
    ]);

    $response->assertRedirect();
    expect($user->fresh()->ui_version)->toBe('v2');
});

test('modern user receives inertia project view on clean /projects/{id} url', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'ui_version' => 'v2',
    ]);
    $project = Project::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->get("/projects/{$project->id}");

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('New/ProjectView')
        ->has('project')
        ->has('tasks')
        ->has('stats')
    );
});

test('classic user receives blade view on clean /projects/{id} url', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'ui_version' => 'v1',
    ]);
    $project = Project::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->get("/projects/{$project->id}");

    $response->assertStatus(200);
    $response->assertViewIs('projects.show');
});
