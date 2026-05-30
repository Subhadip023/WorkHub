@extends('layouts.admin')

@section('title', 'Task Details - ' . $task->title)

@push('styles')
<!-- Quill rich text editor library styles -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
    .ql-container {
        font-size: 1rem;
        border-bottom-left-radius: 0.35rem;
        border-bottom-right-radius: 0.35rem;
    }
    .ql-toolbar {
        border-top-left-radius: 0.35rem;
        border-top-right-radius: 0.35rem;
        background-color: #f8f9fc;
    }
    .image-card:hover .image-actions {
        opacity: 1 !important;
    }
    .upload-zone {
        border: 2px dashed #dddfeb;
        border-radius: 0.35rem;
        background-color: #f8f9fc;
        transition: all 0.2s ease-in-out;
        cursor: pointer;
    }
    .upload-zone:hover {
        border-color: #4e73df;
        background-color: #f0f3fc;
    }
</style>
@endpush

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 bg-transparent p-0">
            <li class="breadcrumb-item"><a href="{{ route('tasks.index') }}" class="font-weight-bold">Tasks</a></li>
            <li class="breadcrumb-item"><a href="{{ route('projects.show', $task->project) }}">{{ $task->project->name }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($task->title, 30) }}</li>
        </ol>
    </nav>
    <div>
        <a href="{{ route('projects.show', $task->project) }}" class="btn btn-secondary btn-sm shadow-sm">
            <i class="fas fa-arrow-left fa-sm mr-1"></i> Back to Project
        </a>
    </div>
</div>

@php
    $canMutate = ($user_role == 1) || ($task->assigned_to === auth()->id());
@endphp

