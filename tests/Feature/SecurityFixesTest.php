<?php

/*
 * Regression tests for the issues listed in SECURITY_AUDIT_REPORT.md.
 */

use App\Models\Company;
use App\Models\CompanyUsers;
use App\Models\ExternalTaskApi;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Http;

function secCompanyWith(User $admin, array $members = []): Company
{
    $company = Company::factory()->create();
    // The creating user is the company's Owner (as CompanyController::store does).
    CompanyUsers::create(['company_id' => $company->id, 'user_id' => $admin->id, 'role' => 3, 'is_approved' => true]);
    foreach ($members as $member) {
        CompanyUsers::create(['company_id' => $company->id, 'user_id' => $member->id, 'role' => 0, 'is_approved' => true]);
    }

    return $company;
}

function secProject(User $owner, ?Company $company = null, string $name = 'Sec Project'): Project
{
    return Project::create([
        'name' => $name,
        'slug' => str($name)->slug().'-'.uniqid(),
        'theme' => '#000000',
        'status' => 1,
        'priority' => 1,
        'user_id' => $owner->id,
        'company_id' => $company?->id,
    ]);
}

function secTask(User $owner, ?Project $project = null, array $attrs = []): Task
{
    return Task::create(array_merge([
        'title' => 'Sec Task',
        'user_id' => $owner->id,
        'project_id' => $project?->id,
        'status' => 1,
        'priority' => 1,
    ], $attrs));
}

// C3
it('forbids toggling an issue the user cannot update', function () {
    $victim = User::factory()->create();
    $attacker = User::factory()->create();
    $task = secTask($victim, secProject($victim));

    $this->actingAs($attacker)->patch(route('v2.issues.toggle', $task))->assertForbidden();

    expect($task->fresh()->status)->toBe(1);
});

// H1
it('forbids reporting a V2 issue into a foreign project', function () {
    $victim = User::factory()->create();
    $attacker = User::factory()->create();
    $project = secProject($victim);

    $this->actingAs($attacker)->post(route('v2.issues.store'), [
        'title' => 'Injected', 'category' => 'bug', 'priority' => 'low',
        'description' => 'x', 'project_id' => $project->id,
    ])->assertForbidden();

    expect(Task::where('title', 'Injected')->exists())->toBeFalse();
});

// H2
it('forbids moving a task into a project the user cannot access', function () {
    $attacker = User::factory()->create();
    $victim = User::factory()->create();
    $task = secTask($attacker, secProject($attacker, null, 'Own'));
    $foreign = secProject($victim, null, 'Foreign');

    $this->actingAs($attacker)->patch(route('tasks.update', $task), [
        'title' => 'Sec Task', 'project_id' => $foreign->id,
    ])->assertForbidden();

    expect($task->fresh()->project_id)->not->toBe($foreign->id);
});

// H3
it('prevents an API key from creating tasks outside its own project', function () {
    $owner = User::factory()->create();
    $victim = User::factory()->create();
    $ownProject = secProject($owner, null, 'Key Project');
    $foreign = secProject($victim, null, 'Victim Project');

    $creds = ExternalTaskApi::generateCredentials();
    ExternalTaskApi::create([
        'project_id' => $ownProject->id, 'user_id' => $owner->id, 'name' => 'Key',
        'api_key' => $creds['api_key'], 'api_secret' => $creds['api_secret'], 'is_active' => true,
    ]);

    $payload = ['title' => 'Cross project', 'project_id' => $foreign->id];
    $sig = hash_hmac('sha256', json_encode($payload), $creds['api_secret']);

    $this->withHeaders(['X-Api-Key' => $creds['api_key'], 'X-Api-Signature' => $sig])
        ->postJson(route('api.tasks.store'), $payload)
        ->assertForbidden();

    expect(Task::where('project_id', $foreign->id)->exists())->toBeFalse();
});

// H4
it('rejects the constant empty-body signature when the request carries fields', function () {
    $owner = User::factory()->create();
    $project = secProject($owner);
    $creds = ExternalTaskApi::generateCredentials();
    ExternalTaskApi::create([
        'project_id' => $project->id, 'user_id' => $owner->id, 'name' => 'Key',
        'api_key' => $creds['api_key'], 'api_secret' => $creds['api_secret'], 'is_active' => true,
    ]);

    $this->withHeaders([
        'X-Api-Key' => $creds['api_key'],
        'X-Api-Signature' => hash_hmac('sha256', '', $creds['api_secret']),
    ])->postJson(route('api.tasks.store'), ['title' => 'Forged'])->assertForbidden();
});

