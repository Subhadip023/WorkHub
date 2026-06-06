@extends('layouts.admin')

@section('title', 'Tasks')

@push('styles')
<style>
    .text-line-through {
        text-decoration: line-through;
    }
    @media (max-width: 576px) {
        .btn-block-xs {
            display: block;
            width: 100%;
        }
    }
</style>
@endpush

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
        <div class="form-check">
            <input class="form-check-input" type="checkbox" value="" id="showCompleted" style="margin-top: 6px">
            <label class="form-check-label" for="showCompleted">
                Show Completed Tasks
            </label>
        </div>
    </div>
    <div class="card-body">
        <div id="noTasksContainer" class="text-center py-5" style="display: {{ $tasks->isEmpty() ? 'block' : 'none' }}">
            <i class="fas fa-tasks fa-3x text-gray-300 mb-3"></i>
            <h5 class="text-gray-500 font-weight-bold">No tasks found</h5>
            <p class="text-gray-500 mb-0">Create tasks for your projects to see them listed here!</p>
        </div>

        <div class="table-responsive" id="tasksTableContainer" style="display: {{ $tasks->isEmpty() ? 'none' : 'block' }}">
            <table class="table table-hover table-bordered" id="tasksTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th style="width: 60px;" class="text-center">Done</th>
                        <th>Task Details</th>
                        <th class="d-none d-md-table-cell">Type</th>
                        <th class="d-none d-md-table-cell">Project</th>
                        <th class="d-none d-md-table-cell">Assigned To</th>
                        <th class="d-none d-md-table-cell">Due Date</th>
                        <th class="d-none d-md-table-cell">Status</th>
                        <th class="d-none d-md-table-cell">Priority</th>
                        <th style="width: 120px;" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Notion-like Inline Add Row -->
                    <tr id="inlineAddRow" data-user-id="{{ auth()->id() }}" style="display: none; background-color: rgba(78, 115, 223, 0.05);">
                        <td class="text-center align-middle">
                            <i class="far fa-square fa-2x text-gray-300"></i>
                        </td>
                        <td class="align-middle">
                            <input type="text" id="inline_title" name="title" form="inlineAddTaskForm" class="form-control form-control-sm font-weight-bold mb-1" placeholder="What needs to be done? (Press Enter to save)" required>
                        </td>
                        <td class="align-middle d-none d-md-table-cell">
                            <select name="type" form="inlineAddTaskForm" class="form-control form-control-sm">
                                <option value="1" selected>Task</option>
                                <option value="2">Bug</option>
                                <option value="3">Feature</option>
                                <option value="4">Improvement</option>
                            </select>
                        </td>
                        <td class="align-middle d-none d-md-table-cell">
                            <select name="project_id" form="inlineAddTaskForm" class="form-control form-control-sm">
                                <option value="">-- Personal --</option>
                                @foreach($projects as $proj)
                                    <option value="{{ $proj->id }}" {{ request('project') == $proj->id ? 'selected' : '' }}>{{ $proj->name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="align-middle d-none d-md-table-cell">
                            <select name="assigned_to" form="inlineAddTaskForm" class="form-control form-control-sm">
                                <option value="">-- Unassigned --</option>
                                @foreach($companyUsers as $user)
                                    <option value="{{ $user->id }}" {{ $user->id == auth()->id() ? 'selected' : '' }}>{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="align-middle d-none d-md-table-cell">
                            <input type="date" name="due_date" form="inlineAddTaskForm" class="form-control form-control-sm">
                        </td>
                        <td class="align-middle d-none d-md-table-cell">
                            <select name="status" form="inlineAddTaskForm" class="form-control form-control-sm">
                                <option value="1" selected>To Do</option>
                                <option value="2">In Progress</option>
                                <option value="3">Completed</option>
                                <option value="4">On Hold</option>
                            </select>
                        </td>
                        <td class="align-middle d-none d-md-table-cell">
                            <select name="priority" form="inlineAddTaskForm" class="form-control form-control-sm">
                                <option value="1">Low</option>
                                <option value="2" selected>Medium</option>
                                <option value="3">High</option>
                                <option value="4">Urgent</option>
                            </select>
                        </td>
                        <td class="text-center align-middle">
                            <button type="submit" form="inlineAddTaskForm" class="btn btn-sm btn-success shadow-sm" title="Save Todo">
                                <i class="fas fa-check"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-secondary shadow-sm ml-1" id="cancelInlineAdd" title="Cancel">
                                <i class="fas fa-times"></i>
                            </button>
                        </td>
                    </tr>

                    @foreach($tasks as $task)
                        @php
                            $canMutate = false;
                            if ($task->project === null) {
                                $canMutate = ($task->user_id === auth()->id()) || ($task->assigned_to === auth()->id());
                            } elseif ($task->project->company_id === null) {
                                $canMutate = $task->project->user_id === auth()->id();
                            } else {
                                $role = auth()->user()->companies->where('company_id', $task->project->company_id)->first()->role ?? 0;
                                $canMutate = ($role == 1) || ($task->assigned_to === auth()->id());
                            }
                        @endphp
                        <tr class="task-row" 
                            data-project="{{ $task->project_id }}" 
                            data-completed="{{ $task->status == 3 ? 'completed' : 'pending' }}" 
                            data-assigned="{{ $task->assigned_to ?? 'unassigned' }}" 
                            style="display: {{ $task->status == 3 && request('status') != 'completed' ? 'none' : 'table-row' }}">
                            <td class="text-center align-middle" >
                                @if($canMutate)
                                    <form action="{{ route('tasks.toggle', $task) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-link p-0 text-decoration-none">
                                            @if($task->status == 3)
                                                <i class="far fa-check-square fa-2x text-success"></i>
                                            @else
                                                <i class="far fa-square fa-2x text-gray-300"></i>
                                            @endif
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted" style="cursor: not-allowed;" title="You can only toggle tasks assigned to you.">
                                        @if($task->status == 3)
                                            <i class="far fa-check-square fa-2x text-success" style="opacity: 0.6;"></i>
                                        @else
                                            <i class="far fa-square fa-2x text-gray-300"></i>
                                        @endif
                                    </span>
                                @endif
                            </td>
                            <td class="align-middle">
                                <div class="font-weight-bold {{ $task->status == 3 ? 'text-muted text-line-through' : 'text-gray-800' }}" style="font-size: 1.05rem;">
                                    <a href="{{ route('tasks.show', $task) }}" class="text-decoration-none text-gray-900 hover-text-primary">
                                        {{ $task->title }}
                                    </a>
                                </div>
                                @if($task->description)
                                    <div class="text-gray-500 small mt-1">{!! Str::limit(strip_tags($task->description), 100) !!}</div>
                                @endif

                                <!-- Compact details for mobile views -->
                                <div class="d-block d-md-none mt-2">
                                    <div class="d-flex flex-wrap align-items-center" style="gap: 6px;">
                                        <!-- Type -->
                                        <span class="badge {{ $task->getTypeBadgeClass() }} px-2 py-1 shadow-sm text-xs">
                                            <i class="fas {{ $task->getTypeIcon() }} mr-1"></i>{{ $task->getTypeName() }}
                                        </span>

                                        <!-- Project -->
                                        @if($task->project)
                                            <a href="{{ route('projects.show', $task->project) }}" class="badge text-white px-2 py-1 shadow-sm text-xs" style="background-color: {{ $task->project->theme }}">
                                                <i class="fas fa-folder mr-1"></i>{{ $task->project->name }}
                                            </a>
                                        @else
                                            <span class="badge badge-light border text-muted px-2 py-1 shadow-sm text-xs">
                                                <i class="fas fa-user-lock mr-1"></i>Personal
                                            </span>
                                        @endif

                                        <!-- Status -->
                                        @if($task->status == 1)
                                            <span class="badge badge-secondary px-2 py-1 text-xs">To Do</span>
                                        @elseif($task->status == 2)
                                            <span class="badge badge-warning px-2 py-1 text-xs">In Progress</span>
                                        @elseif($task->status == 3)
                                            <span class="badge badge-success px-2 py-1 text-xs">Completed</span>
                                        @elseif($task->status == 4)
                                            <span class="badge badge-danger px-2 py-1 text-xs">On Hold</span>
                                        @endif

                                        <!-- Priority -->
                                        @if($task->priority == 1)
                                            <span class="badge badge-secondary px-2 py-1 text-xs">Low</span>
                                        @elseif($task->priority == 2)
                                            <span class="badge badge-info px-2 py-1 text-xs">Medium</span>
                                        @elseif($task->priority == 3)
                                            <span class="badge badge-warning px-2 py-1 text-xs">High</span>
                                        @elseif($task->priority == 4)
                                            <span class="badge badge-danger px-2 py-1 text-xs">Urgent</span>
                                        @endif

                                        <!-- Assigned User -->
                                        @if($task->assignedUser)
                                            <span class="badge badge-light border text-gray-800 px-2 py-1 text-xs">
                                                <i class="fas fa-user mr-1 text-primary"></i>{{ $task->assignedUser->name }}
                                            </span>
                                        @else
                                            <span class="badge badge-light border text-muted px-2 py-1 text-xs font-italic">Unassigned</span>
                                        @endif

                                        <!-- Due Date -->
                                        @if($task->due_date)
                                            @php
                                                $isOverdue = $task->status != 3 && \Carbon\Carbon::parse($task->due_date)->isPast();
                                            @endphp
                                            <span class="badge {{ $isOverdue ? 'badge-danger' : 'badge-light border text-gray-800' }} px-2 py-1 text-xs">
                                                <i class="far fa-calendar-alt mr-1 text-danger"></i>{{ \Carbon\Carbon::parse($task->due_date)->format('M d') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="align-middle d-none d-md-table-cell">
                                <span class="badge {{ $task->getTypeBadgeClass() }} p-2 shadow-sm">
                                    <i class="fas {{ $task->getTypeIcon() }} mr-1"></i>
                                    {{ $task->getTypeName() }}
                                </span>
                            </td>
                            <td class="align-middle d-none d-md-table-cell">
                                @if($task->project)
                                    <a href="{{ route('projects.show', $task->project) }}" class="badge text-white p-2 shadow-sm" style="background-color: {{ $task->project->theme }}">
                                        <i class="fas fa-project-diagram mr-1"></i>
                                        {{ $task->project->name }}
                                    </a>
                                @else
                                    <span class="badge badge-light border text-muted p-2 shadow-sm">
                                        <i class="fas fa-user-lock mr-1"></i>Personal
                                    </span>
                                @endif
                            </td>
                            <td class="align-middle d-none d-md-table-cell">
                                @if($task->assignedUser)
                                    <span class="badge badge-light p-2 border text-gray-800">
                                        <i class="fas fa-user fa-sm mr-1 text-primary"></i>
                                        {{ $task->assignedUser->name }}
                                    </span>
                                @else
                                    <span class="text-muted small font-italic">Unassigned</span>
                                @endif
                            </td>
                            <td class="align-middle d-none d-md-table-cell">
                                @if($task->due_date)
                                    @php
                                        $isOverdue = $task->status != 3 && \Carbon\Carbon::parse($task->due_date)->isPast();
                                    @endphp
                                    <span class="badge {{ $isOverdue ? 'badge-danger' : 'badge-secondary' }} p-2">
                                        <i class="far fa-calendar-alt fa-sm mr-1"></i>
                                        {{ \Carbon\Carbon::parse($task->due_date)->format('M d, Y') }}
                                        @if($isOverdue)
                                            (Overdue)
                                        @endif
                                    </span>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="align-middle d-none d-md-table-cell">
                                @if($task->status == 1)
                                    <span class="badge badge-secondary p-2">To Do</span>
                                @elseif($task->status == 2)
                                    <span class="badge badge-warning p-2">In Progress</span>
                                @elseif($task->status == 3)
                                    <span class="badge badge-success p-2">Completed</span>
                                @elseif($task->status == 4)
                                    <span class="badge badge-danger p-2">On Hold</span>
                                @else
                                    <span class="badge badge-light p-2">To Do</span>
                                @endif
                            </td>
                            <td class="align-middle d-none d-md-table-cell">
                                @if($task->priority == 1)
                                    <span class="badge badge-secondary p-2">Low</span>
                                @elseif($task->priority == 2)
                                    <span class="badge badge-info p-2">Medium</span>
                                @elseif($task->priority == 3)
                                    <span class="badge badge-warning p-2">High</span>
                                @elseif($task->priority == 4)
                                    <span class="badge badge-danger p-2">Urgent</span>
                                @else
                                    <span class="badge badge-info p-2">Medium</span>
                                @endif
                            </td>
                            <td class="text-center align-middle">
                                @if($canMutate)
                                    <button class="btn btn-sm btn-info edit-task-btn" 
                                            data-toggle="modal" 
                                            data-target="#editTaskModal"
                                            data-id="{{ $task->id }}"
                                            data-title="{{ $task->title }}"
                                            data-description="{{ $task->description }}"
                                            data-due_date="{{ $task->due_date }}"
                                            data-assigned_to="{{ $task->assigned_to }}"
                                            data-status="{{ $task->status }}"
                                            data-priority="{{ $task->priority }}"
                                            data-type="{{ $task->type }}"
                                            data-action="{{ route('tasks.update', $task) }}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="d-inline ml-1">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted small font-italic">No actions</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
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