<div class="row">
    <!-- Left Column: Task Header, Description & Image Gallery -->
    <div class="col-lg-8">
        
        <!-- Task Header & Meta Editing Card -->
        <div class="card shadow mb-4 border-left-info">
            <div class="card-body">
                @if($canMutate)
                    <form action="{{ route('tasks.update', $task) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="description" value="{{ $task->description }}">
                        <div class="form-group">
                            <label for="task_title" class="font-weight-bold text-gray-700 text-xs text-uppercase">Task Title</label>
                            <input type="text" class="form-control form-control-lg font-weight-bold text-gray-900 border-0 bg-light" id="task_title" name="title" value="{{ $task->title }}" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="assigned_to" class="font-weight-bold text-gray-700 text-xs text-uppercase">Assignee</label>
                                <select class="form-control" name="assigned_to" id="assigned_to">
                                    <option value="">-- Unassigned --</option>
                                    @foreach($companyUsers as $user)
                                        <option value="{{ $user->id }}" {{ $task->assigned_to == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="due_date" class="font-weight-bold text-gray-700 text-xs text-uppercase">Due Date</label>
                                <input type="date" class="form-control" name="due_date" id="due_date" value="{{ $task->due_date }}">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="status" class="font-weight-bold text-gray-700 text-xs text-uppercase">Status</label>
                                <select class="form-control" name="status" id="status">
                                    <option value="1" {{ $task->status == 1 ? 'selected' : '' }}>To Do</option>
                                    <option value="2" {{ $task->status == 2 ? 'selected' : '' }}>In Progress</option>
                                    <option value="3" {{ $task->status == 3 ? 'selected' : '' }}>Completed</option>
                                    <option value="4" {{ $task->status == 4 ? 'selected' : '' }}>On Hold</option>
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="priority" class="font-weight-bold text-gray-700 text-xs text-uppercase">Priority</label>
                                <select class="form-control" name="priority" id="priority">
                                    <option value="1" {{ $task->priority == 1 ? 'selected' : '' }}>Low</option>
                                    <option value="2" {{ $task->priority == 2 ? 'selected' : '' }}>Medium</option>
                                    <option value="3" {{ $task->priority == 3 ? 'selected' : '' }}>High</option>
                                    <option value="4" {{ $task->priority == 4 ? 'selected' : '' }}>Urgent</option>
                                </select>
                            </div>
                        </div>
                        <div class="text-right">
                            <button type="submit" class="btn btn-sm btn-info shadow-sm">
                                <i class="fas fa-save mr-1"></i> Update Meta Fields
                            </button>
                        </div>
                    </form>
                @else
                    <h2 class="font-weight-bold text-gray-900 mb-2">{{ $task->title }}</h2>
                    <div class="d-flex align-items-center mt-3 text-gray-600 flex-wrap">
                        <div class="mr-4 mb-2">
                            <span class="font-weight-bold text-xs text-uppercase d-block mb-1">Assignee</span>
                            @if($task->assignedUser)
                                <span class="badge badge-light p-2 border"><i class="fas fa-user mr-1 text-primary"></i> {{ $task->assignedUser->name }}</span>
                            @else
                                <span class="text-muted small italic">Unassigned</span>
                            @endif
                        </div>
                        <div class="mr-4 mb-2">
                            <span class="font-weight-bold text-xs text-uppercase d-block mb-1">Due Date</span>
                            @if($task->due_date)
                                <span class="badge badge-light p-2 border"><i class="far fa-calendar-alt mr-1"></i> {{ \Carbon\Carbon::parse($task->due_date)->format('M d, Y') }}</span>
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </div>
                        <div class="mr-4 mb-2">
                            <span class="font-weight-bold text-xs text-uppercase d-block mb-1">Status</span>
                            @if($task->status == 1)
                                <span class="badge badge-secondary p-2">To Do</span>
                            @elseif($task->status == 2)
                                <span class="badge badge-warning p-2">In Progress</span>
                            @elseif($task->status == 3)
                                <span class="badge badge-success p-2">Completed</span>
                            @elseif($task->status == 4)
                                <span class="badge badge-danger p-2">On Hold</span>
                            @endif
                        </div>
                        <div class="mb-2">
                            <span class="font-weight-bold text-xs text-uppercase d-block mb-1">Priority</span>
                            @if($task->priority == 1)
                                <span class="badge badge-secondary p-2">Low</span>
                            @elseif($task->priority == 2)
                                <span class="badge badge-info p-2">Medium</span>
                            @elseif($task->priority == 3)
                                <span class="badge badge-warning p-2">High</span>
                            @elseif($task->priority == 4)
                                <span class="badge badge-danger p-2">Urgent</span>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Description Card with Quill.js Editor -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-align-left mr-1"></i> Description</h6>
            </div>
            <div class="card-body">
                <form id="description-form" action="{{ route('tasks.update', $task) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="title" value="{{ $task->title }}">
                    <input type="hidden" name="assigned_to" value="{{ $task->assigned_to }}">
                    <input type="hidden" name="due_date" value="{{ $task->due_date }}">
                    <input type="hidden" name="status" value="{{ $task->status }}">
                    <input type="hidden" name="priority" value="{{ $task->priority }}">
                    <input type="hidden" name="description" id="hidden-description">
                    
                    <div id="editor-container" style="height: 250px;">{!! $task->description !!}</div>
                    
                    @if($canMutate)
                        <div class="text-right mt-3">
                            <button type="submit" class="btn btn-primary shadow-sm">
                                <i class="fas fa-save mr-1"></i> Save Description
                            </button>
                        </div>
                    @endif
                </form>
            </div>
        </div>

        <!-- Task Image Attachments -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-images mr-1"></i> Attachments & Images</h6>
            </div>
            <div class="card-body">
                @if($canMutate)
                    <form action="{{ route('tasks.images.store', $task) }}" method="POST" enctype="multipart/form-data" class="mb-4">
                        @csrf
                        <div class="upload-zone p-4 text-center" onclick="document.getElementById('image').click();">
                            <i class="fas fa-cloud-upload-alt fa-3x text-gray-300 mb-2"></i>
                            <h5 class="text-gray-600 font-weight-bold mb-1">Click to select an image</h5>
                            <p class="text-gray-500 small mb-0">Supported formats: JPEG, PNG, JPG, GIF, WEBP (Max 10MB)</p>
                            <input type="file" id="image" name="image" class="d-none" onchange="this.form.submit();" accept="image/*">
                        </div>
                    </form>
                @endif

                @if($task->images->isEmpty())
                    <div class="text-center py-5">
                        <i class="far fa-image fa-3x text-gray-300 mb-3"></i>
                        <h6 class="text-gray-500">No images uploaded yet</h6>
                    </div>
                @else
                    <div class="row">
                        @foreach($task->images as $img)
                            <div class="col-md-4 col-sm-6 mb-4">
                                <div class="card h-100 shadow-sm image-card position-relative overflow-hidden border">
                                    <img src="{{ asset('storage/' . $img->image_path) }}" class="card-img-top img-fluid" style="height: 180px; object-fit: cover;" alt="Task Image">
                                    <div class="image-actions position-absolute w-100 h-100 d-flex align-items-center justify-content-center" style="background: rgba(0,0,0,0.55); opacity: 0; transition: opacity 0.2s ease; top:0; left:0;">
                                        <a href="{{ asset('storage/' . $img->image_path) }}" target="_blank" class="btn btn-light btn-sm mx-1 rounded-circle" title="View Fullscreen">
                                            <i class="fas fa-search-plus"></i>
                                        </a>
                                        @if($canMutate)
                                            <form action="{{ route('tasks.images.destroy', $img) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this image?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm mx-1 rounded-circle" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Notes Section -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-sticky-note mr-1"></i> Task Notes</h6>
                <a href="{{ route('notes.create', ['note_type' => 2, 'note_type_id' => $task->id, 'redirect_back' => request()->fullUrl()]) }}" class="btn btn-sm btn-primary shadow-sm">
                    <i class="fas fa-plus fa-sm mr-1"></i> Add Note
                </a>
            </div>
            <div class="card-body">
                <div class="row">
                    @forelse($task->notes as $note)
                        <div class="col-md-6 col-12 mb-3">
                            <div class="card border-left-warning shadow-sm h-100">
                                <div class="card-body py-3 d-flex flex-column">
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
                        </div>
                    @empty
                        <div class="col-12 text-center py-4">
                            <p class="text-muted mb-0">No notes found for this task. Add one to document task details!</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

    <!-- Right Column: Status & Sidebar Meta -->
    <div class="col-lg-4">
        <!-- Status Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Task Properties</h6>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    @if($task->status == 3)
                        <div class="mb-2">
                            <i class="fas fa-check-circle fa-4x text-success"></i>
                        </div>
                        <h4 class="font-weight-bold text-success">Completed</h4>
                    @else
                        <div class="mb-2">
                            <i class="far fa-circle fa-4x text-warning"></i>
                        </div>
                        <h4 class="font-weight-bold text-warning">Pending</h4>
                    @endif
                </div>

                @if($canMutate)
                    <form action="{{ route('tasks.toggle', $task) }}" method="POST" class="mb-3">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn {{ $task->status == 3 ? 'btn-warning' : 'btn-success' }} btn-block shadow-sm">
                            <i class="fas {{ $task->status == 3 ? 'fa-undo' : 'fa-check' }} mr-1"></i>
                            Mark as {{ $task->status == 3 ? 'Pending' : 'Completed' }}
                        </button>
                    </form>
                    
                    <form action="{{ route('tasks.destroy', $task) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this task?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-block btn-sm shadow-sm">
                            <i class="fas fa-trash mr-1"></i> Delete Task
                        </button>
                    </form>
                @endif

                <hr class="my-4">

                <div class="text-xs font-weight-bold text-gray-700 text-uppercase mb-2">Status</div>
                <div class="mb-3">
                    @if($task->status == 1)
                        <span class="badge badge-secondary p-2" style="font-size: 0.85rem;">To Do</span>
                    @elseif($task->status == 2)
                        <span class="badge badge-warning p-2" style="font-size: 0.85rem;">In Progress</span>
                    @elseif($task->status == 3)
                        <span class="badge badge-success p-2" style="font-size: 0.85rem;">Completed</span>
                    @elseif($task->status == 4)
                        <span class="badge badge-danger p-2" style="font-size: 0.85rem;">On Hold</span>
                    @else
                        <span class="badge badge-light p-2" style="font-size: 0.85rem;">To Do</span>
                    @endif
                </div>

                <div class="text-xs font-weight-bold text-gray-700 text-uppercase mb-2">Priority</div>
                <div class="mb-3">
                    @if($task->priority == 1)
                        <span class="badge badge-secondary p-2" style="font-size: 0.85rem;">Low</span>
                    @elseif($task->priority == 2)
                        <span class="badge badge-info p-2" style="font-size: 0.85rem;">Medium</span>
                    @elseif($task->priority == 3)
                        <span class="badge badge-warning p-2" style="font-size: 0.85rem;">High</span>
                    @elseif($task->priority == 4)
                        <span class="badge badge-danger p-2" style="font-size: 0.85rem;">Urgent</span>
                    @else
                        <span class="badge badge-info p-2" style="font-size: 0.85rem;">Medium</span>
                    @endif
                </div>

                <div class="text-xs font-weight-bold text-gray-700 text-uppercase mb-2">Project</div>
                <div class="mb-3">
                    <a href="{{ route('projects.show', $task->project) }}" class="badge text-white p-2 shadow-sm" style="background-color: {{ $task->project->theme }}; font-size: 0.85rem;">
                        <i class="fas fa-project-diagram mr-1"></i>
                        {{ $task->project->name }}
                    </a>
                </div>

                <div class="text-xs font-weight-bold text-gray-700 text-uppercase mb-2">Timeline</div>
                <div>
                    @if($task->due_date)
                        @php
                            $isOverdue = $task->status != 3 && \Carbon\Carbon::parse($task->due_date)->isPast();
                        @endphp
                        <span class="badge {{ $isOverdue ? 'badge-danger' : 'badge-secondary' }} p-2" style="font-size: 0.85rem;">
                            <i class="far fa-calendar-alt mr-1"></i>
                            {{ \Carbon\Carbon::parse($task->due_date)->format('M d, Y') }}
                            @if($isOverdue)
                                (Overdue)
                            @endif
                        </span>
                    @else
                        <span class="text-muted font-italic">No due date set</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Task History Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Task History</h6>
            </div>
            <div class="card-body" style="max-height: 350px; overflow-y: auto;">
                @if($task->histories && $task->histories->isNotEmpty())
                    <div class="timeline-history">
                        @foreach($task->histories as $history)
                            <div class="mb-3 pl-3" style="border-left: 3px solid #36b9cc !important;">
                                <div class="font-weight-bold text-gray-800" style="font-size: 0.85rem;">
                                    @if($history->old_status === null)
                                        Task Created
                                    @else
                                        Status changed to <span class="badge badge-light text-gray-800 border" style="font-size: 0.75rem;">{{ \App\Models\TaskHistory::getStatusName($history->new_status) }}</span>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500">
                                    by {{ $history->user ? $history->user->name : 'System/Unknown' }} &bull; {{ $history->created_at->diffForHumans() }}
                                </div>
                                @if($history->old_status !== null)
                                    <div class="text-xs text-muted">
                                        From: {{ \App\Models\TaskHistory::getStatusName($history->old_status) }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted small mb-0 font-italic">No status history available for this task.</p>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<!-- Quill rich text editor library script -->
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script>
    $(document).ready(function() {
        var quill = new Quill('#editor-container', {
            theme: 'snow',
            readOnly: {{ $canMutate ? 'false' : 'true' }},
            modules: {
                toolbar: {{ $canMutate ? 'true' : 'false' }}
            }
        });

        $('#description-form').submit(function() {
            var descHtml = quill.root.innerHTML;
            if (descHtml === '<p><br></p>' || descHtml.trim() === '') {
                descHtml = '';
            }
            $('#hidden-description').val(descHtml);
        });
    });
</script>
@endpush
