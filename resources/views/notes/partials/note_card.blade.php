<div class="col-lg-4 col-md-6 col-12 mb-4">
    <div class="card shadow-sm h-100 border-0 note-card-hover" style="border-left: 4px solid {{ $note->note_type == 4 ? '#1cc88a' : ($note->note_type == 1 ? '#4e73df' : ($note->note_type == 2 ? '#f6c23e' : '#36b9cc')) }} !important; border-radius: 8px;">
        <div class="card-body d-flex flex-column p-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                @if($note->note_type == 4)
                    <span class="badge badge-success px-2.5 py-1 font-weight-bold shadow-sm" style="background-color: rgba(28, 200, 138, 0.15); color: #1cc88a;">Personal</span>
                @elseif($note->note_type == 1)
                    <span class="badge badge-primary px-2.5 py-1 font-weight-bold shadow-sm" style="background-color: rgba(78, 115, 223, 0.15); color: #4e73df;">Project</span>
                @elseif($note->note_type == 2)
                    <span class="badge badge-warning px-2.5 py-1 font-weight-bold shadow-sm" style="background-color: rgba(246, 194, 62, 0.15); color: #f6c23e;">Task</span>
                @elseif($note->note_type == 3)
                    <span class="badge badge-info px-2.5 py-1 font-weight-bold shadow-sm" style="background-color: rgba(54, 185, 204, 0.15); color: #36b9cc;">Organization</span>
                @endif
                
                <div class="dropdown no-arrow">
                    <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink{{ $note->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right shadow border-0 animated--fade-in" aria-labelledby="dropdownMenuLink{{ $note->id }}" style="border-radius: 8px;">
                        <a class="dropdown-item py-2" href="{{ route('notes.edit', $note) }}">
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
                <a href="{{ route('notes.show', $note) }}" class="text-gray-900 text-decoration-none hover-link" style="font-size: 1.15rem; line-height: 1.4;">
                    {{ $note->title }}
                </a>
            </h5>

            <!-- Description Preview with clamp lines -->
            <p class="text-gray-600 mb-4 flex-grow-1 note-description-preview" style="font-size: 0.9rem; line-height: 1.6; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; white-space: normal;">
                {!! strip_tags($note->description) !!}
            </p>
            
            <div class="d-flex align-items-center justify-content-between text-xs text-gray-500 font-weight-bold border-top pt-3">
                <span>
                    @if($note->note_type == 1)
                        @if($note->noteable)
                            <a href="{{ route('projects.show', $note->noteable) }}" class="text-primary text-decoration-none d-inline-flex align-items-center">
                                <i class="fas fa-project-diagram mr-1"></i>{{ Str::limit($note->noteable->name, 15) }}
                            </a>
                        @else
                            <span class="text-muted d-inline-flex align-items-center"><i class="fas fa-project-diagram mr-1"></i>Deleted Project</span>
                        @endif
                    @elseif($note->note_type == 2)
                        @if($note->noteable)
                            <a href="{{ route('tasks.show', $note->noteable) }}" class="text-warning text-decoration-none d-inline-flex align-items-center">
                                <i class="fas fa-tasks mr-1"></i>{{ Str::limit($note->noteable->title, 15) }}
                            </a>
                        @else
                            <span class="text-muted d-inline-flex align-items-center"><i class="fas fa-tasks mr-1"></i>Deleted Task</span>
                        @endif
                    @elseif($note->note_type == 3)
                        @if($note->noteable)
                            <span class="text-info d-inline-flex align-items-center" title="{{ $note->noteable->name }}"><i class="fas fa-building mr-1"></i>{{ Str::limit($note->noteable->name, 15) }}</span>
                        @else
                            <span class="text-muted d-inline-flex align-items-center"><i class="fas fa-building mr-1"></i>Deleted Org</span>
                        @endif
                    @elseif($note->note_type == 4)
                        <span class="text-success d-inline-flex align-items-center"><i class="fas fa-lock mr-1"></i>Private</span>
                    @endif
                </span>
                <span title="Created {{ $note->created_at->format('M d, Y h:i A') }}" class="d-inline-flex align-items-center text-muted">
                    <i class="far fa-clock mr-1"></i>{{ $note->created_at->diffForHumans() }}
                </span>
            </div>
        </div>
    </div>
</div>
