<?php

/*
 * Owner / Admin / Manager / Member company roles.
 * Rules are defined once in App\Authorization\CompanyAbility.
 */

use App\Authorization\CompanyAbility;
use App\Enums\CompanyRole;
use App\Models\Company;
use App\Models\CompanyUsers;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Activitylog\Models\Activity;

/**
 * @return array{0: Company, 1: array<string, User>}
 */
function rolesCompany(): array
{
    $company = Company::create(['name' => 'Roles Co', 'code' => 'R'.strtoupper(substr(uniqid(), -5))]);
    $users = [];
    foreach (['owner' => 3, 'admin' => 1, 'manager' => 2, 'member' => 0] as $key => $role) {
        $users[$key] = User::factory()->create(['email_verified_at' => now()]);
        CompanyUsers::create(['company_id' => $company->id, 'user_id' => $users[$key]->id, 'role' => $role, 'is_approved' => true]);
    }

    return [$company, $users];
}

function rolesProject(Company $company, User $creator): Project
{
    return Project::create([
        'name' => 'Roles Project '.uniqid(),
        'slug' => 'roles-project-'.uniqid(),
        'theme' => '#111111',
        'status' => 1,
        'priority' => 1,
        'user_id' => $creator->id,
        'company_id' => $company->id,
    ]);
}

function roleOf(Company $company, User $user): ?CompanyRole
{
    return CompanyAbility::roleOf($user, $company->id);
}

it('orders roles by rank and only lets actors assign roles below their own', function () {
    expect(CompanyRole::Owner->outranks(CompanyRole::Admin))->toBeTrue()
        ->and(CompanyRole::Admin->outranks(CompanyRole::Manager))->toBeTrue()
        ->and(CompanyRole::Manager->outranks(CompanyRole::Member))->toBeTrue()
        ->and(CompanyRole::assignableBy(CompanyRole::Owner))->toEqualCanonicalizing([CompanyRole::Admin, CompanyRole::Manager, CompanyRole::Member])
        ->and(CompanyRole::assignableBy(CompanyRole::Admin))->toEqualCanonicalizing([CompanyRole::Manager, CompanyRole::Member])
        ->and(CompanyRole::assignableBy(CompanyRole::Member))->toBe([])
        ->and(CompanyRole::fromStored(99))->toBe(CompanyRole::Member);
});

it('lets an admin promote a member to manager', function () {
    [$company, $u] = rolesCompany();

    $this->actingAs($u['admin'])
        ->patch(route('v2.companies.members.role', [$company, $u['member']]), ['role' => CompanyRole::Manager->value])
        ->assertSessionHasNoErrors();

    expect(roleOf($company, $u['member']))->toBe(CompanyRole::Manager)
        ->and(Activity::where('event', 'role_changed')->exists())->toBeTrue();
});

it('does not let an admin create another admin', function () {
    [$company, $u] = rolesCompany();

    $this->actingAs($u['admin'])
        ->patch(route('v2.companies.members.role', [$company, $u['member']]), ['role' => CompanyRole::Admin->value])
        ->assertSessionHasErrors('role');

    expect(roleOf($company, $u['member']))->toBe(CompanyRole::Member);
});

it('does not let anyone below admin change roles', function () {
    [$company, $u] = rolesCompany();

    $this->actingAs($u['manager'])
        ->patch(route('v2.companies.members.role', [$company, $u['member']]), ['role' => CompanyRole::Manager->value])
        ->assertForbidden();
});

it('protects the owner and peers from admins', function () {
    [$company, $u] = rolesCompany();
    $otherAdmin = User::factory()->create();
    CompanyUsers::create(['company_id' => $company->id, 'user_id' => $otherAdmin->id, 'role' => 1, 'is_approved' => true]);

    $this->actingAs($u['admin'])->delete(route('v2.companies.members.destroy', [$company, $u['owner']]))->assertForbidden();
    $this->actingAs($u['admin'])->delete(route('v2.companies.members.destroy', [$company, $otherAdmin]))->assertForbidden();
    $this->actingAs($u['admin'])
        ->patch(route('v2.companies.members.role', [$company, $u['owner']]), ['role' => CompanyRole::Member->value])
        ->assertForbidden();

    expect(roleOf($company, $u['owner']))->toBe(CompanyRole::Owner)
        ->and(roleOf($company, $otherAdmin))->toBe(CompanyRole::Admin);
});

