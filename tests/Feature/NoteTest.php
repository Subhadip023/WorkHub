<?php

use App\Models\User;
use App\Models\Company;
use App\Models\CompanyUsers;
use App\Models\Project;
use App\Models\Task;
use App\Models\Note;

it('allows authenticated users to view notes index page', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    
    $response = $this->actingAs($user)->get(route('notes.index'));
    
    $response->assertStatus(200);
    $response->assertSee('Notes');
});

it('allows creating a personal note', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    
    $response = $this->actingAs($user)->post(route('notes.store'), [
        'title' => 'My Personal Note',
        'note_type' => Note::TYPE_PERSONAL,
        'description' => 'Personal note description here',
    ]);
    
    $response->assertStatus(302);
    $this->assertDatabaseHas('notes', [
        'title' => 'My Personal Note',
        'user_id' => $user->id,
        'note_type' => Note::TYPE_PERSONAL,
        'note_type_id' => $user->id,
        'description' => 'Personal note description here',
    ]);
});

it('allows creating a project note', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $project = Project::create([
        'name' => 'Project A',
        'slug' => 'project-a',
        'theme' => '#ff0000',
        'status' => 1,
        'priority' => 1,
        'user_id' => $user->id,
    ]);

    $response = $this->actingAs($user)->post(route('notes.store'), [
        'title' => 'Project A Note',
        'note_type' => Note::TYPE_PROJECT,
        'note_type_id' => $project->id,
        'description' => 'Project note description',
    ]);

    $response->assertStatus(302);
    $this->assertDatabaseHas('notes', [
        'title' => 'Project A Note',
        'user_id' => $user->id,
        'note_type' => Note::TYPE_PROJECT,
        'note_type_id' => $project->id,
    ]);
});

it('allows updating a note', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $note = Note::create([
        'title' => 'Initial Title',
        'description' => 'Initial Description',
        'note_type' => Note::TYPE_PERSONAL,
        'note_type_id' => $user->id,
    ]);

    $response = $this->actingAs($user)->patch(route('notes.update', $note), [
        'title' => 'Updated Title',
        'description' => 'Updated Description',
    ]);

    $response->assertStatus(302);
    $this->assertDatabaseHas('notes', [
        'id' => $note->id,
        'title' => 'Updated Title',
        'description' => 'Updated Description',
    ]);
});

it('allows deleting a note', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $note = Note::create([
        'title' => 'Title to delete',
        'description' => 'Description to delete',
        'note_type' => Note::TYPE_PERSONAL,
        'note_type_id' => $user->id,
    ]);

    $response = $this->actingAs($user)->delete(route('notes.destroy', $note));

    $response->assertStatus(302);
    $this->assertDatabaseMissing('notes', [
        'id' => $note->id,
    ]);
});

it('prevents users from updating notes they do not own or have access to', function () {
    $user1 = User::factory()->create(['email_verified_at' => now()]);
    $user2 = User::factory()->create(['email_verified_at' => now()]);
    
    $note = Note::create([
        'title' => 'User 1 Note',
        'description' => 'Secret note',
        'note_type' => Note::TYPE_PERSONAL,
        'note_type_id' => $user1->id,
    ]);

    $response = $this->actingAs($user2)->patch(route('notes.update', $note), [
        'title' => 'Hacked Title',
        'description' => 'Hacked Description',
    ]);

    $response->assertStatus(403);
});

it('allows viewing notes create page', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    
    $response = $this->actingAs($user)->get(route('notes.create'));
    
    $response->assertStatus(200);
    $response->assertSee('Create New Note');
});

it('allows viewing notes edit page for owned note', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $note = Note::create([
        'title' => 'My Editable Note',
        'description' => 'Edit desc',
        'note_type' => Note::TYPE_PERSONAL,
        'note_type_id' => $user->id,
    ]);
    
    $response = $this->actingAs($user)->get(route('notes.edit', $note));
    
    $response->assertStatus(200);
    $response->assertSee('Edit Note');
    $response->assertSee('My Editable Note');
});

it('prevents viewing notes edit page for unauthorized note', function () {
    $user1 = User::factory()->create(['email_verified_at' => now()]);
    $user2 = User::factory()->create(['email_verified_at' => now()]);
    $note = Note::create([
        'title' => 'User 1 Secret Note',
        'description' => 'Secret desc',
        'note_type' => Note::TYPE_PERSONAL,
        'note_type_id' => $user1->id,
    ]);
    
    $response = $this->actingAs($user2)->get(route('notes.edit', $note));
    
    $response->assertStatus(403);
});

it('allows viewing note details page for authorized note', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $note = Note::create([
        'title' => 'My Viewable Note',
        'description' => 'View desc info',
        'note_type' => Note::TYPE_PERSONAL,
        'note_type_id' => $user->id,
    ]);
    
    $response = $this->actingAs($user)->get(route('notes.show', $note));
    
    $response->assertStatus(200);
    $response->assertSee('My Viewable Note');
});

it('prevents viewing note details page for unauthorized note', function () {
    $user1 = User::factory()->create(['email_verified_at' => now()]);
    $user2 = User::factory()->create(['email_verified_at' => now()]);
    $note = Note::create([
        'title' => 'Private Secret Note',
        'description' => 'Secret content',
        'note_type' => Note::TYPE_PERSONAL,
        'note_type_id' => $user1->id,
    ]);
    
    $response = $this->actingAs($user2)->get(route('notes.show', $note));
    
    $response->assertStatus(403);
});

it('allows downloading note as PDF for authorized note', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $note = Note::create([
        'title' => 'My PDF Note',
        'description' => 'PDF desc content',
        'note_type' => Note::TYPE_PERSONAL,
        'note_type_id' => $user->id,
    ]);
    
    $response = $this->actingAs($user)->get(route('notes.pdf', $note));
    
    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/pdf');
});

it('prevents downloading note as PDF for unauthorized note', function () {
    $user1 = User::factory()->create(['email_verified_at' => now()]);
    $user2 = User::factory()->create(['email_verified_at' => now()]);
    $note = Note::create([
        'title' => 'User 1 Secret PDF',
        'description' => 'Secret PDF desc',
        'note_type' => Note::TYPE_PERSONAL,
        'note_type_id' => $user1->id,
    ]);
    
    $response = $this->actingAs($user2)->get(route('notes.pdf', $note));
    
    $response->assertStatus(403);
});
