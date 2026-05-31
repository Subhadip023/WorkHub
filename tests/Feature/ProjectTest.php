<?php

use App\Models\Company;
use App\Models\CompanyUsers;
use App\Models\Project;
use App\Models\User;

it('allows authenticated user to create a project in personal space', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->post(route('projects.store'), [
        'name' => 'Personal Project Title',
        'description' => 'A description of the new project.',
        'theme' => '#ff0000',
        'status' => 2, // In Progress
        'priority' => 3, // High
        'company_id' => 'personal',
    ]);

    $response->assertRedirect(route('projects.index'));
    $this->assertDatabaseHas('projects', [
        'name' => 'Personal Project Title',
        'theme' => '#ff0000',
        'status' => 2,
        'priority' => 3,
        'user_id' => $user->id,
        'company_id' => null,
    ]);
});

it('allows authenticated user to create a project in their organization', function () {
    $user = User::factory()->create();
    $company = Company::create(['name' => 'Test Org', 'slug' => 'test-org']);
    CompanyUsers::create([
        'company_id' => $company->id,
        'user_id' => $user->id,
        'role' => 1,
    ]);

    $this->actingAs($user);

    $response = $this->post(route('projects.store'), [
        'name' => 'Org Project Title',
        'description' => 'A description of the new org project.',
        'theme' => '#0000ff',
        'status' => 1, // To Do
        'priority' => 2, // Medium
        'company_id' => $company->id,
    ]);

    $response->assertRedirect(route('projects.index'));
    $this->assertDatabaseHas('projects', [
        'name' => 'Org Project Title',
        'theme' => '#0000ff',
        'status' => 1,
        'priority' => 2,
        'user_id' => $user->id,
        'company_id' => $company->id,
    ]);
});

it('prevents authenticated user from creating a project in an organization they do not belong to', function () {
    $user = User::factory()->create();
    $otherCompany = Company::create(['name' => 'Other Org', 'slug' => 'other-org']);

    $this->actingAs($user);

    $response = $this->post(route('projects.store'), [
        'name' => 'Unauthorized Org Project',
        'description' => 'Description.',
        'theme' => '#ffffff',
        'status' => 1,
        'priority' => 1,
        'company_id' => $otherCompany->id,
    ]);

    $response->assertStatus(403);
});

it('allows authenticated user to update project status and priority', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $project = Project::factory()->create([
        'user_id' => $user->id,
        'company_id' => null,
        'status' => 1,
        'priority' => 2,
    ]);

    $response = $this->put(route('projects.update', $project), [
        'name' => 'Updated Project Title',
        'description' => 'Updated description.',
        'theme' => '#00ff00',
        'status' => 3, // Completed
        'priority' => 4, // Urgent
        'company_id' => 'personal',
    ]);

    $response->assertRedirect(route('projects.index'));
    $this->assertDatabaseHas('projects', [
        'id' => $project->id,
        'name' => 'Updated Project Title',
        'status' => 3,
        'priority' => 4,
    ]);
});

it('sorts tasks in project details page by due_date ascending and priority descending', function () {
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

    // Create tasks with different due dates and priorities
    // Task A: No due date, priority Urgent
    $taskA = \App\Models\Task::create([
        'title' => 'Task A',
        'project_id' => $project->id,
        'due_date' => null,
        'priority' => 4,
        'status' => 1,
    ]);

    // Task B: Later due date, priority Medium
    $taskB = \App\Models\Task::create([
        'title' => 'Task B',
        'project_id' => $project->id,
        'due_date' => '2026-06-05',
        'priority' => 2,
        'status' => 1,
    ]);

    // Task C: Earlier due date, priority Low
    $taskC = \App\Models\Task::create([
        'title' => 'Task C',
        'project_id' => $project->id,
        'due_date' => '2026-06-01',
        'priority' => 1,
        'status' => 1,
    ]);

    // Task D: Earlier due date, priority Urgent (same due date as C, higher priority)
    $taskD = \App\Models\Task::create([
        'title' => 'Task D',
        'project_id' => $project->id,
        'due_date' => '2026-06-01',
        'priority' => 4,
        'status' => 1,
    ]);

    // Task E: Completed Task
    $taskE = \App\Models\Task::create([
        'title' => 'Task E',
        'project_id' => $project->id,
        'due_date' => '2026-06-10',
        'priority' => 1,
        'status' => 3, // Completed
    ]);

    $this->actingAs($user);

    $response = $this->get(route('projects.show', $project));
    $response->assertStatus(200);

    // Verify Show Completed Tasks checkbox and row classes are in HTML
    $response->assertSee('id="toggleCompletedTasks"', false);
    $response->assertSee('completed-task');
    $response->assertSee('pending-task');

    // Expected order:
    // 1. Task D (Earlier due date 2026-06-01, Priority Urgent 4)
    // 2. Task C (Earlier due date 2026-06-01, Priority Low 1)
    // 3. Task B (Later due date 2026-06-05, Priority Medium 2)
    // 4. Task E (Later due date 2026-06-10, Priority Low 1)
    // 5. Task A (No due date, Priority Urgent 4 - comes last because due_date is null)
    $tasks = $response->viewData('project')->tasks;

    expect($tasks->get(0)->id)->toBe($taskD->id)
        ->and($tasks->get(1)->id)->toBe($taskC->id)
        ->and($tasks->get(2)->id)->toBe($taskB->id)
        ->and($tasks->get(3)->id)->toBe($taskE->id)
        ->and($tasks->get(4)->id)->toBe($taskA->id);
});
