<?php

use App\Models\User;
use App\Models\Project;
use App\Models\Task;
use App\Models\Company;
use App\Models\CompanyUsers;

it('allows authenticated user to create a task in a personal project with status and priority', function () {
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

    $response = $this->post(route('projects.tasks.store', $project), [
        'title' => 'New Task Title',
        'description' => 'Task description.',
        'status' => 2, // In Progress
        'priority' => 3, // High
        'assigned_to' => $user->id,
        'due_date' => '2026-06-01',
    ]);

    $response->assertRedirect(route('projects.show', $project));
    $this->assertDatabaseHas('tasks', [
        'title' => 'New Task Title',
        'project_id' => $project->id,
        'status' => 2,
        'priority' => 3,
        'is_completed' => false,
    ]);
});

it('automatically marks task as completed when status is set to 3', function () {
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

    $response = $this->post(route('projects.tasks.store', $project), [
        'title' => 'Completed Task',
        'status' => 3, // Completed
        'priority' => 2,
    ]);

    $this->assertDatabaseHas('tasks', [
        'title' => 'Completed Task',
        'status' => 3,
        'is_completed' => true,
    ]);
});

it('allows user to update task status and priority', function () {
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

    $task = Task::create([
        'title' => 'Original Task',
        'project_id' => $project->id,
        'status' => 1,
        'priority' => 2,
        'is_completed' => false,
    ]);

    $this->actingAs($user);

    $response = $this->patch(route('tasks.update', $task), [
        'title' => 'Updated Task Title',
        'status' => 4, // On Hold
        'priority' => 4, // Urgent
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'title' => 'Updated Task Title',
        'status' => 4,
        'priority' => 4,
        'is_completed' => false,
    ]);
});

it('syncs status when toggling task completion state', function () {
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

    $task = Task::create([
        'title' => 'Toggle Task',
        'project_id' => $project->id,
        'status' => 1, // To Do
        'priority' => 2,
        'is_completed' => false,
    ]);

    $this->actingAs($user);

    // Toggle to completed
    $response = $this->patch(route('tasks.toggle', $task));
    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'status' => 3, // Completed
        'is_completed' => true,
    ]);

    // Toggle back to pending
    $response = $this->patch(route('tasks.toggle', $task));
    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'status' => 1, // Should revert to To Do (or 1)
        'is_completed' => false,
    ]);
});
