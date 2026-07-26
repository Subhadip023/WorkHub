@extends('layouts.admin')

@section('title', 'Search Results')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800 font-weight-bold">
            <i class="fas fa-search mr-2 text-primary"></i>Search Results
        </h1>
    </div>

    <!-- Query Info & Search Input -->
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; background: linear-gradient(135deg, #f8f9fc 0%, #ffffff 100%);">
        <div class="card-body p-4">
            <form action="{{ route('search.index') }}" method="GET" class="mw-100">
                <div class="row align-items-center">
                    <div class="col-lg-8 col-md-10">
                        <div class="input-group input-group-lg shadow-sm" style="border-radius: 10px; overflow: hidden;">
                            <input type="text" name="q" class="form-control border-0 px-4" 
                                   value="{{ $query }}" placeholder="Search tasks, projects, notes, or team members..." 
                                   style="font-size: 1.05rem;" required>
                            <div class="input-group-append">
                                <button class="btn btn-primary px-4" type="submit">
                                    <i class="fas fa-search mr-2"></i>Search
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            @if(trim($query) !== '')
                <p class="text-muted mb-0 mt-3" style="font-size: 0.95rem;">
                    Showing results for <strong class="text-gray-900">"{{ $query }}"</strong>
                </p>
            @endif
        </div>
    </div>

    @if(trim($query) === '')
        <!-- Empty State - No Query -->
        <div class="text-center py-5">
            <div class="mb-4">
                <span class="fa-stack fa-3x text-gray-300">
                    <i class="fas fa-circle fa-stack-2x"></i>
                    <i class="fas fa-search fa-stack-1x fa-inverse"></i>
                </span>
            </div>
            <h4 class="font-weight-bold text-gray-800">Find anything instantly</h4>
            <p class="text-muted max-width-500 mx-auto">Type a keyword in the search bar above to look up tasks, projects, notes, or team members across your workspace.</p>
        </div>
    @else
        @php
            $totalCount = $projects->count() + $tasks->count() + $notes->count() + $users->count();
        @endphp

        @if($totalCount === 0)
            <!-- Empty State - No Results -->
            <div class="card shadow-sm border-0 py-5 text-center" style="border-radius: 12px;">
                <div class="card-body">
                    <div class="mb-4">
                        <span class="fa-stack fa-3x text-gray-300">
                            <i class="fas fa-circle fa-stack-2x"></i>
                            <i class="fas fa-search-minus fa-stack-1x fa-inverse"></i>
                        </span>
                    </div>
                    <h4 class="font-weight-bold text-gray-800">No results found</h4>
                    <p class="text-muted max-width-500 mx-auto mb-0">We couldn't find anything matching "{{ $query }}". Try checking your spelling or using different keywords.</p>
                </div>
            </div>
        @else
            <!-- Search Results Tabs and Cards -->
            <div class="row">
                <!-- Search Navigation / Stats -->
                <div class="col-lg-3 mb-4">
                    <div class="card shadow-sm border-0" style="border-radius: 12px;">
                        <div class="card-body p-3">
                            <h6 class="font-weight-bold text-xs text-uppercase tracking-wider text-muted mb-3 px-2">Categories</h6>
                            <div class="list-group list-group-flush border-0" id="searchTabs" role="tablist">
                                <a class="list-group-item list-group-item-action active border-0 d-flex align-items-center justify-content-between rounded mb-1 px-3 py-2.5" 
                                   id="all-tab" data-toggle="tab" href="#all" role="tab" aria-controls="all" aria-selected="true" style="font-weight: 600;">
                                    <span><i class="fas fa-align-left mr-2.5 text-gray-500"></i>All Matches</span>
                                    <span class="badge badge-light border px-2 py-1 font-weight-bold text-gray-700">{{ $totalCount }}</span>
                                </a>
                                <a class="list-group-item list-group-item-action border-0 d-flex align-items-center justify-content-between rounded mb-1 px-3 py-2.5" 
                                   id="projects-tab" data-toggle="tab" href="#projects-list" role="tab" aria-controls="projects-list" aria-selected="false" style="font-weight: 600;">
                                    <span><i class="fas fa-project-diagram mr-2.5 text-info"></i>Projects</span>
                                    <span class="badge badge-light border px-2 py-1 font-weight-bold text-gray-700">{{ $projects->count() }}</span>
                                </a>
                                <a class="list-group-item list-group-item-action border-0 d-flex align-items-center justify-content-between rounded mb-1 px-3 py-2.5" 
                                   id="tasks-tab" data-toggle="tab" href="#tasks-list" role="tab" aria-controls="tasks-list" aria-selected="false" style="font-weight: 600;">
                                    <span><i class="fas fa-check-circle mr-2.5 text-success"></i>Tasks</span>
                                    <span class="badge badge-light border px-2 py-1 font-weight-bold text-gray-700">{{ $tasks->count() }}</span>
                                </a>
                                <a class="list-group-item list-group-item-action border-0 d-flex align-items-center justify-content-between rounded mb-1 px-3 py-2.5" 
                                   id="notes-tab" data-toggle="tab" href="#notes-list" role="tab" aria-controls="notes-list" aria-selected="false" style="font-weight: 600;">
                                    <span><i class="fas fa-sticky-note mr-2.5 text-warning"></i>Notes</span>
                                    <span class="badge badge-light border px-2 py-1 font-weight-bold text-gray-700">{{ $notes->count() }}</span>
                                </a>
                                <a class="list-group-item list-group-item-action border-0 d-flex align-items-center justify-content-between rounded mb-1 px-3 py-2.5" 
                                   id="users-tab" data-toggle="tab" href="#users-list" role="tab" aria-controls="users-list" aria-selected="false" style="font-weight: 600;">
                                    <span><i class="fas fa-users mr-2.5 text-primary"></i>Team Members</span>
                                    <span class="badge badge-light border px-2 py-1 font-weight-bold text-gray-700">{{ $users->count() }}</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab Content Area -->
                <div class="col-lg-9">
                    <div class="tab-content" id="searchTabContent">
                        
                        <!-- ALL RESULTS TAB -->
                        <div class="tab-pane fade show active" id="all" role="tabpanel" aria-labelledby="all-tab">
                            
                            <!-- Projects Section (All tab preview) -->
                            @if($projects->isNotEmpty())
                                <div class="d-flex align-items-center justify-content-between mb-3 mt-1">
                                    <h5 class="font-weight-bold text-gray-900 mb-0">Projects</h5>
                                    <a class="btn btn-sm btn-link text-primary font-weight-bold p-0 text-decoration-none" 
                                       href="#" onclick="$('#projects-tab').tab('show'); return false;">View all ({{ $projects->count() }})</a>
                                </div>
                                <div class="row mb-4">
                                    @foreach($projects->take(3) as $project)
                                        <div class="col-md-6 mb-3">
                                            @include('search.partials.project_card', ['project' => $project])
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <!-- Tasks Section (All tab preview) -->
                            @if($tasks->isNotEmpty())
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <h5 class="font-weight-bold text-gray-900 mb-0">Tasks</h5>
                                    <a class="btn btn-sm btn-link text-primary font-weight-bold p-0 text-decoration-none" 
                                       href="#" onclick="$('#tasks-tab').tab('show'); return false;">View all ({{ $tasks->count() }})</a>
                                </div>
                                <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; overflow: hidden;">
                                    <div class="list-group list-group-flush">
                                        @foreach($tasks->take(5) as $task)
                                            @include('search.partials.task_row', ['task' => $task])
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Notes Section (All tab preview) -->
                            @if($notes->isNotEmpty())
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <h5 class="font-weight-bold text-gray-900 mb-0">Notes</h5>
                                    <a class="btn btn-sm btn-link text-primary font-weight-bold p-0 text-decoration-none" 
                                       href="#" onclick="$('#notes-tab').tab('show'); return false;">View all ({{ $notes->count() }})</a>
                                </div>
                                <div class="row mb-4">
                                    @foreach($notes->take(3) as $note)
                                        <div class="col-md-6 mb-3">
                                            @include('search.partials.note_card', ['note' => $note])
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <!-- Team Members Section (All tab preview) -->
                            @if($users->isNotEmpty())
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <h5 class="font-weight-bold text-gray-900 mb-0">Team Members</h5>
                                    <a class="btn btn-sm btn-link text-primary font-weight-bold p-0 text-decoration-none" 
                                       href="#" onclick="$('#users-tab').tab('show'); return false;">View all ({{ $users->count() }})</a>
                                </div>
                                <div class="row mb-4">
                                    @foreach($users->take(4) as $u)
                                        <div class="col-sm-6 col-md-4 mb-3">
                                            @include('search.partials.user_card', ['user' => $u])
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                        </div>

                        <!-- PROJECTS TAB -->
                        <div class="tab-pane fade" id="projects-list" role="tabpanel" aria-labelledby="projects-tab">
                            <h5 class="font-weight-bold text-gray-900 mb-3">Matching Projects</h5>
                            <div class="row">
                                @forelse($projects as $project)
                                    <div class="col-md-6 mb-4">
                                        @include('search.partials.project_card', ['project' => $project])
                                    </div>
                                @empty
                                    <div class="col-12 py-4 text-center text-muted">No projects found.</div>
                                @endforelse
                            </div>
                        </div>

                        <!-- TASKS TAB -->
                        <div class="tab-pane fade" id="tasks-list" role="tabpanel" aria-labelledby="tasks-tab">
                            <h5 class="font-weight-bold text-gray-900 mb-3">Matching Tasks</h5>
                            @if($tasks->isNotEmpty())
                                <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; overflow: hidden;">
                                    <div class="list-group list-group-flush">
                                        @foreach($tasks as $task)
                                            @include('search.partials.task_row', ['task' => $task])
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <div class="py-4 text-center text-muted">No tasks found.</div>
                            @endif
                        </div>

                        <!-- NOTES TAB -->
                        <div class="tab-pane fade" id="notes-list" role="tabpanel" aria-labelledby="notes-tab">
                            <h5 class="font-weight-bold text-gray-900 mb-3">Matching Notes</h5>
                            <div class="row">
                                @forelse($notes as $note)
                                    <div class="col-md-6 mb-4">
                                        @include('search.partials.note_card', ['note' => $note])
                                    </div>
                                @empty
                                    <div class="col-12 py-4 text-center text-muted">No notes found.</div>
                                @endforelse
                            </div>
                        </div>

                        <!-- USERS TAB -->
                        <div class="tab-pane fade" id="users-list" role="tabpanel" aria-labelledby="users-tab">
                            <h5 class="font-weight-bold text-gray-900 mb-3">Matching Team Members</h5>
                            <div class="row">
                                @forelse($users as $u)
                                    <div class="col-sm-6 col-md-4 mb-4">
                                        @include('search.partials.user_card', ['user' => $u])
                                    </div>
                                @empty
                                    <div class="col-12 py-4 text-center text-muted">No team members found.</div>
                                @endforelse
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        @endif
    @endif
</div>
@endsection
