<!DOCTYPE html>
<html>
<head>
    <title>Projects</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="d-flex justify-content-between mb-3">
        <h3>Your Projects</h3>
        <a href="/projects/create" class="btn btn-primary">+ New Project</a>
    </div>

    @foreach($projects as $project)
        <div class="card mb-2">
            <div class="card-body d-flex justify-content-between">
                <div>
                    <h5>{{ $project->name }}</h5>
                    <p>{{ $project->description }}</p>
                </div>
                <a href="/projects/{{ $project->id }}/tasks" class="btn btn-success">
                    View Tasks
                </a>
            </div>
        </div>
    @endforeach
</div>

</body>
</html>
