@extends('layouts.admin')

@section('title', $project->name)

@push('styles')
<style>
    .text-line-through {
        text-decoration: line-through;
    }
    .note-card-hover {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }
    .note-card-hover:hover {
        transform: translateY(-4px);
        box-shadow: 0 0.75rem 2rem rgba(58, 59, 69, 0.15) !important;
    }
    .note-description-preview {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
        color: #5a5c69;
        font-size: 0.9rem;
        line-height: 1.6;
    }
    /* Custom Modern Underline Tabs */
    #projectShowTabs {
        border-bottom: 2px solid #eaecf4;
    }
    #projectShowTabs .nav-link {
        border: none;
        background: transparent;
        color: #858796;
        padding: 0.75rem 1.25rem;
        border-bottom: 3px solid transparent;
        font-weight: 700;
        transition: all 0.15s ease-in-out;
        border-radius: 0;
    }
    #projectShowTabs .nav-link:hover {
        color: #5a5c69;
        border-bottom: 3px solid #dddfeb;
    }
    #projectShowTabs .nav-link.active {
        color: {{ $project->theme }} !important;
        border-bottom: 3px solid {{ $project->theme }} !important;
        background: transparent;
    }
    #projectShowTabs .nav-link i {
        transition: transform 0.2s;
    }
    #projectShowTabs .nav-link:hover i {
        transform: translateY(-1px);
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
            @can('delete', $project)
                <form action="{{ route('projects.destroy', $project) }}" method="POST" class="d-inline ml-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger shadow-sm">
                        <i class="fas fa-trash fa-sm text-white-50 mr-1"></i> Delete Project
                    </button>
                </form>
            @endcan
        </div>
    </div>
    @if($project->description)
        <div class="mt-2 text-gray-600 lead">{!! $project->description !!}</div>
    @else
        <p class="mt-2 text-gray-500 italic">No description provided for this project.</p>
    @endif
</div>

