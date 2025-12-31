<!DOCTYPE html>
<html>
<head>
    <title>Create Project</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
@if(auth()->user()->role === 'admin')
    <div class="alert alert-info">
        <strong>Company Join Code:</strong>
        {{ $company->join_code }}
    </div>
@endif


<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4>Create New Project</h4>
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('projects.store') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Project Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control"></textarea>
                </div>

                <button class="btn btn-success w-100">Create Project</button>
            </form>
        </div>
    </div>
</div>
@if(auth()->user()->role === 'admin')
    <h5>Company Members</h5>

    <ul class="list-group mb-3">
        @foreach($members as $member)
            <li class="list-group-item d-flex justify-content-between">
                {{ $member->name ?? $member->email }}

                <span class="badge bg-secondary">
                    {{ $member->role }}
                </span>
            </li>
        @endforeach
    </ul>
@endif


</body>
</html>
