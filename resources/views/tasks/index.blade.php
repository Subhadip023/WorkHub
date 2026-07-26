@extends('layouts.admin')

@section('title', 'Tasks')



@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800 font-weight-bold">Tasks</h1>
    <div class="d-flex flex-column align-items-center gap-2">
        <button class="btn btn-primary shadow-sm" id="btnShowInlineAdd">
            <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Add Task
        </button>
        <small class="text-muted"> Press (Alt + t) to add task</small>
    </div>
</div>
<div class="row">
    <!-- Total Tasks Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Tasks</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalCount }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Tasks Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending Tasks</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $pendingCount }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Completed Tasks Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Completed Tasks</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $completedCount }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-double fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Overdue Tasks Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-danger shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Overdue Tasks</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $overdueCount }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters Card -->
<div class="card shadow mb-4">
    <div class="card-body py-3">
        <div class="row align-items-center">
            <div class="col-md-3 mb-2 mb-md-0">
                <label for="filterProject" class="font-weight-bold text-xs text-gray-700 text-uppercase">Project</label>
                <select id="filterProject" class="form-control form-control-sm">
                    <option value="all" {{ request('project') == 'all' || !request('project') ? 'selected' : '' }}>All Projects</option>
                    <option value="none" {{ request('project') == 'none' ? 'selected' : '' }}>Personal</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}" {{ request('project') == $project->id ? 'selected' : '' }}>{{ $project->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 mb-2 mb-md-0">
                <label for="filterStatus" class="font-weight-bold text-xs text-gray-700 text-uppercase">Status</label>
                <select id="filterStatus" class="form-control form-control-sm">
                    <option value="all" {{ request('status') == 'all' || !request('status') ? 'selected' : '' }}>All Statuses</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                </select>
            </div>
            <div class="col-md-2 mb-2 mb-md-0">
                <label for="filterAssignee" class="font-weight-bold text-xs text-gray-700 text-uppercase">Assignee</label>
                <select id="filterAssignee" class="form-control form-control-sm">
                    <option value="all" {{ request('assignee') == 'all' || !request('assignee') ? 'selected' : '' }}>All Assignees</option>
                    <option value="unassigned" {{ request('assignee') == 'unassigned' ? 'selected' : '' }}>Unassigned</option>
                    @foreach($companyUsers as $user)
                        <option value="{{ $user->id }}" {{ request('assignee') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 mb-2 mb-md-0">
                <label for="filterType" class="font-weight-bold text-xs text-gray-700 text-uppercase">Type</label>
                <select id="filterType" class="form-control form-control-sm">
                    <option value="all" {{ request('type') == 'all' || !request('type') ? 'selected' : '' }}>All Types</option>
                    <option value="1" {{ request('type') == '1' ? 'selected' : '' }}>Task</option>
                    <option value="2" {{ request('type') == '2' ? 'selected' : '' }}>Bug</option>
                    <option value="3" {{ request('type') == '3' ? 'selected' : '' }}>Feature</option>
                    <option value="4" {{ request('type') == '4' ? 'selected' : '' }}>Improvement</option>
                </select>
            </div>
            <div class="col-md-3 text-left text-md-right mt-3 mt-md-0 pt-md-4">
                <button id="resetFilters" class="btn btn-sm btn-secondary btn-block-xs">
                    <i class="fas fa-undo fa-xs mr-1"></i> Reset Filters
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Tasks List Card -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">All Tasks</h6> 
    </div>
    <div class="card-body">
        @include('partials.tasks_table', [
            'tasks' => $tasks,
            'companyUsers' => $companyUsers,
            'projects' => $projects,
            'showProjectColumn' => true
        ])
    </div>
    <div class="card-footer py-2">
        <div class="d-flex justify-content-center">
            {!! $tasks->withQueryString()->links() !!}
        </div>
    </div>
</div>

        <form action="{{ route('tasks.store') }}" method="POST" id="inlineAddTaskForm" style="display:none;">
            @csrf
        </form>

@include('partials.edit_task_modal')
@endsection

@push('scripts')
<script src="{{ asset('asset/js/tasks.js') }}"></script>
<script>
    $(document).ready(function() {
        // Filter handler
        function applyFilters() {
            var selectedProject = $('#filterProject').val();
            var selectedStatus = $('#filterStatus').val();
            var selectedAssignee = $('#filterAssignee').val();
            var selectedType = $('#filterType').val();

            var params = new URLSearchParams(window.location.search);
            
            if (selectedProject && selectedProject !== 'all') {
                params.set('project', selectedProject);
            } else {
                params.delete('project');
            }

            if (selectedStatus && selectedStatus !== 'all') {
                params.set('status', selectedStatus);
            } else {
                params.delete('status');
            }

            if (selectedAssignee && selectedAssignee !== 'all') {
                params.set('assignee', selectedAssignee);
            } else {
                params.delete('assignee');
            }

            if (selectedType && selectedType !== 'all') {
                params.set('type', selectedType);
            } else {
                params.delete('type');
            }

            params.delete('page');

            window.location.href = window.location.pathname + '?' + params.toString();
        }

        // Event listeners for filters
        $('#filterProject, #filterStatus, #filterAssignee, #filterType').change(function() {
            applyFilters();
        });

        // Reset filters
        $('#resetFilters').click(function() {
            $('#filterProject').val('all');
            $('#filterStatus').val('all');
            $('#filterAssignee').val('all');
            $('#filterType').val('all');
            applyFilters();
        });

        $('#showCompleted').change(function() {
            if ($(this).is(':checked')) {
                $('.task-row').show();
            } else {
                $('.task-row').each(function() {
                    if ($(this).data('completed') === 'completed') {
                        $(this).hide();
                    }
                });
            }
        });

        // When press alt + t show inline add row
        $(document).keydown(function(e) {
            if (e.altKey && e.key === 't') {
                e.preventDefault();
                $('#btnShowInlineAdd').click();
            }
        });

        // When press esc close inline add row
        $(document).keydown(function(e) {
            if (e.key === 'Escape') {
                e.preventDefault();
                $('#inlineAddRow').hide();
            }
        });
    });
</script>
@endpush
