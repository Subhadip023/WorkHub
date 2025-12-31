@extends('layouts.app')

@section('content')
<div class="container">

<h3>Tasks for {{ $project->name }}</h3>

@if(auth()->user()->role === 'admin')
<form method="POST" action="{{ route('tasks.store') }}">
@csrf

<input type="hidden" name="project_id" value="{{ $project->id }}">

<input name="title" class="form-control mb-2" placeholder="Task title" required>
<input name="description" class="form-control mb-2" placeholder="Description">

<button class="btn btn-primary">Add Task</button>
</form>
<hr>
@endif

@foreach($tasks as $task)
<div class="card mb-2 p-2">
<strong>{{ $task->title }}</strong>
<p>{{ $task->description }}</p>
<span class="badge bg-secondary">{{ $task->status }}</span>
</div>
@endforeach

</div>
@endsection
