<?php

use App\Models\Comment;
use App\Models\Company;
use App\Models\CompanyUsers;
use App\Models\Note;
use App\Models\Project;
use App\Models\ProjectCredentials;
use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Spatie\Activitylog\Models\Activity;

it('logs task creation and updates automatically using spatie activitylog', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $task = Task::create([
        'title' => 'Initial Activity Task',
        'status' => 1,
        'priority' => 1,
        'user_id' => $user->id,
    ]);

    expect(Activity::where('subject_id', $task->id)->exists())->toBeTrue();

    $task->update([
        'title' => 'Updated Activity Task Title',
        'status' => 2,
    ]);

    $lastActivity = Activity::where('subject_id', $task->id)
        ->latest('id')
        ->first();

    expect($lastActivity->description)->toBe('updated');
    expect($lastActivity->properties['attributes']['title'])->toBe('Updated Activity Task Title');
    expect($lastActivity->properties['old']['title'])->toBe('Initial Activity Task');
});

it('logs project changes using spatie activitylog', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $project = Project::create([
        'name' => 'Activity Project',
        'slug' => 'activity-project',
        'user_id' => $user->id,
    ]);

    expect(Activity::where('subject_id', $project->id)->exists())->toBeTrue();

    $project->update(['name' => 'Renamed Activity Project']);

    $activity = Activity::where('subject_id', $project->id)
        ->latest('id')
        ->first();

    expect($activity->properties['attributes']['name'])->toBe('Renamed Activity Project');
});

it('allows authenticated user to view activity logs index page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('activity-logs.index'));
    $response->assertStatus(200);
    $response->assertSee('My Activity Logs');
});

it('logs user login events in activitylog and updates last_login_at', function () {
    $user = User::factory()->create(['last_login_at' => null]);

    event(new Login('web', $user, false));

    expect(Activity::where('event', 'login')->where('causer_id', $user->id)->exists())->toBeTrue();
    expect($user->fresh()->last_login_at)->not()->toBeNull();
});

it('logs comment, note, company, project credentials, and external task api activity', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $company = Company::create(['name' => 'Log Co', 'code' => 'LOGCO1']);
    expect(Activity::where('subject_type', 'company')->where('subject_id', $company->id)->exists())->toBeTrue();

    $note = Note::create([
        'user_id' => $user->id,
        'title' => 'Test Log Note',
        'description' => 'Test Note Description',
        'note_type' => Note::TYPE_PERSONAL,
        'note_type_id' => $user->id,
    ]);
    expect(Activity::where('subject_type', 'note')->where('subject_id', $note->id)->exists())->toBeTrue();

    $comment = Comment::create([
        'user_id' => $user->id,
        'content' => 'Test Log Comment',
        'commentable_type' => 'company',
        'commentable_id' => $company->id,
    ]);
    expect(Activity::where('subject_type', 'comment')->where('subject_id', $comment->id)->exists())->toBeTrue();

    $project = Project::create([
        'name' => 'Test Credentials Project',
        'slug' => 'test-cred-project',
        'user_id' => $user->id,
    ]);

    $cred = ProjectCredentials::create([
        'project_id' => $project->id,
        'type' => ProjectCredentials::TYPE_DEVELOPMENT,
        'name' => 'Dev DB',
        'host_or_identifier' => 'localhost',
        'password_or_secret' => 'secret123',
    ]);
    expect(Activity::where('subject_type', 'project_credentials')->where('subject_id', $cred->id)->exists())->toBeTrue();
});

it('allows company members to fetch team member activity via memberActivity endpoint', function () {
    $owner = User::factory()->create();
    $memberUser = User::factory()->create();
    $nonMemberUser = User::factory()->create();

    $company = Company::create(['name' => 'Member Activity Co', 'code' => 'MACO1']);

    CompanyUsers::create([
        'company_id' => $company->id,
        'user_id' => $owner->id,
        'role' => 1,
        'is_approved' => true,
    ]);

    CompanyUsers::create([
        'company_id' => $company->id,
        'user_id' => $memberUser->id,
        'role' => 0,
        'is_approved' => true,
    ]);

    $this->actingAs($memberUser);
    Task::create([
        'title' => 'Member Created Task',
        'status' => 1,
        'priority' => 1,
        'user_id' => $memberUser->id,
    ]);

    // Authorized call
    $this->actingAs($owner);
    $response = $this->getJson(route('companies.members.activity', [$company, $memberUser]));
    $response->assertStatus(200);
    $response->assertJsonPath('success', true);
    $response->assertJsonPath('user.email', $memberUser->email);
    expect(count($response->json('activities')))->toBeGreaterThan(0);

    // Unauthorized call (non-member)
    $this->actingAs($nonMemberUser);
    $unauthResponse = $this->getJson(route('companies.members.activity', [$company, $memberUser]));
    $unauthResponse->assertStatus(403);
});

it('logs activity when user views task page or dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $task = Task::create([
        'title' => 'Page View Task',
        'status' => 1,
        'priority' => 1,
        'user_id' => $user->id,
    ]);

    // View task page
    $this->get(route('tasks.show', $task))->assertStatus(200);

    expect(Activity::where('subject_type', 'task')
        ->where('subject_id', $task->id)
        ->where('event', 'viewed')
        ->where('causer_id', $user->id)
        ->exists())->toBeTrue();

    // View dashboard
    $this->get(route('dashboard'))->assertStatus(200);

    expect(Activity::where('event', 'viewed_dashboard')
        ->where('causer_id', $user->id)
        ->exists())->toBeTrue();

    // View project page
    $project = Project::create([
        'name' => 'Viewed Project',
        'slug' => 'viewed-project',
        'user_id' => $user->id,
    ]);

    $this->get(route('projects.show', $project))->assertStatus(200);

    expect(Activity::where('subject_type', 'project')
        ->where('subject_id', $project->id)
        ->where('event', 'viewed')
        ->where('causer_id', $user->id)
        ->exists())->toBeTrue();
});
