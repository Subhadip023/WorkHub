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
                        <div class="text-right">
                            <button type="submit" class="btn btn-sm btn-info shadow-sm">
                                <i class="fas fa-save mr-1"></i> Update Meta Fields
                            </button>
                        </div>
                    </form>
                @else
                    <h2 class="font-weight-bold text-gray-900 mb-2">{{ $task->title }}</h2>
                    <div class="d-flex align-items-center mt-3 text-gray-600">
                        <div class="mr-4">
                            <span class="font-weight-bold text-xs text-uppercase d-block mb-1">Assignee</span>
                            @if($task->assignedUser)
                                <span class="badge badge-light p-2 border"><i class="fas fa-user mr-1 text-primary"></i> {{ $task->assignedUser->name }}</span>
                            @else
                                <span class="text-muted small italic">Unassigned</span>
                            @endif
                        </div>
                        <div>
                            <span class="font-weight-bold text-xs text-uppercase d-block mb-1">Due Date</span>
                            @if($task->due_date)
                                <span class="badge badge-light p-2 border"><i class="far fa-calendar-alt mr-1"></i> {{ \Carbon\Carbon::parse($task->due_date)->format('M d, Y') }}</span>
                            @else
                                <span class="text-muted small">-</span>
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
                    @if($task->is_completed)
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
                        <button type="submit" class="btn {{ $task->is_completed ? 'btn-warning' : 'btn-success' }} btn-block shadow-sm">
                            <i class="fas {{ $task->is_completed ? 'fa-undo' : 'fa-check' }} mr-1"></i>
                            Mark as {{ $task->is_completed ? 'Pending' : 'Completed' }}
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

                <!-- Meta list -->
                <div class="text-xs font-weight-bold text-gray-700 text-uppercase mb-2">Project</div>
                <div class="mb-3">
                    <a href="{{ route('projects.show', $task->project) }}" class="badge text-white p-2 shadow-sm font-weight-bold" style="background-color: {{ $task->project->theme }}; font-size: 0.85rem;">
                        <i class="fas fa-project-diagram mr-1"></i>
                        {{ $task->project->name }}
                    </a>
                </div>

                <div class="text-xs font-weight-bold text-gray-700 text-uppercase mb-2">Timeline</div>
                <div>
                    @if($task->due_date)
                        @php
                            $isOverdue = !$task->is_completed && \Carbon\Carbon::parse($task->due_date)->isPast();
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
            // Set value of hidden input to the HTML content of the editor
            var descHtml = quill.root.innerHTML;
            // If Quill editor only contains empty tags, normalize to empty string
            if (descHtml === '<p><br></p>' || descHtml.trim() === '') {
                descHtml = '';
            }
            $('#hidden-description').val(descHtml);
        });
    });
</script>
@endpush
