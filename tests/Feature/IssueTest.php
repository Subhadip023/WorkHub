<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Laravel\Pennant\Feature;

beforeEach(function () {
    //
});

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
    Feature::for($user)->activate('access-issues');
    $this->actingAs($user);

    $response = $this->postJson(route('issues.store'), []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['title', 'priority', 'category', 'description']);
});

it('returns 500 if GITHUB_PAT is not configured', function () {
    config(['services.github.pat' => null]);
    $user = User::factory()->create();
    Feature::for($user)->activate('access-issues');
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
    Feature::for($user)->activate('access-issues');
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

it('requires authentication to view issues', function () {
    $response = $this->get(route('issues.index'));

    $response->assertRedirect(route('login'));
});

it('shows a warning if GITHUB_PAT is not configured on the index page', function () {
    config(['services.github.pat' => null]);
    $user = User::factory()->create();
    Feature::for($user)->activate('access-issues');
    $this->actingAs($user);

    $response = $this->get(route('issues.index'));

    $response->assertStatus(200)
        ->assertSee('GitHub Personal Access Token (PAT) is not configured');
});

it('successfully fetches and displays issues from GitHub', function () {
    config([
        'services.github.pat' => 'mock-pat',
        'services.github.owner' => 'mock-owner',
        'services.github.repo' => 'mock-repo',
    ]);

    Http::fake([
        'https://api.github.com/repos/mock-owner/mock-repo/issues?state=all&per_page=100' => Http::response([
            [
                'number' => 1,
                'title' => 'First Test Issue',
                'state' => 'open',
                'body' => 'This is a test issue description',
                'html_url' => 'https://github.com/mock-owner/mock-repo/issues/1',
                'created_at' => '2026-07-26T12:00:00Z',
                'labels' => [
                    ['name' => 'bug'],
                    ['name' => 'high'],
                ],
                'user' => [
                    'login' => 'testuser',
                ],
            ],
            [
                'number' => 2,
                'title' => 'Second Test Pull Request',
                'state' => 'open',
                'body' => 'This is a pull request description',
                'html_url' => 'https://github.com/mock-owner/mock-repo/pull/2',
                'created_at' => '2026-07-26T12:30:00Z',
                'labels' => [],
                'user' => [
                    'login' => 'testuser2',
                ],
                'pull_request' => [], // should be filtered out
            ],
        ], 200),
    ]);

    $user = User::factory()->create();
    Feature::for($user)->activate('access-issues');
    $this->actingAs($user);

    $response = $this->get(route('issues.index'));

    $response->assertStatus(200)
        ->assertSee('First Test Issue')
        ->assertDontSee('Second Test Pull Request');
});

it('successfully stores attachment locally and links it in the GitHub issue', function () {
    Storage::fake('public');

    config([
        'services.github.pat' => 'mock-pat',
        'services.github.owner' => 'mock-owner',
        'services.github.repo' => 'mock-repo',
    ]);

    $file = UploadedFile::fake()->image('test_screenshot.png');

    Http::fake([
        'https://api.github.com/repos/mock-owner/mock-repo/issues' => Http::response([
            'html_url' => 'https://github.com/mock-owner/mock-repo/issues/1',
        ], 201),
    ]);

    $user = User::factory()->create();
    Feature::for($user)->activate('access-issues');
    $this->actingAs($user);

    $response = $this->postJson(route('issues.store'), [
        'title' => 'Test Issue with Attachment',
        'priority' => 'high',
        'category' => 'bug',
        'description' => 'Test description with image',
        'attachment' => $file,
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
        ]);

    // Check that the file was stored on the public disk
    Storage::disk('public')->assertExists('issues/'.$file->hashName());

    Http::assertSent(function ($request) use ($file) {
        if ($request->url() === 'https://api.github.com/repos/mock-owner/mock-repo/issues') {
            return $request->method() === 'POST' &&
                str_contains($request['body'], '![test_screenshot.png]('.asset('storage/issues/'.$file->hashName()).')');
        }

        return true;
    });
});
