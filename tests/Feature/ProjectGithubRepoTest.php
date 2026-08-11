<?php

use App\Models\Project;
use App\Models\ProjectGithubRepo;
use App\Models\User;
use Illuminate\Support\Facades\DB;

it('allows authorized user to view project github repositories page', function () {
    $user = User::factory()->create();
    $project = Project::create([
        'name' => 'GitHub Test Project',
        'slug' => 'github-test-project',
        'theme' => '#0055ff',
        'status' => 1,
        'priority' => 1,
        'user_id' => $user->id,
        'company_id' => null,
    ]);

    $this->actingAs($user);

    $response = $this->get(route('projects.github', $project));

    $response->assertStatus(200)
        ->assertSee('Connected GitHub Repositories')
        ->assertSee($project->name);
});

it('allows authorized user to store a github repository connection for a project', function () {
    $user = User::factory()->create();
    $project = Project::create([
        'name' => 'GitHub Storage Project',
        'slug' => 'github-storage-project',
        'theme' => '#0055ff',
        'status' => 1,
        'priority' => 1,
        'user_id' => $user->id,
        'company_id' => null,
    ]);

    $this->actingAs($user);

    $response = $this->post(route('projects.github-repos.store', $project), [
        'repo_owner' => 'laravel',
        'repo_name' => 'framework',
        'access_token' => 'ghp_secret_token_12345',
        'webhook_secret' => 'super_secret_webhook_key',
        'auto_sync_issues' => '1',
        'is_active' => '1',
    ]);

    $response->assertRedirect(route('projects.github', $project));

    $this->assertDatabaseHas('project_github_repos', [
        'project_id' => $project->id,
        'user_id' => $user->id,
        'repo_owner' => 'laravel',
        'repo_name' => 'framework',
        'auto_sync_issues' => true,
        'is_active' => true,
    ]);

    $repo = ProjectGithubRepo::where('project_id', $project->id)->first();
    expect($repo)->not->toBeNull();
    expect($repo->repo_owner)->toBe('laravel');
    expect($repo->repo_name)->toBe('framework');
    expect($repo->access_token)->toBe('ghp_secret_token_12345');
    expect($repo->webhook_secret)->toBe('super_secret_webhook_key');

    // Verify secret is encrypted in database raw storage
    $rawRecord = DB::table('project_github_repos')->where('id', $repo->id)->first();
    expect($rawRecord->access_token)->not->toBe('ghp_secret_token_12345');
    expect($rawRecord->webhook_secret)->not->toBe('super_secret_webhook_key');
});

it('allows authorized user to update a github repository connection', function () {
    $user = User::factory()->create();
    $project = Project::create([
        'name' => 'GitHub Update Project',
        'slug' => 'github-update-project',
        'theme' => '#0055ff',
        'status' => 1,
        'priority' => 1,
        'user_id' => $user->id,
        'company_id' => null,
    ]);

    $repo = ProjectGithubRepo::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'repo_owner' => 'old_owner',
        'repo_name' => 'old_repo',
        'auto_sync_issues' => true,
        'is_active' => true,
    ]);

    $this->actingAs($user);

    $response = $this->patch(route('projects.github-repos.update', [$project, $repo]), [
        'repo_owner' => 'new_owner',
        'repo_name' => 'new_repo',
        'auto_sync_issues' => '0',
        'is_active' => '1',
    ]);

    $response->assertRedirect(route('projects.github', $project));

    $repo->refresh();
    expect($repo->repo_owner)->toBe('new_owner');
    expect($repo->repo_name)->toBe('new_repo');
    expect($repo->auto_sync_issues)->toBeFalse();
    expect($repo->is_active)->toBeTrue();
});

it('allows authorized user to disconnect a github repository connection', function () {
    $user = User::factory()->create();
    $project = Project::create([
        'name' => 'GitHub Delete Project',
        'slug' => 'github-delete-project',
        'theme' => '#0055ff',
        'status' => 1,
        'priority' => 1,
        'user_id' => $user->id,
        'company_id' => null,
    ]);

    $repo = ProjectGithubRepo::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'repo_owner' => 'delete_owner',
        'repo_name' => 'delete_repo',
        'auto_sync_issues' => true,
        'is_active' => true,
    ]);

    $this->actingAs($user);

    $response = $this->delete(route('projects.github-repos.destroy', [$project, $repo]));

    $response->assertRedirect(route('projects.github', $project));

    $this->assertSoftDeleted('project_github_repos', [
        'id' => $repo->id,
    ]);
});

it('prevents unauthorized user from managing project github repositories', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();

    $project = Project::create([
        'name' => 'Private Project',
        'slug' => 'private-project',
        'theme' => '#0055ff',
        'status' => 1,
        'priority' => 1,
        'user_id' => $owner->id,
        'company_id' => null,
    ]);

    $this->actingAs($otherUser);

    $response = $this->get(route('projects.github', $project));
    $response->assertStatus(403);

    $storeResponse = $this->post(route('projects.github-repos.store', $project), [
        'repo_owner' => 'hacker',
        'repo_name' => 'exploit',
    ]);
    $storeResponse->assertStatus(403);
});
