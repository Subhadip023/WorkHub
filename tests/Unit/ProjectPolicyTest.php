<?php

use App\Models\Company;
use App\Models\Project;
use App\Models\User;
use App\Policies\ProjectPolicy;

// view and update project

// non company / personal project

test('user can view and update personal project', function () {
    $user = User::factory()->create();
    $project = createProject($user->id);
    $policy = new ProjectPolicy;
    $result = $policy->view($user, $project);
    expect($result)->toBeTrue();
    $result = $policy->update($user, $project);
    expect($result)->toBeTrue();
});
test('user can not view and update other personal project', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $project = createProject($user->id);
    $policy = new ProjectPolicy;
    $result = $policy->view($otherUser, $project);
    expect($result)->toBeFalse();
    $result = $policy->update($otherUser, $project);
    expect($result)->toBeFalse();
});

// company project

test('user can view and update own company project', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();
    createCompanyUser($company->id, $user->id);
    $project = createProject($user->id, $company->id);
    $policy = new ProjectPolicy;
    $result = $policy->view($user, $project);
    expect($result)->toBeTrue();
    $result = $policy->update($user, $project);
    expect($result)->toBeTrue();
});
test('user can not view and update other company project', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $company = Company::factory()->create();
    createCompanyUser($company->id, $user->id);
    $project = createProject($user->id, $company->id);
    $policy = new ProjectPolicy;
    $result = $policy->view($otherUser, $project);
    expect($result)->toBeFalse();
    $result = $policy->update($otherUser, $project);
    expect($result)->toBeFalse();
});

// delete project

// 1 Personal project
test('user can delete own personal project', function () {
    $user = User::factory()->create();
    $project = createProject($user->id);
    $policy = new ProjectPolicy;
    $result = $policy->delete($user, $project);
    expect($result)->toBeTrue();
});
test('user can not delete other personal project', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $project = createProject($otherUser->id);
    $policy = new ProjectPolicy;
    $result = $policy->delete($user, $project);
    expect($result)->toBeFalse();
});

// 2 Company project

test('admin can delete any company project', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();
    createCompanyUser($company->id, $user->id, 1);
    $project = createProject($user->id, $company->id);
    $policy = new ProjectPolicy;
    $result = $policy->delete($user, $project);
    expect($result)->toBeTrue();
});

test('user can not delete any company project', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();
    createCompanyUser($company->id, $user->id);
    $project = createProject($user->id, $company->id);
    $policy = new ProjectPolicy;
    $result = $policy->delete($user, $project);
    expect($result)->toBeFalse();
});

test('non company user can not delete any other company project', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $company = Company::factory()->create();
    createCompanyUser($company->id, $user->id);
    $project = createProject($user->id, $company->id);
    $policy = new ProjectPolicy;
    $result = $policy->delete($otherUser, $project);
    expect($result)->toBeFalse();
});
