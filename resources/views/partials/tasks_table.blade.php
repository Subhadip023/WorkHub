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
    <table class="table table-hover align-middle mb-0" id="tasksTable" width="100%" cellspacing="0">
        <thead class="bg-light text-gray-700 text-xs font-weight-bold text-uppercase">
            <tr>
                <th style="width: 50px;" class="text-center">Done</th>
                <th>Task Title</th>
                <th class="d-none d-md-table-cell">Type</th>
                @if($showProjectColumn ?? false)
                    <th class="d-none d-md-table-cell">Project</th>
                @endif
                <th class="d-none d-md-table-cell">Assigned To</th>
                <th class="d-none d-md-table-cell">Due Date</th>
                <th class="d-none d-md-table-cell">Status</th>
                <th class="d-none d-md-table-cell">Priority</th>
                <th class="d-none d-md-table-cell text-center" style="width: 80px;">Points</th>
                <th style="width: 100px;" class="text-center">Actions</th>
            </tr>
        </thead>
        <tbody>
            <!-- Notion-like Inline Add Row -->
            <tr id="inlineAddRow" data-user-id="{{ auth()->id() }}" style="display: none; background-color: rgba(78, 115, 223, 0.05);">
                <td class="text-center align-middle">
                    <i class="far fa-square fa-lg text-gray-400"></i>
                </td>
                <td class="align-middle">
                    <input type="text" id="inline_title" name="title" form="inlineAddTaskForm" class="form-control form-control-sm font-weight-bold mb-1" placeholder="What needs to be done? (Press Ctrl+S to save)" required>
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
                <td class="align-middle d-none d-md-table-cell text-center">
                    <input type="number" name="points" form="inlineAddTaskForm" class="form-control form-control-sm text-center" placeholder="Pts" min="0" max="99999" style="width: 70px; margin: 0 auto;">
                </td>
                <td class="text-center align-middle">
                    <button type="submit" form="inlineAddTaskForm" class="btn btn-sm btn-success shadow-sm" title="Save Task">
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
                    style="display: table-row; {{ $task->priority == 4 && $task->status != 3 ? 'background-color: rgba(231, 74, 59, 0.03);' : '' }}">
                    <td class="text-center align-middle">
                        @can('update', $task)
                            <form action="{{ route('tasks.toggle', $task) }}" method="POST" class="toggle-task-form d-inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-link p-0 text-decoration-none" title="Mark as {{ $task->status == 3 ? 'Pending' : 'Completed' }}">
                                    @if($task->status == 3)
                                        <i class="far fa-check-square fa-lg text-success"></i>
                                    @else
                                        <i class="far fa-square fa-lg text-gray-400 hover-text-success"></i>
                                    @endif
                                </button>
                            </form>
                        @else
                            <i class="far {{ $task->status == 3 ? 'fa-check-square text-success' : 'fa-square text-gray-300' }} fa-lg" style="opacity: 0.6;"></i>
                        @endcan
                    </td>
                    <td class="align-middle">
                        <div class="font-weight-bold {{ $task->status == 3 ? 'text-muted text-line-through' : '' }}">
                            <a href="{{ route('tasks.show', [$task, 'redirect_back' => request()->fullUrl()]) }}" class="text-gray-900 text-decoration-none hover-primary task-title-link">
                                {{ $task->title }}
                            </a>
                            @if($task->points !== null)
                                <span class="badge badge-light border text-primary font-weight-bold px-2 py-1 ml-2 shadow-xs" title="{{ $task->points }} Points">
                                    {{ $task->points }} pts
                                </span>
                            @endif
                            @if($task->externalSource)
                                <span class="badge badge-dark text-xs px-1 ml-1" title="Created via External API Key: {{ $task->externalSource->externalTaskApi?->name }}">
                                    <i class="fas fa-plug text-warning mr-1"></i>API
                                </span>
                            @endif
                        </div>

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
                                    <span class="badge badge-warning text-dark px-2 py-1 text-xs">In Progress</span>
                                @elseif($task->status == 3)
                                    <span class="badge badge-success px-2 py-1 text-xs">Completed</span>
                                @elseif($task->status == 4)
                                    <span class="badge badge-danger px-2 py-1 text-xs">On Hold</span>
                                @endif

                                @if($task->priority == 4)
                                    <span class="badge badge-danger px-2 py-1 text-xs font-weight-bold"><i class="fas fa-fire mr-1"></i> Urgent</span>
                                @elseif($task->priority == 3)
                                    <span class="badge badge-warning text-dark px-2 py-1 text-xs font-weight-bold"><i class="fas fa-arrow-up mr-1"></i> High</span>
                                @elseif($task->priority == 2)
                                    <span class="badge badge-info px-2 py-1 text-xs font-weight-bold"><i class="fas fa-minus mr-1"></i> Medium</span>
                                @else
                                    <span class="badge badge-secondary px-2 py-1 text-xs"><i class="fas fa-arrow-down mr-1"></i> Low</span>
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
                                        $todayStr = \Carbon\Carbon::today()->toDateString();
                                        $isOverdue = $task->status != 3 && $task->due_date < $todayStr;
                                    @endphp
                                    <span class="badge {{ $isOverdue ? 'badge-danger' : 'badge-light border text-gray-800' }} px-2 py-1 text-xs">
                                        <i class="far fa-calendar-alt mr-1"></i>{{ \Carbon\Carbon::parse($task->due_date)->format('M d') }}
                                    </span>
                                @endif

                                @if($task->points !== null)
                                    <span class="badge badge-light border text-primary px-2 py-1 text-xs font-weight-bold">
                                        {{ $task->points }} pts
                                    </span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <x-task-type-badge
                        :task-type="$task->type"
                        :task-id="$task->id"
                        :editable="auth()->user()->can('update', $task)"
                    />
                    @if($showProjectColumn ?? false)
                        <x-project-badge
                            :project-id="$task->project_id"
                            :project="$task->project"
                            :projects="$projects ?? []"
                            :task-id="$task->id"
                            :editable="auth()->user()->can('update', $task)"
                        />
                    @endif
                    <x-assignee-badge
                        :assigned-to="$task->assigned_to"
                        :assigned-user="$task->assignedUser"
                        :users="$companyUsers ?? []"
                        :task-id="$task->id"
                        :editable="auth()->user()->can('update', $task)"
                    />
                    <x-due-date-badge
                        :task-id="$task->id"
                        :due-date="$task->due_date"
                        :status="$task->status"
                        :editable="auth()->user()->can('update', $task)"
                    />
                    <x-task-status-badge
                        :status="$task->status"
                        :task-id="$task->id"
                        :editable="auth()->user()->can('update', $task)"
                    />
                    <x-priority-badge
                        :priority="$task->priority"
                        :task-id="$task->id"
                        :editable="auth()->user()->can('update', $task)"
                    />
                    <td class="align-middle d-none d-md-table-cell text-center" style="width: 80px;">
                        @can('update', $task)
                            <input type="number" 
                                   class="form-control form-control-sm text-center font-weight-bold inline-task-update border-0 bg-light" 
                                   data-task-id="{{ $task->id }}" 
                                   data-field="points" 
                                   value="{{ $task->points }}" 
                                   placeholder="-" 
                                   min="0" 
                                   max="99999"
                                   style="width: 65px; margin: 0 auto;">
                        @else
                            @if($task->points !== null)
                                <span class="badge badge-light border text-primary font-weight-bold px-2 py-1" title="{{ $task->points }} Points">
                                    {{ $task->points }} pts
                                </span>
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        @endcan
                    </td>
                    <td class="text-center align-middle" style="white-space: nowrap;">
                        @can('update', $task)
                            <div class="d-inline-flex align-items-center" style="gap: 4px;">
                                <x-edit-button 
                                    :href="route('tasks.show', $task)" 
                                    class="edit-task-btn" 
                                    data-id="{{ $task->id }}"
                                    data-title="{{ $task->title }}"
                                    data-description="{{ $task->description }}"
                                    data-due_date="{{ $task->due_date }}"
                                    data-assigned_to="{{ $task->assigned_to }}"
                                    data-status="{{ $task->status }}"
                                    data-priority="{{ $task->priority }}"
                                    data-type="{{ $task->type }}"
                                    data-points="{{ $task->points }}"
                                    data-action="{{ route('tasks.update', $task) }}"
                                    title="View / Edit Task" 
                                />
                                <x-move-button 
                                    class="move-task-btn" 
                                    data-id="{{ $task->id }}"
                                    data-title="{{ $task->title }}"
                                    data-project_id="{{ $task->project_id }}"
                                    data-action="{{ route('tasks.update', $task) }}"
                                    title="Move Task" 
                                />
                                <form action="{{ route('tasks.copy', $task) }}" method="POST" class="d-inline">
                                    @csrf
                                    <x-copy-button type="submit" title="Copy Task" />
                                </form>
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
<form action="{{ isset($project) ? route('projects.tasks.store', $project) : route('tasks.store') }}" method="POST" id="inlineAddTaskForm" style="display:none;">
    @csrf
    @if(isset($project))
        <input type="hidden" name="project_id" value="{{ $project->id }}">
    @endif
