<?php

use App\Models\Note;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

it('requires authentication to search', function () {
    $response = $this->get(route('search.index', ['q' => 'test']));
    $response->assertRedirect(route('login'));
});

it('allows authenticated users to view search results page', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    $response = $this->actingAs($user)->get(route('search.index', ['q' => '']));

    $response->assertStatus(200);
    $response->assertSee('Search Results');
});

it('successfully searches for matching projects, tasks, and notes', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    $project = Project::create([
        'name' => 'Project Alpha Searchable',
        'slug' => 'project-alpha-searchable',
        'theme' => '#ff0000',
        'status' => 1,
        'priority' => 1,
        'user_id' => $user->id,
    ]);

    $task = Task::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'title' => 'Task Searchable Keyword',
        'description' => 'Specific content text',
        'status' => 1,
        'priority' => 2,
        'type' => 1,
    ]);

    $note = Note::create([
        'user_id' => $user->id,
        'title' => 'Note Searchable Keyword',
        'description' => 'Note details text',
        'note_type' => Note::TYPE_PERSONAL,
        'note_type_id' => $user->id,
    ]);

    // Query 'Searchable'
    $response = $this->actingAs($user)->get(route('search.index', ['q' => 'Searchable']));

    $response->assertStatus(200);
    $response->assertSee('Project Alpha Searchable');
    $response->assertSee('Task Searchable Keyword');
    $response->assertSee('Note Searchable Keyword');
});

it('returns JSON format for autocomplete AJAX request', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    $project = Project::create([
        'name' => 'UniqueProjectQuery',
        'slug' => 'uniqueprojectquery',
        'theme' => '#ff0000',
        'status' => 1,
        'priority' => 1,
        'user_id' => $user->id,
    ]);

    $response = $this->actingAs($user)->get(route('search.index', ['q' => 'UniqueProjectQuery']), [
        'X-Requested-With' => 'XMLHttpRequest',
        'Accept' => 'application/json',
    ]);

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'projects' => [
            '*' => ['id', 'title', 'url', 'theme'],
        ],
        'tasks',
        'notes',
        'users',
    ]);

    $response->assertJsonFragment([
        'title' => 'UniqueProjectQuery',
    ]);
});
