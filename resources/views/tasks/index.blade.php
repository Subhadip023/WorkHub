@extends('layouts.admin')

@section('title', 'Tasks')

@push('styles')
<style>
    .text-line-through {
        text-decoration: line-through;
    }
</style>
@endpush

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800 font-weight-bold">Tasks</h1>
    <button class="btn btn-primary shadow-sm" data-toggle="modal" data-target="#addTaskModal">
        <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Add Task
    </button>
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
                    <option value="all">All Projects</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}">{{ $project->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 mb-2 mb-md-0">
                <label for="filterStatus" class="font-weight-bold text-xs text-gray-700 text-uppercase">Status</label>
                <select id="filterStatus" class="form-control form-control-sm">
                    <option value="all">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="completed">Completed</option>
                </select>
            </div>
            <div class="col-md-3 mb-2 mb-md-0">
                <label for="filterAssignee" class="font-weight-bold text-xs text-gray-700 text-uppercase">Assignee</label>
                <select id="filterAssignee" class="form-control form-control-sm">
                    <option value="all">All Assignees</option>
                    <option value="unassigned">Unassigned</option>
                    @foreach($companyUsers as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 text-right pt-4">
                <button id="resetFilters" class="btn btn-sm btn-secondary">
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
                        <th>Project</th>
                        <th>Assigned To</th>
                        <th>Due Date</th>
                        <th style="width: 120px;" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tasks as $task)
                        @php
                            $canMutate = false;
                            if ($task->project->company_id === null) {
                                $canMutate = $task->project->user_id === auth()->id();
                            } else {
                                $role = auth()->user()->companies->where('company_id', $task->project->company_id)->first()->role ?? 0;
                                $canMutate = ($role == 1) || ($task->assigned_to === auth()->id());
                            }
                        @endphp
                        <tr class="task-row" 
                            data-project="{{ $task->project_id }}" 
                            data-completed="{{ $task->is_completed ? 'completed' : 'pending' }}" 
                            data-assigned="{{ $task->assigned_to ?? 'unassigned' }}">
                            <td class="text-center align-middle">
                                @if($canMutate)
                                    <form action="{{ route('tasks.toggle', $task) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-link p-0 text-decoration-none">
                                            @if($task->is_completed)
                                                <i class="far fa-check-square fa-2x text-success"></i>
                                            @else
                                                <i class="far fa-square fa-2x text-gray-300"></i>
                                            @endif
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted" style="cursor: not-allowed;" title="You can only toggle tasks assigned to you.">
                                        @if($task->is_completed)
                                            <i class="far fa-check-square fa-2x text-success" style="opacity: 0.6;"></i>
                                        @else
                                            <i class="far fa-square fa-2x text-gray-300"></i>
                                        @endif
                                    </span>
                                @endif
                            </td>
                            <td class="align-middle">
                                <div class="font-weight-bold {{ $task->is_completed ? 'text-muted text-line-through' : 'text-gray-800' }}" style="font-size: 1.05rem;">
                                    <a href="{{ route('tasks.show', $task) }}" class="text-decoration-none text-gray-900 hover-text-primary">
                                        {{ $task->title }}
                                    </a>
                                </div>
                                @if($task->description)
                                    <div class="text-gray-500 small mt-1">{!! Str::limit(strip_tags($task->description), 100) !!}</div>
                                @endif
                            </td>
                            <td class="align-middle">
                                <a href="{{ route('projects.show', $task->project) }}" class="badge text-white p-2 shadow-sm" style="background-color: {{ $task->project->theme }}">
                                    <i class="fas fa-project-diagram mr-1"></i>
                                    {{ $task->project->name }}
                                </a>
                            </td>
                            <td class="align-middle">
                                @if($task->assignedUser)
                                    <span class="badge badge-light p-2 border text-gray-800">
                                        <i class="fas fa-user fa-sm mr-1 text-primary"></i>
                                        {{ $task->assignedUser->name }}
                                    </span>
                                @else
                                    <span class="text-muted small font-italic">Unassigned</span>
                                @endif
                            </td>
                            <td class="align-middle">
                                @if($task->due_date)
                                    @php
                                        $isOverdue = !$task->is_completed && \Carbon\Carbon::parse($task->due_date)->isPast();
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
                                            data-action="{{ route('tasks.update', $task) }}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="d-inline ml-1" onsubmit="return confirm('Are you sure you want to delete this task?');">
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
            {!! $tasks->links() !!}
        </div>
    </div>
