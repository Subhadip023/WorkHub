@extends('layouts.admin')

@section('content')
<div class="container mt-4">

    <h2>Create Project</h2>

    <form method="POST" action="{{ route('projects.store') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label">Project Name</label>
            <input type="text"
                   name="name"
                   class="form-control"
                   required>
        </div>

        <button type="submit" class="btn btn-primary">
            Create Project
        </button>

        <a href="{{ route('projects.index') }}" class="btn btn-secondary">
            Back
        </a>
    </form>

</div>
@endsection
