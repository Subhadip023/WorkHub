@extends('layouts.admin')

@section('title', $project->name)

@push('styles')
{{-- Only this rule stays inline because it uses the dynamic $project->theme PHP variable.
     All other #projectShowTabs styles are in public/asset/css/admin-custom.css --}}
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
@include('partials.project_tabs')
<div class="row">
   
    <!-- Main Content Area (Full width) -->
    <div class="col-lg-12">
        <!-- Nav Option Tabs -->
        

        <div class="tab-content" id="projectShowTabsContent">
            <!-- Tasks Tab Pane -->
            <div class="tab-pane fade show active" id="tasks-content" role="tabpanel" aria-labelledby="tasks-tab">
                <!-- Task Stats & Progress Card -->
                <div class="card shadow mb-4" style="border-left: 4px solid {{ $project->theme }}">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col mr-2">
                <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: {{ $project->theme }}">
                    Tasks Completion Progress
                </div>
                <div class="row no-gutters align-items-center">
                    <div class="col-auto">
                        <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">{{ $percentage }}%</div>
                    </div>
                    <div class="col">
                        <div class="progress progress-sm mr-2">
                            <div class="progress-bar" role="progressbar" 
                                 style="width: {{ $percentage }}%; background-color: {{ $project->theme }}" 
                                 aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                    <div class="col-auto">
                        <span class="text-gray-600 font-weight-bold">({{ $completedTasks }} / {{ $totalTasks }})</span>
                    </div>
                </div>
            </div>
            <div class="col-auto">
                <button class="btn btn-primary shadow-sm" id="btnShowInlineAdd">
                    <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Add Task
                </button>
                <button class="btn btn-info shadow-sm ml-1" data-toggle="modal" data-target="#importJsonModal">
                    <i class="fas fa-file-import fa-sm text-white-50 mr-1"></i> Import JSON
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Tasks Section -->
<div id="tasksListWrapper">
    @include('projects.partials.tasks_table', [
        'tasks' => $tasks,
        'companyUsers' => $companyUsers,
        'showFilters' => true,
        'cardTitle' => 'Project Tasks'
    ])
</div>
            </div> <!-- End Tasks Tab Pane -->

            <!-- Notes Tab Pane -->
            <div class="tab-pane fade" id="notes-content" role="tabpanel" aria-labelledby="notes-tab">
                <!-- Notes Section -->
                <div class="card shadow mb-4">
    <div class="card-header py-3 d-flex align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-sticky-note mr-1"></i> Project Notes</h6>
        <a href="{{ route('notes.create', ['note_type' => 1, 'note_type_id' => $project->id, 'redirect_back' => request()->fullUrl()]) }}" class="btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm mr-1"></i> Add Note
        </a>
    </div>
    <div class="card-body">
        <div class="row">
            @forelse($project->notes as $note)
                <div class="col-lg-6 col-12 mb-3">
                    <div class="card shadow-sm h-100 border-0 note-card-hover" style="border-left: 4px solid #4e73df !important; border-radius: 8px;">
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

                            <!-- Description Preview with clamp lines -->
                            <p class="text-gray-600 mb-4 flex-grow-1 note-description-preview" style="font-size: 0.9rem; line-height: 1.6; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; white-space: normal;">
                                {!! strip_tags($note->description) !!}
                            </p>
                            
                            <div class="d-flex align-items-center justify-content-between text-xs text-gray-500 font-weight-bold border-top pt-3">
                                <span class="text-muted d-inline-flex align-items-center">
                                    <i class="fas fa-project-diagram mr-1"></i>{{ Str::limit($project->name, 15) }}
                                </span>
                                <span title="Created {{ $note->created_at->format('M d, Y h:i A') }}" class="d-inline-flex align-items-center text-muted">
                                    <i class="far fa-clock mr-1"></i>{{ $note->created_at->diffForHumans() }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-4">
                    <p class="text-muted mb-0">No notes found for this project. Add one to document project info!</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
            </div> <!-- End Notes Tab Pane -->
        </div> <!-- End Tab Content Container -->

    </div>
</div>

@include('partials.discussion_drawer', ['project' => $project, 'comments' => $comments])

@include('partials.edit_task_modal')

{{-- Bulk Import Tasks Modal --}}
<div class="modal fade" id="importJsonModal" tabindex="-1" role="dialog" aria-labelledby="importJsonModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title font-weight-bold text-white mb-0" id="importJsonModalLabel">
                    <i class="fas fa-file-import mr-2"></i> Bulk Import Tasks (JSON / CSV)
                </h5>
                <button class="close text-white opacity-75" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <form action="{{ route('projects.tasks.import', $project) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <!-- Nav Tabs for Upload Type -->
                    <ul class="nav nav-pills nav-justified mb-3" id="importMethodTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active font-weight-bold" id="paste-tab" data-toggle="pill" href="#paste-pane" role="tab">
                                <i class="fas fa-code mr-1"></i> Paste JSON / CSV
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link font-weight-bold" id="file-tab" data-toggle="pill" href="#file-pane" role="tab">
                                <i class="fas fa-upload mr-1"></i> Upload File (.json / .csv)
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content" id="importMethodTabsContent">
                        <!-- Paste Pane -->
                        <div class="tab-pane fade show active" id="paste-pane" role="tabpanel">
                            <div class="form-group mb-0">
                                <label for="json_data" class="font-weight-bold text-gray-800 text-xs text-uppercase mb-2">Paste JSON Array or CSV Content</label>
                                <textarea class="form-control font-monospace border" id="json_data" name="json_data" rows="7" placeholder='[
  {
    "title": "Design Database Schema",
    "description": "Define tables for users and projects",
    "status": "In Progress",
    "priority": "High",
    "type": "Task",
    "points": 5,
    "due_date": "2026-09-01",
    "subtasks": ["Create ERD diagram", "Define migration files"]
  }
]' style="font-size: 0.85rem; border-radius: 6px;"></textarea>
                            </div>
                        </div>

                        <!-- File Upload Pane -->
                        <div class="tab-pane fade" id="file-pane" role="tabpanel">
                            <div class="form-group mb-0">
                                <label for="import_file" class="font-weight-bold text-gray-800 text-xs text-uppercase mb-2">Select JSON or CSV File</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="import_file" name="import_file" accept=".json,.csv,.txt">
                                    <label class="custom-file-label" for="import_file">Choose file (.json, .csv)...</label>
                                </div>
                                <small class="form-text text-muted mt-2">Maximum file size: 5MB.</small>
                            </div>
                        </div>
                    </div>

                    <!-- Supported Structure Accordion / Help -->
                    <div class="card mt-4 border-0 bg-light rounded">
                        <div class="card-body p-3">
                            <h6 class="font-weight-bold text-gray-800 text-xs text-uppercase mb-2">
                                <i class="fas fa-info-circle text-info mr-1"></i> Supported Fields & Format Details
                            </h6>
                            <div class="row text-xs text-gray-700">
                                <div class="col-md-6 mb-2">
                                    <strong>Core Fields:</strong>
                                    <ul class="pl-3 mb-0">
                                        <li><code>title</code> <span class="text-danger font-weight-bold">*Required</span></li>
                                        <li><code>description</code> (HTML or Plain text)</li>
                                        <li><code>due_date</code> (e.g. <code>2026-09-01</code>)</li>
                                        <li><code>points</code> (Story points integer)</li>
                                    </ul>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <strong>Flexible Mappings:</strong>
                                    <ul class="pl-3 mb-0">
                                        <li><code>status</code>: <code>To Do</code>, <code>In Progress</code>, <code>Completed</code>, <code>On Hold</code> (or 1-4)</li>
                                        <li><code>priority</code>: <code>Low</code>, <code>Medium</code>, <code>High</code>, <code>Urgent</code> (or 1-4)</li>
                                        <li><code>type</code>: <code>Task</code>, <code>Bug</code>, <code>Feature</code>, <code>Improvement</code> (or 1-4)</li>
                                        <li><code>assignee</code>: User ID, Email, or Name</li>
                                        <li><code>subtasks</code>: Array of titles or objects (JSON)</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-3">
                    <button class="btn btn-secondary btn-sm shadow-sm" type="button" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm shadow-sm font-weight-bold">
                        <i class="fas fa-file-import mr-1"></i> Import Tasks Now
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('asset/js/tasks.js') }}"></script>
<script>
    $(document).ready(function() {
        // Tab switching persistence
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            localStorage.setItem('activeProjectTab_' + {{ $project->id }}, $(e.target).attr('href'));
        });
        var activeTab = localStorage.getItem('activeProjectTab_' + {{ $project->id }});
        if (activeTab) {
            $('#projectShowTabs a[href="' + activeTab + '"]').tab('show');
        }

        // Toggle Completed Tasks logic
        var toggleCheckbox = document.getElementById('toggleCompletedTasks');
        if (toggleCheckbox) {
            var params = new URLSearchParams(window.location.search);
            toggleCheckbox.checked = params.get('show_completed') === 'true' || localStorage.getItem('showCompletedTasks') === 'true';

            toggleCheckbox.addEventListener('change', function() {
                localStorage.setItem('showCompletedTasks', toggleCheckbox.checked);
                var currentUrl = new URL(window.location.href);
                if (toggleCheckbox.checked) {
                    currentUrl.searchParams.set('show_completed', 'true');
                } else {
                    currentUrl.searchParams.delete('show_completed');
                }
                window.location.href = currentUrl.toString();
            });
        // Custom file input label update
        $('.custom-file-input').on('change', function() {
            let fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').addClass("selected").html(fileName);
        });
    });
</script>
@endpush
