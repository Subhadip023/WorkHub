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

it('allows authenticated user to view task details page', function () {
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
        'title' => 'Detailed Task Name',
        'project_id' => $project->id,
        'status' => 1,
        'priority' => 2,
    ]);

    $this->actingAs($user);

    $response = $this->get(route('tasks.show', $task));
    $response->assertStatus(200);
    $response->assertSee('Detailed Task Name');
});

it('allows authenticated user to create a task via general store route', function () {
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

    $response = $this->post(route('tasks.store'), [
        'project_id' => $project->id,
        'title' => 'General Store Task',
        'description' => 'A general description.',
        'status' => 2,
        'priority' => 1,
    ]);

    $response->assertRedirect(route('tasks.index'));
    $this->assertDatabaseHas('tasks', [
        'title' => 'General Store Task',
        'project_id' => $project->id,
        'status' => 2,
    ]);
});

it('allows user to delete a task', function () {
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
        'title' => 'Task to delete',
        'project_id' => $project->id,
        'status' => 1,
        'priority' => 1,
    ]);

    $this->actingAs($user);

    $response = $this->delete(route('tasks.destroy', $task));
    $response->assertRedirect();
    $this->assertDatabaseMissing('tasks', [
        'id' => $task->id,
    ]);
});

it('allows user to import tasks from JSON format', function () {
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

    $jsonData = json_encode([
        [
            'title' => 'Imported Task 1',
            'description' => 'First imported description.',
            'status' => 2,
            'priority' => 3,
        ],
        [
            'title' => 'Imported Task 2',
            'description' => 'Second imported description.',
            'status' => 3,
            'priority' => 1,
        ]
    ]);

    $response = $this->post(route('projects.tasks.import', $project), [
        'json_data' => $jsonData,
    ]);

    $response->assertRedirect(route('projects.show', $project));
    $this->assertDatabaseHas('tasks', [
        'title' => 'Imported Task 1',
        'status' => 2,
        'priority' => 3,
    ]);
    $this->assertDatabaseHas('tasks', [
        'title' => 'Imported Task 2',
        'status' => 3,
        'priority' => 1,
        'is_completed' => true,
    ]);
});

it('allows user to upload and delete task images', function () {
    \Illuminate\Support\Facades\Storage::fake('public');

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
        'title' => 'Image Task',
        'project_id' => $project->id,
        'status' => 1,
        'priority' => 1,
    ]);

    $this->actingAs($user);
    session(['current_company_id' => 'personal']);

    $file = \Illuminate\Http\UploadedFile::fake()->image('task_attachment.png', 100, 100);

    $response = $this->post(route('tasks.images.store', $task), [
        'image' => $file,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('task_images', [
        'task_id' => $task->id,
    ]);

    $taskImage = $task->images()->first();
    \Illuminate\Support\Facades\Storage::disk('public')->assertExists($taskImage->image_path);

    // Delete the image
    $deleteResponse = $this->delete(route('tasks.images.destroy', $taskImage));
    $deleteResponse->assertRedirect();
    $this->assertDatabaseMissing('task_images', [
        'id' => $taskImage->id,
    ]);
    \Illuminate\Support\Facades\Storage::disk('public')->assertMissing($taskImage->image_path);
});
