@extends('layouts.admin')

@section('title', 'Notes')

@push('styles')
<style>
    .note-card-hover {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }
    .note-card-hover:hover {
        transform: translateY(-4px);
        box-shadow: 0 0.75rem 2rem rgba(58, 59, 69, 0.15) !important;
    }
    /* Custom Modern Underline Tabs for Notes */
    #notesFilterTab {
        border-bottom: 2px solid #eaecf4;
    }
    #notesFilterTab .nav-link {
        border: none;
        background: transparent;
        color: #858796;
        padding: 0.75rem 1.25rem;
        border-bottom: 3px solid transparent;
        font-weight: 700;
        transition: all 0.15s ease-in-out;
        border-radius: 0;
    }
    #notesFilterTab .nav-link:hover {
        color: #5a5c69;
        border-bottom: 3px solid #dddfeb;
    }
    #notesFilterTab .nav-link i {
        transition: transform 0.2s;
    }
    #notesFilterTab .nav-link:hover i {
        transform: translateY(-1px);
    }
    /* Specific Active colors for each tab pill underline */
    #notesFilterTab #all-tab.active { 
        color: #4e73df !important; 
        border-bottom: 3px solid #4e73df !important;
    }
    #notesFilterTab #personal-tab.active { 
        color: #1cc88a !important; 
        border-bottom: 3px solid #1cc88a !important;
    }
    #notesFilterTab #project-tab.active { 
        color: #4e73df !important; 
        border-bottom: 3px solid #4e73df !important;
    }
    #notesFilterTab #task-tab.active { 
        color: #f6c23e !important; 
        border-bottom: 3px solid #f6c23e !important;
    }
    #notesFilterTab #org-tab.active { 
        color: #36b9cc !important; 
        border-bottom: 3px solid #36b9cc !important;
    }
    .hover-link:hover {
        color: #4e73df !important;
    }
</style>
@endpush

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800 font-weight-bold">Notes & Documentation</h1>
        <p class="text-muted mb-0 small">Create, organize, and search your personal or workspace documentation.</p>
    </div>
    <a href="{{ route('notes.create') }}" class="btn btn-primary shadow-sm px-4">
        <i class="fas fa-plus fa-sm text-white-50 mr-2"></i> Add Note
    </a>
</div>

<!-- Notes Type Filter Tab Bar -->
<div class="mb-4">
    <ul class="nav nav-tabs" id="notesFilterTab" role="tablist">
        <li class="nav-item mr-2">
            <a class="nav-link active font-weight-bold" id="all-tab" data-toggle="tab" href="#all-notes" role="tab">
                <i class="fas fa-th-large mr-2"></i>All Notes
            </a>
        </li>
        <li class="nav-item mr-2">
            <a class="nav-link font-weight-bold" id="personal-tab" data-toggle="tab" href="#personal-notes" role="tab">
                <i class="fas fa-user-lock mr-2"></i>Personal
            </a>
        </li>
        <li class="nav-item mr-2">
            <a class="nav-link font-weight-bold" id="project-tab" data-toggle="tab" href="#project-notes" role="tab">
                <i class="fas fa-project-diagram mr-2"></i>Projects
            </a>
        </li>
        <li class="nav-item mr-2">
            <a class="nav-link font-weight-bold" id="task-tab" data-toggle="tab" href="#task-notes" role="tab">
                <i class="fas fa-tasks mr-2"></i>Tasks
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link font-weight-bold" id="org-tab" data-toggle="tab" href="#org-notes" role="tab">
                <i class="fas fa-building mr-2"></i>Organizations
            </a>
        </li>
    </ul>
</div>

<!-- Notes Grid -->
<div class="tab-content" id="notesTabContent">
    <!-- ALL NOTES -->
    <div class="tab-pane fade show active" id="all-notes" role="tabpanel">
        <div class="row">
            @forelse($notes as $note)
                @include('notes.partials.note_card', ['note' => $note])
            @empty
                <div class="col-12 text-center py-5 bg-white rounded shadow-sm">
                    <div class="text-center mb-3">
                        <i class="far fa-sticky-note fa-3x text-gray-300"></i>
                    </div>
                    <h5 class="font-weight-bold text-gray-700">No notes found</h5>
                    <p class="text-muted">Get started by creating your very first note!</p>
                    <a href="{{ route('notes.create') }}" class="btn btn-primary btn-sm px-4 mt-2">
                        <i class="fas fa-plus fa-sm mr-2"></i>Create Note
                    </a>
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
                <div class="col-12 text-center py-5 bg-white rounded shadow-sm">
                    <div class="text-center mb-3">
                        <i class="fas fa-user-lock fa-3x text-gray-300"></i>
                    </div>
                    <h5 class="font-weight-bold text-gray-700">No personal notes</h5>
                    <p class="text-muted">Personal notes are kept private to you.</p>
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
                <div class="col-12 text-center py-5 bg-white rounded shadow-sm">
                    <div class="text-center mb-3">
                        <i class="fas fa-project-diagram fa-3x text-gray-300"></i>
                    </div>
                    <h5 class="font-weight-bold text-gray-700">No project notes</h5>
                    <p class="text-muted">Notes created under your projects will appear here.</p>
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
                <div class="col-12 text-center py-5 bg-white rounded shadow-sm">
                    <div class="text-center mb-3">
                        <i class="fas fa-tasks fa-3x text-gray-300"></i>
                    </div>
                    <h5 class="font-weight-bold text-gray-700">No task notes</h5>
                    <p class="text-muted">Notes created under your tasks will appear here.</p>
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
                <div class="col-12 text-center py-5 bg-white rounded shadow-sm">
                    <div class="text-center mb-3">
                        <i class="fas fa-building fa-3x text-gray-300"></i>
                    </div>
                    <h5 class="font-weight-bold text-gray-700">No organization notes</h5>
                    <p class="text-muted">Notes shared with the entire workspace will appear here.</p>
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