// H5
it('rejects assigning a task to a user outside the company', function () {
    $admin = User::factory()->create();
    $outsider = User::factory()->create();
    $project = secProject($admin, secCompanyWith($admin));

    $this->actingAs($admin)->post(route('projects.tasks.store', $project), [
        'title' => 'Assign outsider', 'assigned_to' => $outsider->id,
    ])->assertSessionHasErrors('assigned_to');

    expect(Task::where('title', 'Assign outsider')->exists())->toBeFalse();
});

it('allows assigning a task to an approved company member', function () {
    $admin = User::factory()->create();
    $member = User::factory()->create();
    $project = secProject($admin, secCompanyWith($admin, [$member]));

    $this->actingAs($admin)->post(route('projects.tasks.store', $project), [
        'title' => 'Assign member', 'assigned_to' => $member->id,
    ])->assertSessionHasNoErrors();

    expect(Task::where('title', 'Assign member')->value('assigned_to'))->toBe($member->id);
});

// H6
it('hides project credentials from non-admin company members', function () {
    $admin = User::factory()->create();
    $member = User::factory()->create();
    $project = secProject($admin, secCompanyWith($admin, [$member]));

    $this->actingAs($member)->get(route('projects.credentials', $project))->assertForbidden();
    $this->actingAs($member)->get(route('projects.external-api.postman', $project))->assertForbidden();
    $this->actingAs($admin)->get(route('projects.credentials', $project))->assertOk();
});

// C4
it('prevents a removed admin from restoring their own membership', function () {
    $owner = User::factory()->create();
    $removedAdmin = User::factory()->create();
    $company = secCompanyWith($owner);
    $membership = CompanyUsers::create(['company_id' => $company->id, 'user_id' => $removedAdmin->id, 'role' => 1, 'is_approved' => true]);
    $membership->delete(); // removed by the owner

    $this->actingAs($removedAdmin)->post(route('trash.members.restore', $membership->id))->assertForbidden();

    expect(CompanyUsers::onlyTrashed()->whereKey($membership->id)->exists())->toBeTrue();
});

it('prevents a removed admin from force-deleting company projects', function () {
    $owner = User::factory()->create();
    $removedAdmin = User::factory()->create();
    $company = secCompanyWith($owner);
    CompanyUsers::create(['company_id' => $company->id, 'user_id' => $removedAdmin->id, 'role' => 1, 'is_approved' => true])->delete();

    $project = secProject($owner, $company);
    $project->delete();

    $this->actingAs($removedAdmin)->delete(route('trash.projects.forceDelete', $project->id))->assertForbidden();

    expect(Project::onlyTrashed()->whereKey($project->id)->exists())->toBeTrue();
});

// M3
it('does not re-add previously removed members when a company is restored', function () {
    $admin = User::factory()->create();
    $removed = User::factory()->create();
    $company = secCompanyWith($admin);
    $removedMembership = CompanyUsers::create(['company_id' => $company->id, 'user_id' => $removed->id, 'role' => 0, 'is_approved' => true]);

    $this->travel(-1)->days();
    $removedMembership->delete();
    $this->travelBack();

    CompanyUsers::where('company_id', $company->id)->delete();
    $company->delete();

    $this->actingAs($admin)->post(route('trash.companies.restore', $company->id))->assertRedirect();

    expect(CompanyUsers::where('company_id', $company->id)->where('user_id', $admin->id)->exists())->toBeTrue()
        ->and(CompanyUsers::where('company_id', $company->id)->where('user_id', $removed->id)->exists())->toBeFalse();
});

// C2
it('stores task descriptions sanitized', function () {
    $user = User::factory()->create();
    $task = secTask($user, null, [
        'description' => '<p>ok</p><img src=x onerror="alert(1)"><script>alert(2)</script><a href="javascript:alert(3)">l</a>',
    ]);

    $stored = $task->fresh()->description;

    expect($stored)->toContain('<p>ok</p>')
        ->not->toContain('onerror')
        ->not->toContain('<script')
        ->not->toContain('javascript:');
});