it('lets the owner remove an admin', function () {
    [$company, $u] = rolesCompany();

    $this->actingAs($u['owner'])->delete(route('v2.companies.members.destroy', [$company, $u['admin']]))->assertRedirect();

    expect(roleOf($company, $u['admin']))->toBeNull();
});

it('only lets the owner delete the organization', function () {
    [$company, $u] = rolesCompany();

    $this->actingAs($u['admin'])->delete(route('v2.companies.destroy', $company))->assertForbidden();
    expect(Company::find($company->id))->not->toBeNull();

    $this->actingAs($u['owner'])->delete(route('v2.companies.destroy', $company))->assertRedirect();
    $this->assertSoftDeleted('companies', ['id' => $company->id]);
});

it('requires the owner to transfer ownership before leaving, but lets an admin leave', function () {
    [$company, $u] = rolesCompany();

    $this->actingAs($u['owner'])->post(route('v2.companies.leave', $company))->assertForbidden();
    $this->actingAs($u['admin'])->post(route('v2.companies.leave', $company))->assertRedirect();

    expect(roleOf($company, $u['admin']))->toBeNull();
});

it('transfers ownership and demotes the previous owner to admin', function () {
    [$company, $u] = rolesCompany();

    $this->actingAs($u['admin'])->post(route('v2.companies.transfer-ownership', [$company, $u['manager']]))->assertForbidden();

    $this->actingAs($u['owner'])->post(route('v2.companies.transfer-ownership', [$company, $u['manager']]))->assertRedirect();

    expect(roleOf($company, $u['manager']))->toBe(CompanyRole::Owner)
        ->and(roleOf($company, $u['owner']))->toBe(CompanyRole::Admin)
        ->and(CompanyUsers::where('company_id', $company->id)->where('role', CompanyRole::Owner->value)->count())->toBe(1);
});

it('lets managers manage secrets and edit any project, but not members', function () {
    [$company, $u] = rolesCompany();
    $project = rolesProject($company, $u['owner']);

    $this->actingAs($u['manager'])->get(route('projects.credentials', $project))->assertOk();
    $this->actingAs($u['member'])->get(route('projects.credentials', $project))->assertForbidden();

    $this->actingAs($u['manager'])->get(route('projects.edit', $project))->assertOk();
    $this->actingAs($u['member'])->get(route('projects.edit', $project))->assertForbidden();
});

it('still lets members create tasks in company projects and edit projects they created', function () {
    [$company, $u] = rolesCompany();
    $project = rolesProject($company, $u['owner']);
    $ownProject = rolesProject($company, $u['member']);

    $this->actingAs($u['member'])
        ->post(route('projects.tasks.store', $project), ['title' => 'Member task'])
        ->assertSessionHasNoErrors();

    expect(Task::where('title', 'Member task')->where('project_id', $project->id)->exists())->toBeTrue();

    $this->actingAs($u['member'])->get(route('projects.edit', $ownProject))->assertOk();
});

it('lets managers but not members edit other people\'s tasks', function () {
    [$company, $u] = rolesCompany();
    $project = rolesProject($company, $u['owner']);
    $task = Task::create(['title' => 'Owner task', 'user_id' => $u['owner']->id, 'project_id' => $project->id, 'status' => 1, 'priority' => 1]);

    $this->actingAs($u['member'])->patch(route('tasks.update', $task), ['title' => 'Hijacked'])->assertForbidden();
    $this->actingAs($u['manager'])->patch(route('tasks.update', $task), ['title' => 'Edited by manager'])->assertRedirect();

    expect($task->fresh()->title)->toBe('Edited by manager');
});

