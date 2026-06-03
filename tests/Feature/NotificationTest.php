<?php

use App\Models\Company;
use App\Models\CompanyUsers;
use App\Models\Notification;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\NotificationService;

it('can create a notification and persist it to database', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();

    $notification = Notification::create([
        'organization_id' => $company->id,
        'user_id' => $user->id,
        'type' => 'task_assigned',
        'title' => 'New Task Assigned',
        'message' => 'You have been assigned a new task: Fix database bugs.',
        'data' => ['task_id' => 42, 'priority' => 'high'],
        'read_at' => null,
    ]);

    $this->assertDatabaseHas('notifications', [
        'id' => $notification->id,
        'organization_id' => $company->id,
        'user_id' => $user->id,
        'type' => 'task_assigned',
        'title' => 'New Task Assigned',
        'message' => 'You have been assigned a new task: Fix database bugs.',
        'read_at' => null,
    ]);

    $retrieved = Notification::find($notification->id);
    expect($retrieved->data)->toBe(['task_id' => 42, 'priority' => 'high']);
    expect($retrieved->read_at)->toBeNull();
});

it('can associate notifications with user and organization relations', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();

    $notification = Notification::factory()->create([
        'user_id' => $user->id,
        'organization_id' => $company->id,
    ]);

    // Check relationship from Notification
    expect($notification->user->id)->toBe($user->id);
    expect($notification->organization->id)->toBe($company->id);

    // Check relationship from User
    expect($user->notifications->contains($notification))->toBeTrue();

    // Check relationship from Company
    expect($company->notifications->contains($notification))->toBeTrue();
});

it('supports marking notifications as read', function () {
    $notification = Notification::factory()->create(['read_at' => null]);

    expect($notification->read_at)->toBeNull();

    $now = now();
    $notification->update(['read_at' => $now]);

    expect($notification->fresh()->read_at)->not->toBeNull();
});

it('correctly uses NotificationService to manage notifications', function () {
    $service = new NotificationService;
    $user = User::factory()->create();
    $company = Company::factory()->create();

    // 1. Test send()
    $notification = $service->send(
        $user,
        'task_created',
        'Task Created',
        'A new task was created.',
        $company->id,
        ['foo' => 'bar']
    );

    $this->assertDatabaseHas('notifications', [
        'id' => $notification->id,
        'user_id' => $user->id,
        'organization_id' => $company->id,
        'type' => 'task_created',
        'title' => 'Task Created',
        'message' => 'A new task was created.',
        'read_at' => null,
    ]);

    // 2. Test getUnreadForUser() and getAllForUser()
    $unread = $service->getUnreadForUser($user, $company->id);
    expect($unread->count())->toBe(1);
    expect($unread->first()->id)->toBe($notification->id);

    $all = $service->getAllForUser($user, $company->id);
    expect($all->count())->toBe(1);

    // 3. Test markAsRead()
    $service->markAsRead($notification);
    expect($notification->fresh()->read_at)->not->toBeNull();

    $unreadAfter = $service->getUnreadForUser($user, $company->id);
    expect($unreadAfter->count())->toBe(0);

    // 4. Test markAllAsRead()
    $notification2 = $service->send(
        $user,
        'another_type',
        'Another title',
        'Another message',
        $company->id
    );
    expect($service->getUnreadForUser($user, $company->id)->count())->toBe(1);

    $service->markAllAsRead($user, $company->id);
    expect($service->getUnreadForUser($user, $company->id)->count())->toBe(0);
});