</form>

@include('partials.edit_task_modal')
@include('partials.move_task_modal')


<script>
(function() {
    if (window.inlineTaskUpdateHandlerSet) return;
    window.inlineTaskUpdateHandlerSet = true;

    function handleInlineUpdate(target) {
        if (!target || !target.classList.contains('inline-task-update')) return;

        const taskId = target.getAttribute('data-task-id');
        const field = target.getAttribute('data-field');
        const value = target.value;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        if (target._lastSavedValue === value) return;

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
            target._lastSavedValue = value;
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
                            toggleIcon.className = 'far fa-check-square fa-lg text-success';
                        }
                    } else {
                        tr.classList.remove('completed-task');
                        tr.classList.add('pending-task');
                        if (titleLink) {
                            titleLink.classList.remove('text-muted', 'text-line-through');
                        }
                        if (toggleIcon) {
                            toggleIcon.className = 'far fa-square fa-lg text-gray-400 hover-text-success';
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
    }

    function scheduleDebouncedUpdate(target, delay) {
        if (target._debounceTimer) {
            clearTimeout(target._debounceTimer);
        }
        target._debounceTimer = setTimeout(function() {
            handleInlineUpdate(target);
        }, delay || 800);
    }

    document.addEventListener('input', function(e) {
        if (e.target && e.target.classList.contains('inline-task-update')) {
            scheduleDebouncedUpdate(e.target, 800);
        }
    });

    document.addEventListener('change', function(e) {
        if (e.target && e.target.classList.contains('inline-task-update')) {
            scheduleDebouncedUpdate(e.target, 800);
        }
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

            $(document).on('click', '.move-task-btn', function() {
                var title = $(this).data('title');
                var projectId = $(this).data('project_id');
                var action = $(this).data('action');

                $('#moveTaskForm').attr('action', action);
                $('#moveTaskTitle').text('"' + title + '"');
                $('#move_task_project_id').val(projectId !== null && projectId !== '' ? projectId : '');
                $('#moveTaskModal').modal('show');
            });
        });
    </script>
    @endpush
@endif
