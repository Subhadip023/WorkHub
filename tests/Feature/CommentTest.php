<?php

use App\Models\Comment;
use App\Models\Company;
use App\Models\CompanyUsers;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

it('allows authenticated user to comment on an accessible project and delete it', function () {
    $user = User::factory()->create();
    $project = Project::create([
        'name' => 'Personal Project',
        'slug' => 'personal-project',
        'theme' => '#ff0000',
        'status' => 1,
        'priority' => 1,
        'user_id' => $user->id,
        'company_id' => null,
    ]);

    $this->actingAs($user);

    // Post comment
    $response = $this->post(route('comments.store'), [
        'content' => 'This is a test comment',
        'commentable_type' => 'project',
        'commentable_id' => $project->id,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('comments', [
        'content' => 'This is a test comment',
        'commentable_type' => 'project',
        'commentable_id' => $project->id,
        'user_id' => $user->id,
    ]);

    $comment = Comment::firstOrFail();

    // Delete comment
    $deleteResponse = $this->delete(route('comments.destroy', $comment));
    $deleteResponse->assertRedirect();
    $this->assertDatabaseMissing('comments', [
        'id' => $comment->id,
    ]);
});

it('prevents user from commenting on a project they do not own or belong to', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $project = Project::create([
        'name' => 'Personal Project',
        'slug' => 'personal-project',
        'theme' => '#ff0000',
        'status' => 1,
        'priority' => 1,
        'user_id' => $user1->id,
        'company_id' => null,
    ]);

    $this->actingAs($user2);

    $response = $this->post(route('comments.store'), [
        'content' => 'Unauthorized comment attempt',
        'commentable_type' => 'project',
        'commentable_id' => $project->id,
    ], ['HTTP_REFERER' => '/']);

    $response->assertStatus(403);
    $this->assertDatabaseMissing('comments', [
        'content' => 'Unauthorized comment attempt',
    ]);
});

it('prevents user from deleting someone else\'s comment', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $project = Project::create([
        'name' => 'Personal Project',
        'slug' => 'personal-project',
        'theme' => '#ff0000',
        'status' => 1,
        'priority' => 1,
        'user_id' => $user1->id,
        'company_id' => null,
    ]);

    $comment = Comment::create([
        'user_id' => $user1->id,
        'content' => 'Nice work!',
        'commentable_type' => 'project',
        'commentable_id' => $project->id,
    ]);

    $this->actingAs($user2);

    $response = $this->delete(route('comments.destroy', $comment));
    $response->assertStatus(403);
    $this->assertDatabaseHas('comments', [
        'id' => $comment->id,
    ]);
});

it('allows company members to comment on company projects and tasks', function () {
    $user = User::factory()->create();
    $company = Company::create([
        'name' => 'Acme Corp',
        'code' => 'ACME',
    ]);

    CompanyUsers::create([
        'company_id' => $company->id,
        'user_id' => $user->id,
        'role' => 0, // member
    ]);

    $project = Project::create([
        'name' => 'Company Project',
        'slug' => 'company-project',
        'theme' => '#0000ff',
        'status' => 1,
        'priority' => 1,
        'user_id' => $user->id,
        'company_id' => $company->id,
    ]);

    $task = Task::create([
        'title' => 'Company Task',
        'description' => 'Fix it',
        'project_id' => $project->id,
        'status' => 1,
        'priority' => 1,
    ]);

    $this->actingAs($user);

    // Comment on Company
    $this->post(route('comments.store'), [
        'content' => 'Welcome to Acme!',
        'commentable_type' => 'company',
        'commentable_id' => $company->id,
    ])->assertRedirect();

    // Comment on Task
    $this->post(route('comments.store'), [
        'content' => 'Working on this task now',
        'commentable_type' => 'task',
        'commentable_id' => $task->id,
    ])->assertRedirect();

    $this->assertDatabaseHas('comments', [
        'content' => 'Welcome to Acme!',
        'commentable_type' => 'company',
        'commentable_id' => $company->id,
    ]);

    $this->assertDatabaseHas('comments', [
        'content' => 'Working on this task now',
        'commentable_type' => 'task',
        'commentable_id' => $task->id,
    ]);
});
