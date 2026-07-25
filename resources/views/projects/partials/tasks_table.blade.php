<div id="noTasksContainer" class="text-center py-5" style="display: {{ $tasks->isEmpty() ? 'block' : 'none' }}">
    <i class="fas fa-clipboard-list fa-3x text-gray-300 mb-3"></i>
    <h5 class="text-gray-500 font-weight-bold">
        @if($project->tasks()->count() == 0)
            No tasks found
        @else
            No matching tasks
        @endif
    </h5>
    <p class="text-gray-500 mb-0">
        @if($project->tasks()->count() == 0)
            Get started by creating your first task for this project!
        @else
            Try adjusting your filters or search terms.
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

            @foreach($tasks as $task)
                <tr class="task-row-item {{ $task->status == 3 ? 'completed-task' : 'pending-task' }}">
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
                            <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="d-inline ml-1 delete-task-form">
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
