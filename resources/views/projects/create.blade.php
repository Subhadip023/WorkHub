@extends('layouts.app')

@section('content')
<div class="container">

    <h2>Create Project</h2>

    <!-- 🔑 JOIN CODE CARD -->
    <div class="alert alert-info mb-4">
        <strong>Company Join Code:</strong>
        <span class="badge bg-dark fs-6">
            {{ $company->join_code }}
        </span>
        <br>
        <small>Share this code with your team members to join your company.</small>
    </div>

    <!-- PROJECT FORM -->
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

        <button class="btn btn-primary">Create Project</button>
    </form>

</div>
@endsection