it('sends notifications when a project is created', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();
    // Add user to company
    CompanyUsers::create([
        'company_id' => $company->id,
        'user_id' => $user->id,
        'role' => 1,
    ]);

    $this->actingAs($user);

    $response = $this->post(route('projects.store'), [
        'name' => 'Project Alpha',
        'description' => 'Alpha project description',
        'theme' => '#00ff00',
        'company_id' => $company->id,
        'status' => 1,
        'priority' => 1,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('projects', [
        'name' => 'Project Alpha',
        'company_id' => $company->id,
    ]);

    // Since user was the only member, we won't notify anyone else in the organization.
    // Let's add another member to test notifications are sent to others.
    $otherUser = User::factory()->create();
    CompanyUsers::create([
        'company_id' => $company->id,
        'user_id' => $otherUser->id,
        'role' => 0,
    ]);

    $this->post(route('projects.store'), [
        'name' => 'Project Beta',
        'description' => 'Beta project description',
        'theme' => '#0000ff',
        'company_id' => $company->id,
        'status' => 1,
        'priority' => 1,
    ])->assertRedirect();

    // The other user should have received a notification
    $this->assertDatabaseHas('notifications', [
        'user_id' => $otherUser->id,
        'organization_id' => $company->id,
        'type' => 'project_created',
        'title' => 'New Project Created',
    ]);
});

it('sends notifications when a task is created or its deadline is updated', function () {
    $user = User::factory()->create();
    $assignee = User::factory()->create();
    $company = Company::factory()->create();

    CompanyUsers::create([
        'company_id' => $company->id,
        'user_id' => $user->id,
        'role' => 1,
    ]);
    CompanyUsers::create([
        'company_id' => $company->id,
        'user_id' => $assignee->id,
        'role' => 0,
    ]);

    $project = Project::create([
        'name' => 'Company Project',
        'slug' => 'company-project',
        'description' => 'Project description',
        'theme' => '#0000ff',
        'status' => 1,
        'priority' => 1,
        'user_id' => $user->id,
        'company_id' => $company->id,
    ]);

    $this->actingAs($user);

    // 1. Create task via store
    $response = $this->post(route('projects.tasks.store', $project), [
        'title' => 'Task A',
        'description' => 'Test description',
        'assigned_to' => $assignee->id,
        'due_date' => '2026-06-10',
    ]);
    $response->assertRedirect();

    $task = Task::where('title', 'Task A')->firstOrFail();

    $this->assertDatabaseHas('notifications', [
        'user_id' => $assignee->id,
        'organization_id' => $company->id,
        'type' => 'task_created',
        'title' => 'Task Assigned',
    ]);

    // 2. Update task deadline
    $response2 = $this->patch(route('tasks.update', $task), [
        'title' => 'Task A',
        'due_date' => '2026-06-15', // new due date
        'assigned_to' => $assignee->id,
    ]);
    $response2->assertRedirect();

    $this->assertDatabaseHas('notifications', [
        'user_id' => $assignee->id,
        'organization_id' => $company->id,
        'type' => 'task_deadline_updated',
        'title' => 'Task Deadline Updated',
        'message' => "The deadline for task 'Task A' has been set/updated to 2026-06-15.",
    ]);
});

it('provides endpoints to fetch and mark notifications as read', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();
    // Add user to company
    CompanyUsers::create([
        'company_id' => $company->id,
        'user_id' => $user->id,
        'role' => 1,
    ]);

    // Create a couple of notifications for this user and company
    $service = new NotificationService;
    $notif1 = $service->send($user, 'task_created', 'Task 1 Created', 'Desc 1', $company->id);
    $notif2 = $service->send($user, 'task_created', 'Task 2 Created', 'Desc 2', $company->id);

    $this->actingAs($user);

    // Set session active company
    session(['current_company_id' => $company->id]);

    // 1. Fetch unread notifications
    $response = $this->getJson(route('notifications.index'));
    $response->assertStatus(200)
        ->assertJsonCount(2, 'notifications')
        ->assertJsonPath('notifications.0.id', $notif1->id)
        ->assertJsonPath('notifications.1.id', $notif2->id);

    // 2. Mark one as read
    $responseRead = $this->patchJson(route('notifications.read', $notif1));
    $responseRead->assertStatus(200)
        ->assertJson(['success' => true]);

    expect($notif1->fresh()->read_at)->not->toBeNull();

    // 3. Fetch again (should only have 1 left)
    $response2 = $this->getJson(route('notifications.index'));
    $response2->assertStatus(200)
        ->assertJsonCount(1, 'notifications')
        ->assertJsonPath('notifications.0.id', $notif2->id);

    // 4. Mark all as read
    $responseReadAll = $this->postJson(route('notifications.readAll'));
    $responseReadAll->assertStatus(200)
        ->assertJson(['success' => true]);

    expect($notif2->fresh()->read_at)->not->toBeNull();

    // 5. Fetch again (should have 0)
    $response3 = $this->getJson(route('notifications.index'));
    $response3->assertStatus(200)
        ->assertJsonCount(0, 'notifications');
});

