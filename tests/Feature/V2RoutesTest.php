<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('v2 team index loads successfully with Inertia component and team members', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $teammate = User::factory()->create();

    $response = $this->actingAs($user)->get(route('v2.team.index'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('New/Team')
        ->has('team_members')
        ->has('stats')
        ->has('companies')
    );
});

test('users route redirects to v2 team index', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    $response = $this->actingAs($user)->get('/users');

    $response->assertRedirect(route('v2.team.index'));
});

test('v2 issues index loads successfully and allows creating and toggling issues', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $project = Project::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->get(route('v2.issues.index'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('New/Issues')
        ->has('issues')
        ->has('stats')
        ->has('projects')
    );

    // Create a new issue (which is a bug task)
    $createResponse = $this->actingAs($user)->post(route('v2.issues.store'), [
        'title' => 'Critical UI Glitch in Navbar',
        'category' => 'bug',
        'priority' => 'high',
        'description' => 'The dropdown menu clips on small viewports.',
        'project_id' => $project->id,
    ]);

    $createResponse->assertRedirect();
    $this->assertDatabaseHas('tasks', [
        'title' => 'Critical UI Glitch in Navbar',
        'type' => Task::TYPE_BUG,
        'priority' => 3,
        'user_id' => $user->id,
    ]);

    $issue = Task::where('title', 'Critical UI Glitch in Navbar')->first();

    // Toggle resolution
    $toggleResponse = $this->actingAs($user)->patch(route('v2.issues.toggle', $issue));
    $toggleResponse->assertRedirect();
    $this->assertEquals(3, $issue->fresh()->status); // Completed / Resolved
});

test('v2 activity logs index loads successfully and lists events', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    activity()
        ->causedBy($user)
        ->log('Created test deployment configuration');

    $response = $this->actingAs($user)->get(route('v2.activity-logs.index'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('New/ActivityLogs')
        ->has('activities')
        ->has('stats')
    );
});

test('v2 trash index loads soft-deleted items and supports restore and purge', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $project = Project::factory()->create(['user_id' => $user->id]);
    $task = Task::factory()->create(['user_id' => $user->id, 'project_id' => $project->id]);

    $task->delete(); // Soft delete

    $response = $this->actingAs($user)->get(route('v2.trash.index'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('New/Trash')
        ->has('trashed_tasks')
        ->has('stats')
    );

    // Restore task
    $restoreResponse = $this->actingAs($user)->post(route('v2.trash.tasks.restore', ['id' => $task->id]));

    $restoreResponse->assertRedirect();
    $this->assertNull($task->fresh()->deleted_at);

    // Soft delete again and force delete
    $task->fresh()->delete();
    $purgeResponse = $this->actingAs($user)->delete(route('v2.trash.tasks.forceDelete', ['id' => $task->id]));

    $purgeResponse->assertRedirect();
    $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
});

test('v2 permissions index loads successfully with roles and matrix', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    $response = $this->actingAs($user)->get(route('v2.permissions.index'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('New/Permissions')
        ->has('roles')
        ->has('matrix')
        ->has('user_memberships')
    );
});

test('v2 settings allows viewing, updating profile, and changing password', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'password' => 'old-password-123',
    ]);

    // View settings
    $response = $this->actingAs($user)->get(route('v2.settings.index'));
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('New/Settings')
        ->has('user')
        ->has('companies')
    );

    // Update profile
    $updateResponse = $this->actingAs($user)->post(route('v2.settings.profile'), [
        'name' => 'Subhadip Senior Dev',
        'email' => 'subhadip.new@workhub.io',
    ]);
    $updateResponse->assertRedirect();
    $this->assertEquals('Subhadip Senior Dev', $user->fresh()->name);
    $this->assertEquals('subhadip.new@workhub.io', $user->fresh()->email);

    // Update password
    $passwordResponse = $this->actingAs($user)->post(route('v2.settings.password'), [
        'current_password' => 'old-password-123',
        'password' => 'new-secure-password-456',
        'password_confirmation' => 'new-secure-password-456',
    ]);
    $passwordResponse->assertRedirect();
    $this->assertTrue(Hash::check('new-secure-password-456', $user->fresh()->password));
});
