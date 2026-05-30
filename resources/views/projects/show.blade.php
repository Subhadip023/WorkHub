@extends('layouts.admin')

@section('title', $project->name)

@push('styles')
<style>
    .text-line-through {
        text-decoration: line-through;
    }
</style>
@endpush

@section('content')
<!-- Back Button and Heading -->
<div class="mb-4">
    <a href="{{ route('projects.index') }}" class="btn btn-sm btn-secondary shadow-sm mb-3">
        <i class="fas fa-arrow-left fa-sm text-white-50 mr-1"></i> Back to Projects
    </a>
    <div class="d-sm-flex align-items-center justify-content-between">
        <div>
            <h1 class="h3 mb-0 text-gray-800 font-weight-bold d-inline-block align-middle">{{ $project->name }}</h1>
            <span class="badge ml-2 text-white px-2 py-1 align-middle" style="background-color: {{ $project->theme }}; font-size: 0.8rem;">
                {{ $project->theme }}
            </span>
            @if($project->status == 1)
                <span class="badge ml-2 badge-secondary px-2 py-1 align-middle" style="font-size: 0.8rem;">To Do</span>
            @elseif($project->status == 2)
                <span class="badge ml-2 badge-primary px-2 py-1 align-middle" style="font-size: 0.8rem;">In Progress</span>
            @elseif($project->status == 3)
                <span class="badge ml-2 badge-success px-2 py-1 align-middle" style="font-size: 0.8rem;">Completed</span>
            @elseif($project->status == 4)
                <span class="badge ml-2 badge-warning px-2 py-1 align-middle" style="font-size: 0.8rem;">On Hold</span>
            @endif

            @if($project->priority == 1)
                <span class="badge ml-2 badge-light border px-2 py-1 align-middle text-gray-800" style="font-size: 0.8rem;">Low</span>
            @elseif($project->priority == 2)
                <span class="badge ml-2 badge-info px-2 py-1 align-middle" style="font-size: 0.8rem;">Medium</span>
            @elseif($project->priority == 3)
                <span class="badge ml-2 badge-warning px-2 py-1 align-middle" style="font-size: 0.8rem;">High</span>
            @elseif($project->priority == 4)
                <span class="badge ml-2 badge-danger px-2 py-1 align-middle" style="font-size: 0.8rem;">Urgent</span>
            @endif
        </div>
        <div>
            <a href="{{ route('projects.edit', $project) }}" class="btn btn-sm btn-info shadow-sm">
                <i class="fas fa-edit fa-sm text-white-50 mr-1"></i> Edit Project
            </a>
            @php
                $current_company = session('current_company_id');
                $canDelete = false;
                if ($current_company === 'personal') {
                    $canDelete = ($project->company_id === null && $project->user_id === auth()->id());
                } else {
                    $is_admin = \App\Models\CompanyUsers::where('company_id', $current_company)
                        ->where('user_id', auth()->id())
                        ->where('role', 1)
                        ->exists();
                    $canDelete = ($project->company_id == $current_company && $is_admin);
                }
            @endphp
            @if($canDelete)
                <form action="{{ route('projects.destroy', $project) }}" method="POST" class="d-inline ml-1" onsubmit="return confirm('Are you sure you want to delete this project? This will delete all tasks within it.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger shadow-sm">
                        <i class="fas fa-trash fa-sm text-white-50 mr-1"></i> Delete Project
                    </button>
                </form>
            @endif
        </div>
    </div>
    @if($project->description)
        <div class="mt-2 text-gray-600 lead">{!! $project->description !!}</div>
    @else
        <p class="mt-2 text-gray-500 italic">No description provided for this project.</p>
    @endif
</div>

<!-- Task Stats & Progress Card -->
@php
    $totalTasks = $project->tasks->count();
    $completedTasks = $project->tasks->where('is_completed', true)->count();
    $percentage = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;
