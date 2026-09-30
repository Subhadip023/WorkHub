<?php

use App\Models\User;

it('redirects unauthenticated guest users accessing routes explorer to login', function () {
    $response = $this->get(route('routes.index'));
    $response->assertRedirect(route('login'));
});

it('loads routes explorer page successfully for authenticated user', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $this->actingAs($user);

    $response = $this->get(route('routes.index'));
    $response->assertStatus(200);

    $response->assertInertia(fn ($page) => $page
        ->component('New/Routes')
        ->has('routes')
        ->has('stats')
        ->has('categories')
        ->where('stats.total', fn ($total) => $total > 100)
        ->where('stats.get_count', fn ($count) => $count > 50)
        ->where('stats.inertia_count', fn ($count) => $count > 0)
    );
});

it('redirects all-routes and new-routes aliases to routes index', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $this->actingAs($user);

    $this->get('/all-routes')->assertRedirect(route('routes.index'));
    $this->get('/new/routes')->assertRedirect(route('routes.index'));
});

it('includes core application endpoints in routes registry', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $this->actingAs($user);

    $response = $this->get(route('routes.index'));
    $routes = $response->viewData('page')['props']['routes'];

    $uris = collect($routes)->pluck('uri')->all();
    $names = collect($routes)->pluck('name')->filter()->all();

    expect($uris)->toContain('/dashboard')
        ->toContain('/projects')
        ->toContain('/tasks')
        ->toContain('/analytics')
        ->toContain('/routes');

    expect($names)->toContain('dashboard')
        ->toContain('projects.index')
        ->toContain('tasks.index')
        ->toContain('routes.index');
});
