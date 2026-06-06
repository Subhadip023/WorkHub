<?php

use App\Models\Company;
use App\Models\CompanyUsers;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('allows authenticated user to create a task in a personal project with status and priority', function () {
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

    $this->actingAs($user);

    $response = $this->post(route('projects.tasks.store', $project), [
        'title' => 'New Task Title',
        'description' => 'Task description.',
        'status' => 2, // In Progress
        'priority' => 3, // High
        'assigned_to' => $user->id,
        'due_date' => '2026-06-01',
    ]);

    $response->assertRedirect(route('projects.show', $project));
    $this->assertDatabaseHas('tasks', [
        'title' => 'New Task Title',
        'project_id' => $project->id,
        'status' => 2,
        'priority' => 3,
    ]);
});

it('automatically marks task as completed when status is set to 3', function () {
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

    $this->actingAs($user);

    $response = $this->post(route('projects.tasks.store', $project), [
        'title' => 'Completed Task',
        'status' => 3, // Completed
        'priority' => 2,
    ]);

    $this->assertDatabaseHas('tasks', [
        'title' => 'Completed Task',
        'status' => 3,
    ]);
});

it('allows user to update task status and priority', function () {
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
        'title' => 'Original Task',
        'project_id' => $project->id,
        'status' => 1,
        'priority' => 2,
    ]);

    $this->actingAs($user);

    $response = $this->patch(route('tasks.update', $task), [
        'title' => 'Updated Task Title',
        'status' => 4, // On Hold
        'priority' => 4, // Urgent
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'title' => 'Updated Task Title',
        'status' => 4,
        'priority' => 4,
    ]);
});

it('syncs status when toggling task completion state', function () {
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
        'title' => 'Toggle Task',
        'project_id' => $project->id,
        'status' => 1, // To Do
        'priority' => 2,
    ]);

    $this->actingAs($user);

    // Toggle to completed
    $response = $this->patch(route('tasks.toggle', $task));
    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'status' => 3, // Completed
    ]);

    // Toggle back to pending
    $response = $this->patch(route('tasks.toggle', $task));
    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'status' => 1, // Should revert to To Do (or 1)
    ]);
});

it('allows authenticated user to view task details page', function () {
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
        'title' => 'Detailed Task Name',
        'project_id' => $project->id,
        'status' => 1,
        'priority' => 2,
    ]);

    $this->actingAs($user);

    $response = $this->get(route('tasks.show', $task));
    $response->assertStatus(200);
    $response->assertSee('Detailed Task Name');
});

it('allows authenticated user to create a task via general store route', function () {
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

    $this->actingAs($user);

    $response = $this->post(route('tasks.store'), [
        'project_id' => $project->id,
        'title' => 'General Store Task',
        'description' => 'A general description.',
        'status' => 2,
        'priority' => 1,
    ]);

    $response->assertRedirect(route('tasks.index'));
    $this->assertDatabaseHas('tasks', [
        'title' => 'General Store Task',
        'project_id' => $project->id,
        'status' => 2,
    ]);
});

it('allows user to delete a task', function () {
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
        'title' => 'Task to delete',
        'project_id' => $project->id,
        'status' => 1,
        'priority' => 1,
    ]);

    $this->actingAs($user);

    $response = $this->delete(route('tasks.destroy', $task));
    $response->assertRedirect();
    $this->assertSoftDeleted('tasks', [
        'id' => $task->id,
    ]);
});

it('redirects to the project details page if deleted from the task details page', function () {
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
        'title' => 'Task to delete',
        'project_id' => $project->id,
        'status' => 1,
        'priority' => 1,
        'user_id' => $user->id,
    ]);

    $this->actingAs($user);

    $response = $this->from(route('tasks.show', $task))
        ->delete(route('tasks.destroy', $task));

    $response->assertRedirect(route('projects.show', $project->id));
});

it('redirects to the tasks list page if a projectless task is deleted from its details page', function () {
    $user = User::factory()->create();
    $task = Task::create([
        'title' => 'Projectless Task to delete',
        'project_id' => null,
        'status' => 1,
        'priority' => 1,
        'user_id' => $user->id,
    ]);

    $this->actingAs($user);

    $response = $this->from(route('tasks.show', $task))
        ->delete(route('tasks.destroy', $task));

    $response->assertRedirect(route('tasks.index'));
});

