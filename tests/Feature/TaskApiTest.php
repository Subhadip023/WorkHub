<?php

use App\Models\ExternalTaskApi;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;

it('allows authenticated user to create a task in a project via API', function () {
    $user = User::factory()->create();
    $project = Project::create([
        'name' => 'API Project',
        'slug' => 'api-project',
        'theme' => '#0055ff',
        'status' => 1,
        'priority' => 1,
        'user_id' => $user->id,
        'company_id' => null,
    ]);

    $this->actingAs($user);

    $response = $this->postJson(route('api.projects.tasks.store', $project), [
        'title' => 'API Created Task',
        'description' => 'Created via API route',
        'status' => 1,
        'priority' => 2,
        'type' => 1,
        'due_date' => '2026-12-31',
    ]);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'Task created successfully',
            'data' => [
                'title' => 'API Created Task',
                'description' => 'Created via API route',
                'project_id' => $project->id,
                'user_id' => $user->id,
                'status' => 1,
                'priority' => 2,
                'type' => 1,
            ],
        ]);

    $this->assertDatabaseHas('tasks', [
        'title' => 'API Created Task',
        'project_id' => $project->id,
        'user_id' => $user->id,
    ]);
});

it('prevents unauthorized user from adding task to project via API', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();

    $project = Project::create([
        'name' => 'Private Project',
        'slug' => 'private-project',
        'theme' => '#0055ff',
        'status' => 1,
        'priority' => 1,
        'user_id' => $owner->id,
        'company_id' => null,
    ]);

    $this->actingAs($otherUser);

    $response = $this->postJson(route('api.projects.tasks.store', $project), [
        'title' => 'Unauthorized Task',
    ]);

    $response->assertStatus(403);
});

it('allows authenticated user to create general task via API route', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->postJson(route('api.tasks.store'), [
        'title' => 'General API Task',
        'description' => 'No project task',
        'status' => 1,
        'priority' => 1,
    ]);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'Task created successfully',
            'data' => [
                'title' => 'General API Task',
                'project_id' => null,
                'user_id' => $user->id,
            ],
        ]);

    $this->assertDatabaseHas('tasks', [
        'title' => 'General API Task',
        'project_id' => null,
    ]);
});

it('validates task creation payload via API', function () {
    $user = User::factory()->create();
    $project = Project::create([
        'name' => 'Validation Project',
        'slug' => 'val-project',
        'theme' => '#0055ff',
        'status' => 1,
        'priority' => 1,
        'user_id' => $user->id,
        'company_id' => null,
    ]);

    $this->actingAs($user);

    $response = $this->postJson(route('api.projects.tasks.store', $project), [
        // Missing title
        'status' => 999, // Invalid status
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['title', 'status']);
});

it('allows generating external task API credentials for a project with member assignment and encrypted secret', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();

    $project = Project::create([
        'name' => 'Integration Project',
        'slug' => 'integration-project',
        'theme' => '#ff5500',
        'status' => 1,
        'priority' => 1,
        'user_id' => $owner->id,
        'company_id' => null,
    ]);

    $this->actingAs($owner);

    // View external API management tab
    $viewResponse = $this->get(route('projects.external-api', $project));
    $viewResponse->assertStatus(200)
        ->assertSee('External Task API Credentials');

    // Generate API Key with assigned member
    $response = $this->postJson(route('projects.external-api.store', $project), [
        'name' => 'GitHub Integration Webhook',
        'assigned_user_id' => $member->id,
    ]);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'External Task API generated successfully',
        ]);

    $externalApi = ExternalTaskApi::where('project_id', $project->id)->first();
    expect($externalApi)->not->toBeNull();
    expect($externalApi->name)->toBe('GitHub Integration Webhook');
    expect($externalApi->assigned_user_id)->toBe($member->id);
    expect($externalApi->api_key)->toStartWith('wh_pk_');

    // Verify secret is encrypted in database raw storage
    $rawRecord = DB::table('external_task_apis')->where('id', $externalApi->id)->first();
    expect($rawRecord->api_secret)->not->toBeNull();
    expect($rawRecord->api_secret)->not->toBe($externalApi->api_secret); // Cast decrypts it upon access
});

