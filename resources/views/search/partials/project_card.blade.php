<div class="card h-100 shadow-sm border-0 note-card-hover" style="border-left: 4px solid {{ $project->theme }}; border-radius: 8px;">
    <div class="card-body p-4 d-flex flex-column">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="badge text-white px-2.5 py-1 font-weight-bold" style="background-color: {{ $project->theme }};">
                Project
            </span>
            <div class="d-flex align-items-center">
                @if($project->status == 1)
                    <span class="badge badge-secondary px-2 py-1 mr-1">To Do</span>
                @elseif($project->status == 2)
                    <span class="badge badge-primary px-2 py-1 mr-1">In Progress</span>
                @elseif($project->status == 3)
                    <span class="badge badge-success px-2 py-1 mr-1">Completed</span>
                @elseif($project->status == 4)
                    <span class="badge badge-warning px-2 py-1 mr-1">On Hold</span>
                @endif
            </div>
        </div>

        <h5 class="font-weight-bold mb-2">
            <a href="{{ route('projects.show', $project) }}" class="text-gray-900 text-decoration-none hover-link" style="font-size: 1.1rem;">
                {{ $project->name }}
            </a>
        </h5>

        <p class="text-gray-600 mb-3 flex-grow-1" style="font-size: 0.85rem; line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; white-space: normal;">
            {!! strip_tags($project->description ?? 'No description provided.') !!}
        </p>

        <div class="d-flex align-items-center justify-content-between text-xs text-gray-500 font-weight-bold border-top pt-2">
            <span>
                <i class="fas fa-list mr-1"></i>{{ $project->tasks->count() }} Tasks
            </span>
            <span>
                <i class="far fa-clock mr-1"></i>{{ $project->created_at->diffForHumans() }}
            </span>
        </div>
    </div>
</div>