it('allows user to import tasks from JSON format', function () {
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

    $this->actingAs($user);

    $jsonData = json_encode([
        [
            'title' => 'Imported Task 1',
            'description' => 'First imported description.',
            'status' => 2,
            'priority' => 3,
        ],
        [
            'title' => 'Imported Task 2',
            'description' => 'Second imported description.',
            'status' => 3,
            'priority' => 1,
        ],
    ]);

    $response = $this->post(route('projects.tasks.import', $project), [
        'json_data' => $jsonData,
    ]);

    $response->assertRedirect(route('projects.show', $project));
    $this->assertDatabaseHas('tasks', [
        'title' => 'Imported Task 1',
        'status' => 2,
        'priority' => 3,
    ]);
    $this->assertDatabaseHas('tasks', [
        'title' => 'Imported Task 2',
        'status' => 3,
        'priority' => 1,
    ]);
});

it('allows user to upload and delete task images', function () {
    Storage::fake('public');

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
        'title' => 'Image Task',
        'project_id' => $project->id,
        'status' => 1,
        'priority' => 1,
    ]);

    $this->actingAs($user);
    session(['current_company_id' => 'personal']);

    $file = UploadedFile::fake()->image('task_attachment.png', 100, 100);

    $response = $this->post(route('tasks.images.store', $task), [
        'image' => $file,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('task_images', [
        'task_id' => $task->id,
    ]);

    $taskImage = $task->images()->first();
    Storage::disk('public')->assertExists($taskImage->image_path);

    // Delete the image
    $deleteResponse = $this->delete(route('tasks.images.destroy', $taskImage));
    $deleteResponse->assertRedirect();
    $this->assertDatabaseMissing('task_images', [
        'id' => $taskImage->id,
    ]);
    Storage::disk('public')->assertMissing($taskImage->image_path);
});

it('filters tasks by project, status, and assignee', function () {
    $user = User::factory()->create();
    $project1 = Project::create([
        'name' => 'Project One',
        'slug' => 'project-one',
        'theme' => '#ff0000',
        'status' => 1,
        'priority' => 1,
        'user_id' => $user->id,
        'company_id' => null,
    ]);
    $project2 = Project::create([
        'name' => 'Project Two',
        'slug' => 'project-two',
        'theme' => '#00ff00',
        'status' => 1,
        'priority' => 1,
        'user_id' => $user->id,
        'company_id' => null,
    ]);

    $task1 = Task::create([
        'title' => 'Task in Project 1',
        'project_id' => $project1->id,
        'status' => 1,
        'priority' => 1,
    ]);
    $task2 = Task::create([
        'title' => 'Completed Task in Project 2',
        'project_id' => $project2->id,
        'status' => 3,
        'priority' => 1,
    ]);

    $this->actingAs($user);

    // Filter by project 1
    $response = $this->get(route('tasks.index', ['project' => $project1->id]));
    $response->assertStatus(200);
    $response->assertSee('Task in Project 1');
    $response->assertDontSee('Completed Task in Project 2');

    // Filter by status completed
    $response = $this->get(route('tasks.index', ['status' => 'completed']));
    $response->assertStatus(200);
    $response->assertSee('Completed Task in Project 2');
    $response->assertDontSee('Task in Project 1');
});

it('logs task status history and displays it on the task details page', function () {
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

    $this->actingAs($user);

    // 1. Log task creation
    $task = Task::create([
        'title' => 'Test Task History',
        'project_id' => $project->id,
        'status' => 1,
        'priority' => 1,
    ]);

    $this->assertDatabaseHas('task_histories', [
        'task_id' => $task->id,
        'old_status' => null,
        'new_status' => 1,
    ]);

    // 2. Log status update
    $task->update([
        'status' => 2,
    ]);

    $this->assertDatabaseHas('task_histories', [
        'task_id' => $task->id,
        'old_status' => 1,
        'new_status' => 2,
    ]);

    // 3. Log priority update
    $task->update([
        'priority' => 3,
        'title' => 'Test Task History Updated',
    ]);

    $this->assertDatabaseHas('task_histories', [
        'task_id' => $task->id,
        'field' => 'priority',
        'old_value' => '1',
        'new_value' => '3',
    ]);

    $this->assertDatabaseHas('task_histories', [
        'task_id' => $task->id,
        'field' => 'title',
        'old_value' => 'Test Task History',
        'new_value' => 'Test Task History Updated',
    ]);

    // 4. View detail page and see logs
    $response = $this->get(route('tasks.show', $task));
    $response->assertStatus(200);
    $response->assertSee('Task created with status');
    $response->assertSee('Status changed to');
    $response->assertSee('In Progress');
    $response->assertSee('Priority changed to');
    $response->assertSee('High');
    $response->assertSee('Title changed to');
});

