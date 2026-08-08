@extends('layouts.admin')

@section('title', 'Tasks')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800 font-weight-bold">Tasks</h1>
    <div class="d-flex flex-column align-items-center gap-2">
        <button class="btn btn-primary shadow-sm" id="btnShowInlineAdd">
            <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Add Task
        </button>
        <small class="text-muted"> Press (Alt + t) to add task</small>
    </div>
</div>

<div class="row">
    <!-- Total Tasks Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Tasks</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalCount }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Tasks Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending Tasks</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $pendingCount }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Completed Tasks Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Completed Tasks</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $completedCount }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-double fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Overdue Tasks Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-danger shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Overdue Tasks</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $overdueCount }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- All-in-One Tasks Section (Filters, Table, Inline Form, Modal & Scripts) -->
@include('partials.tasks_table', [
    'tasks' => $tasks,
    'companyUsers' => $companyUsers,
    'projects' => $projects,
    'showFilters' => true,
    'showProjectColumn' => true,
    'cardTitle' => 'All Tasks'
])
@endsection