</div>

{{-- Add Task Modal --}}
<div class="modal fade" id="addTaskModal" tabindex="-1" role="dialog" aria-labelledby="addTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-primary font-weight-bold" id="addTaskModalLabel">Add New Task</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <form action="{{ route('tasks.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="project_id" class="font-weight-bold text-gray-700">Project <span class="text-danger">*</span></label>
                        <select class="form-control" id="project_id" name="project_id" required>
                            <option value="">-- Select Project --</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}">{{ $project->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="task_title" class="font-weight-bold text-gray-700">Task Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="task_title" name="title" required placeholder="What needs to be done?">
                    </div>
                    <div class="form-group">
                        <label for="task_description" class="font-weight-bold text-gray-700">Description</label>
                        <textarea class="form-control" id="task_description" name="description" rows="3" placeholder="Add optional details..."></textarea>
                    </div>
                    <div class="form-group">
                        <label for="task_assigned_to" class="font-weight-bold text-gray-700">Assign To</label>
                        <select class="form-control" id="task_assigned_to" name="assigned_to">
                            <option value="">-- Unassigned --</option>
                            @foreach($companyUsers as $user)
                                <option value="{{ $user->id }}" {{ $user->id == auth()->id() ? 'selected' : '' }}>{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="task_due_date" class="font-weight-bold text-gray-700">Due Date</label>
                        <input type="date" class="form-control" id="task_due_date" name="due_date">
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Task</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Task Modal --}}
<div class="modal fade" id="editTaskModal" tabindex="-1" role="dialog" aria-labelledby="editTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-info font-weight-bold" id="editTaskModalLabel">Edit Task</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <form action="" method="POST" id="editTaskForm">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_task_title" class="font-weight-bold text-gray-700">Task Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_task_title" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_task_description" class="font-weight-bold text-gray-700">Description</label>
                        <textarea class="form-control" id="edit_task_description" name="description" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="edit_task_assigned_to" class="font-weight-bold text-gray-700">Assign To</label>
                        <select class="form-control" id="edit_task_assigned_to" name="assigned_to">
                            <option value="">-- Unassigned --</option>
                            @foreach($companyUsers as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_task_due_date" class="font-weight-bold text-gray-700">Due Date</label>
                        <input type="date" class="form-control" id="edit_task_due_date" name="due_date">
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Filter handler
        function applyFilters() {
            var selectedProject = $('#filterProject').val();
            var selectedStatus = $('#filterStatus').val();
            var selectedAssignee = $('#filterAssignee').val();

            var visibleRows = 0;

            $('.task-row').each(function() {
                var row = $(this);
                var project = row.data('project').toString();
                var status = row.data('completed');
                var assignee = row.data('assigned').toString();

                var matchProject = (selectedProject === 'all' || project === selectedProject);
                var matchStatus = (selectedStatus === 'all' || status === selectedStatus);
                var matchAssignee = (selectedAssignee === 'all' || assignee === selectedAssignee);

                if (matchProject && matchStatus && matchAssignee) {
                    row.show();
                    visibleRows++;
                } else {
                    row.hide();
                }
            });

            if (visibleRows === 0) {
                $('#tasksTableContainer').hide();
                $('#noTasksContainer').show();
            } else {
                $('#noTasksContainer').hide();
                $('#tasksTableContainer').show();
            }
        }

        // Event listeners for filters
        $('#filterProject, #filterStatus, #filterAssignee').change(function() {
            applyFilters();
        });

        // Reset filters
        $('#resetFilters').click(function() {
            $('#filterProject').val('all');
            $('#filterStatus').val('all');
            $('#filterAssignee').val('all');
            applyFilters();
        });

        // Populate edit modal values
        $('.edit-task-btn').click(function() {
            var id = $(this).data('id');
            var title = $(this).data('title');
            var description = $(this).data('description');
            var due_date = $(this).data('due_date');
            var assigned_to = $(this).data('assigned_to');
            var action = $(this).data('action');

            $('#editTaskForm').attr('action', action);
            $('#edit_task_title').val(title);
            $('#edit_task_description').val(description);
            $('#edit_task_due_date').val(due_date);
            $('#edit_task_assigned_to').val(assigned_to);
        });
    });
</script>
@endpush
