@extends('layouts.app')

@section('title', 'Tasks')

@section('content')
<div class="container mt-4">

    <h1 class="h3 mb-4 text-gray-800">
        {{ $project->name }} – Tasks
    </h1>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('tasks.store', $project) }}" class="mb-4">
        @csrf

        <div class="form-group">
            <input type="text" name="title" class="form-control" placeholder="Task title" required>
        </div>

        <div class="form-group mt-2">
            <textarea name="description" class="form-control" placeholder="Task description"></textarea>
        </div>

        <button class="btn btn-primary mt-3">
            Add Task
        </button>
    </form>

    @forelse($tasks as $task)
        <div class="card mb-2">
            <div class="card-body">
                <strong>{{ $task->title }}</strong>
                <p class="mb-0 text-muted">{{ $task->description ?? '-' }}</p>
            </div>
        </div>
    @empty
        <p class="text-muted">No tasks yet.</p>
    @endforelse

</div>
@endsection
