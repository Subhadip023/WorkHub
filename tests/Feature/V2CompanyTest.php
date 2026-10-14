<?php

use App\Mail\InviteMember;
use App\Models\Company;
use App\Models\CompanyUsers;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

it('loads companies v2 page with Inertia component and correct props', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $company = Company::create(['name' => 'Acme Technologies', 'code' => 'ACME']);
    CompanyUsers::create([
        'company_id' => $company->id,
        'user_id' => $user->id,
        'role' => 1,
        'is_approved' => true,
    ]);

    $response = $this->actingAs($user)->get(route('v2.companies.index'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('New/Companies')
        ->has('companies')
        ->has('stats')
        ->has('current_workspace_name')
        ->where('stats.total_organizations', 1)
        ->where('stats.admin_organizations_count', 1)
    );
});

it('allows creating a company in v2', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    $response = $this->actingAs($user)->post(route('v2.companies.store'), [
        'name' => 'Future Corp',
    ]);

    $company = Company::where('name', 'Future Corp')->first();
    expect($company)->not->toBeNull();
    expect(strlen($company->code))->toBe(4);

    $response->assertRedirect(route('v2.companies.index', ['company' => $company->id]));

    $this->assertDatabaseHas('company_users', [
        'company_id' => $company->id,
        'user_id' => $user->id,
        'role' => 1,
        'is_approved' => true,
    ]);

    expect(session('current_company_id'))->toBe($company->id);
});

it('allows joining a company in v2 with code', function () {
    $admin = User::factory()->create(['email_verified_at' => now()]);
    $user = User::factory()->create(['email_verified_at' => now()]);

    $company = Company::create(['name' => 'Stark Industries', 'code' => 'STRK']);
    CompanyUsers::create([
        'company_id' => $company->id,
        'user_id' => $admin->id,
        'role' => 1,
        'is_approved' => true,
    ]);

    $response = $this->actingAs($user)->post(route('v2.companies.join'), [
        'code' => 'STRK',
    ]);

    $response->assertRedirect(route('v2.companies.index'));
    $this->assertDatabaseHas('company_users', [
        'company_id' => $company->id,
        'user_id' => $user->id,
        'role' => 0,
        'is_approved' => false,
    ]);
});

it('allows switching workspace in v2', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $company = Company::create(['name' => 'Wayne Enterprises', 'code' => 'WAYN']);
    CompanyUsers::create([
        'company_id' => $company->id,
        'user_id' => $user->id,
        'role' => 1,
        'is_approved' => true,
    ]);

    $response = $this->actingAs($user)->get(route('v2.companies.switch', $company));

    $response->assertRedirect(route('v2.companies.index', ['company' => $company->id]));
    expect(session('current_company_id'))->toBe($company->id);
});

it('allows switching to personal space in v2', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    session(['current_company_id' => 99]);

    $response = $this->actingAs($user)->get(route('v2.companies.personal.switch'));

    $response->assertRedirect(route('v2.companies.index'));
    expect(session('current_company_id'))->toBe('personal');
});

it('allows admin to update company name in v2', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $company = Company::create(['name' => 'Old Name Corp', 'code' => 'ONC1']);
    CompanyUsers::create([
        'company_id' => $company->id,
        'user_id' => $user->id,
        'role' => 1,
        'is_approved' => true,
    ]);

    $response = $this->actingAs($user)->patch(route('v2.companies.update', $company), [
        'name' => 'Renamed Corp',
    ]);

    $response->assertRedirect(route('v2.companies.index', ['company' => $company->id]));
    $this->assertDatabaseHas('companies', [
        'id' => $company->id,
        'name' => 'Renamed Corp',
    ]);
});

it('allows admin to delete company in v2', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $company = Company::create(['name' => 'Delete Me Corp', 'code' => 'DEL1']);
    CompanyUsers::create([
        'company_id' => $company->id,
        'user_id' => $user->id,
        'role' => 1,
        'is_approved' => true,
    ]);

    $response = $this->actingAs($user)->delete(route('v2.companies.destroy', $company));

    $response->assertRedirect(route('v2.companies.index'));
    $this->assertSoftDeleted('companies', [
        'id' => $company->id,
    ]);
});

