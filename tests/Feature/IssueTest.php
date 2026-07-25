<?php

use App\Models\User;
use Illuminate\Support\Facades\Http;

it('requires authentication to submit an issue', function () {
    $response = $this->postJson(route('issues.store'), [
        'title' => 'Test Issue',
        'priority' => 'high',
        'category' => 'bug',
        'description' => 'Test description',
    ]);

    $response->assertStatus(401);
});

it('validates the required fields for issue submission', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->postJson(route('issues.store'), []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['title', 'priority', 'category', 'description']);
});

it('returns 500 if GITHUB_PAT is not configured', function () {
    config(['services.github.pat' => null]);
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->postJson(route('issues.store'), [
        'title' => 'Test Issue',
        'priority' => 'high',
        'category' => 'bug',
        'description' => 'Test description',
    ]);

    $response->assertStatus(500)
        ->assertJson([
            'success' => false,
            'message' => 'GitHub Personal Access Token (PAT) is not configured in the server environment (.env).',
        ]);
});

it('successfully submits an issue to GitHub when GITHUB_PAT is configured', function () {
    config([
        'services.github.pat' => 'mock-pat',
        'services.github.owner' => 'mock-owner',
        'services.github.repo' => 'mock-repo',
    ]);

    Http::fake([
        'https://api.github.com/repos/mock-owner/mock-repo/issues' => Http::response([
            'html_url' => 'https://github.com/mock-owner/mock-repo/issues/1',
        ], 201),
    ]);

    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->postJson(route('issues.store'), [
        'title' => 'Test Issue Title',
        'priority' => 'high',
        'category' => 'bug',
        'description' => 'Detailed test description',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Issue successfully created on GitHub.',
            'url' => 'https://github.com/mock-owner/mock-repo/issues/1',
        ]);

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.github.com/repos/mock-owner/mock-repo/issues' &&
            $request->method() === 'POST' &&
            $request->header('Authorization')[0] === 'Bearer mock-pat' &&
            $request['title'] === 'Test Issue Title' &&
            str_contains($request['body'], 'Detailed test description') &&
            in_array('bug', $request['labels']) &&
            in_array('high', $request['labels']);
    });
});