it('auto assigns task created via external API key to the configured member', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();

    $project = Project::create([
        'name' => 'Auto Assign Project',
        'slug' => 'auto-assign-project',
        'theme' => '#00aa55',
        'status' => 1,
        'priority' => 1,
        'user_id' => $owner->id,
        'company_id' => null,
    ]);

    $credentials = ExternalTaskApi::generateCredentials();
    $externalApi = ExternalTaskApi::create([
        'project_id' => $project->id,
        'user_id' => $owner->id,
        'assigned_user_id' => $member->id,
        'name' => 'Zapier Webhook',
        'api_key' => $credentials['api_key'],
        'api_secret' => $credentials['api_secret'],
        'is_active' => true,
    ]);

    // Send API request with X-Api-Key and X-Api-Signature headers
    $payloadData = [
        'title' => 'Incoming Webhook Task',
        'description' => 'Triggered by external webhook',
        'priority' => 3,
    ];
    $sig = hash_hmac('sha256', json_encode($payloadData), $credentials['api_secret']);

    $response = $this->withHeaders([
        'X-Api-Key' => $externalApi->api_key,
        'X-Api-Signature' => $sig,
    ])->postJson(route('api.projects.tasks.store', $project), $payloadData);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'data' => [
                'title' => 'Incoming Webhook Task',
                'assigned_to' => $member->id,
            ],
        ]);

    $this->assertDatabaseHas('tasks', [
        'title' => 'Incoming Webhook Task',
        'project_id' => $project->id,
        'assigned_to' => $member->id,
    ]);
});

it('defaults status and priority configured on external task API key', function () {
    $owner = User::factory()->create();

    $project = Project::create([
        'name' => 'Default Status Project',
        'slug' => 'default-status-project',
        'theme' => '#00aa55',
        'status' => 1,
        'priority' => 1,
        'user_id' => $owner->id,
        'company_id' => null,
    ]);

    $credentials = ExternalTaskApi::generateCredentials();
    $externalApi = ExternalTaskApi::create([
        'project_id' => $project->id,
        'user_id' => $owner->id,
        'default_status' => 2, // In Progress
        'default_priority' => 4, // Urgent
        'default_type' => 2, // Bug
        'name' => 'Custom Configured API',
        'api_key' => $credentials['api_key'],
        'api_secret' => $credentials['api_secret'],
        'is_active' => true,
    ]);

    $payloadData = ['title' => 'Task Without Explicit Status Or Priority'];
    $sig = hash_hmac('sha256', json_encode($payloadData), $credentials['api_secret']);

    $response = $this->withHeaders([
        'X-Api-Key' => $externalApi->api_key,
        'X-Api-Signature' => $sig,
    ])->postJson(route('api.projects.tasks.store', $project), $payloadData);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'data' => [
                'title' => 'Task Without Explicit Status Or Priority',
                'status' => 2,
                'priority' => 4,
                'type' => 2,
            ],
        ]);

    $this->assertDatabaseHas('tasks', [
        'title' => 'Task Without Explicit Status Or Priority',
        'status' => 2,
        'priority' => 4,
        'type' => 2,
    ]);
});

it('resolves project automatically from X-Api-Key when calling POST /api/tasks', function () {
    $owner = User::factory()->create();

    $project = Project::create([
        'name' => 'Resolved Project',
        'slug' => 'resolved-project',
        'theme' => '#3366ff',
        'status' => 1,
        'priority' => 1,
        'user_id' => $owner->id,
        'company_id' => null,
    ]);

    $credentials = ExternalTaskApi::generateCredentials();
    $externalApi = ExternalTaskApi::create([
        'project_id' => $project->id,
        'user_id' => $owner->id,
        'name' => 'Main Webhook',
        'api_key' => $credentials['api_key'],
        'api_secret' => $credentials['api_secret'],
        'is_active' => true,
    ]);

    $payloadData = ['title' => 'Clean Endpoint Task'];
    $sig = hash_hmac('sha256', json_encode($payloadData), $credentials['api_secret']);

    // Send request to general endpoint POST /api/tasks without project ID in URL or body
    $response = $this->withHeaders([
        'X-Api-Key' => $externalApi->api_key,
        'X-Api-Signature' => $sig,
    ])->postJson(route('api.tasks.store'), $payloadData);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'data' => [
                'title' => 'Clean Endpoint Task',
                'project_id' => $project->id,
            ],
        ]);

    $this->assertDatabaseHas('tasks', [
        'title' => 'Clean Endpoint Task',
        'project_id' => $project->id,
    ]);
});

