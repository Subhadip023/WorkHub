<?php

use App\Models\Company;
use App\Models\CompanyUsers;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

it('allows user to view their soft-deleted tasks, projects, and companies in the trash bin', function () {
    $user = User::factory()->create();
    $company = Company::create([
        'name' => 'Trash Org',
        'code' => 'TRSH',
    ]);
    CompanyUsers::create([
        'company_id' => $company->id,
        'user_id' => $user->id,
        'role' => 1, // Admin
        'is_approved' => true,
    ]);

    $project = Project::create([
        'name' => 'Trash Project',
        'slug' => 'trash-project',
        'user_id' => $user->id,
        'company_id' => $company->id,
    ]);

    $task = Task::create([
        'title' => 'Trash Task',
        'project_id' => $project->id,
        'user_id' => $user->id,
    ]);

    $this->actingAs($user);

    // Initial check: Trash is empty
    $response = $this->get(route('trash.index'));
    $response->assertStatus(200);
    $response->assertDontSee('Trash Task');
    $response->assertDontSee('Trash Project');
    $response->assertDontSee('Trash Org');

    // 1. Soft delete task
    $task->delete();
    $response = $this->get(route('trash.index'));
    $response->assertSee('Trash Task');

    // 2. Soft delete project
    $project->delete();
    $response = $this->get(route('trash.index'));
    $response->assertSee('Trash Project');

    // 3. Soft delete company
    // Call controller delete
    $this->delete(route('companies.destroy', $company));
    $response = $this->get(route('trash.index'));
    $response->assertSee('Trash Org');
});

it('allows user to restore their soft-deleted tasks, projects, and companies', function () {
    $user = User::factory()->create();
    $company = Company::create([
        'name' => 'Restore Org',
        'code' => 'RSTR',
    ]);
    CompanyUsers::create([
        'company_id' => $company->id,
        'user_id' => $user->id,
        'role' => 1,
        'is_approved' => true,
    ]);

    $project = Project::create([
        'name' => 'Restore Project',
        'slug' => 'restore-project',
        'user_id' => $user->id,
        'company_id' => $company->id,
    ]);

    $task = Task::create([
        'title' => 'Restore Task',
        'project_id' => $project->id,
        'user_id' => $user->id,
    ]);

    // Soft delete everything
    $task->delete();
    $project->delete();
    CompanyUsers::where('company_id', $company->id)->delete();
    $company->delete();

    $this->actingAs($user);

    // 1. Restore Company
    $response = $this->post(route('trash.companies.restore', $company->id));
    $response->assertRedirect();
    $this->assertDatabaseHas('companies', ['id' => $company->id, 'deleted_at' => null]);
    $this->assertDatabaseHas('company_users', ['company_id' => $company->id, 'user_id' => $user->id, 'deleted_at' => null]);

    // 2. Restore Project
    $response = $this->post(route('trash.projects.restore', $project->id));
    $response->assertRedirect();
    $this->assertDatabaseHas('projects', ['id' => $project->id, 'deleted_at' => null]);

    // 3. Restore Task
    $response = $this->post(route('trash.tasks.restore', $task->id));
    $response->assertRedirect();
    $this->assertDatabaseHas('tasks', ['id' => $task->id, 'deleted_at' => null]);
});