it('promotes the earliest admin to owner and normalizes legacy member values', function () {
    $company = Company::create(['name' => 'Legacy Co', 'code' => 'LEGC']);
    $first = User::factory()->create();
    $second = User::factory()->create();
    $legacyMember = User::factory()->create();
    CompanyUsers::create(['company_id' => $company->id, 'user_id' => $first->id, 'role' => 1, 'is_approved' => true]);
    CompanyUsers::create(['company_id' => $company->id, 'user_id' => $second->id, 'role' => 1, 'is_approved' => true]);
    // Simulate pre-migration data: seeders used 2 for "member".
    DB::table('company_users')->insert([
        'company_id' => $company->id, 'user_id' => $legacyMember->id, 'role' => 2, 'is_approved' => true,
        'created_at' => now(), 'updated_at' => now(),
    ]);

    $migration = require database_path('migrations/2026_11_26_000000_introduce_company_roles.php');
    $migration->up();

    expect(roleOf($company, $first))->toBe(CompanyRole::Owner)
        ->and(roleOf($company, $second))->toBe(CompanyRole::Admin)
        ->and(roleOf($company, $legacyMember))->toBe(CompanyRole::Member);
});

it('does not expose another user\'s activity log to unrelated users', function () {
    $victim = User::factory()->create();
    $snoop = User::factory()->create();
    $this->actingAs($victim);
    Task::create(['title' => 'Victim private task', 'user_id' => $victim->id, 'status' => 1, 'priority' => 1]);

    $this->actingAs($snoop)->get(route('activity-logs.index', ['user_id' => $victim->id]))->assertForbidden();
    $this->actingAs($snoop)->get(route('v2.activity-logs.index', ['user_id' => $victim->id]))->assertForbidden();
});

it('scopes the organization activity view to the admin\'s own companies', function () {
    [$company, $u] = rolesCompany();
    $ownProject = rolesProject($company, $u['owner']);

    $outsider = User::factory()->create();
    $otherCompany = Company::create(['name' => 'Other Tenant', 'code' => 'OTHR']);
    CompanyUsers::create(['company_id' => $otherCompany->id, 'user_id' => $outsider->id, 'role' => 3, 'is_approved' => true]);
    $foreignProject = rolesProject($otherCompany, $outsider);

    $this->actingAs($u['admin'])
        ->get(route('v2.activity-logs.index', ['scope' => 'company']))
        ->assertOk()
        ->assertInertia(function (Assert $page) use ($ownProject, $foreignProject) {
            $titles = collect($page->toArray()['props']['activities'])->pluck('subject_title');
            expect($titles)->toContain($ownProject->name)
                ->and($titles)->not->toContain($foreignProject->name);
            $page->where('scope', 'company')->where('can_view_company', true);
        });

    // A plain member cannot widen the scope: falls back to their own activity.
    $this->actingAs($u['member'])
        ->get(route('v2.activity-logs.index', ['scope' => 'company']))
        ->assertInertia(fn (Assert $page) => $page->where('scope', 'mine')->where('can_view_company', false));
});

it('renders a permissions matrix that matches the enforced abilities', function () {
    [, $u] = rolesCompany();

    $this->actingAs($u['member'])
        ->get(route('v2.permissions.index'))
        ->assertOk()
        ->assertInertia(function (Assert $page) {
            $props = $page->toArray()['props'];
            expect(collect($props['role_columns'])->pluck('label')->all())->toBe(['Owner', 'Admin', 'Manager', 'Member']);

            foreach ($props['matrix'] as $section) {
                foreach ($section['permissions'] as $perm) {
                    foreach (CompanyRole::cases() as $role) {
                        expect($perm[strtolower($role->name)])
                            ->toBe(CompanyAbility::roleAllows($role, $perm['key']), "{$perm['key']} for {$role->name}");
                    }
                }
            }

            // Real counts: one of each role in the fixture company.
            expect(collect($props['roles'])->pluck('users_count', 'name')->all())
                ->toBe(['Owner' => 1, 'Admin' => 1, 'Manager' => 1, 'Member' => 1]);
        });
});
