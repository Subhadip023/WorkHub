@extends('layouts.admin')

@section('title', 'View Note - ' . $note->title)

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 bg-transparent p-0">
            <li class="breadcrumb-item"><a href="{{ route('notes.index') }}" class="font-weight-bold">Notes</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($note->title, 40) }}</li>
        </ol>
    </nav>
    <div class="d-flex align-items-center">
        <a href="{{ request()->query('redirect_back', route('notes.index')) }}" class="btn btn-secondary btn-sm shadow-sm mr-2">
            <i class="fas fa-arrow-left fa-sm mr-1"></i> Back
        </a>
        <a href="{{ route('notes.edit', [$note, 'redirect_back' => request()->query('redirect_back', route('notes.index'))]) }}" class="btn btn-info btn-sm shadow-sm mr-2">
            <i class="fas fa-edit fa-sm mr-1"></i> Edit Note
        </a>
        <form action="{{ route('notes.destroy', $note) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this note?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm shadow-sm">
                <i class="fas fa-trash fa-sm mr-1"></i> Delete
            </button>
        </form>
    </div>
</div>

<div class="row">
    <!-- Main Content Column -->
    <div class="col-lg-8 col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex align-items-center justify-content-between bg-white border-bottom-0">
                <div>
                    <h1 class="h3 font-weight-bold text-gray-900 mb-1">{{ $note->title }}</h1>
                    <div class="text-xs text-gray-500 font-weight-bold">
                        Created {{ $note->created_at->format('F d, Y \a\t h:i A') }} ({{ $note->created_at->diffForHumans() }})
                        @if($note->updated_at != $note->created_at)
                            &bull; Updated {{ $note->updated_at->diffForHumans() }}
                        @endif
                    </div>
                </div>
            </div>
            <div class="card-body pt-0">
                <hr class="mt-0 mb-4">
                <div class="note-content text-gray-800 lead font-weight-normal" style="line-height: 1.7; font-size: 1.1rem;">
                    {!! $note->description !!}
                </div>
            </div>
        </div>
    </div>

    <!-- Side Metadata Column -->
    <div class="col-lg-4 col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Note Context</h6>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <div class="text-xs font-weight-bold text-gray-700 text-uppercase mb-2">Scope / Type</div>
                    <div>
                        @if($note->note_type == 4)
                            <span class="badge badge-success px-3 py-2 font-weight-bold shadow-sm" style="font-size: 0.85rem;">
                                <i class="fas fa-lock mr-1"></i> Private Personal Note
                            </span>
                        @elseif($note->note_type == 1)
                            <span class="badge badge-primary px-3 py-2 font-weight-bold shadow-sm" style="font-size: 0.85rem;">
                                <i class="fas fa-project-diagram mr-1"></i> Project Specific
                            </span>
                        @elseif($note->note_type == 2)
                            <span class="badge badge-warning px-3 py-2 font-weight-bold shadow-sm text-dark" style="font-size: 0.85rem;">
                                <i class="fas fa-tasks mr-1"></i> Task Specific
                            </span>
                        @elseif($note->note_type == 3)
                            <span class="badge badge-info px-3 py-2 font-weight-bold shadow-sm" style="font-size: 0.85rem;">
                                <i class="fas fa-building mr-1"></i> Organization Level
                            </span>
                        @endif
                    </div>
                </div>

                @if($note->noteable)
                    <div class="mb-4">
                        <div class="text-xs font-weight-bold text-gray-700 text-uppercase mb-2">Associated Reference</div>
                        <div>
                            @if($note->note_type == 1)
                                <a href="{{ route('projects.show', $note->noteable) }}" class="btn btn-outline-primary btn-block text-left shadow-sm">
                                    <i class="fas fa-project-diagram mr-2"></i> {{ $note->noteable->name }}
                                </a>
                            @elseif($note->note_type == 2)
                                <a href="{{ route('tasks.show', $note->noteable) }}" class="btn btn-outline-warning btn-block text-left shadow-sm text-dark">
                                    <i class="fas fa-tasks mr-2 text-warning"></i> {{ $note->noteable->title }}
                                </a>
                            @elseif($note->note_type == 3)
                                <div class="p-2 border rounded bg-light text-gray-800">
                                    <i class="fas fa-building mr-2 text-info"></i> {{ $note->noteable->name }}
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
                
                @if($note->note_type == 2 && $note->noteable && $note->noteable->project)
                    <div class="mb-4">
                        <div class="text-xs font-weight-bold text-gray-700 text-uppercase mb-2">Parent Project</div>
                        <div>
                            <a href="{{ route('projects.show', $note->noteable->project) }}" class="btn btn-outline-secondary btn-block text-left shadow-sm">
                                <i class="fas fa-project-diagram mr-2 text-secondary"></i> {{ $note->noteable->project->name }}
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
