<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Task Manager</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">

            <h2 class="text-center mb-4">📝 Task Manager</h2>

            <!-- Success Message -->
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Validation Errors -->
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Add Task Form -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-primary text-white">
                    Add New Task
                </div>
                <div class="card-body">
                    <form method="POST" action="/tasks">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Task Title</label>
                            <input type="text" name="title" class="form-control" placeholder="Enter task title">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" placeholder="Optional description"></textarea>
                        </div>

                        <button class="btn btn-success">Add Task</button>
                    </form>
                </div>
            </div>

            <!-- Task List -->
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    Task List
                </div>
                <div class="card-body">

                    @forelse ($tasks as $task)
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="mb-0">{{ $task->title }}</h6>
                                <small class="text-muted">{{ $task->description }}</small>
                            </div>

                            <form method="POST" action="/tasks/{{ $task->id }}">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </div>
                        <hr>
                    @empty
                        <p class="text-center text-muted">
                            No tasks added yet.
                        </p>
                    @endforelse

                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>