it('allows authorized user to regenerate secret key for external task API', function () {
    $owner = User::factory()->create();

    $project = Project::create([
        'name' => 'Regen Secret Project',
        'slug' => 'regen-secret-project',
        'theme' => '#3366ff',
        'status' => 1,
        'priority' => 1,
        'user_id' => $owner->id,
        'company_id' => null,
    ]);

    $credentials = ExternalTaskApi::generateCredentials();
    $externalApi = ExternalTaskApi::create([
        'project_id' => $project->id,
        'user_id' => $owner->id,
        'name' => 'Secret Test API',
        'api_key' => $credentials['api_key'],
        'api_secret' => $credentials['api_secret'],
        'is_active' => true,
    ]);

    $this->actingAs($owner);

    $response = $this->postJson(route('external-api.regenerate-secret', $externalTaskApi = $externalApi));

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
        ]);

    $newRawSecret = $response->json('raw_secret');
    expect($newRawSecret)->not->toBeNull();
    expect($newRawSecret)->not->toBe($credentials['api_secret']);

    $externalApi->refresh();
    expect($externalApi->api_secret)->toBe($newRawSecret);
});

it('verifies valid HMAC signature when provided in request header', function () {
    $owner = User::factory()->create();

    $project = Project::create([
        'name' => 'HMAC Project',
        'slug' => 'hmac-project',
        'theme' => '#3366ff',
        'status' => 1,
        'priority' => 1,
        'user_id' => $owner->id,
        'company_id' => null,
    ]);

    $credentials = ExternalTaskApi::generateCredentials();
    $externalApi = ExternalTaskApi::create([
        'project_id' => $project->id,
        'user_id' => $owner->id,
        'name' => 'HMAC Webhook',
        'api_key' => $credentials['api_key'],
        'api_secret' => $credentials['api_secret'],
        'is_active' => true,
    ]);

    $data = ['title' => 'Signed Payload Task'];
    $payload = json_encode($data);
    $validSignature = hash_hmac('sha256', $payload, $credentials['api_secret']);

    // Test with invalid signature -> 403
    $invalidResponse = $this->withHeaders([
        'X-Api-Key' => $externalApi->api_key,
        'X-Api-Signature' => 'invalid_signature_hash',
    ])->postJson(route('api.tasks.store'), $data);

    $invalidResponse->assertStatus(403)
        ->assertJson(['message' => 'Invalid HMAC signature.']);

    // Test with valid signature -> 201
    $validResponse = $this->withHeaders([
        'X-Api-Key' => $externalApi->api_key,
        'X-Api-Signature' => $validSignature,
    ])->postJson(route('api.tasks.store'), $data);

    $validResponse->assertStatus(201)
        ->assertJson([
            'success' => true,
            'data' => [
                'title' => 'Signed Payload Task',
            ],
        ]);
});

it('returns JSON 403 status for invalid API key or unauthenticated request without redirecting to login', function () {
    // 1. Invalid API Key
    $responseInvalidKey = $this->post(route('api.tasks.store'), [
        'title' => 'Unauthenticated Task',
    ], [
        'X-Api-Key' => 'invalid_non_existent_key',
    ]);

    $responseInvalidKey->assertStatus(403)
        ->assertJson([
            'success' => false,
            'message' => 'Invalid or inactive API key provided.',
        ]);

    // 2. Missing API Key & Unauthenticated (without Accept: application/json header)
    $responseNoAuth = $this->post(route('api.tasks.store'), [
        'title' => 'Unauthenticated Task',
    ]);

    $responseNoAuth->assertStatus(403)
        ->assertJson([
            'success' => false,
        ]);
});
