<?php

/*
 * AI task drafting (Gemini). Gemini is always faked: these tests never make a
 * real (billed) API call.
 */

use App\Models\Company;
use App\Models\CompanyUsers;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config([
        'services.gemini.key' => 'test-gemini-key',
        'services.gemini.model' => 'test-model',
        'services.gemini.base_url' => 'https://generativelanguage.googleapis.com/v1beta',
    ]);
});

function fakeGemini(array $draft, string $finishReason = 'STOP'): void
{
    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [[
                'content' => ['role' => 'model', 'parts' => [['text' => json_encode($draft)]]],
                'finishReason' => $finishReason,
            ]],
        ]),
    ]);
}

function aiCompanyProject(User $owner, array $members = []): Project
{
    $company = Company::create(['name' => 'AI Co', 'code' => 'AI'.strtoupper(substr(uniqid(), -4))]);
    CompanyUsers::create(['company_id' => $company->id, 'user_id' => $owner->id, 'role' => 3, 'is_approved' => true]);
    foreach ($members as $m) {
        CompanyUsers::create(['company_id' => $company->id, 'user_id' => $m->id, 'role' => 0, 'is_approved' => true]);
    }

    return Project::create([
        'name' => 'AI Project', 'slug' => 'ai-project-'.uniqid(), 'theme' => '#000000',
        'status' => 1, 'priority' => 1, 'user_id' => $owner->id, 'company_id' => $company->id,
    ]);
}

it('returns a validated draft and does not create a task', function () {
    $owner = User::factory()->create();
    $sarah = User::factory()->create(['name' => 'Sarah Chen']);
    $project = aiCompanyProject($owner, [$sarah]);

    fakeGemini([
        'title' => 'Fix checkout button on mobile',
        'description' => '<b>Broken</b> on iOS Safari',
        'priority' => 4, 'type' => 2, 'status' => 1,
        'due_date' => now()->addDays(3)->toDateString(),
        'assigned_to' => $sarah->id,
        'points' => 3,
    ]);

    $this->actingAs($owner)
        ->postJson(route('tasks.ai-draft'), ['text' => 'urgent checkout bug for Sarah, due Friday', 'project_id' => $project->id])
        ->assertOk()
        ->assertJson([
            'success' => true,
            'draft' => [
                'title' => 'Fix checkout button on mobile',
                'description' => 'Broken on iOS Safari',
                'priority' => 4, 'type' => 2, 'status' => 1,
                'assigned_to' => $sarah->id,
                'points' => 3,
            ],
        ]);

    expect(Task::count())->toBe(0);
});

it('sends the API key in a header, never in the URL, and puts the user text inside request tags', function () {
    $user = User::factory()->create();
    fakeGemini(['title' => 'Write docs', 'priority' => 2, 'type' => 1, 'status' => 1]);

    $this->actingAs($user)->postJson(route('tasks.ai-draft'), ['text' => 'write the API docs'])->assertOk();

    Http::assertSent(function (HttpRequest $request) {
        $body = $request->data();

        return $request->hasHeader('x-goog-api-key', 'test-gemini-key')
            && ! str_contains($request->url(), 'test-gemini-key')
            && str_ends_with($request->url(), '/models/test-model:generateContent')
            && ($body['generationConfig']['responseMimeType'] ?? null) === 'application/json'
            && str_contains($body['contents'][0]['parts'][0]['text'], "<request>\nwrite the API docs\n</request>");
    });
});

it('drops an assignee who is outside the project workspace and clamps invalid values', function () {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();
    $project = aiCompanyProject($owner);

    fakeGemini([
        'title' => 'Task', 'priority' => 9, 'type' => 0, 'status' => 7,
        'due_date' => '2199-01-01', 'assigned_to' => $outsider->id, 'points' => -5,
    ]);

    $this->actingAs($owner)
        ->postJson(route('tasks.ai-draft'), ['text' => 'something for the outsider', 'project_id' => $project->id])
        ->assertOk()
        ->assertJson(['draft' => [
            'priority' => 2, 'type' => 1, 'status' => 1,
            'due_date' => null, 'assigned_to' => null, 'points' => 0,
        ]]);
});

it('forbids drafting into a project the user cannot contribute to', function () {
    $owner = User::factory()->create();
    $stranger = User::factory()->create();
    $project = aiCompanyProject($owner);
    Http::fake();

    $this->actingAs($stranger)
        ->postJson(route('tasks.ai-draft'), ['text' => 'sneaky task', 'project_id' => $project->id])
        ->assertForbidden();

    Http::assertNothingSent();
});

it('returns 503 when AI is not configured', function () {
    config(['services.gemini.key' => null]);
    Http::fake();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('tasks.ai-draft'), ['text' => 'anything at all'])
        ->assertStatus(503)
        ->assertJson(['success' => false]);

    Http::assertNothingSent();
});

it('returns 503 with a friendly message when Gemini blocks the prompt', function () {
    $user = User::factory()->create();
    Http::fake(['generativelanguage.googleapis.com/*' => Http::response(['promptFeedback' => ['blockReason' => 'SAFETY']])]);

    $this->actingAs($user)->postJson(route('tasks.ai-draft'), ['text' => 'blocked text'])
        ->assertStatus(503)
        ->assertJsonPath('message', 'The AI declined this request. Try rephrasing it.');
});

it('returns 503 when the Gemini answer is truncated', function () {
    $user = User::factory()->create();
    fakeGemini(['title' => 'Cut off'], 'MAX_TOKENS');

    $this->actingAs($user)->postJson(route('tasks.ai-draft'), ['text' => 'very long text'])
        ->assertStatus(503)
        ->assertJsonPath('message', 'The request was too long for the AI to finish. Try a shorter description.');
});

it('returns 503 when Gemini is rate limited or errors', function () {
    $user = User::factory()->create();

    Http::fake(['generativelanguage.googleapis.com/*' => Http::response(['error' => ['code' => 429]], 429)]);
    $this->actingAs($user)->postJson(route('tasks.ai-draft'), ['text' => 'busy service'])->assertStatus(503);
});

it('rate limits AI drafting to 10 requests per minute', function () {
    $user = User::factory()->create();
    fakeGemini(['title' => 'Task', 'priority' => 2, 'type' => 1, 'status' => 1]);

    for ($i = 0; $i < 10; $i++) {
        $this->actingAs($user)->postJson(route('tasks.ai-draft'), ['text' => "task number {$i}"])->assertOk();
    }

    $this->actingAs($user)->postJson(route('tasks.ai-draft'), ['text' => 'one too many'])->assertStatus(429);
});

it('validates the input text', function () {
    $user = User::factory()->create();
    Http::fake();

    $this->actingAs($user)->postJson(route('tasks.ai-draft'), ['text' => ''])->assertStatus(422);
    $this->actingAs($user)->postJson(route('tasks.ai-draft'), ['text' => str_repeat('a', 2001)])->assertStatus(422);

    Http::assertNothingSent();
});
