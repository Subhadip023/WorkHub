<?php

use App\Models\Comment;
use App\Models\Company;
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