// C1
it('never fetches image URLs that point at internal addresses', function () {
    Http::fake();
    $user = User::factory()->create();
    $task = secTask($user);

    foreach (['http://127.0.0.1/admin', 'http://169.254.169.254/latest/meta-data/', 'http://10.0.0.5/', 'file:///etc/passwd'] as $url) {
        $this->actingAs($user)->postJson(route('api.tasks.images.store', $task), ['image_url' => $url]);
    }

    Http::assertNothingSent();
    expect($task->fresh()->images)->toHaveCount(0);
});

// M2
it('does not redirect to look-alike external hosts after switching UI', function () {
    $user = User::factory()->create();
    $evil = 'http://'.parse_url(url('/'), PHP_URL_HOST).'.evil.test/phish';

    $this->actingAs($user)
        ->withHeaders(['referer' => $evil])
        ->get(route('ui.switch', 'v2'))
        ->assertRedirect(route('dashboard'));
});

// H4 — v2 timestamped signatures and replay protection
function secApiKey(): array
{
    $owner = User::factory()->create();
    $project = secProject($owner, null, 'Signed Project');
    $creds = ExternalTaskApi::generateCredentials();
    ExternalTaskApi::create([
        'project_id' => $project->id, 'user_id' => $owner->id, 'name' => 'Signed Key',
        'api_key' => $creds['api_key'], 'api_secret' => $creds['api_secret'], 'is_active' => true,
    ]);

    return [$creds, $owner, $project];
}

function secSignedHeaders(array $creds, string $body, ?int $timestamp = null): array
{
    $ts = (string) ($timestamp ?? time());

    return [
        'X-Api-Key' => $creds['api_key'],
        'X-Api-Timestamp' => $ts,
        'X-Api-Signature' => hash_hmac('sha256', $ts.'.'.$body, $creds['api_secret']),
    ];
}

it('accepts a v2 timestamped signature', function () {
    [$creds] = secApiKey();
    $payload = ['title' => 'Signed v2 task'];

    $this->withHeaders(secSignedHeaders($creds, json_encode($payload)))
        ->postJson(route('api.tasks.store'), $payload)
        ->assertCreated()
        ->assertHeaderMissing('Deprecation');
});

it('rejects a v2 signature whose timestamp is outside the 5 minute window', function () {
    [$creds] = secApiKey();
    $payload = ['title' => 'Stale task'];

    $this->withHeaders(secSignedHeaders($creds, json_encode($payload), time() - 600))
        ->postJson(route('api.tasks.store'), $payload)
        ->assertForbidden();

    expect(Task::where('title', 'Stale task')->exists())->toBeFalse();
});

it('rejects a replayed v2 request', function () {
    [$creds] = secApiKey();
    $payload = ['title' => 'Replay me'];
    $headers = secSignedHeaders($creds, json_encode($payload));

    $this->withHeaders($headers)->postJson(route('api.tasks.store'), $payload)->assertCreated();
    $this->withHeaders($headers)->postJson(route('api.tasks.store'), $payload)->assertForbidden();

    expect(Task::where('title', 'Replay me')->count())->toBe(1);
});

it('accepts legacy signatures with a deprecation header only while the flag is enabled', function () {
    [$creds] = secApiKey();
    $payload = ['title' => 'Legacy task'];
    $legacy = [
        'X-Api-Key' => $creds['api_key'],
        'X-Api-Signature' => hash_hmac('sha256', json_encode($payload), $creds['api_secret']),
    ];

    $this->withHeaders($legacy)->postJson(route('api.tasks.store'), $payload)
        ->assertCreated()
        ->assertHeader('Deprecation', 'true');

    config(['services.external_api.allow_legacy_signatures' => false]);

    $this->withHeaders($legacy)->postJson(route('api.tasks.store'), $payload)->assertForbidden();
});

it('exports a Postman collection that signs with timestamps', function () {
    [, $owner, $project] = secApiKey();

    $content = $this->actingAs($owner)
        ->get(route('projects.external-api.postman', $project))
        ->streamedContent();

    expect($content)->toContain('X-Api-Timestamp')
        ->and($content)->toContain('api_timestamp')
        ->and($content)->toContain('YOUR_API_SECRET_HERE');
});

// L1
it('requires login for the mock task view pages', function () {
    $this->get(route('new.task.view'))->assertRedirect(route('login'));
    $this->get(route('new.task.show', ['id' => '1']))->assertRedirect(route('login'));
});
