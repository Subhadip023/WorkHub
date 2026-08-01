@extends('layouts.admin')

@section('title', $project->name . ' - Notes')

@push('styles')
<style>
    #projectShowTabs .nav-link.active {
        color: {{ $project->theme }} !important;
        border-bottom: 3px solid {{ $project->theme }} !important;
        background: transparent;
    }
    .note-description-preview {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
        color: #5a5c69;
        font-size: 0.9rem;
        line-height: 1.6;
    }
</style>
@endpush

@section('content')
<!-- Back Button and Heading -->
<div class="mb-4">
    <a href="{{ route('projects.index') }}" class="btn btn-sm btn-secondary shadow-sm mb-3">
        <i class="fas fa-arrow-left fa-sm text-white-50 mr-1"></i> Back to Projects
    </a>
    <div class="d-sm-flex align-items-center justify-content-between">
        <div>
            <h1 class="h3 mb-0 text-gray-800 font-weight-bold d-inline-block align-middle">{{ $project->name }}</h1>
            <span class="badge ml-2 text-white px-2 py-1 align-middle" style="background-color: {{ $project->theme }}; font-size: 0.8rem;">
                {{ $project->theme }}
            </span>
            @if($project->status == 1)
                <span class="badge ml-2 badge-secondary px-2 py-1 align-middle" style="font-size: 0.8rem;">To Do</span>
            @elseif($project->status == 2)
                <span class="badge ml-2 badge-primary px-2 py-1 align-middle" style="font-size: 0.8rem;">In Progress</span>
            @elseif($project->status == 3)
                <span class="badge ml-2 badge-success px-2 py-1 align-middle" style="font-size: 0.8rem;">Completed</span>
            @elseif($project->status == 4)
                <span class="badge ml-2 badge-warning px-2 py-1 align-middle" style="font-size: 0.8rem;">On Hold</span>
            @endif

            @if($project->priority == 1)
                <span class="badge ml-2 badge-light border px-2 py-1 align-middle text-gray-800" style="font-size: 0.8rem;">Low</span>
            @elseif($project->priority == 2)
                <span class="badge ml-2 badge-info px-2 py-1 align-middle" style="font-size: 0.8rem;">Medium</span>
            @elseif($project->priority == 3)
                <span class="badge ml-2 badge-warning px-2 py-1 align-middle" style="font-size: 0.8rem;">High</span>
            @elseif($project->priority == 4)
                <span class="badge ml-2 badge-danger px-2 py-1 align-middle" style="font-size: 0.8rem;">Urgent</span>
            @endif
        </div>
        <div>
            <a href="{{ route('projects.edit', $project) }}" class="btn btn-sm btn-info shadow-sm">
                <i class="fas fa-edit fa-sm text-white-50 mr-1"></i> Edit Project
            </a>
            @can('delete', $project)
                <form action="{{ route('projects.destroy', $project) }}" method="POST" class="d-inline ml-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger shadow-sm">
                        <i class="fas fa-trash fa-sm text-white-50 mr-1"></i> Delete Project
                    </button>
                </form>
            @endcan
        </div>
    </div>
    @if($project->description)
        <div class="mt-2 text-gray-600 lead">{!! $project->description !!}</div>
    @else
        <p class="mt-2 text-gray-500 italic">No description provided for this project.</p>
    @endif
</div>

<!-- Navigation Tabs Bar -->
<div>
    <ul class="nav nav-tabs mb-4" id="projectShowTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link font-weight-bold" href="{{ route('projects.show', $project) }}">
                <i class="fas fa-tasks mr-2"></i>Tasks
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link active font-weight-bold" href="{{ route('projects.notes', $project) }}">
                <i class="fas fa-sticky-note mr-2"></i>Notes
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link font-weight-bold" href="{{ route('projects.credentials', $project) }}">
                <i class="fas fa-key mr-2"></i>Credentials
            </a>
        </li>
    </ul>
</div>

<!-- Main Content Area -->
<div class="row">
    <div class="col-lg-9">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-sticky-note mr-1"></i> Project Notes</h6>
                <a href="{{ route('notes.create', ['note_type' => 1, 'note_type_id' => $project->id, 'redirect_back' => request()->fullUrl()]) }}" class="btn btn-sm btn-primary shadow-sm">
                    <i class="fas fa-plus fa-sm mr-1"></i> Add Note
                </a>
            </div>
            <div class="card-body">
                <div class="row">
                    @forelse($notes as $note)
                        <div class="col-lg-6 col-12 mb-3">
                            <div class="card shadow-sm h-100 border-0 note-card-hover" style="border-left: 4px solid {{ $project->theme }} !important; border-radius: 8px;">
                                <div class="card-body d-flex flex-column p-4">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <span class="badge badge-primary px-2.5 py-1 font-weight-bold shadow-sm" style="background-color: rgba(78, 115, 223, 0.15); color: #4e73df;">Project Note</span>
                                        
                                        <div class="dropdown no-arrow">
                                            <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink{{ $note->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-right shadow border-0 animated--fade-in" aria-labelledby="dropdownMenuLink{{ $note->id }}" style="border-radius: 8px;">
                                                <a class="dropdown-item py-2" href="{{ route('notes.edit', [$note, 'redirect_back' => request()->fullUrl()]) }}">
                                                    <i class="fas fa-edit fa-sm fa-fw mr-2 text-gray-400"></i> Edit Note
                                                </a>
                                                <a class="dropdown-item py-2" href="{{ route('notes.pdf', $note) }}">
                                                    <i class="fas fa-file-pdf fa-sm fa-fw mr-2 text-danger"></i> Download PDF
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
                                        <a href="{{ route('notes.show', [$note, 'redirect_back' => request()->fullUrl()]) }}" class="text-gray-900 text-decoration-none hover-link" style="font-size: 1.15rem; line-height: 1.4;">
                                            {{ $note->title }}
                                        </a>
                                    </h5>

                                    <!-- Description Preview -->
                                    <p class="text-gray-600 mb-4 flex-grow-1 note-description-preview">
                                        {!! strip_tags($note->description) !!}
                                    </p>
                                    
                                    <div class="d-flex align-items-center justify-content-between text-xs text-gray-500 font-weight-bold border-top pt-3">
                                        <span class="text-muted d-inline-flex align-items-center">
                                            <i class="fas fa-user mr-1"></i>{{ $note->user->name ?? 'Unknown' }}
                                        </span>
                                        <span title="Created {{ $note->created_at->format('M d, Y h:i A') }}" class="d-inline-flex align-items-center text-muted">
                                            <i class="far fa-clock mr-1"></i>{{ $note->created_at->diffForHumans() }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center py-5">
                            <i class="fas fa-sticky-note fa-3x text-gray-300 mb-3"></i>
                            <h6 class="font-weight-bold text-gray-700">No Notes Found for {{ $project->name }}</h6>
                            <p class="text-xs text-muted mb-3">Document architecture decisions, meeting minutes, and project guidelines here.</p>
                            <a href="{{ route('notes.create', ['note_type' => 1, 'note_type_id' => $project->id, 'redirect_back' => request()->fullUrl()]) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-plus mr-1"></i> Create First Note
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column (1/3 width) -->
    <div class="col-lg-3">
        <!-- Comments Section -->
        @include('partials.comments', [
            'comments' => $comments,
            'commentableType' => 'project',
            'commentableId' => $project->id
        ])
    </div>
</div>
@endsection