it('allows user to permanently delete (force delete) their soft-deleted tasks, projects, and companies', function () {
    $user = User::factory()->create();
    $company = Company::create([
        'name' => 'Wipe Org',
        'code' => 'WIPE',
    ]);
    CompanyUsers::create([
        'company_id' => $company->id,
        'user_id' => $user->id,
        'role' => 1,
        'is_approved' => true,
    ]);

    $project = Project::create([
        'name' => 'Wipe Project',
        'slug' => 'wipe-project',
        'user_id' => $user->id,
        'company_id' => $company->id,
    ]);

    $task = Task::create([
        'title' => 'Wipe Task',
        'project_id' => $project->id,
        'user_id' => $user->id,
    ]);

    // Soft delete everything
    $task->delete();
    $project->delete();
    CompanyUsers::where('company_id', $company->id)->delete();
    $company->delete();

    $this->actingAs($user);

    // 1. Force delete Task
    $response = $this->delete(route('trash.tasks.forceDelete', $task->id));
    $response->assertRedirect();
    $this->assertDatabaseMissing('tasks', ['id' => $task->id]);

    // 2. Force delete Project
    $response = $this->delete(route('trash.projects.forceDelete', $project->id));
    $response->assertRedirect();
    $this->assertDatabaseMissing('projects', ['id' => $project->id]);

    // 3. Force delete Company
    $response = $this->delete(route('trash.companies.forceDelete', $company->id));
    $response->assertRedirect();
    $this->assertDatabaseMissing('companies', ['id' => $company->id]);
    $this->assertDatabaseMissing('company_users', ['company_id' => $company->id, 'user_id' => $user->id]);
});

it('prevents users from restoring or permanently deleting resources they do not own or administer', function () {
    $owner = User::factory()->create();
    $stranger = User::factory()->create();

    $project = Project::create([
        'name' => 'Private Project',
        'slug' => 'private-project',
        'user_id' => $owner->id,
        'company_id' => null,
    ]);

    $task = Task::create([
        'title' => 'Private Task',
        'project_id' => null,
        'user_id' => $owner->id,
    ]);

    $task->delete();
    $project->delete();

    $this->actingAs($stranger);

    // Try to restore project -> 403
    $response = $this->post(route('trash.projects.restore', $project->id));
    $response->assertStatus(403);

    // Try to force delete project -> 403
    $response = $this->delete(route('trash.projects.forceDelete', $project->id));
    $response->assertStatus(403);

    // Try to restore task -> 403
    $response = $this->post(route('trash.tasks.restore', $task->id));
    $response->assertStatus(403);

    // Try to force delete task -> 403
    $response = $this->delete(route('trash.tasks.forceDelete', $task->id));
    $response->assertStatus(403);
});

it('successfully runs the trash:prune console command', function () {
    $user = User::factory()->create();
    $task = Task::create([
        'title' => 'Prunable Task',
        'project_id' => null,
        'user_id' => $user->id,
    ]);

    $task->delete();

    $this->assertSoftDeleted('tasks', ['id' => $task->id]);

    // Run command
    $this->artisan('trash:prune')
        ->expectsOutputToContain('Starting prune process')
        ->expectsOutputToContain('Pruning complete')
        ->assertExitCode(0);

    // Verify task is permanently deleted
    $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
});

it('prevents restoring a task if its parent project is still in the trash', function () {
    $user = User::factory()->create();
    $project = Project::create([
        'name' => 'Trashed Parent Project',
        'slug' => 'trashed-parent-project',
        'user_id' => $user->id,
        'company_id' => null,
    ]);

    $task = Task::create([
        'title' => 'Child Task',
        'project_id' => $project->id,
        'user_id' => $user->id,
    ]);

    // Soft delete both
    $task->delete();
    $project->delete();

    $this->actingAs($user);

    // Try to restore task
    $response = $this->post(route('trash.tasks.restore', $task->id));
    $response->assertSessionHas('error', 'Cannot restore task because its project is in the trash. Please restore the project first.');
    $this->assertSoftDeleted('tasks', ['id' => $task->id]);
});

it('allows restoring projectless tasks from trash', function () {
    $user = User::factory()->create();
    $task = Task::create([
        'title' => 'Projectless Task',
        'project_id' => null,
        'user_id' => $user->id,
    ]);

    $task->delete();

    $this->actingAs($user);

    // Restore projectless task
    $response = $this->post(route('trash.tasks.restore', $task->id));
    $response->assertRedirect();
    $this->assertDatabaseHas('tasks', ['id' => $task->id, 'deleted_at' => null]);
});