it('allows creating tasks with specific types and validates type values', function () {
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

    $this->actingAs($user);

    // Create a Bug type task via general store
    $response = $this->post(route('tasks.store'), [
        'project_id' => $project->id,
        'title' => 'Important Bug Task',
        'type' => 2, // Bug
        'status' => 1,
        'priority' => 2,
    ]);
    $response->assertRedirect();
    $this->assertDatabaseHas('tasks', [
        'title' => 'Important Bug Task',
        'type' => 2,
    ]);

    // Try creating a task with invalid type (out of bounds)
    $response = $this->post(route('tasks.store'), [
        'project_id' => $project->id,
        'title' => 'Invalid Type Task',
        'type' => 99, // Invalid
        'status' => 1,
        'priority' => 2,
    ]);
    $response->assertSessionHasErrors('type');
});

it('tracks task type changes in task history', function () {
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

    $this->actingAs($user);

    $task = Task::create([
        'title' => 'Test Task Type History',
        'project_id' => $project->id,
        'status' => 1,
        'priority' => 1,
        'type' => 1, // Task
    ]);

    $task->update([
        'type' => 3, // Feature
    ]);

    $this->assertDatabaseHas('task_histories', [
        'task_id' => $task->id,
        'field' => 'type',
        'old_value' => '1',
        'new_value' => '3',
    ]);

    $response = $this->get(route('tasks.show', $task));
    $response->assertStatus(200);
    $response->assertSee('Type changed to');
    $response->assertSee('Feature');
});

it('filters tasks by type in tasks index', function () {
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

    $this->actingAs($user);

    Task::create([
        'title' => 'General Bug Task',
        'project_id' => $project->id,
        'status' => 1,
        'priority' => 2,
        'type' => 2, // Bug
    ]);

    Task::create([
        'title' => 'General Feature Task',
        'project_id' => $project->id,
        'status' => 1,
        'priority' => 2,
        'type' => 3, // Feature
    ]);

    // Filter by type 2 (Bug)
    $response = $this->get(route('tasks.index', ['type' => '2']));
    $response->assertStatus(200);
    $response->assertSee('General Bug Task');
    $response->assertDontSee('General Feature Task');

    // Filter by type 3 (Feature)
    $response = $this->get(route('tasks.index', ['type' => '3']));
    $response->assertStatus(200);
    $response->assertSee('General Feature Task');
    $response->assertDontSee('General Bug Task');
});

it('enforces company permissions for task image uploads', function () {
    Storage::fake('public');

    // 1. Setup Company, Admin, Member, and Non-member
    $admin = User::factory()->create();
    $member = User::factory()->create();
    $nonMember = User::factory()->create();

    $company = Company::create([
        'name' => 'Test Org',
        'slug' => 'test-org',
        'invite_code' => 'INVITE123',
        'owner_id' => $admin->id,
    ]);

    // Attach users to company
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

    // 2. Setup Project & Tasks
    $project = Project::create([
        'name' => 'Company Project',
        'slug' => 'company-project',
        'theme' => '#00ff00',
        'status' => 1,
        'priority' => 1,
        'user_id' => $admin->id,
        'company_id' => $company->id,
    ]);

    // Task 1: Assigned to the member
    $taskAssignedToMember = Task::create([
        'title' => 'Member Task',
        'project_id' => $project->id,
        'status' => 1,
        'priority' => 1,
        'assigned_to' => $member->id,
    ]);

    // Task 2: Assigned to the admin
    $taskAssignedToAdmin = Task::create([
        'title' => 'Admin Task',
        'project_id' => $project->id,
        'status' => 1,
        'priority' => 1,
        'assigned_to' => $admin->id,
    ]);

    // 3. Test: Member uploading to their own task -> Success
    $this->actingAs($member);
    $file = UploadedFile::fake()->image('member_upload.png');
    $response = $this->post(route('tasks.images.store', $taskAssignedToMember), [
        'image' => $file,
    ]);
    $response->assertRedirect();
    $this->assertDatabaseHas('task_images', [
        'task_id' => $taskAssignedToMember->id,
    ]);

    // 4. Test: Member uploading to admin's task -> 403 Forbidden
    $file2 = UploadedFile::fake()->image('member_upload_admin_task.png');
    $response = $this->post(route('tasks.images.store', $taskAssignedToAdmin), [
        'image' => $file2,
    ]);
    $response->assertStatus(403);

    // 5. Test: Admin uploading to member's task -> Success (Admin role bypasses assignment check)
    $this->actingAs($admin);
    $file3 = UploadedFile::fake()->image('admin_upload.png');
    $response = $this->post(route('tasks.images.store', $taskAssignedToMember), [
        'image' => $file3,
    ]);
    $response->assertRedirect();

    // 6. Test: Non-member uploading to member's task -> 403 Forbidden
    $this->actingAs($nonMember);
    $file4 = UploadedFile::fake()->image('non_member_upload.png');
    $response = $this->post(route('tasks.images.store', $taskAssignedToMember), [
        'image' => $file4,
    ]);
    $response->assertStatus(403);
});