it('sends notifications on all task CRUD events including status, priority, assignee changes, and deletion', function () {
    $user = User::factory()->create();
    $assignee = User::factory()->create();
    $company = Company::factory()->create();

    CompanyUsers::create([
        'company_id' => $company->id,
        'user_id' => $user->id,
        'role' => 1,
    ]);
    CompanyUsers::create([
        'company_id' => $company->id,
        'user_id' => $assignee->id,
        'role' => 0,
    ]);

    $project = Project::create([
        'name' => 'Company Project 2',
        'slug' => 'company-project-2',
        'description' => 'Project description 2',
        'theme' => '#0000ff',
        'status' => 1,
        'priority' => 1,
        'user_id' => $user->id,
        'company_id' => $company->id,
    ]);

    $task = Task::create([
        'project_id' => $project->id,
        'title' => 'Task B',
        'description' => 'Initial desc',
        'assigned_to' => $user->id, // Assigned to creator first
        'status' => 1,
        'priority' => 1,
    ]);

    $this->actingAs($user);

    // 1. Update status
    $this->patch(route('tasks.update', $task), [
        'title' => 'Task B',
        'assigned_to' => $user->id,
        'status' => 2, // In Progress
        'priority' => 1,
    ])->assertRedirect();

    // Since it is assigned to $user (current auth user), no notification should be sent.
    $this->assertDatabaseMissing('notifications', [
        'type' => 'task_status_updated',
    ]);

    // Reassign task to assignee
    $task->update(['assigned_to' => $assignee->id]);

    // 2. Update status while assigned to assignee
    $this->patch(route('tasks.update', $task), [
        'title' => 'Task B',
        'assigned_to' => $assignee->id,
        'status' => 3, // Completed
        'priority' => 1,
    ])->assertRedirect();

    $this->assertDatabaseHas('notifications', [
        'user_id' => $assignee->id,
        'type' => 'task_status_updated',
        'message' => "The status of task 'Task B' has been updated to 'Completed'.",
    ]);

    // 3. Update priority while assigned to assignee
    $this->patch(route('tasks.update', $task), [
        'title' => 'Task B',
        'assigned_to' => $assignee->id,
        'status' => 3,
        'priority' => 3, // High
    ])->assertRedirect();

    $this->assertDatabaseHas('notifications', [
        'user_id' => $assignee->id,
        'type' => 'task_priority_updated',
        'message' => "The priority of task 'Task B' has been set to 'High'.",
    ]);

    // 4. Update assignee
    $anotherAssignee = User::factory()->create();
    CompanyUsers::create([
        'company_id' => $company->id,
        'user_id' => $anotherAssignee->id,
        'role' => 0,
    ]);

    $this->patch(route('tasks.update', $task), [
        'title' => 'Task B',
        'assigned_to' => $anotherAssignee->id,
        'status' => 3,
        'priority' => 3,
    ])->assertRedirect();

    $this->assertDatabaseHas('notifications', [
        'user_id' => $anotherAssignee->id,
        'type' => 'task_assigned',
        'title' => 'Task Assigned',
    ]);

    // 5. Delete task
    $this->delete(route('tasks.destroy', $task))->assertRedirect();

    $this->assertDatabaseHas('notifications', [
        'user_id' => $anotherAssignee->id,
        'type' => 'task_deleted',
        'title' => 'Task Deleted',
    ]);
});
