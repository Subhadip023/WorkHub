@extends('layouts.admin')

@section('title', 'Notes')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Notes & Documentation</h1>
    <a href="{{ route('notes.create') }}" class="btn btn-primary shadow-sm">
        <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Add Note
    </a>
</div>

<!-- Notes Type Filter Tab Bar -->
<div class="card shadow mb-4">
    <div class="card-body py-2">
        <ul class="nav nav-pills" id="notesFilterTab" role="tablist">
            <li class="nav-item mr-2">
                <a class="nav-link active font-weight-bold" id="all-tab" data-toggle="pill" href="#all-notes" role="tab">All Notes</a>
            </li>
            <li class="nav-item mr-2">
                <a class="nav-link font-weight-bold" id="personal-tab" data-toggle="pill" href="#personal-notes" role="tab">Personal</a>
            </li>
            <li class="nav-item mr-2">
                <a class="nav-link font-weight-bold" id="project-tab" data-toggle="pill" href="#project-notes" role="tab">Projects</a>
            </li>
            <li class="nav-item mr-2">
                <a class="nav-link font-weight-bold" id="task-tab" data-toggle="pill" href="#task-notes" role="tab">Tasks</a>
            </li>
            <li class="nav-item">
                <a class="nav-link font-weight-bold" id="org-tab" data-toggle="pill" href="#org-notes" role="tab">Organizations</a>
            </li>
        </ul>
    </div>
</div>

<!-- Notes Grid -->
<div class="tab-content" id="notesTabContent">
    <!-- ALL NOTES -->
    <div class="tab-pane fade show active" id="all-notes" role="tabpanel">
        <div class="row">
            @forelse($notes as $note)
                @include('notes.partials.note_card', ['note' => $note])
            @empty
                <div class="col-12 text-center py-5">
                    <div class="text-center mb-4">
                        <i class="far fa-sticky-note fa-3x text-gray-300"></i>
                    </div>
                    <p class="text-muted">No notes found. Create a note to get started!</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- PERSONAL NOTES -->
    <div class="tab-pane fade" id="personal-notes" role="tabpanel">
        <div class="row">
            @forelse($notes->where('note_type', \App\Models\Note::TYPE_PERSONAL) as $note)
                @include('notes.partials.note_card', ['note' => $note])
            @empty
                <div class="col-12 text-center py-5">
                    <div class="text-center mb-4">
                        <i class="far fa-sticky-note fa-3x text-gray-300"></i>
                    </div>
                    <p class="text-muted">No personal notes found.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- PROJECT NOTES -->
    <div class="tab-pane fade" id="project-notes" role="tabpanel">
        <div class="row">
            @forelse($notes->where('note_type', \App\Models\Note::TYPE_PROJECT) as $note)
                @include('notes.partials.note_card', ['note' => $note])
            @empty
                <div class="col-12 text-center py-5">
                    <div class="text-center mb-4">
                        <i class="far fa-sticky-note fa-3x text-gray-300"></i>
                    </div>
                    <p class="text-muted">No project notes found.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- TASK NOTES -->
    <div class="tab-pane fade" id="task-notes" role="tabpanel">
        <div class="row">
            @forelse($notes->where('note_type', \App\Models\Note::TYPE_TASK) as $note)
                @include('notes.partials.note_card', ['note' => $note])
            @empty
                <div class="col-12 text-center py-5">
                    <div class="text-center mb-4">
                        <i class="far fa-sticky-note fa-3x text-gray-300"></i>
                    </div>
                    <p class="text-muted">No task notes found.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- ORGANIZATION NOTES -->
    <div class="tab-pane fade" id="org-notes" role="tabpanel">
        <div class="row">
            @forelse($notes->where('note_type', \App\Models\Note::TYPE_ORGANIZATION) as $note)
                @include('notes.partials.note_card', ['note' => $note])
            @empty
                <div class="col-12 text-center py-5">
                    <div class="text-center mb-4">
                        <i class="far fa-sticky-note fa-3x text-gray-300"></i>
                    </div>
                    <p class="text-muted">No organization notes found.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Pagination -->
@if ($notes->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {!! $notes->links() !!}
    </div>
@endif

@endsection
