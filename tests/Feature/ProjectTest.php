<?php

use App\Models\User;
use App\Models\Project;

it('allows authenticated user to create a project with status and priority', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    // Simulate switching context to personal
    session(['current_company_id' => 'personal']);

    $response = $this->post(route('projects.store'), [
        'name' => 'New Project Title',
        'description' => 'A description of the new project.',
        'theme' => '#ff0000',
        'status' => 2, // In Progress
        'priority' => 3, // High
    ]);

    $response->assertRedirect(route('projects.index'));
    $this->assertDatabaseHas('projects', [
        'name' => 'New Project Title',
        'theme' => '#ff0000',
        'status' => 2,
        'priority' => 3,
        'user_id' => $user->id,
    ]);
});

it('allows authenticated user to update project status and priority', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    session(['current_company_id' => 'personal']);

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
    ]);

    $response->assertRedirect(route('projects.index'));
    $this->assertDatabaseHas('projects', [
        'id' => $project->id,
        'name' => 'Updated Project Title',
        'status' => 3,
        'priority' => 4,
    ]);
});
