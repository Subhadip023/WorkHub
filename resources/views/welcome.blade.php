@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-3">
    <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
    <h2 class="h5 mb-0 text-gray-600 font-weight-bold">
        <i class="fas fa-cubes mr-1 text-primary"></i> {{ $currentWorkspaceName }}
    </h2>
</div>

<!-- Workspace Filter Selector -->
@if(auth()->user()->companies->isNotEmpty())
    <div class="mb-4">
        <span class="text-xs font-weight-bold text-gray-600 text-uppercase mr-2"><i class="fas fa-filter mr-1"></i> Filter Workspace:</span>
        <a href="{{ route('dashboard') }}" class="btn btn-sm {{ empty($company) ? 'btn-primary shadow-sm' : 'btn-light border text-gray-800' }} mr-1 mb-1" style="border-radius: 20px;">
            All Workspaces
        </a>
        @foreach(auth()->user()->companies as $cUser)
            @if($cUser->company)
                <a href="{{ route('dashboard.org', $cUser->company) }}" class="btn btn-sm {{ (!empty($company) && $company->id == $cUser->company->id) ? 'btn-primary shadow-sm' : 'btn-light border text-gray-800' }} mr-1 mb-1" style="border-radius: 20px;">
                    {{ $cUser->company->name }}
                </a>
            @endif
        @endforeach
    </div>
@endif

<!-- Content Row -->
<div class="row">
    <!-- Projects Card Example -->
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Projects
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $projectsCount }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-project-diagram fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

   @php
       $task_percentage = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;
   @endphp
    <!-- Tasks Card Example -->
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Tasks ({{ $completedTasks }}/{{ $totalTasks }})
                        </div>
                        <div class="row no-gutters align-items-center">
                            <div class="col-auto">
                                <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">{{ $task_percentage }}%</div>
                            </div>
                            <div class="col">
                                <div class="progress progress-sm mr-2">
                                    <div class="progress-bar bg-info" role="progressbar"
                                        style="width: {{ $task_percentage }}%" aria-valuenow="{{ $task_percentage }}" aria-valuemin="0"
                                        aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Requests Card Example -->
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Messages
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">18</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-comments fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Content Row -->
<div class="row">
    <div class="col-lg-6 mb-4">

        <!-- Illustrations -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Instractions</h6>
            </div>
            <div class="card-body">
                <div class="text-center">
                    <img class="img-fluid px-3 px-sm-4 mt-3 mb-4" style="width: 25rem;"
                        src="{{ asset('asset/img/undraw_posting_photo.svg') }}" alt="...">
                </div>
                <p>
                    Welcome to your <strong>Task & Project Management System</strong>.
                    Here you can easily manage your projects, assign tasks,
                    track progress, and collaborate with your team — all in one place.
                </p>

                <a target="_blank" rel="nofollow" href="https://your-project-link.com">
                    Learn more about this project →
                </a>

            </div>
        </div>

        <!-- Team Members Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Team Members</h6>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    @forelse($teamMembers as $member)
                        @if($member->user)
                            <div class="list-group-item px-0 py-3 d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <div class="bg-light rounded-circle p-2 mr-3" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-user text-primary"></i>
                                    </div>
                                    <div>
                                        <div class="font-weight-semibold text-gray-800">{{ $member->user->name }}</div>
                                        <div class="small text-gray-500">
                                            {{ $member->user->email }}
                                            @if($member->company)
                                                <span class="badge badge-light border ml-1">{{ $member->company->name }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    @if($member->role == 1)
                                        <span class="badge badge-success p-2">Admin</span>
                                    @else
                                        <span class="badge badge-secondary p-2">Member</span>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @empty
                        <p class="text-muted text-center py-3 mb-0">No team members found. Create or join an organization to collaborate.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Content Column -->
    <div class="col-lg-6 mb-4">

        <!-- Project Card Example -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Projects Progress</h6>
            </div>
            <div class="card-body">
                @if($projects->isEmpty())
                    <p class="text-muted text-center py-3 mb-0">No projects found. Create one from the <a href="{{ route('projects.index') }}">Projects page</a>.</p>
                @else
                    @foreach($projects as $project)
                        @php
                            $pTotal = $project->tasks->count();
                            $pCompleted = $project->tasks->where('status', 3)->count();
                            $pPercentage = $pTotal > 0 ? round(($pCompleted / $pTotal) * 100) : 0;

                            if ($pPercentage < 30) {
                                $barClass = 'bg-danger';
                            } elseif ($pPercentage < 70) {
                                $barClass = 'bg-warning';
                            } elseif ($pPercentage < 100) {
                                $barClass = ''; // default theme color / blue
                            } else {
                                $barClass = 'bg-success';
                            }
                        @endphp
                        <h4 class="small font-weight-bold">
                            <a href="{{ route('projects.show', $project) }}" class="text-gray-800 font-weight-bold">
                                {{ $project->name }}
                            </a>
                            <span class="float-right">
                                @if($pPercentage == 100)
                                    Complete!
                                @else
                                    {{ $pPercentage }}% ({{ $pCompleted }}/{{ $pTotal }})
                                @endif
                            </span>
                        </h4>
                        <div class="progress mb-4">
                            <div class="progress-bar {{ $barClass }}" role="progressbar" 
                                 style="width: {{ $pPercentage }}%; background-color: {{ $barClass == '' ? $project->theme : '' }}"
                                 aria-valuenow="{{ $pPercentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

       

    </div>

    
</div>
@endsection

@push('scripts')
<!-- Page level plugins -->
<script src="{{ asset('asset/vendor/chart.js/Chart.min.js') }}"></script>

<!-- Page level custom scripts -->
<script src="{{ asset('asset/js/demo/chart-area-demo.js') }}"></script>
<script src="{{ asset('asset/js/demo/chart-pie-demo.js') }}"></script>
@endpush
