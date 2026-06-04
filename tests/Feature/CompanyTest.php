<?php

use App\Mail\InviteMember;
use App\Models\Company;
use App\Models\CompanyInvitation;
use App\Models\CompanyUsers;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

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
    $admin = User::factory()->create();
    $user = User::factory()->create();
    $company = Company::create(['name' => 'Joinable Org', 'code' => 'JOIN']);

    CompanyUsers::create([
        'company_id' => $company->id,
        'user_id' => $admin->id,
        'role' => 1,
        'is_approved' => true,
    ]);

    $this->actingAs($user);

    $response = $this->post(route('companies.join'), [
        'code' => 'JOIN',
    ]);

    $response->assertRedirect(route('companies.index'));
    $this->assertDatabaseHas('company_users', [
        'company_id' => $company->id,
        'user_id' => $user->id,
        'role' => 0, // Member
        'is_approved' => false,
    ]);

    $this->assertDatabaseHas('notifications', [
        'user_id' => $admin->id,
        'type' => 'join_request',
        'title' => 'New Join Request',
        'organization_id' => $company->id,
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

it('displays the company show page with correct member task counts', function () {
    $user = User::factory()->create();
    $company = Company::create(['name' => 'Metrics Org', 'code' => 'METR']);
    CompanyUsers::create([
        'company_id' => $company->id,
        'user_id' => $user->id,
        'role' => 1,
    ]);

    $project = Project::create([
        'name' => 'Company Project',
        'slug' => 'company-project',
        'company_id' => $company->id,
        'user_id' => $user->id,
    ]);

    // Task 1: Completed (status 3)
    $project->tasks()->create([
        'title' => 'Completed Task',
        'assigned_to' => $user->id,
        'status' => 3,
    ]);

    // Task 2: Pending (status 1)
    $project->tasks()->create([
        'title' => 'Pending Task',
        'assigned_to' => $user->id,
        'status' => 1,
    ]);

    // Create another company and a task in it (should not be counted)
    $otherCompany = Company::create(['name' => 'Other Org', 'code' => 'OTHR']);
    $otherProject = Project::create([
        'name' => 'Other Project',
        'slug' => 'other-project',
        'company_id' => $otherCompany->id,
        'user_id' => $user->id,
    ]);
    $otherProject->tasks()->create([
        'title' => 'Other Task',
        'assigned_to' => $user->id,
        'status' => 3,
    ]);

    $this->actingAs($user);
    $response = $this->get(route('companies.show', $company));

    $response->assertStatus(200);
    // It should display '1/2' (1 completed out of 2 total)
    $response->assertSee('1/2');
    $response->assertSee('50%');
});

it('allows company admin to remove a member and unassign their tasks', function () {
    $admin = User::factory()->create();
    $member = User::factory()->create();
    $company = Company::create(['name' => 'Test Org', 'code' => 'TEST']);

    CompanyUsers::create([
        'company_id' => $company->id,
        'user_id' => $admin->id,
        'role' => 1, // Admin
    ]);

    CompanyUsers::create([
        'company_id' => $company->id,
        'user_id' => $member->id,
        'role' => 0, // Member
    ]);

    $project = Project::create([
        'name' => 'Project',
        'slug' => 'project',
        'company_id' => $company->id,
        'user_id' => $admin->id,
    ]);

    $task = $project->tasks()->create([
        'title' => 'Assigned Task',
        'assigned_to' => $member->id,
        'status' => 1,
    ]);

    $this->actingAs($admin);

    $response = $this->delete(route('companies.members.destroy', [$company, $member]));

    $response->assertRedirect();
    $this->assertDatabaseMissing('company_users', [
        'company_id' => $company->id,
        'user_id' => $member->id,
    ]);

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'assigned_to' => null,
    ]);
});

it('prevents non-admin from removing a member', function () {
    $admin = User::factory()->create();
    $member1 = User::factory()->create();
    $member2 = User::factory()->create();
    $company = Company::create(['name' => 'Test Org', 'code' => 'TEST']);

    CompanyUsers::create([
        'company_id' => $company->id,
        'user_id' => $admin->id,
        'role' => 1,
    ]);

    CompanyUsers::create([
        'company_id' => $company->id,
        'user_id' => $member1->id,
        'role' => 0,
    ]);

    CompanyUsers::create([
        'company_id' => $company->id,
        'user_id' => $member2->id,
        'role' => 0,
    ]);

    $this->actingAs($member1);

    $response = $this->delete(route('companies.members.destroy', [$company, $member2]));

    $response->assertStatus(403);
    $this->assertDatabaseHas('company_users', [
        'company_id' => $company->id,
        'user_id' => $member2->id,
    ]);
});

