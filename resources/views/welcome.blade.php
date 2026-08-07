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

    <!-- Total Team Members Card -->
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Team Members
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $teamMembers->count() }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Dashboard Content Row -->
<div class="row">
    <!-- 2/3 Column: Today & Pending Tasks -->
    <div class="col-lg-8 mb-4">
        <!-- Today & Pending Tasks Section (Priority-wise) -->
        <div class="card shadow mb-4 border-left-primary">
            <div class="card-header py-3 d-flex flex-wrap align-items-center justify-content-between">
                <div class="d-flex align-items-center mb-2 mb-md-0">
                    <h6 class="m-0 font-weight-bold text-primary mr-3">
                        <i class="fas fa-user-check mr-1"></i> My Tasks (Assigned to Me)
                    </h6>
                    <span class="badge badge-light border text-gray-700 px-2 py-1 text-xs">
                        <i class="fas fa-sort-amount-down text-info mr-1"></i> Priority-wise (Urgent &rarr; Low)
                    </span>
                </div>

                <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                    <!-- Per Page Select Dropdown -->
                    <div class="d-flex align-items-center mr-2">
                        <label for="dashboardPerPage" class="small text-gray-600 font-weight-bold mr-1 mb-0" style="font-size: 0.75rem;">
                            Show:
                        </label>
                        <select id="dashboardPerPage" class="form-control form-control-sm border shadow-sm" style="width: auto; height: 28px; font-size: 0.75rem; padding: 2px 6px; border-radius: 6px;" onchange="changeDashboardPerPage(this.value)">
                            <option value="5" {{ $perPage == 5 ? 'selected' : '' }}>5</option>
                            <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                        </select>
                    </div>

                    <!-- Filter Pills / Tabs -->
                    <div class="btn-group btn-group-toggle flex-wrap" style="gap: 4px;">
                        @php
                            $baseUrl = !empty($company) ? route('dashboard.org', $company) : route('dashboard');
                        @endphp
                        <a href="{{ $baseUrl }}?task_filter=today_past&per_page={{ $perPage }}" class="btn btn-xs {{ $activeTaskFilter == 'today_past' ? 'btn-primary active font-weight-bold shadow-sm' : 'btn-light border text-gray-800' }}" style="border-radius: 15px; font-size: 0.8rem; padding: 4px 10px;">
                            Today & Past <span class="badge {{ $activeTaskFilter == 'today_past' ? 'badge-light text-primary' : 'badge-secondary' }} ml-1">{{ $todayPastCount }}</span>
                        </a>
                        <a href="{{ $baseUrl }}?task_filter=due_today&per_page={{ $perPage }}" class="btn btn-xs {{ $activeTaskFilter == 'due_today' ? 'btn-primary active font-weight-bold shadow-sm' : 'btn-light border text-gray-800' }}" style="border-radius: 15px; font-size: 0.8rem; padding: 4px 10px;">
                            Due Today <span class="badge {{ $activeTaskFilter == 'due_today' ? 'badge-light text-primary' : 'badge-secondary' }} ml-1">{{ $todayCount }}</span>
                        </a>
                        <a href="{{ $baseUrl }}?task_filter=overdue&per_page={{ $perPage }}" class="btn btn-xs {{ $activeTaskFilter == 'overdue' ? 'btn-danger active font-weight-bold shadow-sm' : 'btn-light border text-gray-800' }}" style="border-radius: 15px; font-size: 0.8rem; padding: 4px 10px;">
                            Overdue <span class="badge {{ $activeTaskFilter == 'overdue' ? 'badge-light text-danger' : 'badge-danger' }} ml-1">{{ $overdueCount }}</span>
                        </a>
                        <a href="{{ $baseUrl }}?task_filter=all_pending&per_page={{ $perPage }}" class="btn btn-xs {{ $activeTaskFilter == 'all_pending' ? 'btn-primary active font-weight-bold shadow-sm' : 'btn-light border text-gray-800' }}" style="border-radius: 15px; font-size: 0.8rem; padding: 4px 10px;">
                            All Pending <span class="badge {{ $activeTaskFilter == 'all_pending' ? 'badge-light text-primary' : 'badge-secondary' }} ml-1">{{ $allPendingCount }}</span>
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                @if($todayTasks->isEmpty())
                    <div class="text-center py-5 px-3">
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <h5 class="font-weight-bold text-gray-700">All caught up!</h5>
                        <p class="text-muted small mb-3">No pending tasks found for this filter.</p>
                        <a href="{{ route('tasks.index') }}" class="btn btn-sm btn-primary shadow-sm">
                            <i class="fas fa-plus mr-1"></i> View All Tasks / Create Task
                        </a>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" width="100%">
                            <thead class="bg-light text-gray-700 text-xs font-weight-bold text-uppercase">
                                <tr>
                                    <th style="width: 45px;" class="text-center">Done</th>
                                    <th style="width: 95px;">Priority</th>
                                    <th>Task Title</th>
                                    <th>Project</th>
                                    <th>Due Date</th>
                                    <th style="width: 65px;" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($todayTasks as $task)
                                    @php
                                        $isOverdue = $task->due_date && $task->due_date < $today;
                                        $isDueToday = $task->due_date && $task->due_date == $today;
                                    @endphp
                                    <tr class="task-item-row" style="{{ $task->priority == 4 ? 'background-color: rgba(231, 74, 59, 0.03);' : '' }}">
                                        <td class="text-center align-middle">
                                            @can('update', $task)
                                                <form action="{{ route('tasks.toggle', $task) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-link p-0 text-decoration-none" title="Mark as Completed">
                                                        <i class="far fa-square fa-lg text-gray-400 hover-text-success"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <i class="far fa-square fa-lg text-gray-300" style="opacity: 0.5;"></i>
                                            @endcan
                                        </td>
                                        <td class="align-middle">
                                            @if($task->priority == 4)
                                                <span class="badge badge-danger px-2 py-1 shadow-sm font-weight-bold">
                                                    <i class="fas fa-fire mr-1"></i> Urgent
                                                </span>
                                            @elseif($task->priority == 3)
                                                <span class="badge badge-warning text-dark px-2 py-1 shadow-sm font-weight-bold">
                                                    <i class="fas fa-arrow-up mr-1"></i> High
                                                </span>
                                            @elseif($task->priority == 2)
                                                <span class="badge badge-info px-2 py-1 shadow-sm font-weight-bold">
                                                    <i class="fas fa-minus mr-1"></i> Medium
                                                </span>
                                            @else
                                                <span class="badge badge-secondary px-2 py-1 shadow-sm">
                                                    <i class="fas fa-arrow-down mr-1"></i> Low
                                                </span>
                                            @endif
                                        </td>
                                        <td class="align-middle">
                                            <div class="font-weight-bold">
                                                <a href="{{ route('tasks.show', $task) }}" class="text-gray-900 text-decoration-none hover-primary">
                                                    {{ $task->title }}
                                                </a>
                                                @if($task->externalSource)
                                                    <span class="badge badge-dark text-xs px-1 ml-1" title="Created via External API">API</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            @if($task->project)
                                                <a href="{{ route('projects.show', $task->project) }}" class="badge text-white px-2 py-1 text-xs shadow-sm" style="background-color: {{ $task->project->theme }}">
                                                    <i class="fas fa-folder mr-1"></i>{{ $task->project->name }}
                                                </a>
                                            @else
                                                <span class="badge badge-light border text-muted px-2 py-1 text-xs">
                                                    <i class="fas fa-user-lock mr-1"></i> Personal
                                                </span>
                                            @endif
                                        </td>
                                        <td class="align-middle">
                                            @if($isOverdue)
                                                <span class="badge badge-danger px-2 py-1 shadow-sm" title="Overdue task!">
                                                    <i class="fas fa-exclamation-triangle mr-1"></i> Overdue ({{ \Carbon\Carbon::parse($task->due_date)->format('M d') }})
                                                </span>
                                            @elseif($isDueToday)
                                                <span class="badge badge-warning text-dark px-2 py-1 shadow-sm">
                                                    <i class="fas fa-clock mr-1"></i> Due Today
                                                </span>
                                            @elseif($task->due_date)
                                                <span class="badge badge-light border text-gray-800 px-2 py-1">
                                                    <i class="far fa-calendar-alt text-primary mr-1"></i> {{ \Carbon\Carbon::parse($task->due_date)->format('M d') }}
                                                </span>
                                            @else
                                                <span class="text-muted text-xs font-italic">No due date</span>
                                            @endif
                                        </td>
                                        <td class="text-center align-middle">
                                            <a href="{{ route('tasks.show', $task) }}" class="btn btn-sm btn-light border text-primary shadow-sm" title="View task details">
                                                <i class="fas fa-arrow-right"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
            @if($todayTasks->hasPages())
                <div class="card-footer py-2 bg-light">
                    <div class="d-flex justify-content-center">
                        {!! $todayTasks->withQueryString()->links() !!}
                    </div>
                </div>
            @endif
        </div>

        @if($projectsCount === 0)
            <!-- Getting Started (shown only to new users with no projects) -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Get Started with WorkHub</h6>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <img class="img-fluid px-3 px-sm-4 mt-1 mb-4" style="width: 18rem;"
                            src="{{ asset('asset/img/undraw_posting_photo.svg') }}" alt="WorkHub Workspace Illustration">
                    </div>
                    <p class="text-gray-700">
                        Welcome to <strong>WorkHub</strong>! This collaborative platform streamlines your projects, task classifications, and documentation.
                    </p>
                    <hr class="my-3">
                    <div class="small text-gray-600">
                        <h6 class="font-weight-bold text-gray-800 mb-2"><i class="fas fa-rocket mr-1 text-primary"></i> Quick Guide:</h6>
                        <ul class="pl-3 mb-3">
                            <li class="mb-2"><strong>Context Switching:</strong> Toggle between <em>Personal Space</em> and different <em>Organizations</em> using the top filter.</li>
                            <li class="mb-2"><strong>Projects & Tasks:</strong> Create projects, then add tasks classified as <strong>Bug, Feature, Task, or Improvement</strong> with status/priority settings.</li>
                            <li class="mb-2"><strong>Rich Documentation:</strong> Create notes under projects, tasks, or personal space, and download them as high-quality PDFs.</li>
                            <li class="mb-2"><strong>Collaborate:</strong> Invite team members to organizations, assign tasks, and comment in real-time.</li>
                        </ul>
                    </div>
                    <a href="{{ route('projects.index') }}" class="btn btn-sm btn-primary shadow-sm btn-block mt-3">
                        <i class="fas fa-folder-open mr-1"></i> Go to Projects
                    </a>
                </div>
            </div>
        @endif
    </div>

    <!-- 1/3 Column: Projects Progress & Team Members -->
    <div class="col-lg-4 mb-4">
        <!-- Projects Progress Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-project-diagram mr-1"></i> Projects Progress</h6>
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

        <!-- Team Members Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-users mr-1"></i> Team Members</h6>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    @forelse($teamMembers as $member)
                        @if($member->user)
                            <div class="list-group-item px-0 py-3 d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <div class="bg-light rounded-circle p-2 mr-3" style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-user text-primary text-xs"></i>
                                    </div>
                                    <div>
                                        <div class="font-weight-semibold text-gray-800 text-sm">{{ $member->user->name }}</div>
                                        <div class="small text-gray-500" style="font-size: 0.75rem;">
                                            {{ $member->user->email }}
                                            @if($member->company)
                                                <span class="badge badge-light border ml-1">{{ $member->company->name }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    @if($member->role == 1)
                                        <span class="badge badge-success px-2 py-1 text-xs">Admin</span>
                                    @else
                                        <span class="badge badge-secondary px-2 py-1 text-xs">Member</span>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @empty
                        <p class="text-muted text-center py-3 mb-0 small">No team members found. Create or join an organization to collaborate.</p>
                    @endforelse
                </div>
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
<script>
    function changeDashboardPerPage(val) {
        var url = new URL(window.location.href);
        url.searchParams.set('per_page', val);
        url.searchParams.set('page', '1');
        window.location.href = url.toString();
    }
</script>
@endpush
