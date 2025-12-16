<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assign Task | WorkHub</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>

<h2>Assign Task</h2>

{{-- Success Message --}}
@if (session('success'))
    <p style="color: green;">
        {{ session('success') }}
    </p>
@endif

{{-- Validation Errors --}}
@if ($errors->any())
    <ul style="color: red;">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
@endif

<form method="POST" action="{{ route('tasks.store') }}">
    @csrf

    <label>Task Title</label><br>
    <input type="text" name="title" placeholder="Task title" required>
    <br><br>

    <label>Task Description</label><br>
    <textarea name="description" placeholder="Task description"></textarea>
    <br><br>

    <label>Assign To Employee</label><br>
    <select name="user_id" required>
        <option value="">-- Select Employee --</option>
        @foreach ($employees as $employee)
            <option value="{{ $employee->id }}">
                {{ $employee->name }}
            </option>
        @endforeach
    </select>
    <br><br>

    {{-- TEMP: Static company_id (will be dynamic later) --}}
    <input type="hidden" name="company_id" value="1">

    <button type="submit">Assign Task</button>
</form>

</body>
</html>