it('allows member to leave company in v2', function () {
    $admin = User::factory()->create(['email_verified_at' => now()]);
    $member = User::factory()->create(['email_verified_at' => now()]);

    $company = Company::create(['name' => 'Leaving Corp', 'code' => 'LVE1']);
    CompanyUsers::create([
        'company_id' => $company->id,
        'user_id' => $admin->id,
        'role' => 1,
        'is_approved' => true,
    ]);
    CompanyUsers::create([
        'company_id' => $company->id,
        'user_id' => $member->id,
        'role' => 0,
        'is_approved' => true,
    ]);

    $response = $this->actingAs($member)->post(route('v2.companies.leave', $company));

    $response->assertRedirect(route('v2.companies.index'));
    $this->assertSoftDeleted('company_users', [
        'company_id' => $company->id,
        'user_id' => $member->id,
    ]);
});

it('allows admin to approve member join request in v2', function () {
    $admin = User::factory()->create(['email_verified_at' => now()]);
    $member = User::factory()->create(['email_verified_at' => now()]);

    $company = Company::create(['name' => 'Approve Corp', 'code' => 'APPR']);
    CompanyUsers::create([
        'company_id' => $company->id,
        'user_id' => $admin->id,
        'role' => 1,
        'is_approved' => true,
    ]);
    CompanyUsers::create([
        'company_id' => $company->id,
        'user_id' => $member->id,
        'role' => 0,
        'is_approved' => false,
    ]);

    $response = $this->actingAs($admin)->post(route('v2.companies.approve-member', [$company, $member]));

    $response->assertSessionHas('success');
    $this->assertDatabaseHas('company_users', [
        'company_id' => $company->id,
        'user_id' => $member->id,
        'is_approved' => true,
    ]);
});

it('allows admin to reject member join request in v2', function () {
    $admin = User::factory()->create(['email_verified_at' => now()]);
    $member = User::factory()->create(['email_verified_at' => now()]);

    $company = Company::create(['name' => 'Reject Corp', 'code' => 'REJC']);
    CompanyUsers::create([
        'company_id' => $company->id,
        'user_id' => $admin->id,
        'role' => 1,
        'is_approved' => true,
    ]);
    CompanyUsers::create([
        'company_id' => $company->id,
        'user_id' => $member->id,
        'role' => 0,
        'is_approved' => false,
    ]);

    $response = $this->actingAs($admin)->post(route('v2.companies.reject-member-request', [$company, $member]));

    $response->assertSessionHas('info');
    $this->assertDatabaseMissing('company_users', [
        'company_id' => $company->id,
        'user_id' => $member->id,
        'deleted_at' => null,
    ]);
});

it('allows admin to send invitation in v2', function () {
    Mail::fake();

    $admin = User::factory()->create(['email_verified_at' => now()]);
    $company = Company::create(['name' => 'Invite Corp', 'code' => 'INVT']);
    CompanyUsers::create([
        'company_id' => $company->id,
        'user_id' => $admin->id,
        'role' => 1,
        'is_approved' => true,
    ]);

    $response = $this->actingAs($admin)->post(route('v2.companies.invite', $company), [
        'email' => 'partner@workhub.io',
        'message' => 'Join our awesome workspace!',
    ]);

    $response->assertSessionHas('success');
    $this->assertDatabaseHas('company_invitations', [
        'company_id' => $company->id,
        'email' => 'partner@workhub.io',
    ]);

    Mail::assertSent(InviteMember::class, function ($mail) {
        return $mail->hasTo('partner@workhub.io');
    });
});

it('returns member activity json in v2', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $company = Company::create(['name' => 'Activity Corp', 'code' => 'ACT1']);
    CompanyUsers::create([
        'company_id' => $company->id,
        'user_id' => $user->id,
        'role' => 1,
        'is_approved' => true,
    ]);

    $response = $this->actingAs($user)->getJson(route('v2.companies.members.activity', [$company, $user]));

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'success',
        'user' => ['id', 'name', 'email', 'last_login_at'],
        'activities',
    ]);
});

it('redirects new.companies alias to v2.companies', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    $response = $this->actingAs($user)->get(route('new.companies'));

    $response->assertRedirect(route('v2.companies.index'));
});
