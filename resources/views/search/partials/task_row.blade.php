<div class="list-group-item d-flex align-items-center justify-content-between p-3 border-left-0 border-right-0 border-top-0" style="transition: background-color 0.15s ease;">
    <div class="d-flex align-items-center min-width-0">
        <!-- Status Indicator Icon -->
        <span class="mr-3">
            @if($task->status == 3)
                <i class="fas fa-check-circle text-success fa-lg"></i>
            @elseif($task->status == 4)
                <i class="fas fa-minus-circle text-warning fa-lg"></i>
            @elseif($task->status == 2)
                <i class="fas fa-play-circle text-primary fa-lg"></i>
            @else
                <i class="far fa-circle text-gray-400 fa-lg"></i>
            @endif
        </span>

        <div class="min-width-0">
            <h6 class="font-weight-bold mb-1 text-truncate" style="font-size: 0.95rem;">
                <a href="{{ route('tasks.show', $task) }}" class="text-gray-900 text-decoration-none hover-link {{ $task->status == 3 ? 'text-line-through text-gray-500' : '' }}">
                    {{ $task->title }}
                </a>
            </h6>
            
            <div class="d-flex align-items-center flex-wrap text-xs text-gray-500 font-weight-bold">
                <!-- Task Type Badge -->
                @if($task->type == 2)
                    <span class="badge badge-danger text-uppercase mr-2 font-weight-bold" style="font-size: 0.65rem;">Bug</span>
                @elseif($task->type == 3)
                    <span class="badge badge-primary text-uppercase mr-2 font-weight-bold" style="font-size: 0.65rem;">Feature</span>
                @elseif($task->type == 4)
                    <span class="badge badge-info text-uppercase mr-2 font-weight-bold" style="font-size: 0.65rem;">Improvement</span>
                @else
                    <span class="badge badge-secondary text-uppercase mr-2 font-weight-bold" style="font-size: 0.65rem;">Task</span>
                @endif

                @if($task->project)
                    <span class="mr-2">
                        <i class="fas fa-project-diagram mr-1 text-gray-400"></i>{{ $task->project->name }}
                    </span>
                @else
                    <span class="mr-2">
                        <i class="fas fa-user-lock mr-1 text-gray-400"></i>Personal Task
                    </span>
                @endif

                @if($task->due_date)
                    <span class="mr-2">
                        <i class="far fa-calendar-alt mr-1 text-gray-400"></i>Due {{ \Carbon\Carbon::parse($task->due_date)->format('M d, Y') }}
                    </span>
                @endif
            </div>
        </div>
    </div>

    <div class="d-flex align-items-center">
        <!-- Priority Badges -->
        @if($task->priority == 1)
            <span class="badge badge-light border text-gray-800 mr-3">Low</span>
        @elseif($task->priority == 2)
            <span class="badge badge-info mr-3">Medium</span>
        @elseif($task->priority == 3)
            <span class="badge badge-warning mr-3">High</span>
        @elseif($task->priority == 4)
            <span class="badge badge-danger mr-3">Urgent</span>
        @endif

        <!-- Assigned User Avatar -->
        @if($task->assignedUser)
            <span title="Assigned to {{ $task->assignedUser->name }}">
                @if($task->assignedUser->profile_image)
                    <img class="rounded-circle" style="width: 28px; height: 28px; object-fit: cover;" src="{{ asset('storage/' . $task->assignedUser->profile_image) }}">
                @else
                    <div class="rounded-circle bg-gray-200 text-gray-600 d-flex align-items-center justify-content-center font-weight-bold text-xs" style="width: 28px; height: 28px;">
                        {{ strtoupper(substr($task->assignedUser->name, 0, 1)) }}
                    </div>
                @endif
            </span>
        @else
            <span class="text-gray-400 text-xs italic">Unassigned</span>
        @endif
    </div>
</div>
