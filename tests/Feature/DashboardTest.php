<?php

use App\Models\Company;
use App\Models\CompanyUsers;
use App\Models\Project;
use App\Models\Task;
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

it('displays today and pending tasks ordered priority-wise on the dashboard', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $project = Project::create([
        'name' => 'Dashboard Task Test Project',
        'slug' => 'dashboard-task-test-project',
        'theme' => '#336699',
        'status' => 1,
        'priority' => 1,
        'user_id' => $user->id,
    ]);

    // Create low priority pending task due today
    $lowTask = Task::create([
        'title' => 'Low Priority Today Task',
        'project_id' => $project->id,
        'user_id' => $user->id,
        'assigned_to' => $user->id,
        'status' => 1,
        'priority' => 1, // Low
        'due_date' => now()->toDateString(),
    ]);

    // Create urgent priority pending task due today
    $urgentTask = Task::create([
        'title' => 'Urgent Priority Today Task',
        'project_id' => $project->id,
        'user_id' => $user->id,
        'assigned_to' => $user->id,
        'status' => 1,
        'priority' => 4, // Urgent
        'due_date' => now()->toDateString(),
    ]);

    // Create completed task (should not appear in pending)
    $completedTask = Task::create([
        'title' => 'Completed Today Task',
        'project_id' => $project->id,
        'user_id' => $user->id,
        'assigned_to' => $user->id,
        'status' => 3, // Completed
        'priority' => 4,
        'due_date' => now()->toDateString(),
    ]);

    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertStatus(200);
    $response->assertSee('Pending Tasks');
    $response->assertSee('Urgent Priority Today Task');
    $response->assertSee('Low Priority Today Task');

    // Check that urgent task comes before low task in priority-wise ordering
    $todayTasks = $response->viewData('todayTasks');
    expect($todayTasks->first()->id)->toBe($urgentTask->id);
    expect($todayTasks->pluck('id'))->not->toContain($completedTask->id);
});

it('filters dashboard tasks by filter parameters', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $project = Project::create([
        'name' => 'Filter Test Project',
        'slug' => 'filter-test-project',
        'theme' => '#336699',
        'status' => 1,
        'priority' => 1,
        'user_id' => $user->id,
    ]);

    $overdueTask = Task::create([
        'title' => 'Overdue Task Item',
        'project_id' => $project->id,
        'user_id' => $user->id,
        'assigned_to' => $user->id,
        'status' => 1,
        'priority' => 3,
        'due_date' => now()->subDays(2)->toDateString(),
    ]);

    $futureTask = Task::create([
        'title' => 'Future Task Item',
        'project_id' => $project->id,
        'user_id' => $user->id,
        'assigned_to' => $user->id,
        'status' => 1,
        'priority' => 2,
        'due_date' => now()->addDays(5)->toDateString(),
    ]);

    $this->actingAs($user);

    // Overdue filter check
    $responseOverdue = $this->get(route('dashboard', ['task_filter' => 'overdue']));
    $responseOverdue->assertStatus(200);
    $overdueTasks = $responseOverdue->viewData('todayTasks');
    expect($overdueTasks->pluck('id'))->toContain($overdueTask->id);
    expect($overdueTasks->pluck('id'))->not->toContain($futureTask->id);

    // All Pending filter check
    $responseAll = $this->get(route('dashboard', ['task_filter' => 'all_pending']));
    $responseAll->assertStatus(200);
    $allTasks = $responseAll->viewData('todayTasks');
    expect($allTasks->pluck('id'))->toContain($overdueTask->id);
    expect($allTasks->pluck('id'))->toContain($futureTask->id);
});

it('only includes tasks assigned to current user on the dashboard', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $otherUser = User::factory()->create(['email_verified_at' => now()]);

    $project = Project::create([
        'name' => 'User Assignment Test Project',
        'slug' => 'user-assignment-test-project',
        'theme' => '#336699',
        'status' => 1,
        'priority' => 1,
        'user_id' => $user->id,
    ]);

    $myTask = Task::create([
        'title' => 'Task Assigned To Me',
        'project_id' => $project->id,
        'user_id' => $user->id,
        'assigned_to' => $user->id,
        'status' => 1,
        'priority' => 3,
        'due_date' => now()->toDateString(),
    ]);

    $otherUserTask = Task::create([
        'title' => 'Task Assigned To Someone Else',
        'project_id' => $project->id,
        'user_id' => $user->id,
        'assigned_to' => $otherUser->id,
        'status' => 1,
        'priority' => 4,
        'due_date' => now()->toDateString(),
    ]);

    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertStatus(200);
    $dashboardTasks = $response->viewData('todayTasks');

    expect($dashboardTasks->pluck('id'))->toContain($myTask->id);
    expect($dashboardTasks->pluck('id'))->not->toContain($otherUserTask->id);
});

