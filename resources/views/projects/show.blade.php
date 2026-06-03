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
    $completedTasks = $project->tasks->where('status', 3)->count();
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
    <div class="card-header py-3 d-flex align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary mb-0">Project Tasks</h6>
        <div class="custom-control custom-checkbox">
            <input type="checkbox" class="custom-control-input" id="toggleCompletedTasks">
            <label class="custom-control-label font-weight-bold text-gray-700 small" for="toggleCompletedTasks" style="cursor: pointer; user-select: none;padding-top: 3px">
                Show Completed Tasks
            </label>
        </div>
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
                        <th class="d-none d-md-table-cell">Type</th>
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

                    @foreach($project->tasks as $task)
                        @php
                            $canMutate = ($user_role == 1) || ($task->assigned_to === auth()->id());
                        @endphp
                        <tr class="task-row-item {{ $task->status == 3 ? 'completed-task' : 'pending-task' }}">
                            <td class="text-center align-middle">
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

                                <!-- Compact details for mobile views -->
                                <div class="d-block d-md-none mt-2">
                                    <div class="d-flex flex-wrap align-items-center" style="gap: 6px;">
                                        <!-- Type -->
                                        <span class="badge {{ $task->getTypeBadgeClass() }} px-2 py-1 shadow-sm text-xs">
                                            <i class="fas {{ $task->getTypeIcon() }} mr-1"></i>{{ $task->getTypeName() }}
                                        </span>

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
                                    <a class="btn btn-sm btn-info" href="{{ route('tasks.show', $task) }}">
                                        <i class="fas fa-edit"></i>
                                    </a>
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

<!-- Notes Section -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-sticky-note mr-1"></i> Project Notes</h6>
        <a href="{{ route('notes.create', ['note_type' => 1, 'note_type_id' => $project->id, 'redirect_back' => request()->fullUrl()]) }}" class="btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm mr-1"></i> Add Note
        </a>
    </div>
    <div class="card-body">
        <div class="row">
            @forelse($project->notes as $note)
                <div class="col-lg-6 col-12 mb-3">
                    <div class="card border-left-primary shadow-sm h-100">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <h6 class="font-weight-bold mb-0 text-truncate" style="max-width: 85%;" title="{{ $note->title }}">
                                    <a href="{{ route('notes.show', [$note, 'redirect_back' => request()->fullUrl()]) }}" class="text-gray-900 text-decoration-none">
                                        {{ $note->title }}
                                    </a>
                                </h6>
                                <div class="dropdown no-arrow">
                                    <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink{{ $note->id }}" data-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                                        <a class="dropdown-item" href="{{ route('notes.edit', [$note, 'redirect_back' => request()->fullUrl()]) }}">
                                            <i class="fas fa-edit fa-sm fa-fw mr-2 text-gray-400"></i> Edit Note
                                        </a>
                                        <div class="dropdown-divider"></div>
                                        <form action="{{ route('notes.destroy', $note) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this note?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="fas fa-trash fa-sm fa-fw mr-2 text-danger"></i> Delete Note
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <p class="text-gray-600 small mb-2 flex-grow-1" style="white-space: pre-wrap;">{!! Str::limit(strip_tags($note->description), 200) !!}</p>
                            <div class="text-right text-xs text-gray-500 font-weight-bold mt-auto pt-2">
                                <span>{{ $note->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>
            @empty
                <div class="col-12 text-center py-4">
                    <p class="text-muted mb-0">No notes found for this project. Add one to document project info!</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Comments Section -->
@include('partials.comments', [
    'comments' => $project->comments()->with('user')->latest()->get(),
    'commentableType' => 'project',
    'commentableId' => $project->id
])

@include('partials.edit_task_modal')

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
<script src="{{ asset('asset/js/tasks.js') }}"></script>
<script>
    $(document).ready(function() {
        // Edit task modal populating is handled in the partial

        // Toggle Completed Tasks filtering logic
        var toggleCheckbox = document.getElementById('toggleCompletedTasks');
        if (toggleCheckbox) {
            // Load preference from localStorage or default to unchecked (false)
            var showCompleted = localStorage.getItem('showCompletedTasks') === 'true';
            toggleCheckbox.checked = showCompleted;

            window.applyFilter = function() {
                var show = toggleCheckbox.checked;
                localStorage.setItem('showCompletedTasks', show);
                
                var rows = document.querySelectorAll('.task-row-item');
                var visibleCount = 0;
                
                rows.forEach(function(row) {
                    if (row.classList.contains('completed-task')) {
                        if (show) {
                            row.style.setProperty('display', '', 'important');
                            visibleCount++;
                        } else {
                            row.style.setProperty('display', 'none', 'important');
                        }
                    } else {
                        row.style.setProperty('display', '', 'important');
                        visibleCount++;
                    }
                });

                // Toggle container visibility
                var inlineAddRowVisible = document.getElementById('inlineAddRow').style.display !== 'none';
                var hasVisibleTasks = visibleCount > 0 || inlineAddRowVisible;
                
                var tasksTableContainer = document.getElementById('tasksTableContainer');
                var noTasksContainer = document.getElementById('noTasksContainer');
                
                if (tasksTableContainer && noTasksContainer) {
                    var totalProjectTasks = rows.length;
                    if (totalProjectTasks === 0) {
                        tasksTableContainer.style.display = 'none';
                        noTasksContainer.style.display = 'block';
                        noTasksContainer.querySelector('h5').innerText = 'No tasks found';
                        noTasksContainer.querySelector('p').innerText = 'Get started by creating your first task for this project!';
                    } else {
                        if (hasVisibleTasks) {
                            tasksTableContainer.style.display = 'block';
                            noTasksContainer.style.display = 'none';
                        } else {
                            tasksTableContainer.style.display = 'none';
                            noTasksContainer.style.display = 'block';
                            noTasksContainer.querySelector('h5').innerText = 'No pending tasks';
                            noTasksContainer.querySelector('p').innerText = 'Check "Show Completed Tasks" to see finished tasks.';
                        }
                    }
                }
            };

            toggleCheckbox.addEventListener('change', window.applyFilter);

            // Initial run
            window.applyFilter();
        }
    });
</script>
@endpush
