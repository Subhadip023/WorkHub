@extends('layouts.app')

@section('content')
<div class="container">

    <h2>Create Project</h2>

    <!-- JOIN CODE -->
    <div class="alert alert-info mb-4">
        <strong>Company Join Code:</strong>
        <span class="badge bg-dark fs-6">
            {{ $company->join_code }}
        </span>
    </div>

    {{-- 🔒 ADMIN ONLY: MEMBER LIST --}}
    @if(auth()->user()->isCompanyAdmin())
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                Company Members ({{ $members->count() }})
            </div>

            <div class="card-body p-0">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($members as $index => $member)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    {{ $member->name }}
                                    @if($member->id === $company->created_by)
                                        <span class="badge bg-success">Admin</span>
                                    @endif
                                </td>
                                <td>{{ $member->email }}</td>
                                <td>{{ $member->created_at->diffForHumans() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

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
