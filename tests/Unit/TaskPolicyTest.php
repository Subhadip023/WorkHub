<?php

use App\Models\Company;
use App\Models\CompanyUsers;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Policies\TaskPolicy;

test('owner can update their own personal task', function () {
    $user = User::factory()->create();
    $task = Task::create([
        'title' => 'my task',
        'project_id' => null,
        'user_id' => $user->id,
    ]);
    $policy = new TaskPolicy;

    $result = $policy->update($user, $task);

    expect($result)->toBeTrue();

});

// Unrelated user cannot update
test('Unrelated user cannot update task', function () {
    $user = User::factory()->create();
    $user2 = User::factory()->create();
    $task = Task::create([
        'title' => 'my task',
        'project_id' => null,
        'user_id' => $user->id,
    ]);
    $policy = new TaskPolicy;

    $result = $policy->update($user2, $task);

    expect($result)->toBeFalse();

});

// personal project
test('personal project owner can update task ', function () {
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
        'title' => 'my task',
        'project_id' => $project->id,
        'user_id' => $user->id,
    ]);
    $policy = new TaskPolicy;
    $result = $policy->update($user, $task);
    expect($result)->toBeTrue();
});

test('user cannot update other personal project task', function () {
    $user = User::factory()->create();
    $user2 = User::factory()->create();
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
        'title' => 'my task',
        'project_id' => $project->id,
        'user_id' => $user->id,
    ]);
    $policy = new TaskPolicy;
    $result = $policy->update($user2, $task);
    expect($result)->toBeFalse();
});

test('user can update other personal project task if assigned to them', function () {
    $user = User::factory()->create();
    $user2 = User::factory()->create();
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
        'title' => 'my task',
        'project_id' => $project->id,
        'user_id' => $user->id,
        'assigned_to' => $user2->id,
    ]);
    $policy = new TaskPolicy;
    $result = $policy->update($user2, $task);
    expect($result)->toBeTrue();
});

// company

test('company admin can update any task in their company', function () {
    $user = User::factory()->create();
    $user2 = User::factory()->create();
    $company = Company::factory()->create();
    $project = Project::create([
        'name' => 'Company Project',
        'slug' => 'company-project',
        'theme' => '#ff0000',
        'status' => 1,
        'priority' => 1,
        'user_id' => $user2->id,
        'company_id' => $company->id,
    ]);
    CompanyUsers::create([
        'company_id' => $company->id,
        'user_id' => $user->id,
        'role' => 1,
        'is_approved' => true,
    ]);
    $task = Task::create([
        'title' => 'my task',
        'project_id' => $project->id,
        'user_id' => $user2->id,
    ]);
    $policy = new TaskPolicy;
    $result = $policy->update($user, $task);
    expect($result)->toBeTrue();
});

//  Company — Member, not creator/assignee — false

test('company member who is not creator or assignee cannot update task', function () {
    $user = User::factory()->create();
    $user2 = User::factory()->create();
    $company = Company::factory()->create();
    $project = Project::create([
        'name' => 'Company Project',
        'slug' => 'company-project',
        'theme' => '#ff0000',
        'status' => 1,
        'priority' => 1,
        'user_id' => $user2->id,
        'company_id' => $company->id,
    ]);
    CompanyUsers::create([
        'company_id' => $company->id,
        'user_id' => $user->id,
        'role' => 0,
        'is_approved' => true,
    ]);
    $task = Task::create([
        'title' => 'my task',
        'project_id' => $project->id,
        'user_id' => $user2->id,
    ]);
    $policy = new TaskPolicy;
    $result = $policy->update($user, $task);
    expect($result)->toBeFalse();
});

// Company — Member who is assignee — true

test('company member who is assignee can update any task in their company', function () {
    $user = User::factory()->create();
    $user2 = User::factory()->create();
    $company = Company::factory()->create();
    $project = Project::create([
        'name' => 'Company Project',
        'slug' => 'company-project',
        'theme' => '#ff0000',
        'status' => 1,
        'priority' => 1,
        'user_id' => $user2->id,
        'company_id' => $company->id,
    ]);
    CompanyUsers::create([
        'company_id' => $company->id,
        'user_id' => $user->id,
        'role' => 0,
        'is_approved' => true,
    ]);
    $task = Task::create([
        'title' => 'my task',
        'project_id' => $project->id,
        'user_id' => $user2->id,
        'assigned_to' => $user->id,
    ]);
    $policy = new TaskPolicy;
    $result = $policy->update($user, $task);
    expect($result)->toBeTrue();
});

// Company — Member who is creator — true

test('company member who is creator can update own task', function () {
    $user = User::factory()->create();
    $user2 = User::factory()->create();
    $company = Company::factory()->create();
    $project = Project::create([
        'name' => 'Company Project',
        'slug' => 'company-project',
        'theme' => '#ff0000',
        'status' => 1,
        'priority' => 1,
        'user_id' => $user->id,
        'company_id' => $company->id,
    ]);
    CompanyUsers::create([
        'company_id' => $company->id,
        'user_id' => $user->id,
        'role' => 0,
        'is_approved' => true,
    ]);
    CompanyUsers::create([
        'company_id' => $company->id,
        'user_id' => $user2->id,
        'role' => 0,
        'is_approved' => true,
    ]);
    $task = Task::create([
        'title' => 'my task',
        'project_id' => $project->id,
        'user_id' => $user->id,
        'assigned_to' => $user2->id,
    ]);
    $policy = new TaskPolicy;
    $result = $policy->update($user, $task);
    expect($result)->toBeTrue();
});

