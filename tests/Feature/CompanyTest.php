<?php

use App\Models\Company;
use App\Models\CompanyUsers;
use App\Models\User;

it('allows authenticated user to list their companies', function () {
    $user = User::factory()->create();
    $company = Company::create(['name' => 'My Org', 'code' => 'ABCD']);
    CompanyUsers::create([
        'company_id' => $company->id,
        'user_id' => $user->id,
        'role' => 1,
    ]);

    $this->actingAs($user);
    $response = $this->get(route('companies.index'));
    $response->assertStatus(200);
    $response->assertSee('My Org');
});

it('allows authenticated user to view the create company page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('companies.create'));
    $response->assertStatus(200);
});

it('allows authenticated user to create a company', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->post(route('companies.store'), [
        'name' => 'Brand New Org',
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertDatabaseHas('companies', [
        'name' => 'Brand New Org',
    ]);

    $company = Company::where('name', 'Brand New Org')->first();
    $this->assertDatabaseHas('company_users', [
        'company_id' => $company->id,
        'user_id' => $user->id,
        'role' => 1, // Admin
    ]);

    expect(session('current_company_id'))->toBe($company->id);
});

it('allows company admin to update company name', function () {
    $user = User::factory()->create();
    $company = Company::create(['name' => 'Old Org Name', 'code' => 'ABCD']);
    CompanyUsers::create([
        'company_id' => $company->id,
        'user_id' => $user->id,
        'role' => 1, // Admin
    ]);

    $this->actingAs($user);

    $response = $this->patch(route('companies.update', $company), [
        'name' => 'New Org Name',
    ]);

    $response->assertRedirect(route('companies.index'));
    $this->assertDatabaseHas('companies', [
        'id' => $company->id,
        'name' => 'New Org Name',
    ]);
});

it('allows company admin to delete the company', function () {
    $user = User::factory()->create();
    $company = Company::create(['name' => 'To Be Deleted', 'code' => 'ABCD']);
    CompanyUsers::create([
        'company_id' => $company->id,
        'user_id' => $user->id,
        'role' => 1, // Admin
    ]);

    $this->actingAs($user);

    $response = $this->delete(route('companies.destroy', $company));

    $response->assertRedirect(route('companies.index'));
    $this->assertDatabaseMissing('companies', [
        'id' => $company->id,
    ]);
    $this->assertDatabaseMissing('company_users', [
        'company_id' => $company->id,
    ]);
});

it('prevents non-admin from deleting the company', function () {
    $user = User::factory()->create();
    $company = Company::create(['name' => 'Safe Org', 'code' => 'ABCD']);
    CompanyUsers::create([
        'company_id' => $company->id,
        'user_id' => $user->id,
        'role' => 0, // Member
    ]);

    $this->actingAs($user);

    $response = $this->delete(route('companies.destroy', $company));

    $response->assertStatus(403);
    $this->assertDatabaseHas('companies', [
        'id' => $company->id,
    ]);
});

it('allows user to join a company using a valid code', function () {
    $user = User::factory()->create();
    $company = Company::create(['name' => 'Joinable Org', 'code' => 'JOIN']);

    $this->actingAs($user);

    $response = $this->post(route('companies.join'), [
        'code' => 'JOIN',
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertDatabaseHas('company_users', [
        'company_id' => $company->id,
        'user_id' => $user->id,
        'role' => 0, // Member
    ]);
});

it('allows user to switch active company context', function () {
    $user = User::factory()->create();
    $company = Company::create(['name' => 'Switchable Org', 'code' => 'SWIT']);
    CompanyUsers::create([
        'company_id' => $company->id,
        'user_id' => $user->id,
        'role' => 0,
    ]);

    $this->actingAs($user);

    $response = $this->get(route('companies.switch', $company));
    $response->assertRedirect(route('dashboard'));
    expect(session('current_company_id'))->toBe($company->id);
});

it('allows user to switch back to personal space', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('personal.switch'));
    $response->assertRedirect(route('dashboard'));
    expect(session('current_company_id'))->toBe('personal');
});