<div class="row">
    <!-- Left Column (2/3 width) -->
    <div class="col-lg-9">
        <!-- Nav Option Tabs -->
        <ul class="nav nav-tabs mb-4" id="projectShowTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active font-weight-bold" id="tasks-tab" data-toggle="tab" href="#tasks-content" role="tab" aria-controls="tasks-content" aria-selected="true">
                    <i class="fas fa-tasks mr-2"></i>Tasks
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link font-weight-bold" id="notes-tab" data-toggle="tab" href="#notes-content" role="tab" aria-controls="notes-content" aria-selected="false">
                    <i class="fas fa-sticky-note mr-2"></i>Notes
                </a>
            </li>
        </ul>

        <div class="tab-content" id="projectShowTabsContent">
            <!-- Tasks Tab Pane -->
            <div class="tab-pane fade show active" id="tasks-content" role="tabpanel" aria-labelledby="tasks-tab">
                <!-- Task Stats & Progress Card -->
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
<div class="card shadow mb-4 ">
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
        @if($project->tasks->isNotEmpty())
            <!-- Tasks Search & Filter Bar -->
            <div class="row mb-3 bg-light py-2 px-1 rounded mx-0 border align-items-center" style="border-radius: 8px;">
                <div class="col-md-4 mb-2 mb-md-0">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white border-right-0"><i class="fas fa-search text-gray-400"></i></span>
                        </div>
                        <input type="text" class="form-control border-left-0" id="searchTaskInput" placeholder="Search tasks by title...">
                    </div>
                </div>
                <div class="col-md-2 mb-2 mb-md-0 col-6">
                    <select class="form-control form-control-sm text-xs font-weight-bold" id="filterTaskPriority">
                        <option value="">All Priorities</option>
                        <option value="1">🟢 Low</option>
                        <option value="2">🟡 Medium</option>
                        <option value="3">🟠 High</option>
                        <option value="4">🔴 Urgent</option>
                    </select>
                </div>
                <div class="col-md-3 mb-2 mb-md-0 col-6">
                    <select class="form-control form-control-sm text-xs font-weight-bold" id="filterTaskStatus">
                        <option value="">All Statuses</option>
                        <option value="1">📝 To Do</option>
                        <option value="2">⚙️ In Progress</option>
                        <option value="3">✅ Completed</option>
                        <option value="4">🛑 On Hold</option>
                    </select>
                </div>
                <div class="col-md-3 col-12">
                    <select class="form-control form-control-sm text-xs font-weight-bold" id="filterTaskType">
                        <option value="">All Types</option>
                        <option value="1">Task</option>
                        <option value="2">Bug</option>
                        <option value="3">Feature</option>
                        <option value="4">Improvement</option>
                    </select>
                </div>
            </div>
        @endif

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
                        <tr class="task-row-item {{ $task->status == 3 ? 'completed-task' : 'pending-task' }}"
                            data-priority="{{ $task->priority }}"
                            data-status="{{ $task->status }}"
                            data-type="{{ $task->type }}"
                            data-title="{{ $task->title }}">
                            <td class="text-center align-middle">
                                @can('update', $task)
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
                                @endcan
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
                                @can('update', $task)
                                    <a class="btn btn-sm btn-info" href="{{ route('tasks.show', $task) }}">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="d-inline ml-1">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted small font-italic">No actions</span>
                                @endcan
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
            </div> <!-- End Tasks Tab Pane -->

            <!-- Notes Tab Pane -->
            <div class="tab-pane fade" id="notes-content" role="tabpanel" aria-labelledby="notes-tab">
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
                    <div class="card shadow-sm h-100 border-0 note-card-hover" style="border-left: 4px solid #4e73df !important; border-radius: 8px;">
                        <div class="card-body d-flex flex-column p-4">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="badge badge-primary px-2.5 py-1 font-weight-bold shadow-sm" style="background-color: rgba(78, 115, 223, 0.15); color: #4e73df;">Project Note</span>
                                
                                <div class="dropdown no-arrow">
                                    <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink{{ $note->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-right shadow border-0 animated--fade-in" aria-labelledby="dropdownMenuLink{{ $note->id }}" style="border-radius: 8px;">
                                        <a class="dropdown-item py-2" href="{{ route('notes.edit', [$note, 'redirect_back' => request()->fullUrl()]) }}">
                                            <i class="fas fa-edit fa-sm fa-fw mr-2 text-gray-400"></i> Edit Note
                                        </a>
                                        <div class="dropdown-divider"></div>
                                        <form action="{{ route('notes.destroy', $note) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this note?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger py-2">
                                                <i class="fas fa-trash fa-sm fa-fw mr-2 text-danger"></i> Delete Note
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <h5 class="font-weight-bold mb-2">
                                <a href="{{ route('notes.show', [$note, 'redirect_back' => request()->fullUrl()]) }}" class="text-gray-900 text-decoration-none hover-link" style="font-size: 1.15rem; line-height: 1.4;">
                                    {{ $note->title }}
                                </a>
                            </h5>

                            <!-- Description Preview with clamp lines -->
                            <p class="text-gray-600 mb-4 flex-grow-1 note-description-preview" style="font-size: 0.9rem; line-height: 1.6; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; white-space: normal;">
                                {!! strip_tags($note->description) !!}
                            </p>
                            
                            <div class="d-flex align-items-center justify-content-between text-xs text-gray-500 font-weight-bold border-top pt-3">
                                <span class="text-muted d-inline-flex align-items-center">
                                    <i class="fas fa-project-diagram mr-1"></i>{{ Str::limit($project->name, 15) }}
                                </span>
                                <span title="Created {{ $note->created_at->format('M d, Y h:i A') }}" class="d-inline-flex align-items-center text-muted">
                                    <i class="far fa-clock mr-1"></i>{{ $note->created_at->diffForHumans() }}
                                </span>
                            </div>
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
            </div> <!-- End Notes Tab Pane -->
        </div> <!-- End Tab Content Container -->

    </div>

    <!-- Right Column (1/3 width) -->
    <div class="col-lg-3">
        <!-- Comments Section -->
        @include('partials.comments', [
            'comments' => $comments,
            'commentableType' => 'project',
            'commentableId' => $project->id
        ])
    </div>
</div>

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
        // Tab switching persistence
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            localStorage.setItem('activeProjectTab_' + {{ $project->id }}, $(e.target).attr('href'));
        });
        var activeTab = localStorage.getItem('activeProjectTab_' + {{ $project->id }});
        if (activeTab) {
            $('#projectShowTabs a[href="' + activeTab + '"]').tab('show');
        }

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
                
                var searchVal = (document.getElementById('searchTaskInput') ? document.getElementById('searchTaskInput').value : '').toLowerCase().trim();
                var priorityVal = document.getElementById('filterTaskPriority') ? document.getElementById('filterTaskPriority').value : '';
                var statusVal = document.getElementById('filterTaskStatus') ? document.getElementById('filterTaskStatus').value : '';
                var typeVal = document.getElementById('filterTaskType') ? document.getElementById('filterTaskType').value : '';
                
                var showCompletedOverride = (statusVal === '3');
                
                var rows = document.querySelectorAll('.task-row-item');
                var visibleCount = 0;
                
                rows.forEach(function(row) {
                    var rowStatus = row.getAttribute('data-status');
                    var rowPriority = row.getAttribute('data-priority');
                    var rowType = row.getAttribute('data-type');
                    var rowTitle = (row.getAttribute('data-title') || '').toLowerCase();
                    
                    var isMatch = true;
                    
                    // Completed tasks filter
                    if (rowStatus === '3' && !show && !showCompletedOverride) {
                        isMatch = false;
                    }
                    
                    // Title Search filter
                    if (searchVal && !rowTitle.includes(searchVal)) {
                        isMatch = false;
                    }
                    
                    // Priority filter
                    if (priorityVal && rowPriority !== priorityVal) {
                        isMatch = false;
                    }
                    
                    // Status filter
                    if (statusVal && rowStatus !== statusVal) {
                        isMatch = false;
                    }
                    
                    // Type filter
                    if (typeVal && rowType !== typeVal) {
                        isMatch = false;
                    }
                    
                    if (isMatch) {
                        row.style.setProperty('display', '', 'important');
                        visibleCount++;
                    } else {
                        row.style.setProperty('display', 'none', 'important');
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
                            noTasksContainer.querySelector('h5').innerText = 'No matching tasks';
                            noTasksContainer.querySelector('p').innerText = 'Try adjusting your filters or search terms.';
                        }
                    }
                }
            };

            toggleCheckbox.addEventListener('change', window.applyFilter);
            
            var searchInput = document.getElementById('searchTaskInput');
            if (searchInput) searchInput.addEventListener('input', window.applyFilter);
            
            var prioritySelect = document.getElementById('filterTaskPriority');
            if (prioritySelect) prioritySelect.addEventListener('change', window.applyFilter);
            
            var statusSelect = document.getElementById('filterTaskStatus');
            if (statusSelect) statusSelect.addEventListener('change', window.applyFilter);
            
            var typeSelect = document.getElementById('filterTaskType');
            if (typeSelect) typeSelect.addEventListener('change', window.applyFilter);

            // Initial run
            window.applyFilter();
        }
    });
</script>
@endpush