// Company — Non-member entirely — false

test('company non-member cannot update task', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();
    $project = Project::create([
        'name' => 'Company Project',
        'slug' => 'company-project',
        'theme' => '#ff0000',
        'status' => 1,
        'priority' => 1,
        'user_id' => $user->id,
        'company_id' => $company->id,
    ]);
    $task = Task::create([
        'title' => 'my task',
        'project_id' => $project->id,
        'user_id' => $user->id,
    ]);
    $policy = new TaskPolicy;
    $result = $policy->update($user, $task);
    expect($result)->toBeFalse();
});

// ========================================
// view() tests
// ========================================
/*
Scope 1 — Personal task (no project):

Task creator can view — true
Task assignee can view — true
Unrelated user cannot view — false

Scope 2 — Personal project:

Project owner can view — true
Task assignee (not owner) can view — true
Unrelated user cannot view — false

Scope 3 — Company project:

Company member can view — true
Non-member cannot view — false

*/
// personal tasks
test('personal task creator can view', function () {
    $user = User::factory()->create();
    $task = Task::create([
        'title' => 'my task',
        'project_id' => null,
        'user_id' => $user->id,
    ]);
    $policy = new TaskPolicy;
    $result = $policy->view($user, $task);
    expect($result)->toBeTrue();
});

test('personal task assignee can view', function () {
    $user = User::factory()->create();
    $user2 = User::factory()->create();
    $task = Task::create([
        'title' => 'my task',
        'project_id' => null,
        'assigned_to' => $user->id,
        'user_id' => $user2->id,
    ]);
    $policy = new TaskPolicy;
    $result = $policy->view($user, $task);
    expect($result)->toBeTrue();
});

test('personal task non-assignee or non-creator can not view', function () {
    $user = User::factory()->create();
    $user2 = User::factory()->create();
    $user3 = User::factory()->create();
    $task = Task::create([
        'title' => 'my task',
        'project_id' => null,
        'assigned_to' => $user->id,
        'user_id' => $user2->id,
    ]);
    $policy = new TaskPolicy;
    $result = $policy->view($user3, $task);
    expect($result)->toBeFalse();
});

// personal project tasks
test('personal project owner can view', function () {
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
        'title' => 'my task',
        'project_id' => $project->id,
        'user_id' => $user->id,
    ]);
    $policy = new TaskPolicy;
    $result = $policy->view($user, $task);
    expect($result)->toBeTrue();
});
test('personal project assignee can view', function () {
    $user = User::factory()->create();
    $assignee = User::factory()->create();
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
        'title' => 'my task',
        'project_id' => $project->id,
        'user_id' => $user->id,
        'assigned_to' => $assignee->id,
    ]);
    $policy = new TaskPolicy;
    $result = $policy->view($assignee, $task);
    expect($result)->toBeTrue();
});
test('non-related user cannot view', function () {
    $user = User::factory()->create();
    $assignee = User::factory()->create();
    $other = User::factory()->create();
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
        'title' => 'my task',
        'project_id' => $project->id,
        'user_id' => $user->id,
        'assigned_to' => $assignee->id,
    ]);
    $policy = new TaskPolicy;
    $result = $policy->view($other, $task);
    expect($result)->toBeFalse();
});

// company project tasks

test('company user can view the task', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();
    CompanyUsers::create([
        'company_id' => $company->id,
        'user_id' => $user->id,
        'role' => 0,
        'is_approved' => true,
    ]);
    $project = Project::create([
        'name' => 'Company Project',
        'slug' => 'company-project',
        'theme' => '#ff0000',
        'status' => 1,
        'priority' => 1,
        'user_id' => $user->id,
        'company_id' => $company->id,
    ]);
    $task = Task::create([
        'title' => 'my task',
        'project_id' => $project->id,
        'user_id' => $user->id,
    ]);
    $policy = new TaskPolicy;
    $result = $policy->view($user, $task);
    expect($result)->toBeTrue();
});

test('company non-member can not view the task', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();
    CompanyUsers::create([
        'company_id' => $company->id,
        'user_id' => $user->id,
        'role' => 0,
        'is_approved' => true,
    ]);
    $other_user = User::factory()->create();
    $project = Project::create([
        'name' => 'Company Project',
        'slug' => 'company-project',
        'theme' => '#ff0000',
        'status' => 1,
        'priority' => 1,
        'user_id' => $user->id,
        'company_id' => $company->id,
    ]);
    $task = Task::create([
        'title' => 'my task',
        'project_id' => $project->id,
        'user_id' => $user->id,
    ]);
    $policy = new TaskPolicy;
    $result = $policy->view($other_user, $task);
    expect($result)->toBeFalse();
});
