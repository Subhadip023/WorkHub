<!DOCTYPE html>
<html>
<head>
    <title>Task Manager</title>
    <style>
        body { font-family: Arial; background:#f4f4f4; }
        .box { width:500px; margin:auto; background:#fff; padding:20px; }
        input, textarea { width:100%; padding:8px; margin:5px 0; }
        button { padding:8px 15px; background:#28a745; color:white; border:none; }
        .task { margin-top:10px; padding:10px; border-bottom:1px solid #ccc; }
        .delete { background:red; margin-top:5px; }
        .error { color:red; }
        .success { color:green; }
    </style>
</head>
<body>

<div class="box">
    <h2>Add Task</h2>

    @if(session('success'))
        <p class="success">{{ session('success') }}</p>
    @endif

    @if($errors->any())
        @foreach($errors->all() as $error)
            <p class="error">{{ $error }}</p>
        @endforeach
    @endif

    <form method="POST" action="/tasks">
        @csrf
        <input type="text" name="title" placeholder="Task title">
        <textarea name="description" placeholder="Task description"></textarea>
        <button type="submit">Add Task</button>
    </form>

    <hr>

    <h3>Task List</h3>

    @foreach($tasks as $task)
        <div class="task">
            <strong>{{ $task->title }}</strong><br>
            {{ $task->description }}

            <form method="POST" action="/tasks/{{ $task->id }}">
                @csrf
                @method('DELETE')
                <button class="delete">Delete</button>
            </form>
        </div>
    @endforeach
</div>

</body>
</html>
