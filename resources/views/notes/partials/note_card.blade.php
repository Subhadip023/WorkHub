<div class="col-lg-4 col-md-6 col-12 mb-4">
    <div class="card shadow h-100 border-left-{{ $note->note_type == 4 ? 'success' : ($note->note_type == 1 ? 'primary' : ($note->note_type == 2 ? 'warning' : 'info')) }}">
        <div class="card-body d-flex flex-column">
            <div class="d-flex align-items-center justify-content-between mb-2">
                @if($note->note_type == 4)
                    <span class="badge badge-success px-2 py-1 font-weight-bold shadow-sm">Personal</span>
                @elseif($note->note_type == 1)
                    <span class="badge badge-primary px-2 py-1 font-weight-bold shadow-sm">Project</span>
                @elseif($note->note_type == 2)
                    <span class="badge badge-warning px-2 py-1 font-weight-bold shadow-sm">Task</span>
                @elseif($note->note_type == 3)
                    <span class="badge badge-info px-2 py-1 font-weight-bold shadow-sm">Organization</span>
                @endif
                
                <div class="dropdown no-arrow">
                    <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink{{ $note->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink{{ $note->id }}">
                        <a class="dropdown-item" href="{{ route('notes.edit', $note) }}">
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

            <h5 class="font-weight-bold mb-2">
                <a href="{{ route('notes.show', $note) }}" class="text-gray-900 text-decoration-none hover-link">
                    {{ $note->title }}
                </a>
            </h5>
            
            <hr class="my-2">
            
            <div class="d-flex align-items-center justify-content-between text-xs text-gray-500 font-weight-bold">
                <span>
                    @if($note->note_type == 1)
                        @if($note->noteable)
                            <a href="{{ route('projects.show', $note->noteable) }}" class="text-primary text-decoration-none">
                                <i class="fas fa-project-diagram mr-1"></i>{{ $note->noteable->name }}
                            </a>
                        @else
                            <span class="text-muted"><i class="fas fa-project-diagram mr-1"></i>Deleted Project</span>
                        @endif
                    @elseif($note->note_type == 2)
                        @if($note->noteable)
                            <a href="{{ route('tasks.show', $note->noteable) }}" class="text-warning text-decoration-none">
                                <i class="fas fa-tasks mr-1"></i>{{ $note->noteable->title }}
                            </a>
                        @else
                            <span class="text-muted"><i class="fas fa-tasks mr-1"></i>Deleted Task</span>
                        @endif
                    @elseif($note->note_type == 3)
                        @if($note->noteable)
                            <span class="text-info"><i class="fas fa-building mr-1"></i>{{ $note->noteable->name }}</span>
                        @else
                            <span class="text-muted"><i class="fas fa-building mr-1"></i>Deleted Org</span>
                        @endif
                    @elseif($note->note_type == 4)
                        <span class="text-success"><i class="fas fa-user mr-1"></i>Private</span>
                    @endif
                </span>
                <span>
                    {{ $note->created_at->diffForHumans() }}
                    @if($note->user)
                        by {{ $note->user->name }}
                    @endif
                </span>
            </div>
        </div>
    </div>
</div>
