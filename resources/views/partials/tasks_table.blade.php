@if($showFilters ?? false)
    @php
        $hasProjectFilter = isset($projects) && ($showProjectColumn ?? true);
        $filterColClass = $hasProjectFilter ? 'col-md-2' : 'col-md-3';
    @endphp
    <!-- Filters Card -->
    <div class="card shadow mb-4">
        <div class="card-body py-3">
            <div class="row align-items-center">
                @if($hasProjectFilter)
                    <div class="col-md-3 mb-2 mb-md-0">
                        <label for="filterProject" class="font-weight-bold text-xs text-gray-700 text-uppercase">Project</label>
                        <select id="filterProject" class="form-control form-control-sm">
                            <option value="all" {{ request('project') == 'all' || !request('project') ? 'selected' : '' }}>All Projects</option>
                            <option value="none" {{ request('project') == 'none' ? 'selected' : '' }}>Personal</option>
                            @foreach($projects as $projectItem)
                                <option value="{{ $projectItem->id }}" {{ request('project') == $projectItem->id ? 'selected' : '' }}>{{ $projectItem->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="{{ $filterColClass }} mb-2 mb-md-0">
                    <label for="filterStatus" class="font-weight-bold text-xs text-gray-700 text-uppercase">Status</label>
                    <select id="filterStatus" class="form-control form-control-sm">
                        <option value="all" {{ request('status') == 'all' || !request('status') ? 'selected' : '' }}>All Statuses</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>
                <div class="{{ $filterColClass }} mb-2 mb-md-0">
                    <label for="filterAssignee" class="font-weight-bold text-xs text-gray-700 text-uppercase">Assignee</label>
                    <select id="filterAssignee" class="form-control form-control-sm">
                        <option value="all" {{ request('assignee') == 'all' || !request('assignee') ? 'selected' : '' }}>All Assignees</option>
                        <option value="unassigned" {{ request('assignee') == 'unassigned' ? 'selected' : '' }}>Unassigned</option>
                        @if(isset($companyUsers))
                            @foreach($companyUsers as $cUser)
                                <option value="{{ $cUser->id }}" {{ request('assignee') == $cUser->id ? 'selected' : '' }}>{{ $cUser->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="{{ $filterColClass }} mb-2 mb-md-0">
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
@endif

@if($wrapInCard ?? ($showFilters ?? false))
    <!-- Tasks List Card Wrapper -->
    <div class="card shadow mb-4">
        @if(isset($cardTitle))
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">{{ $cardTitle }}</h6> 
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="toggleCompletedTasks" {{ request('show_completed') == 'true' ? 'checked' : '' }}>
                    <label class="custom-control-label font-weight-bold text-gray-700 small" for="toggleCompletedTasks" style="cursor: pointer; user-select: none; padding-top: 3px;">
                        Show Completed Tasks
                    </label>
                </div>
            </div>
        @endif
        <div class="card-body">
@endif

<div id="noTasksContainer" class="text-center py-5" style="display: {{ $tasks->isEmpty() ? 'block' : 'none' }}">
    <i class="fas fa-clipboard-list fa-3x text-gray-300 mb-3"></i>
    <h5 class="text-gray-500 font-weight-bold">
        @if(isset($project) && $project->tasks()->count() == 0)
            No tasks found
        @elseif(isset($project))
            No matching tasks
        @else
            No tasks found
        @endif
    </h5>
    <p class="text-gray-500 mb-0">
        @if(isset($project) && $project->tasks()->count() == 0)
            Get started by creating your first task for this project!
        @elseif(isset($project))
            Try adjusting your filters or search terms.
        @else
            Create tasks for your projects to see them listed here!
        @endif
    </p>
</div>

<div class="table-responsive" id="tasksTableContainer" style="display: {{ $tasks->isEmpty() ? 'none' : 'block' }}">
    <table class="table table-hover table-bordered" id="tasksTable" width="100%" cellspacing="0">
        <thead>
            <tr>
                <th style="width: 60px;" class="text-center">Done</th>
                <th>Task Details</th>
                <th class="d-none d-md-table-cell">Type</th>
                @if($showProjectColumn ?? false)
                    <th class="d-none d-md-table-cell">Project</th>
                @endif
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
                @if($showProjectColumn ?? false)
                    <td class="align-middle d-none d-md-table-cell">
                        <select name="project_id" form="inlineAddTaskForm" class="form-control form-control-sm">
                            <option value="">-- Personal --</option>
                            @if(isset($projects))
                                @foreach($projects as $proj)
                                    <option value="{{ $proj->id }}" {{ request('project') == $proj->id ? 'selected' : '' }}>{{ $proj->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </td>
                @endif
                <td class="align-middle d-none d-md-table-cell">
                    <select name="assigned_to" form="inlineAddTaskForm" class="form-control form-control-sm">
                        <option value="">-- Unassigned --</option>
                        @if(isset($companyUsers))
                            @foreach($companyUsers as $user)
                                <option value="{{ $user->id }}" {{ $user->id == auth()->id() ? 'selected' : '' }}>{{ $user->name }}</option>
                            @endforeach
                        @endif
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
                <tr class="task-row task-row-item {{ $task->status == 3 ? 'completed-task' : 'pending-task' }}" 
                    data-project="{{ $task->project_id }}" 
                    data-completed="{{ $task->status == 3 ? 'completed' : 'pending' }}" 
                    data-assigned="{{ $task->assigned_to ?? 'unassigned' }}" 
                    style="display: table-row;">
                    <td class="text-center align-middle">
                        @can('update', $task)
                            <form action="{{ route('tasks.toggle', $task) }}" method="POST" class="toggle-task-form d-inline">
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
                            <a href="{{ route('tasks.show', $task) }}" class="text-decoration-none text-gray-900 hover-text-primary task-title-link">
                                {{ $task->title }}
                            </a>
                            @if($task->externalSource)
                                <span class="badge badge-dark px-2 py-1 text-xs shadow-sm ml-1" title="Created via External API Key: {{ $task->externalSource->externalTaskApi?->name }}">
                                    <i class="fas fa-plug text-warning mr-1"></i>Via API
                                </span>
                            @endif
                        </div>
                        @if($task->description)
                            <div class="text-gray-500 small mt-1">{!! Str::limit(strip_tags($task->description), 100) !!}</div>
                        @endif

                        <!-- Compact details for mobile views -->
                        <div class="d-block d-md-none mt-2">
                            <div class="d-flex flex-wrap align-items-center" style="gap: 6px;">
                                <span class="badge {{ $task->getTypeBadgeClass() }} px-2 py-1 shadow-sm text-xs">
                                    <i class="fas {{ $task->getTypeIcon() }} mr-1"></i>{{ $task->getTypeName() }}
                                </span>

                                @if($showProjectColumn ?? false)
                                    @if($task->project)
                                        <a href="{{ route('projects.show', $task->project) }}" class="badge text-white px-2 py-1 shadow-sm text-xs" style="background-color: {{ $task->project->theme }}">
                                            <i class="fas fa-folder mr-1"></i>{{ $task->project->name }}
                                        </a>
                                    @else
                                        <span class="badge badge-light border text-muted px-2 py-1 shadow-sm text-xs">
                                            <i class="fas fa-user-lock mr-1"></i>Personal
                                        </span>
                                    @endif
                                @endif

                                @if($task->status == 1)
                                    <span class="badge badge-secondary px-2 py-1 text-xs">To Do</span>
                                @elseif($task->status == 2)
                                    <span class="badge badge-warning px-2 py-1 text-xs">In Progress</span>
                                @elseif($task->status == 3)
                                    <span class="badge badge-success px-2 py-1 text-xs">Completed</span>
                                @elseif($task->status == 4)
                                    <span class="badge badge-danger px-2 py-1 text-xs">On Hold</span>
                                @endif

                                @if($task->priority == 1)
                                    <span class="badge badge-secondary px-2 py-1 text-xs">Low</span>
                                @elseif($task->priority == 2)
                                    <span class="badge badge-info px-2 py-1 text-xs">Medium</span>
                                @elseif($task->priority == 3)
                                    <span class="badge badge-warning px-2 py-1 text-xs">High</span>
                                @elseif($task->priority == 4)
                                    <span class="badge badge-danger px-2 py-1 text-xs">Urgent</span>
                                @endif

                                @if($task->assignedUser)
                                    <span class="badge badge-light border text-gray-800 px-2 py-1 text-xs">
                                        <i class="fas fa-user mr-1 text-primary"></i>{{ $task->assignedUser->name }}
                                    </span>
                                @else
                                    <span class="badge badge-light border text-muted px-2 py-1 text-xs font-italic">Unassigned</span>
                                @endif

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
                        @can('update', $task)
                            <select class="form-control form-control-sm inline-task-update font-weight-bold" 
                                    data-task-id="{{ $task->id }}" data-field="type" style="min-width: 110px;">
                                <option value="1" {{ $task->type == 1 ? 'selected' : '' }}>Task</option>
                                <option value="2" {{ $task->type == 2 ? 'selected' : '' }}>Bug</option>
                                <option value="3" {{ $task->type == 3 ? 'selected' : '' }}>Feature</option>
                                <option value="4" {{ $task->type == 4 ? 'selected' : '' }}>Improvement</option>
                            </select>
                        @else
                            <span class="badge {{ $task->getTypeBadgeClass() }} p-2 shadow-sm">
                                <i class="fas {{ $task->getTypeIcon() }} mr-1"></i>
                                {{ $task->getTypeName() }}
                            </span>
                        @endcan
                    </td>
                    @if($showProjectColumn ?? false)
                        <td class="align-middle d-none d-md-table-cell">
                            @can('update', $task)
                                <select class="form-control form-control-sm inline-task-update" 
                                        data-task-id="{{ $task->id }}" data-field="project_id" style="min-width: 130px;">
                                    <option value="" {{ is_null($task->project_id) ? 'selected' : '' }}>-- Personal --</option>
                                    @if(isset($projects))
                                        @foreach($projects as $proj)
                                            <option value="{{ $proj->id }}" {{ $task->project_id == $proj->id ? 'selected' : '' }}>{{ $proj->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            @else
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
                            @endcan
                        </td>
                    @endif
                    <td class="align-middle d-none d-md-table-cell">
                        @can('update', $task)
                            <select class="form-control form-control-sm inline-task-update" 
                                    data-task-id="{{ $task->id }}" data-field="assigned_to" style="min-width: 130px;">
                                <option value="" {{ is_null($task->assigned_to) ? 'selected' : '' }}>-- Unassigned --</option>
                                @if(isset($companyUsers))
                                    @foreach($companyUsers as $u)
                                        <option value="{{ $u->id }}" {{ $task->assigned_to == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        @else
                            @if($task->assignedUser)
                                <span class="badge badge-light p-2 border text-gray-800">
                                    <i class="fas fa-user fa-sm mr-1 text-primary"></i>
                                    {{ $task->assignedUser->name }}
                                </span>
                            @else
                                <span class="text-muted small font-italic">Unassigned</span>
                            @endif
                        @endcan
                    </td>
                    <td class="align-middle d-none d-md-table-cell">
                        @can('update', $task)
                            <input type="date" class="form-control form-control-sm inline-task-update" 
                                   data-task-id="{{ $task->id }}" data-field="due_date" value="{{ $task->due_date }}" style="width: 135px;">
                        @else
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
                        @endcan
                    </td>
                    <td class="align-middle d-none d-md-table-cell">
                        @can('update', $task)
                            <select class="form-control form-control-sm inline-task-update font-weight-bold" 
                                    data-task-id="{{ $task->id }}" data-field="status" style="min-width: 120px;">
                                <option value="1" {{ $task->status == 1 ? 'selected' : '' }}>To Do</option>
                                <option value="2" {{ $task->status == 2 ? 'selected' : '' }}>In Progress</option>
                                <option value="3" {{ $task->status == 3 ? 'selected' : '' }}>Completed</option>
                                <option value="4" {{ $task->status == 4 ? 'selected' : '' }}>On Hold</option>
                            </select>
                        @else
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
                        @endcan
                    </td>
                    <td class="align-middle d-none d-md-table-cell">
                        @can('update', $task)
                            <select class="form-control form-control-sm inline-task-update font-weight-bold" 
                                    data-task-id="{{ $task->id }}" data-field="priority" style="min-width: 110px;">
                                <option value="1" {{ $task->priority == 1 ? 'selected' : '' }}>Low</option>
                                <option value="2" {{ $task->priority == 2 ? 'selected' : '' }}>Medium</option>
                                <option value="3" {{ $task->priority == 3 ? 'selected' : '' }}>High</option>
                                <option value="4" {{ $task->priority == 4 ? 'selected' : '' }}>Urgent</option>
                            </select>
                        @else
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
                        @endcan
                    </td>
                    <td class="text-center align-middle" style="white-space: nowrap;">
                        @can('update', $task)
                            <div class="d-inline-flex align-items-center" style="gap: 4px;">
                                <x-edit-button :href="route('tasks.show', $task)" class="edit-task-btn" title="View / Edit Task" />
                                <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="d-inline delete-task-form">
                                    @csrf
                                    @method('DELETE')
                                    <x-delete-button type="submit" title="Delete Task" />
                                </form>
                            </div>
                        @else
                            <span class="text-muted small font-italic">No actions</span>
                        @endcan
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

@if($wrapInCard ?? ($showFilters ?? false))
        </div>
        @if(method_exists($tasks, 'hasPages') && $tasks->hasPages())
            <div class="card-footer py-2">
                <div class="d-flex justify-content-center">
                    {!! $tasks->withQueryString()->links() !!}
                </div>
            </div>
        @endif
    </div>
@endif

<!-- Hidden form for inline task addition -->
<form action="{{ route('tasks.store') }}" method="POST" id="inlineAddTaskForm" style="display:none;">
    @csrf
</form>

@include('partials.edit_task_modal')

<script>
(function() {
    if (window.inlineTaskUpdateHandlerSet) return;
    window.inlineTaskUpdateHandlerSet = true;

    document.addEventListener('change', function(e) {
        const target = e.target;
        if (!target || !target.classList.contains('inline-task-update')) return;

        const taskId = target.getAttribute('data-task-id');
        const field = target.getAttribute('data-field');
        const value = target.value;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        target.style.opacity = '0.5';

        const bodyData = {};
        bodyData[field] = value;

        fetch('/tasks/' + taskId, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(bodyData)
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => { throw err; });
            }
            return response.json();
        })
        .then(data => {
            target.style.opacity = '1.0';
            target.classList.add('is-valid');
            setTimeout(() => target.classList.remove('is-valid'), 1200);

            if (typeof showToast === 'function') {
                showToast(data.message || 'Task updated successfully', 'success');
            }

            if (field === 'status') {
                const tr = target.closest('tr');
                if (tr) {
                    const titleLink = tr.querySelector('.task-title-link');
                    const toggleIcon = tr.querySelector('.toggle-task-form i');
                    if (value == '3') {
                        tr.classList.add('completed-task');
                        tr.classList.remove('pending-task');
                        if (titleLink) {
                            titleLink.classList.add('text-muted', 'text-line-through');
                        }
                        if (toggleIcon) {
                            toggleIcon.className = 'far fa-check-square fa-2x text-success';
                        }
                    } else {
                        tr.classList.remove('completed-task');
                        tr.classList.add('pending-task');
                        if (titleLink) {
                            titleLink.classList.remove('text-muted', 'text-line-through');
                        }
                        if (toggleIcon) {
                            toggleIcon.className = 'far fa-square fa-2x text-gray-300';
                        }
                    }
                }
            }
        })
        .catch(err => {
            target.style.opacity = '1.0';
            target.classList.add('is-invalid');
            setTimeout(() => target.classList.remove('is-invalid'), 1200);
            const msg = err && err.message ? err.message : 'Failed to update task.';
            if (typeof showToast === 'function') {
                showToast(msg, 'error');
            } else {
                alert(msg);
            }
        });
    });
})();
</script>

@if($includeFilterScripts ?? ($showFilters ?? false))
    @push('scripts')
    <script src="{{ asset('asset/js/tasks.js') }}"></script>
    <script>
        $(document).ready(function() {
            function applyTaskFilters() {
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

                if ($('#toggleCompletedTasks').length) {
                    if ($('#toggleCompletedTasks').is(':checked')) {
                        params.set('show_completed', 'true');
                    } else {
                        params.delete('show_completed');
                    }
                }

                params.delete('page');
                window.location.href = window.location.pathname + '?' + params.toString();
            }

            $('#filterProject, #filterStatus, #filterAssignee, #filterType, #toggleCompletedTasks').change(function() {
                applyTaskFilters();
            });

            $('#resetFilters').click(function() {
                $('#filterProject').val('all');
                $('#filterStatus').val('all');
                $('#filterAssignee').val('all');
                $('#filterType').val('all');
                applyTaskFilters();
            });

            $(document).keydown(function(e) {
                if (e.altKey && (e.key === 't' || e.key === 'T')) {
                    e.preventDefault();
                    if ($('#btnShowInlineAdd').length) {
                        $('#btnShowInlineAdd').click();
                    } else {
                        $('#inlineAddRow').show();
                        $('#inline_title').focus();
                    }
                }
            });

            $(document).keydown(function(e) {
                if (e.key === 'Escape') {
                    e.preventDefault();
                    $('#inlineAddRow').hide();
                }
            });
        });
    </script>
    @endpush
@endif