it('allows authenticated user to create a task without a project', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->post(route('tasks.store'), [
        'project_id' => null,
        'title' => 'Personal Projectless Task',
        'description' => 'Personal description.',
        'status' => 1,
        'priority' => 2,
    ]);

    $response->assertRedirect(route('tasks.index'));
    $this->assertDatabaseHas('tasks', [
        'title' => 'Personal Projectless Task',
        'project_id' => null,
        'user_id' => $user->id,
        'status' => 1,
        'priority' => 2,
    ]);
});

it('allows user to view, edit and delete a projectless task they own', function () {
    $user = User::factory()->create();
    $task = Task::create([
        'title' => 'Projectless Task',
        'project_id' => null,
        'user_id' => $user->id,
        'status' => 1,
        'priority' => 2,
    ]);

    $this->actingAs($user);

    // View task
    $response = $this->get(route('tasks.show', $task));
    $response->assertStatus(200);
    $response->assertSee('Projectless Task');

    // Update task
    $response = $this->patch(route('tasks.update', $task), [
        'title' => 'Updated Projectless Task',
        'status' => 2,
    ]);
    $response->assertRedirect();
    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'title' => 'Updated Projectless Task',
        'status' => 2,
    ]);

    // Delete task
    $response = $this->delete(route('tasks.destroy', $task));
    $response->assertRedirect();
    $this->assertSoftDeleted('tasks', [
        'id' => $task->id,
    ]);
});

it('prevents other users from editing or viewing a projectless task they do not own or are not assigned to', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $task = Task::create([
        'title' => 'Private Projectless Task',
        'project_id' => null,
        'user_id' => $owner->id,
        'status' => 1,
        'priority' => 2,
    ]);

    $this->actingAs($other);

    // View task -> 403
    $response = $this->get(route('tasks.show', $task));
    $response->assertStatus(403);

    // Update task -> 403
    $response = $this->patch(route('tasks.update', $task), [
        'title' => 'Hacked Task',
    ]);
    $response->assertStatus(403);

    // Delete task -> 403
    $response = $this->delete(route('tasks.destroy', $task));
    $response->assertStatus(403);
});

it('allows user to filter tasks by personal / projectless tasks', function () {
    $user = User::factory()->create();
    $project = Project::create([
        'name' => 'Some Project',
        'slug' => 'some-project',
        'theme' => '#ff0000',
        'status' => 1,
        'priority' => 1,
        'user_id' => $user->id,
        'company_id' => null,
    ]);

    // Task with project
    Task::create([
        'title' => 'Project Task',
        'project_id' => $project->id,
        'status' => 1,
        'priority' => 1,
    ]);

    // Task without project (Personal)
    Task::create([
        'title' => 'Personal Task',
        'project_id' => null,
        'user_id' => $user->id,
        'status' => 1,
        'priority' => 1,
    ]);

    $this->actingAs($user);

    $response = $this->get(route('tasks.index', ['project' => 'none']));
    $response->assertStatus(200);
    $response->assertSee('Personal Task');
    $response->assertDontSee('Project Task');
});
