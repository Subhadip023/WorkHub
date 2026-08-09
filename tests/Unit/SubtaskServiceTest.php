<?php

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskServiceInterface;

test('TaskService creates a subtask for a parent task inheriting project_id', function () {
    $user = User::factory()->create();
    $project = Project::create([
        'name' => 'Test Project',
        'slug' => 'test-project',
        'theme' => '#00ff00',
        'status' => 1,
        'priority' => 1,
        'user_id' => $user->id,
    ]);

    $parentTask = Task::create([
        'title' => 'Parent Task',
        'project_id' => $project->id,
        'user_id' => $user->id,
    ]);

    $taskService = app(TaskServiceInterface::class);

    $subtask = $taskService->createSubtask($parentTask, [
        'title' => 'Subtask 1',
        'status' => 1,
    ], $user);

    expect($subtask->parent_id)->toBe($parentTask->id)
        ->and($subtask->project_id)->toBe($project->id)
        ->and($subtask->title)->toBe('Subtask 1');
});

test('TaskService retrieves all subtasks for a parent task', function () {
    $user = User::factory()->create();
    $parentTask = Task::create([
        'title' => 'Parent Task',
        'user_id' => $user->id,
    ]);

    $subtask1 = Task::create([
        'title' => 'Subtask 1',
        'parent_id' => $parentTask->id,
        'user_id' => $user->id,
    ]);

    $subtask2 = Task::create([
        'title' => 'Subtask 2',
        'parent_id' => $parentTask->id,
        'user_id' => $user->id,
    ]);

    $taskService = app(TaskServiceInterface::class);
    $subtasks = $taskService->getSubtasks($parentTask);

    expect($subtasks)->toHaveCount(2)
        ->and($subtasks->pluck('id'))->toContain($subtask1->id, $subtask2->id);
});

test('TaskService updates and deletes a subtask using task service methods', function () {
    $user = User::factory()->create();
    $parentTask = Task::create([
        'title' => 'Parent Task',
        'user_id' => $user->id,
    ]);

    $subtask = Task::create([
        'title' => 'Initial Subtask',
        'parent_id' => $parentTask->id,
        'user_id' => $user->id,
    ]);

    $taskService = app(TaskServiceInterface::class);

    $updated = $taskService->updateTask($subtask, [
        'title' => 'Updated Subtask Title',
    ], $user);

    expect($updated->title)->toBe('Updated Subtask Title');

    $deleted = $taskService->deleteTask($subtask, $user);

    expect($deleted)->toBeTrue();
    expect(Task::find($subtask->id))->toBeNull();
});

test('TaskService correctly calculates subtask progress statistics', function () {
    $user = User::factory()->create();
    $parentTask = Task::create([
        'title' => 'Parent Task',
        'user_id' => $user->id,
    ]);

    $taskService = app(TaskServiceInterface::class);

    // Initial empty progress
    $emptyProgress = $taskService->getSubtaskProgress($parentTask);
    expect($emptyProgress)->toBe([
        'total' => 0,
        'completed' => 0,
        'percentage' => 0.0,
    ]);

    // Create 4 subtasks: 2 completed (status=3), 2 pending (status=1)
    Task::create(['title' => 'Sub1', 'parent_id' => $parentTask->id, 'user_id' => $user->id, 'status' => 3]);
    Task::create(['title' => 'Sub2', 'parent_id' => $parentTask->id, 'user_id' => $user->id, 'status' => 3]);
    Task::create(['title' => 'Sub3', 'parent_id' => $parentTask->id, 'user_id' => $user->id, 'status' => 1]);
    Task::create(['title' => 'Sub4', 'parent_id' => $parentTask->id, 'user_id' => $user->id, 'status' => 1]);

    $progress = $taskService->getSubtaskProgress($parentTask->fresh());

    expect($progress['total'])->toBe(4)
        ->and($progress['completed'])->toBe(2)
        ->and($progress['percentage'])->toBe(50.0);
});
