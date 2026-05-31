<?php

use App\Models\Company;
use App\Models\CompanyUsers;
use App\Models\Project;
use App\Models\User;

it('loads default dashboard successfully showing all workspaces', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertStatus(200);
    $response->assertSee('All Workspaces');
});

it('loads organization-specific dashboard successfully for member', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $company = Company::create(['name' => 'Member Org', 'code' => 'MEMB']);
    CompanyUsers::create([
        'company_id' => $company->id,
        'user_id' => $user->id,
        'role' => 0,
    ]);

    $project = Project::create([
        'name' => 'Org Project Unique Name',
        'slug' => 'org-project-unique-name',
        'theme' => '#00ff00',
        'status' => 1,
        'priority' => 1,
        'company_id' => $company->id,
        'user_id' => $user->id,
    ]);

    $this->actingAs($user);

    $response = $this->get(route('dashboard.org', $company));
    $response->assertStatus(200);
    $response->assertSee('Member Org');
    $response->assertSee('Org Project Unique Name');
});

it('prevents non-members from accessing organization-specific dashboard', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $otherCompany = Company::create(['name' => 'Secret Org', 'code' => 'SECR']);

    $this->actingAs($user);

    $response = $this->get(route('dashboard.org', $otherCompany));
    $response->assertStatus(403);
});