it('prevents admin from removing themselves', function () {
    $admin = User::factory()->create();
    $company = Company::create(['name' => 'Test Org', 'code' => 'TEST']);

    CompanyUsers::create([
        'company_id' => $company->id,
        'user_id' => $admin->id,
        'role' => 1,
    ]);

    $this->actingAs($admin);

    $response = $this->delete(route('companies.members.destroy', [$company, $admin]));

    $response->assertRedirect();
    $this->assertDatabaseHas('company_users', [
        'company_id' => $company->id,
        'user_id' => $admin->id,
    ]);
});

it('allows company admin to invite a team member via email', function () {
    Mail::fake();

    $admin = User::factory()->create();
    $company = Company::create(['name' => 'Invite Org', 'code' => 'INVT']);

    CompanyUsers::create([
        'company_id' => $company->id,
        'user_id' => $admin->id,
        'role' => 1, // Admin
    ]);

    $this->actingAs($admin);

    $response = $this->post(route('companies.invite', $company), [
        'email' => 'invitee@example.com',
        'message' => 'Join our awesome team!',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    Mail::assertSent(function (InviteMember $mail) {
        return $mail->hasTo('invitee@example.com') && $mail->companyName === 'Invite Org';
    });
});

it('stores invitation in the database when company admin invites a team member', function () {
    Mail::fake();

    $admin = User::factory()->create();
    $company = Company::create(['name' => 'Invite Org', 'code' => 'INVT']);

    CompanyUsers::create([
        'company_id' => $company->id,
        'user_id' => $admin->id,
        'role' => 1, // Admin
    ]);

    $this->actingAs($admin);

    $response = $this->post(route('companies.invite', $company), [
        'email' => 'invitee@example.com',
        'message' => 'Join our awesome team!',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('company_invitations', [
        'company_id' => $company->id,
        'email' => 'invitee@example.com',
    ]);
});

it('prevents inviting an email that is already a member of the company', function () {
    Mail::fake();

    $admin = User::factory()->create();
    $member = User::factory()->create(['email' => 'member@example.com']);
    $company = Company::create(['name' => 'Invite Org', 'code' => 'INVT']);

    CompanyUsers::create([
        'company_id' => $company->id,
        'user_id' => $admin->id,
        'role' => 1, // Admin
    ]);

    CompanyUsers::create([
        'company_id' => $company->id,
        'user_id' => $member->id,
        'role' => 0, // Member
    ]);

    $this->actingAs($admin);

    $response = $this->post(route('companies.invite', $company), [
        'email' => 'member@example.com',
        'message' => 'Join our awesome team!',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error', 'User with email member@example.com is already a member of this organization.');

    $this->assertDatabaseMissing('company_invitations', [
        'company_id' => $company->id,
        'email' => 'member@example.com',
    ]);
});

it('renders the invitations modal on dashboard if user has pending invitations', function () {
    $invitedUser = User::factory()->create(['email' => 'invitee@example.com']);
    $company = Company::create(['name' => 'Invite Org', 'code' => 'INVT']);

    CompanyInvitation::create([
        'company_id' => $company->id,
        'email' => 'invitee@example.com',
    ]);

    $this->actingAs($invitedUser);

    $response = $this->get(route('dashboard'));
    $response->assertStatus(200);
    $response->assertSee('Workspace Invitation');
    $response->assertSee('Invite Org');
});

it('allows user to accept a company invitation', function () {
    $invitedUser = User::factory()->create(['email' => 'invitee@example.com']);
    $company = Company::create(['name' => 'Invite Org', 'code' => 'INVT']);

    $invitation = CompanyInvitation::create([
        'company_id' => $company->id,
        'email' => 'invitee@example.com',
    ]);

    $this->actingAs($invitedUser);

    $response = $this->post(route('invitations.accept', $invitation));
    $response->assertRedirect(route('dashboard'));

    $this->assertDatabaseHas('company_users', [
        'company_id' => $company->id,
        'user_id' => $invitedUser->id,
        'role' => 0,
    ]);

    $this->assertDatabaseMissing('company_invitations', [
        'id' => $invitation->id,
    ]);

    expect(session('current_company_id'))->toBe($company->id);
});

it('allows user to reject a company invitation', function () {
    $invitedUser = User::factory()->create(['email' => 'invitee@example.com']);
    $company = Company::create(['name' => 'Invite Org', 'code' => 'INVT']);

    $invitation = CompanyInvitation::create([
        'company_id' => $company->id,
        'email' => 'invitee@example.com',
    ]);

    $this->actingAs($invitedUser);

    $response = $this->post(route('invitations.reject', $invitation));
    $response->assertRedirect();

    $this->assertDatabaseMissing('company_users', [
        'company_id' => $company->id,
        'user_id' => $invitedUser->id,
    ]);

    $this->assertDatabaseMissing('company_invitations', [
        'id' => $invitation->id,
    ]);
});

it('prevents user from switching context to an unapproved company', function () {
    $user = User::factory()->create();
    $company = Company::create(['name' => 'Pending Org', 'code' => 'PEND']);
    CompanyUsers::create([
        'company_id' => $company->id,
        'user_id' => $user->id,
        'role' => 0,
        'is_approved' => false,
    ]);

    $this->actingAs($user);

    $response = $this->get(route('companies.switch', $company));
    $response->assertStatus(403);
});

it('allows company admin to approve a join request', function () {
    $admin = User::factory()->create();
    $user = User::factory()->create();
    $company = Company::create(['name' => 'Join Org', 'code' => 'JOIN']);

    CompanyUsers::create([
        'company_id' => $company->id,
        'user_id' => $admin->id,
        'role' => 1, // Admin
        'is_approved' => true,
    ]);

    CompanyUsers::create([
        'company_id' => $company->id,
        'user_id' => $user->id,
        'role' => 0, // Member
        'is_approved' => false, // Pending
    ]);

    $this->actingAs($admin);

    $response = $this->post(route('companies.approve-member', [$company, $user]));
    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('company_users', [
        'company_id' => $company->id,
        'user_id' => $user->id,
        'is_approved' => true,
    ]);

    $this->assertDatabaseHas('notifications', [
        'user_id' => $user->id,
        'type' => 'join_approved',
        'title' => 'Join Request Approved',
    ]);
});

it('allows company admin to reject a join request', function () {
    $admin = User::factory()->create();
    $user = User::factory()->create();
    $company = Company::create(['name' => 'Join Org', 'code' => 'JOIN']);

    CompanyUsers::create([
        'company_id' => $company->id,
        'user_id' => $admin->id,
        'role' => 1, // Admin
        'is_approved' => true,
    ]);

    CompanyUsers::create([
        'company_id' => $company->id,
        'user_id' => $user->id,
        'role' => 0, // Member
        'is_approved' => false, // Pending
    ]);

    $this->actingAs($admin);

    $response = $this->post(route('companies.reject-member-request', [$company, $user]));
    $response->assertRedirect();
    $response->assertSessionHas('info');

    $this->assertDatabaseMissing('company_users', [
        'company_id' => $company->id,
        'user_id' => $user->id,
    ]);

    $this->assertDatabaseHas('notifications', [
        'user_id' => $user->id,
        'type' => 'join_rejected',
        'title' => 'Join Request Rejected',
    ]);
});

it('allows a member to leave the company and unassigns tasks', function () {
    $user = User::factory()->create();
    $company = Company::create(['name' => 'Leave Org', 'code' => 'LEAV']);
    CompanyUsers::create([
        'company_id' => $company->id,
        'user_id' => $user->id,
        'role' => 0, // Member
        'is_approved' => true,
    ]);

    $project = Project::create([
        'name' => 'Proj 1',
        'slug' => 'proj-1',
        'company_id' => $company->id,
        'user_id' => $user->id,
    ]);

    $task = Task::create([
        'title' => 'Task 1',
        'project_id' => $project->id,
        'assigned_to' => $user->id,
        'status' => 1,
        'priority' => 1,
        'type' => 1,
    ]);

    $this->actingAs($user);
    session(['current_company_id' => $company->id]);

    $response = $this->post(route('companies.leave', $company));
    $response->assertRedirect(route('companies.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseMissing('company_users', [
        'company_id' => $company->id,
        'user_id' => $user->id,
    ]);

    $task->refresh();
    expect($task->assigned_to)->toBeNull();
    expect(session('current_company_id'))->toBe('personal');
});

it('prevents an admin from leaving the company', function () {
    $admin = User::factory()->create();
    $company = Company::create(['name' => 'Admin Org', 'code' => 'ADMN']);
    CompanyUsers::create([
        'company_id' => $company->id,
        'user_id' => $admin->id,
        'role' => 1, // Admin
        'is_approved' => true,
    ]);

    $this->actingAs($admin);

    $response = $this->post(route('companies.leave', $company));
    $response->assertStatus(403);

    $this->assertDatabaseHas('company_users', [
        'company_id' => $company->id,
        'user_id' => $admin->id,
    ]);
});
