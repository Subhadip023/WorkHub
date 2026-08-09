@extends('layouts.admin')

@section('title', 'Task Details - ' . $task->title)

@push('styles')
<!-- Quill rich text editor library styles -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
    .task-description-content img,
    .description-body img,
    #editor-container .ql-editor img {
        max-width: 100% !important;
        height: auto !important;
        border-radius: 6px;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    .task-description-content,
    .description-body,
    #editor-container .ql-editor {
        word-break: break-word;
        overflow-wrap: break-word;
    }
</style>
@endpush

@section('content')
@php
    $canMutate = auth()->user()->can('update', $task);
@endphp

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4 flex-wrap">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 bg-transparent p-0">
            <li class="breadcrumb-item"><a href="{{ route('tasks.index') }}" class="font-weight-bold">Tasks</a></li>
            @if($task->project)
                <li class="breadcrumb-item"><a href="{{ route('projects.show', $task->project) }}">{{ $task->project->name }}</a></li>
            @else
                <li class="breadcrumb-item text-muted">No Project</li>
            @endif
            <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($task->title, 30) }}</li>
        </ol>
    </nav>
    <div class="d-flex align-items-center flex-wrap" style="gap: 6px;">
        @if($canMutate)
            {{-- Primary Action: Add Subtask --}}
            <button type="button" class="btn btn-primary btn-sm shadow-sm font-weight-bold" data-toggle="modal" data-target="#addSubtaskModal">
                <i class="fas fa-plus fa-sm mr-1"></i> Add Subtask
            </button>

            <div class="border-left mx-1" style="height: 20px; border-color: #d1d3e2 !important;"></div>

            {{-- Icon Action Group --}}
            <form action="{{ route('tasks.toggle', $task) }}" method="POST" class="d-inline mb-0">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn {{ $task->status == 3 ? 'btn-warning' : 'btn-success' }} btn-sm shadow-sm" title="Mark as {{ $task->status == 3 ? 'Pending' : 'Completed' }}" data-toggle="tooltip">
                    <i class="fas {{ $task->status == 3 ? 'fa-undo' : 'fa-check' }}"></i>
                </button>
            </form>

            <form action="{{ route('tasks.copy', $task) }}" method="POST" class="d-inline mb-0">
                @csrf
                <button type="submit" class="btn btn-outline-secondary btn-sm shadow-sm" title="Copy Task" data-toggle="tooltip">
                    <i class="fas fa-copy"></i>
                </button>
            </form>

            <button type="button" class="btn btn-outline-secondary btn-sm shadow-sm move-task-btn"
                    title="Move Task"
                    data-toggle="tooltip"
                    data-id="{{ $task->id }}"
                    data-title="{{ $task->title }}"
                    data-project_id="{{ $task->project_id }}"
                    data-action="{{ route('tasks.update', $task) }}">
                <i class="fas fa-exchange-alt"></i>
            </button>
            
            <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="d-inline mb-0"
                  onsubmit="return confirm('Are you sure you want to delete this task? This cannot be undone.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm shadow-sm" title="Delete Task" data-toggle="tooltip">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </form>

            <div class="border-left mx-1" style="height: 20px; border-color: #d1d3e2 !important;"></div>
        @endif

        @php
            $referer = request('redirect_back') ?? url()->previous();
            $currentUrl = request()->url();
            $isSamePage = $referer && (str_contains($referer, '/tasks/' . $task->id) || $referer === $currentUrl);

            if ($referer && !$isSamePage) {
                $backUrl = $referer;
            } else {
                $backUrl = $task->project ? route('projects.show', $task->project) : route('tasks.index');
            }

            $backLabel = $task->project ? 'Back to Project' : 'Back to Tasks';
        @endphp

        {{-- Navigation Action: Back --}}
        <a href="{{ $backUrl }}" class="btn btn-outline-secondary btn-sm shadow-sm">
            <i class="fas fa-arrow-left fa-sm mr-1"></i> Back
        </a>
    </div>
</div>

