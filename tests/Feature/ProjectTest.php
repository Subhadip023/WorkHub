<?php

use App\Models\User;
use App\Models\Project;
use App\Models\Company;
use App\Models\CompanyUsers;

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
