<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

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

it('successfully submits an issue to Task API', function () {
    config([
        'services.task_api.url' => 'https://workhub.subhadip.online/api/tasks',
    ]);

    Http::fake([
        'https://workhub.subhadip.online/api/tasks' => Http::response([
            'success' => true,
            'data' => [
                'id' => 101,
                'title' => 'Test Issue Title',
            ],
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
            'message' => 'Issue successfully created on Task API.',
        ]);

    Http::assertSent(function ($request) {
        return $request->url() === 'https://workhub.subhadip.online/api/tasks' &&
            $request->method() === 'POST' &&
            $request['title'] === 'Test Issue Title' &&
            str_contains($request['description'], 'Detailed test description') &&
            $request['type'] === 2 &&
            $request['priority'] === 3;
    });
});

it('requires authentication to view issues', function () {
    $response = $this->get(route('issues.index'));

    $response->assertRedirect(route('login'));
});

it('successfully fetches and displays issues from Task API', function () {
    config([
        'services.task_api.url' => 'https://workhub.subhadip.online/api/tasks',
    ]);

    Http::fake([
        'https://workhub.subhadip.online/api/tasks?per_page=100&only_external=1' => Http::response([
            'success' => true,
            'data' => [
                [
                    'id' => 101,
                    'title' => 'First Task Issue',
                    'status' => 1,
                    'type' => 2,
                    'priority' => 3,
                    'description' => 'This is a test task issue description',
                    'created_at' => '2026-07-26T12:00:00Z',
                    'user' => ['name' => 'testuser'],
                    'external_source' => ['id' => 1],
                ],
            ],
        ], 200),
    ]);

    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('issues.index'));

    $response->assertStatus(200)
        ->assertSee('First Task Issue');
});

it('successfully stores attachment locally and links it in the Task API submission', function () {
    Storage::fake('public');

    config([
        'services.task_api.url' => 'https://workhub.subhadip.online/api/tasks',
    ]);

    $file = UploadedFile::fake()->image('test_screenshot.png');

    Http::fake([
        'https://workhub.subhadip.online/api/tasks' => Http::response([
            'success' => true,
            'data' => [
                'id' => 102,
                'title' => 'Test Issue with Attachment',
            ],
        ], 201),
    ]);

    $user = User::factory()->create();
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
        if ($request->url() === 'https://workhub.subhadip.online/api/tasks') {
            return $request->method() === 'POST' &&
                str_contains($request['description'], '<img src="'.asset('storage/issues/'.$file->hashName()).'"');
        }

        return true;
    });
});

it('extracts embedded base64 clipboard images from description into public storage', function () {
    Storage::fake('public');

    config([
        'services.task_api.url' => 'https://workhub.subhadip.online/api/tasks',
    ]);

    Http::fake([
        'https://workhub.subhadip.online/api/tasks' => Http::response([
            'success' => true,
            'data' => ['id' => 103],
        ], 201),
    ]);

    $user = User::factory()->create();
    $this->actingAs($user);

    // 1x1 transparent PNG base64
    $base64Image = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';
    $descriptionWithImage = '<p>Pasted screenshot:</p><p><img src="'.$base64Image.'"></p>';

    $response = $this->postJson(route('issues.store'), [
        'title' => 'Issue with Pasted Clipboard Screenshot',
        'priority' => 'high',
        'category' => 'bug',
        'description' => $descriptionWithImage,
    ]);

    $response->assertStatus(200);

    Http::assertSent(function ($request) {
        if ($request->url() === 'https://workhub.subhadip.online/api/tasks') {
            // Verify base64 is no longer in description, but a storage URL is present
            return ! str_contains($request['description'], 'data:image/png;base64') &&
                   str_contains($request['description'], '/storage/task_images/');
        }

        return true;
    });
});

it('accepts multi-format image attachments like image_url and images_base64 when submitting issue', function () {
    Storage::fake('public');

    config([
        'services.task_api.url' => 'https://workhub.subhadip.online/api/tasks',
    ]);

    Http::fake([
        'https://workhub.subhadip.online/api/tasks' => Http::response([
            'success' => true,
            'data' => ['id' => 104],
        ], 201),
    ]);

    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->postJson(route('issues.store'), [
        'title' => 'Issue with Multi Format Images',
        'priority' => 'critical',
        'category' => 'bug',
        'description' => 'Testing multi-format image support',
        'image_url' => 'https://example.com/screenshot.png',
    ]);

    $response->assertStatus(200);

    Http::assertSent(function ($request) {
        return $request->url() === 'https://workhub.subhadip.online/api/tasks' &&
               $request['image_url'] === 'https://example.com/screenshot.png';
    });
});