@if($task->parent)
    <div class="alert alert-info py-2 px-3 mb-4 d-flex align-items-center justify-content-between shadow-sm rounded border-left-info" style="background: linear-gradient(135deg, #eaecf4 0%, #e3f2fd 100%);">
        <div class="d-flex align-items-center">
            <i class="fas fa-sitemap text-primary fa-lg mr-3"></i>
            <div>
                <span class="text-xs text-uppercase font-weight-bold text-gray-600 d-block">Parent Task</span>
                <a href="{{ route('tasks.show', $task->parent) }}" class="font-weight-bold text-primary text-decoration-none h6 mb-0">
                    {{ $task->parent->title }}
                </a>
            </div>
        </div>
        <a href="{{ route('tasks.show', $task->parent) }}" class="btn btn-sm btn-primary shadow-sm font-weight-bold">
            <i class="fas fa-arrow-left fa-sm mr-1"></i> Go to Parent Task
        </a>
    </div>
@endif

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
                        <div class="form-row align-items-center mb-3">
                            <div class="col">
                                <label for="task_title" class="font-weight-bold text-gray-700 text-xs text-uppercase mb-1 d-block">Task Title</label>
                                <input type="text" class="form-control form-control-lg font-weight-bold text-gray-900 border-0 bg-light" id="task_title" name="title" value="{{ $task->title }}" required>
                            </div>
                            <div class="col-auto">
                                <label for="task_points" class="font-weight-bold text-gray-700 text-xs text-uppercase mb-1 d-block">Points</label>
                                <input type="number" name="points" id="task_points" class="form-control form-control-lg font-weight-bold text-gray-900 border-0 bg-light" style="width: 110px;" value="{{ $task->points }}" min="0" max="99999" placeholder="Pts">
                            </div>
                        </div>
                        <div class="row align-items-start">
                            <div class="col-lg col-md-6 form-group mb-3">
                                <label class="font-weight-bold text-gray-700 text-xs text-uppercase mb-2">Assignee</label>
                                <x-assignee-badge
                                    name="assigned_to"
                                    :assigned-to="$task->assigned_to"
                                    :assigned-user="$task->assignedUser"
                                    :users="$companyUsers"
                                    :editable="$canMutate"
                                    wrapper="div"
                                />
                            </div>
                            <div class="col-lg col-md-6 form-group mb-3">
                                <label class="font-weight-bold text-gray-700 text-xs text-uppercase mb-2">Due Date</label>
                                <x-due-date-badge
                                    name="due_date"
                                    :due-date="$task->due_date"
                                    :status="$task->status"
                                    :editable="$canMutate"
                                    wrapper="div"
                                />
                            </div>
                            <div class="col-lg col-md-4 form-group mb-3">
                                <label class="font-weight-bold text-gray-700 text-xs text-uppercase mb-2">Status</label>
                                <x-task-status-badge
                                    name="status"
                                    :status="$task->status"
                                    :editable="$canMutate"
                                    wrapper="div"
                                />
                            </div>
                            <div class="col-lg col-md-4 form-group mb-3">
                                <label class="font-weight-bold text-gray-700 text-xs text-uppercase mb-2">Priority</label>
                                <x-priority-badge
                                    name="priority"
                                    :priority="$task->priority"
                                    :editable="auth()->user()->can('update', $task)"
                                    wrapper="div"
                                />
                            </div>
                            <div class="col-lg col-md-4 form-group mb-3">
                                <label class="font-weight-bold text-gray-700 text-xs text-uppercase mb-2">Type</label>
                                <x-task-type-badge
                                    name="type"
                                    :task-type="$task->type"
                                    :editable="$canMutate"
                                    wrapper="div"
                                />
                            </div>
                        </div>
                        <div class="text-right border-top pt-3 mt-1">
                            <button type="submit" class="btn btn-sm btn-info shadow-sm">
                                <i class="fas fa-save mr-1"></i> Update Meta Fields
                            </button>
                        </div>
                    </form>
                @else
                    <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap" style="gap: 12px;">
                        <h2 class="font-weight-bold text-gray-900 mb-0">{{ $task->title }}</h2>
                        @if($task->points !== null)
                            <span class="badge badge-light border text-primary p-2 font-weight-bold text-sm shadow-xs" title="{{ $task->points }} Points">
                                {{ $task->points }} pts
                            </span>
                        @endif
                    </div>
                    <div class="d-flex align-items-center mt-3 text-gray-600 flex-wrap">
                        <div class="mr-4 mb-2">
                            <span class="font-weight-bold text-xs text-uppercase d-block mb-1">Assignee</span>
                            <x-assignee-badge
                                :assigned-to="$task->assigned_to"
                                :assigned-user="$task->assignedUser"
                                :users="$companyUsers"
                                :editable="false"
                                wrapper="div"
                            />
                        </div>
                        <div class="mr-4 mb-2">
                            <span class="font-weight-bold text-xs text-uppercase d-block mb-1">Due Date</span>
                            <x-due-date-badge :task-id="$task->id" :due-date="$task->due_date" :status="$task->status" :editable="false" wrapper="div" />
                        </div>
                        <div class="mr-4 mb-2">
                            <span class="font-weight-bold text-xs text-uppercase d-block mb-1">Status</span>
                            <x-task-status-badge :status="$task->status" :editable="false" wrapper="div" />
                        </div>
                        <div class="mr-4 mb-2">
                            <span class="font-weight-bold text-xs text-uppercase d-block mb-1">Priority</span>
                            <x-priority-badge
                                :priority="$task->priority"
                                :editable="auth()->user()->can('update', $task)"
                                wrapper="div"
                            />
                        </div>
                        <div class="mr-4 mb-2">
                            <span class="font-weight-bold text-xs text-uppercase d-block mb-1">Type</span>
                            <x-task-type-badge :task-type="$task->type" :editable="false" wrapper="div" />
                        </div>
                        @if($task->externalSource)
                            <div class="mb-2 ml-4">
                                <span class="font-weight-bold text-xs text-uppercase d-block mb-1">Source</span>
                                <span class="badge badge-dark p-2 shadow-sm" title="Created via External API Key">
                                    <i class="fas fa-plug text-warning mr-1"></i> Via API: {{ $task->externalSource->externalTaskApi?->name ?? 'External API' }}
                                </span>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- Description Card with HTML View & Toggleable Quill.js Editor -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-align-left mr-1"></i> Description</h6>
                @if($canMutate)
                    <button type="button" class="btn btn-sm btn-outline-primary shadow-sm" id="btn-edit-description">
                        <i class="fas fa-pencil-alt fa-sm mr-1"></i> Edit Description
                    </button>
                @endif
            </div>
            <div class="card-body">
                <!-- Read-only HTML View -->
                <div id="description-read-view" class="task-description-content">
                    @if($task->description && trim(strip_tags($task->description, '<img>')) !== '')
                        <div class="description-body text-gray-900 ql-editor" style="padding:0; min-height:auto;">{!! $task->description !!}</div>
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-align-left fa-2x mb-2 text-gray-300"></i>
                            <p class="mb-0 italic small">No description provided for this task.</p>
                            @if($canMutate)
                                <button type="button" class="btn btn-sm btn-link text-primary mt-1" onclick="$('#btn-edit-description').click();">
                                    Click here to add description
                                </button>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Edit View with Quill Editor -->
                @if($canMutate)
                    <div id="description-edit-view" class="d-none">
                        <form id="description-form" action="{{ route('tasks.update', $task) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="title" value="{{ $task->title }}">
                            <input type="hidden" name="assigned_to" value="{{ $task->assigned_to }}">
                            <input type="hidden" name="due_date" value="{{ $task->due_date }}">
                            <input type="hidden" name="status" value="{{ $task->status }}">
                            <input type="hidden" name="priority" value="{{ $task->priority }}">
                            <input type="hidden" name="type" value="{{ $task->type }}">
                            <input type="hidden" name="points" value="{{ $task->points }}">
                            <input type="hidden" name="description" id="hidden-description">
                            
                            <div id="editor-container" style="height: 250px;">{!! $task->description !!}</div>
                            
                            <div class="text-right mt-3">
                                <button type="button" class="btn btn-sm btn-secondary mr-2" id="btn-cancel-edit">Cancel</button>
                                <button type="submit" class="btn btn-sm btn-primary shadow-sm">
                                    <i class="fas fa-save mr-1"></i> Save Description
                                </button>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
        </div>

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
                                            <button type="button" class="btn btn-light btn-sm mx-1 rounded-circle insert-img-btn" data-url="{{ asset('storage/' . $img->image_path) }}" title="Insert into Description">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-light btn-sm mx-1 rounded-circle copy-url-btn" data-url="{{ asset('storage/' . $img->image_path) }}" title="Copy URL">
                                                <i class="fas fa-copy"></i>
                                            </button>
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

    <!-- Right Column: Subtasks, Discussion & History -->
    <div class="col-lg-4">
        <!-- Subtasks Card -->
        @if($subtaskProgress['total'] > 0)
        <div class="card shadow mb-4 border-left-primary">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between flex-wrap">
                <div class="d-flex align-items-center mb-1 mb-sm-0">
                    <h6 class="m-0 font-weight-bold text-primary mr-2">
                        <i class="fas fa-sitemap mr-1"></i> Subtasks
                    </h6>
                    <span class="badge badge-primary badge-pill">{{ $subtaskProgress['completed'] }}/{{ $subtaskProgress['total'] }}</span>
                </div>
                @if($canMutate)
                    <button type="button" class="btn btn-xs btn-primary shadow-sm" data-toggle="modal" data-target="#addSubtaskModal">
                        <i class="fas fa-plus fa-sm mr-1"></i> Add
                    </button>
                @endif
            </div>
            <div class="card-body">
                @if($subtaskProgress['total'] > 0)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between text-xs font-weight-bold text-gray-600 mb-1">
                            <span>Progress</span>
                            <span>{{ $subtaskProgress['percentage'] }}%</span>
                        </div>
                        <div class="progress" style="height: 8px; border-radius: 4px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $subtaskProgress['percentage'] }}%; border-radius: 4px;" aria-valuenow="{{ $subtaskProgress['percentage'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>

                    <div class="list-group list-group-flush">
                        @foreach($task->subtasks as $subtask)
                            <div class="list-group-item px-0 py-2 d-flex align-items-center justify-content-between border-bottom">
                                <div class="d-flex align-items-center mr-2 text-truncate" style="max-width: 65%;">
                                    @if($canMutate)
                                        <form action="{{ route('tasks.toggle', $subtask) }}" method="POST" class="d-inline mr-2 mb-0">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-link p-0 text-decoration-none border-0" title="Toggle status">
                                                <i class="{{ $subtask->status == 3 ? 'far fa-check-circle text-success' : 'far fa-circle text-gray-400' }}"></i>
                                            </button>
                                        </form>
                                    @else
                                        <i class="{{ $subtask->status == 3 ? 'far fa-check-circle text-success' : 'far fa-circle text-gray-400' }} mr-2"></i>
                                    @endif

                                    <a href="{{ route('tasks.show', $subtask) }}" class="font-weight-bold text-gray-900 text-truncate small {{ $subtask->status == 3 ? 'text-decoration-line-through text-muted' : '' }}">
                                        {{ $subtask->title }}
                                    </a>
                                </div>

                                <div class="d-flex align-items-center">
                                    <div class="mr-1">
                                        <x-task-status-badge :status="$subtask->status" :editable="false" wrapper="div" />
                                    </div>
                                    @if($canMutate)
                                        <form action="{{ route('tasks.destroy', $subtask) }}" method="POST" class="d-inline ml-1 mb-0" onsubmit="return confirm('Delete this subtask?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-link text-danger p-0 border-0" title="Delete subtask">
                                                <i class="fas fa-trash-alt text-gray-400"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-3 text-muted">
                        <i class="fas fa-sitemap fa-2x mb-2 text-gray-300"></i>
                        <p class="mb-0 italic small">No subtasks created yet.</p>
                        @if($canMutate)
                            <button type="button" class="btn btn-sm btn-link text-primary font-weight-bold mt-1" data-toggle="modal" data-target="#addSubtaskModal">
                                + Add first subtask
                            </button>
                        @endif
                    </div>
                @endif
            </div>
        </div>
        @endif
        <!-- Comments Section -->
        @include('partials.comments', [
            'comments' => $comments,
            'commentableType' => 'task',
            'commentableId' => $task->id
        ])

        <!-- Task History Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Task History</h6>
            </div>
            <div class="card-body" style="max-height: 350px; overflow-y: auto;">
                @if($task->histories && $task->histories->isNotEmpty())
                    <div class="timeline-history">
                        @foreach($task->histories as $history)
                            <div class="mb-3 pl-3" style="border-left: 3px solid #4e73df !important;">
                                <div class="font-weight-bold text-gray-800" style="font-size: 0.85rem;">
                                    {{ $history->getDescription() }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    by {{ $history->user ? $history->user->name : 'System/Unknown' }} &bull; {{ $history->created_at->diffForHumans() }}
                                </div>
                                @if($history->getOldValueDetails())
                                    <div class="text-xs text-muted">
                                        {{ $history->getOldValueDetails() }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted small mb-0 font-italic">No history available for this task.</p>
                @endif
            </div>
        </div>
    </div>
</div>

@include('partials.create_subtask_modal', ['task' => $task, 'companyUsers' => $companyUsers, 'canMutate' => $canMutate])
@include('partials.move_task_modal')

@endsection

@push('scripts')
<!-- Quill rich text editor library script -->
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script>
    $(document).ready(function() {
        // Render & Normalize Checkboxes in Description Read View
        function renderDescriptionCheckboxes() {
            // Remove Quill ql-ui spans in read view
            $('#description-read-view .ql-ui').remove();

            // 1. Process markdown style checkboxes ([ ], [x], - [ ], * [ ]) in p tags
            $('#description-read-view p').each(function() {
                var $p = $(this);
                var text = $p.text().trim();
                var html = $p.html();

                if (/^(\s*(-|\*)?\s*\[\s*\]\s*)/.test(text)) {
                    var newHtml = html.replace(/^(\s*(-|\*)?\s*\[\s*\]\s*)/, '');
                    $p.replaceWith('<ul><li data-list="unchecked"><input type="checkbox" class="task-desc-checkbox"><span class="task-desc-text">' + newHtml + '</span></li></ul>');
                } else if (/^(\s*(-|\*)?\s*\[[xX]\]\s*)/.test(text)) {
                    var newHtml = html.replace(/^(\s*(-|\*)?\s*\[[xX]\]\s*)/, '');
                    $p.replaceWith('<ul><li data-list="checked" class="task-item-completed"><input type="checkbox" class="task-desc-checkbox" checked="checked"><span class="task-desc-text">' + newHtml + '</span></li></ul>');
                }
            });

            // 2. Process Quill li[data-list] elements
            $('#description-read-view li[data-list]').each(function() {
                var $li = $(this);
                var isChecked = $li.attr('data-list') === 'checked';
                $li.find('.ql-ui').remove();

                var $chk = $li.children('input.task-desc-checkbox');
                if ($chk.length === 0) {
                    var textHtml = $li.html().replace(/<input[^>]*>/gi, '').trim();
                    $chk = $('<input type="checkbox" class="task-desc-checkbox">');
                    $li.html('').append($chk).append('<span class="task-desc-text">' + textHtml + '</span>');
                }

                if (isChecked) {
                    $chk.prop('checked', true).attr('checked', 'checked');
                    $li.addClass('task-item-completed');
                } else {
                    $chk.prop('checked', false).removeAttr('checked');
                    $li.removeClass('task-item-completed');
                }
            });
        }

        renderDescriptionCheckboxes();

        // Native Checkbox Change Handler with AJAX Save
        $(document).on('change', '#description-read-view input.task-desc-checkbox', function(e) {
            @if($canMutate)
                var $chk = $(this);
                var isChecked = $chk.is(':checked');
                var $li = $chk.closest('li[data-list]');

                if (isChecked) {
                    $chk.attr('checked', 'checked');
                    if ($li.length) {
                        $li.attr('data-list', 'checked').addClass('task-item-completed');
                    }
                } else {
                    $chk.removeAttr('checked');
                    if ($li.length) {
                        $li.attr('data-list', 'unchecked').removeClass('task-item-completed');
                    }
                }

                // Clean clone of HTML for saving to database
                var $clone = $('#description-read-view .description-body').clone();
                $clone.find('input.task-desc-checkbox').remove();
                $clone.find('span.task-desc-text').each(function() {
                    $(this).replaceWith($(this).html());
                });

                var updatedHtml = $clone.html();

                if (typeof quill !== 'undefined' && quill.root) {
                    quill.root.innerHTML = updatedHtml;
                }

                $.ajax({
                    url: "{{ route('tasks.update', $task) }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        _method: "PATCH",
                        title: "{{ e($task->title) }}",
                        description: updatedHtml
                    },
                    success: function() {
                        showToast(isChecked ? 'Item completed' : 'Item unchecked', 'success');
                    },
                    error: function() {
                        showToast('Failed to update checklist item', 'error');
                        $chk.prop('checked', !isChecked);
                    }
                });
            @else
                e.preventDefault();
            @endif
        });

        // Click on Text Label Toggles Checkbox
        $(document).on('click', '#description-read-view .task-desc-text', function(e) {
            @if($canMutate)
                e.preventDefault();
                var $li = $(this).closest('li[data-list]');
                var $chk = $li.find('input.task-desc-checkbox');
                if ($chk.length) {
                    $chk.prop('checked', !$chk.is(':checked')).trigger('change');
                }
            @endif
        });

        @if($canMutate)
            var quill = new Quill('#editor-container', {
                theme: 'snow',
                readOnly: false,
                modules: {
                    toolbar: [
                        [{ 'header': [1, 2, 3, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }, { 'list': 'check' }],
                        ['link', 'blockquote', 'code-block'],
                        ['clean']
                    ]
                }
            });

            // Toggle Edit Mode
            $('#btn-edit-description').click(function() {
                $('#description-read-view').addClass('d-none');
                $(this).addClass('d-none');
                $('#description-edit-view').removeClass('d-none');
                quill.focus();
            });

            // Cancel Edit Mode
            $('#btn-cancel-edit').click(function() {
                $('#description-edit-view').addClass('d-none');
                $('#description-read-view').removeClass('d-none');
                $('#btn-edit-description').removeClass('d-none');
            });

            $('#description-form').submit(function() {
                var descHtml = quill.root.innerHTML;
                if (descHtml === '<p><br></p>' || descHtml.trim() === '') {
                    descHtml = '';
                }
                $('#hidden-description').val(descHtml);
            });

            // Insert image into editor
            $('.insert-img-btn').click(function() {
                if ($('#description-edit-view').hasClass('d-none')) {
                    $('#btn-edit-description').click();
                }
                var url = $(this).data('url');
                var range = quill.getSelection();
                if (range) {
                    quill.insertEmbed(range.index, 'image', url);
                    quill.setSelection(range.index + 1);
                } else {
                    quill.insertEmbed(quill.getLength() - 1, 'image', url);
                }
                // Scroll to editor
                $('html, body').animate({
                    scrollTop: $("#editor-container").offset().top - 100
                }, 500);
            });
        @endif

        // Copy URL to clipboard
        $('.copy-url-btn').click(function() {
            var url = $(this).data('url');
            if (navigator.clipboard) {
                navigator.clipboard.writeText(url).then(showSuccess).catch(fallbackCopy);
            } else {
                fallbackCopy();
            }

            var $btn = $(this);
            function showSuccess() {
                var originalHtml = $btn.html();
                $btn.html('<i class="fas fa-check text-success"></i>');
                setTimeout(function() {
                    $btn.html(originalHtml);
                }, 2000);
            }

            function fallbackCopy() {
                var $temp = $("<input>");
                $("body").append($temp);
                $temp.val(url).select();
                document.execCommand("copy");
                $temp.remove();
                showSuccess();
            }
        });
    });
</script>
@endpush