it('paginates dashboard tasks with 5 per page', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    $project = Project::create([
        'name' => 'Pagination Project',
        'slug' => 'pagination-project',
        'theme' => '#336699',
        'status' => 1,
        'priority' => 1,
        'user_id' => $user->id,
    ]);

    for ($i = 1; $i <= 8; $i++) {
        Task::create([
            'title' => "Paginated Task {$i}",
            'project_id' => $project->id,
            'user_id' => $user->id,
            'assigned_to' => $user->id,
            'status' => 1,
            'priority' => 2,
            'due_date' => now()->toDateString(),
        ]);
    }

    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertStatus(200);
    $todayTasks = $response->viewData('todayTasks');

    expect($todayTasks->perPage())->toBe(5);
    expect($todayTasks->count())->toBe(5);
    expect($todayTasks->total())->toBe(8);

    // Test custom per_page parameter
    $responseCustom = $this->get(route('dashboard', ['per_page' => 10]));
    $responseCustom->assertStatus(200);
    $customTasks = $responseCustom->viewData('todayTasks');

    expect($customTasks->perPage())->toBe(10);
    expect($customTasks->count())->toBe(8);
});

it('does not display status 4 tasks or tasks of status 4 projects on dashboard', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    $activeProject = Project::create([
        'name' => 'Active Project',
        'slug' => 'active-project',
        'theme' => '#336699',
        'status' => 1,
        'priority' => 1,
        'user_id' => $user->id,
    ]);

    $onHoldProject = Project::create([
        'name' => 'On Hold Project',
        'slug' => 'on-hold-project',
        'theme' => '#336699',
        'status' => 4, // Status 4 project
        'priority' => 1,
        'user_id' => $user->id,
    ]);

    $normalTask = Task::create([
        'title' => 'Normal Active Task',
        'project_id' => $activeProject->id,
        'user_id' => $user->id,
        'assigned_to' => $user->id,
        'status' => 1,
        'priority' => 2,
        'due_date' => now()->toDateString(),
    ]);

    $onHoldTask = Task::create([
        'title' => 'Status 4 Task',
        'project_id' => $activeProject->id,
        'user_id' => $user->id,
        'assigned_to' => $user->id,
        'status' => 4, // Status 4 task
        'priority' => 2,
        'due_date' => now()->toDateString(),
    ]);

    $taskInOnHoldProject = Task::create([
        'title' => 'Task In Status 4 Project',
        'project_id' => $onHoldProject->id,
        'user_id' => $user->id,
        'assigned_to' => $user->id,
        'status' => 1,
        'priority' => 2,
        'due_date' => now()->toDateString(),
    ]);

    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertStatus(200);
    $dashboardTasks = $response->viewData('todayTasks');

    expect($dashboardTasks->pluck('id'))->toContain($normalTask->id);
    expect($dashboardTasks->pluck('id'))->not->toContain($onHoldTask->id);
    expect($dashboardTasks->pluck('id'))->not->toContain($taskInOnHoldProject->id);
});

it('loads new inertia dashboard page successfully', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $this->actingAs($user);

    $response = $this->get(route('new.dashboard'));
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('New/Dashboard')
        ->has('stats')
        ->has('recent_activity')
        ->has('chart_data')
        ->has('projects')
    );
});

it('loads new inertia analytics page successfully', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $this->actingAs($user);

    $response = $this->get(route('new.analytics'));
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('New/Analytics')
        ->has('analytics_data')
        ->has('team_members')
    );
});

it('loads new inertia projects page successfully', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $this->actingAs($user);

    $response = $this->get(route('new.projects'));
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('New/Projects')
        ->has('initial_projects')
    );
});

it('loads new inertia tasks page successfully', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $this->actingAs($user);

    $response = $this->get(route('new.tasks'));
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('New/Tasks')
        ->has('initial_tasks')
    );
});

it('loads coming soon placeholder page for unbuilt sidebar items', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $this->actingAs($user);

    $response = $this->get(route('new.settings'));
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('New/ComingSoon')
        ->has('feature')
        ->has('activeItem')
    );
});

it('loads new inertia task view page successfully', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $this->actingAs($user);

    $response = $this->get(route('new.task.view'));
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('New/TaskView')
    );

    $responseWithId = $this->get(route('new.task.show', ['id' => '042']));
    $responseWithId->assertStatus(200);
    $responseWithId->assertInertia(fn ($page) => $page
        ->component('New/TaskView')
        ->has('task')
    );
});
