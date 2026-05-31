@extends('layouts.admin')

@section('title', 'Projects')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Projects</h1>
    <a href="{{ route('projects.create') }}" class="btn btn-primary shadow-sm">
        <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Add Project
    </a>
</div>

<!-- Projects Grid -->
<div class="row">
    @forelse ($projects as $project)
        <div class="col-lg-4 col-md-6 col-12 mb-4">
            <div class="card shadow h-100" style="border-top: 4px solid {{ $project->theme }}; border-left: 1px solid rgba(0,0,0,0.05); border-right: 1px solid rgba(0,0,0,0.05); border-bottom: 1px solid rgba(0,0,0,0.05);">
                <div class="card-body d-flex flex-column">
                    <!-- Title and Status -->
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <a href="{{ route('projects.show', $project) }}" class="h5 font-weight-bold text-primary mb-0 text-decoration-none text-truncate" style="max-width: 70%;" title="{{ $project->name }}">
                            {{ $project->name }}
                        </a>
                        <div>
                            @if($project->status == 1)
                                <span class="badge badge-secondary px-2 py-1 font-weight-bold shadow-sm">To Do</span>
                            @elseif($project->status == 2)
                                <span class="badge badge-primary px-2 py-1 font-weight-bold shadow-sm">In Progress</span>
                            @elseif($project->status == 3)
                                <span class="badge badge-success px-2 py-1 font-weight-bold shadow-sm">Completed</span>
                            @elseif($project->status == 4)
                                <span class="badge badge-warning px-2 py-1 font-weight-bold shadow-sm">On Hold</span>
                            @else
                                <span class="badge badge-dark px-2 py-1 font-weight-bold shadow-sm">Unknown</span>
                            @endif
                        </div>
                    </div>

                    <!-- Priority and Workspace Context -->
                    <div class="mb-3 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-xs text-gray-500 font-weight-bold text-uppercase mr-1">Priority:</span>
                            @if($project->priority == 1)
                                <span class="badge badge-light border px-2 py-1 font-weight-bold text-gray-800 shadow-sm">Low</span>
                            @elseif($project->priority == 2)
                                <span class="badge badge-info px-2 py-1 font-weight-bold shadow-sm">Medium</span>
                            @elseif($project->priority == 3)
                                <span class="badge badge-warning px-2 py-1 font-weight-bold shadow-sm">High</span>
                            @elseif($project->priority == 4)
                                <span class="badge badge-danger px-2 py-1 font-weight-bold shadow-sm">Urgent</span>
                            @else
                                <span class="badge badge-dark px-2 py-1 font-weight-bold shadow-sm">Unknown</span>
                            @endif
                        </div>
                        @if($project->company)
                            <span class="badge badge-light border text-gray-600 px-2 py-1 font-weight-semibold shadow-sm text-truncate" style="max-width: 110px;" title="{{ $project->company->name }}">
                                <i class="fas fa-building mr-1 text-primary"></i>{{ $project->company->name }}
                            </span>
                        @else
                            <span class="badge badge-light border text-gray-600 px-2 py-1 font-weight-semibold shadow-sm">
                                <i class="fas fa-user mr-1 text-primary"></i>Personal
                            </span>
                        @endif
                    </div>

                    @php
                        $pTotal = $project->tasks->count();
                        $pCompleted = $project->tasks->where('status', 3)->count();
                        $pPercentage = $pTotal > 0 ? round(($pCompleted / $pTotal) * 100) : 0;

                        if ($pPercentage < 30) {
                            $barClass = 'bg-danger';
                        } elseif ($pPercentage < 70) {
                            $barClass = 'bg-warning';
                        } elseif ($pPercentage < 100) {
                            $barClass = ''; // default theme color
                        } else {
                            $barClass = 'bg-success';
                        }
                    @endphp

                    <!-- Progress Section -->
                    <div class="mb-3">
                        <div class="progress progress-sm mb-1" style="height: 8px; border-radius: 4px;">
                            <div class="progress-bar {{ $barClass }}" role="progressbar" 
                                 style="width: {{ $pPercentage }}%; background-color: {{ $barClass == '' ? $project->theme : '' }}; border-radius: 4px;"
                                 aria-valuenow="{{ $pPercentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center text-xs font-weight-bold text-gray-600">
                            <span>Progress: {{ $pPercentage }}%</span>
                            <span>{{ $pCompleted }}/{{ $pTotal }} Tasks</span>
                        </div>
                    </div>

                    <hr class="my-2">

                    <!-- Actions -->
                    <div class="row no-gutters mt-2">
                        <div class="col-6 pr-1">
                            <a href="{{ route('projects.show', $project) }}" class="btn btn-sm btn-primary btn-block shadow-sm">
                                <i class="fas fa-eye mr-1"></i> View Tasks
                            </a>
                        </div>
                        <div class="col-6 pl-1">
                            <a href="{{ route('projects.edit', $project) }}" class="btn btn-sm btn-outline-info btn-block shadow-sm">
                                <i class="fas fa-edit mr-1"></i> Edit
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-center py-5">
            <div class="text-center mb-4">
                <img class="img-fluid px-3 px-sm-4 mt-3 mb-4" style="width: 15rem;"
                    src="{{ asset('asset/img/undraw_to-do-list_o3jf.svg') }}" alt="No projects found">
            </div>
            <h4 class="text-gray-600 font-weight-bold">No projects found.</h4>
            <p class="text-gray-500">Create a project to start tracking your tasks.</p>
            <a href="{{ route('projects.create') }}" class="btn btn-primary shadow-sm mt-2">
                <i class="fas fa-plus mr-1"></i> Create Project
            </a>
        </div>
    @endforelse
</div>

<!-- Pagination -->
@if ($projects->hasPages())
    <div class="row mt-3">
        <div class="col-12 d-flex justify-content-center">
            {!! $projects->links() !!}
        </div>
    </div>
@endif

@endsection
