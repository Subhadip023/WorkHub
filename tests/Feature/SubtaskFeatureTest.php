<?php

use App\Models\Task;
use App\Models\User;

test('authenticated user can view task show page with subtasks section', function () {
    $user = User::factory()->create();
    $task = Task::create([
        'title' => 'Parent Task 30',
        'user_id' => $user->id,
    ]);

    $response = $this->actingAs($user)->get(route('tasks.show', $task));

    $response->assertStatus(200);
    $response->assertSee('Subtasks');
    $response->assertSee('Add Subtask');
});

test('authenticated user can create a subtask from parent task page', function () {
    $user = User::factory()->create();
    $parentTask = Task::create([
        'title' => 'Parent Task for Subtask',
        'user_id' => $user->id,
    ]);

    $response = $this->actingAs($user)->post(route('tasks.subtasks.store', $parentTask), [
        'title' => 'My Brand New Subtask',
        'priority' => 2,
        'type' => 1,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success', 'Subtask created successfully.');

    $this->assertDatabaseHas('tasks', [
        'title' => 'My Brand New Subtask',
        'parent_id' => $parentTask->id,
        'user_id' => $user->id,
    ]);

    $subtask = Task::where('title', 'My Brand New Subtask')->first();
    expect($subtask->parent_id)->toBe($parentTask->id);
});

test('subtask detail page displays parent task banner link', function () {
    $user = User::factory()->create();
    $parentTask = Task::create([
        'title' => 'Main Parent Task Title',
        'user_id' => $user->id,
    ]);

    $subtask = Task::create([
        'title' => 'Child Subtask Title',
        'parent_id' => $parentTask->id,
        'user_id' => $user->id,
    ]);

    $response = $this->actingAs($user)->get(route('tasks.show', $subtask));

    $response->assertStatus(200);
    $response->assertSee('Parent Task');
    $response->assertSee('Main Parent Task Title');
});