@endphp
<div class="card shadow mb-4" style="border-left: 4px solid {{ $project->theme }}">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col mr-2">
                <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: {{ $project->theme }}">
                    Tasks Completion Progress
                </div>
                <div class="row no-gutters align-items-center">
                    <div class="col-auto">
                        <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">{{ $percentage }}%</div>
                    </div>
                    <div class="col">
                        <div class="progress progress-sm mr-2">
                            <div class="progress-bar" role="progressbar" 
                                 style="width: {{ $percentage }}%; background-color: {{ $project->theme }}" 
                                 aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                    <div class="col-auto">
                        <span class="text-gray-600 font-weight-bold">({{ $completedTasks }} / {{ $totalTasks }})</span>
                    </div>
                </div>
            </div>
            <div class="col-auto">
                <button class="btn btn-primary shadow-sm" id="btnShowInlineAdd">
                    <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Add Task
                </button>
                <button class="btn btn-info shadow-sm ml-1" data-toggle="modal" data-target="#importJsonModal">
                    <i class="fas fa-file-import fa-sm text-white-50 mr-1"></i> Import JSON
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Tasks List Card -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Project Tasks</h6>
    </div>
    <div class="card-body">
        <div id="noTasksContainer" class="text-center py-5" style="display: {{ $project->tasks->isEmpty() ? 'block' : 'none' }}">
            <i class="fas fa-clipboard-list fa-3x text-gray-300 mb-3"></i>
            <h5 class="text-gray-500 font-weight-bold">No tasks found</h5>
            <p class="text-gray-500 mb-0">Get started by creating your first task for this project!</p>
        </div>

        <div class="table-responsive" id="tasksTableContainer" style="display: {{ $project->tasks->isEmpty() ? 'none' : 'block' }}">
            <table class="table table-hover table-bordered" id="tasksTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th style="width: 60px;" class="text-center">Done</th>
                        <th>Task Details</th>
                        <th>Assigned To</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th style="width: 120px;" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Notion-like Inline Add Row -->
                    <tr id="inlineAddRow" style="display: none; background-color: rgba(78, 115, 223, 0.05);">
                        <td class="text-center align-middle">
                            <i class="far fa-square fa-2x text-gray-300"></i>
                        </td>
                        <td class="align-middle">
                            <input type="text" id="inline_title" name="title" form="inlineAddTaskForm" class="form-control form-control-sm font-weight-bold mb-1" placeholder="What needs to be done? (Press Enter to save)" required>
                        </td>
                        <td class="align-middle">
                            <select name="assigned_to" form="inlineAddTaskForm" class="form-control form-control-sm">
                                <option value="">-- Unassigned --</option>
                                @foreach($companyUsers as $user)
                                    <option value="{{ $user->id }}" {{ $user->id == auth()->id() ? 'selected' : '' }}>{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="align-middle">
                            <input type="date" name="due_date" form="inlineAddTaskForm" class="form-control form-control-sm">
                        </td>
                        <td class="align-middle">
                            <select name="status" form="inlineAddTaskForm" class="form-control form-control-sm">
                                <option value="1" selected>To Do</option>
                                <option value="2">In Progress</option>
                                <option value="3">Completed</option>
                                <option value="4">On Hold</option>
                            </select>
                        </td>
                        <td class="align-middle">
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

                    @foreach($project->tasks as $task)
                        @php
                            $canMutate = ($user_role == 1) || ($task->assigned_to === auth()->id());
                        @endphp
                        <tr>
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
                            <td class="align-middle">
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
                            <td class="align-middle">
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

        <form action="{{ route('projects.tasks.store', $project) }}" method="POST" id="inlineAddTaskForm" style="display:none;">
            @csrf
        </form>
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
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="edit_task_status" class="font-weight-bold text-gray-700">Status <span class="text-danger">*</span></label>
                            <select class="form-control" id="edit_task_status" name="status" required>
                                <option value="1">To Do</option>
                                <option value="2">In Progress</option>
                                <option value="3">Completed</option>
                                <option value="4">On Hold</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="edit_task_priority" class="font-weight-bold text-gray-700">Priority <span class="text-danger">*</span></label>
                            <select class="form-control" id="edit_task_priority" name="priority" required>
                                <option value="1">Low</option>
                                <option value="2">Medium</option>
                                <option value="3">High</option>
                                <option value="4">Urgent</option>
                            </select>
                        </div>
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

{{-- Import JSON Modal --}}
<div class="modal fade" id="importJsonModal" tabindex="-1" role="dialog" aria-labelledby="importJsonModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-info font-weight-bold" id="importJsonModalLabel">Import Tasks from JSON</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <form action="{{ route('projects.tasks.import', $project) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="json_data" class="font-weight-bold text-gray-700">Paste JSON Content <span class="text-danger">*</span></label>
                        <textarea class="form-control font-monospace" id="json_data" name="json_data" rows="8" placeholder='[
  {
    "title": "Design new database schema",
    "description": "Define tables for users, tasks, and companies",
    "due_date": "2026-06-01"
  },
  {
    "title": "Setup development server",
    "description": "Configure Docker, Nginx, and PHP settings"
  }
]' required></textarea>
                        <small class="form-text text-muted mt-2">
                            Please provide a valid JSON array of tasks or a single task object. Supported fields: <code>title</code> (required), <code>description</code>, and <code>due_date</code> (YYYY-MM-DD).
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info">Import Tasks</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Toggle inline add row visibility
        $('#btnShowInlineAdd').click(function(e) {
            e.preventDefault();
            $('#noTasksContainer').hide();
            $('#tasksTableContainer').show();
            $('#inlineAddRow').show();
            $('#inline_title').focus();
        });

        // Cancel inline add
        $('#cancelInlineAdd').click(function() {
            $('#inlineAddRow').hide();
            
            // If there are no other tasks, restore the "No tasks found" state
            var taskCount = $('#tasksTable tbody tr').length - 1; // subtract 1 for the inline row itself
            if (taskCount <= 0) {
                $('#tasksTableContainer').hide();
                $('#noTasksContainer').show();
            }

            // Clear values
            $('#inlineAddRow input').val('');
            $('#inlineAddRow select').val('{{ auth()->id() }}');
        });

        // Submit inline form on Enter in the title input
        $('#inline_title').keypress(function(e) {
            if (e.which === 13) {
                e.preventDefault();
                $('#inlineAddTaskForm').submit();
            }
        });

        // Populate edit modal values
        $('.edit-task-btn').click(function() {
            var id = $(this).data('id');
            var title = $(this).data('title');
            var description = $(this).data('description');
            var due_date = $(this).data('due_date');
            var assigned_to = $(this).data('assigned_to');
            var status = $(this).data('status');
            var priority = $(this).data('priority');
            var action = $(this).data('action');

            $('#editTaskForm').attr('action', action);
            $('#edit_task_title').val(title);
            $('#edit_task_description').val(description);
            $('#edit_task_due_date').val(due_date);
            $('#edit_task_assigned_to').val(assigned_to);
            $('#edit_task_status').val(status);
            $('#edit_task_priority').val(priority);
        });
    });
</script>
@endpush
