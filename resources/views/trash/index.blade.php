@extends('layouts.admin')

@section('title', 'Trash Bin')



@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800 font-weight-bold">Trash Bin</h1>
    <span class="text-muted text-xs">Review and restore recently deleted items</span>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
        <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

<!-- Nav Tabs -->
<div class="mb-4">
    <ul class="nav nav-tabs" id="trashFilterTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link active font-weight-bold text-xs text-uppercase" id="tasks-tab" data-toggle="tab" href="#tasks" role="tab" aria-controls="tasks" aria-selected="true">
                <i class="fas fa-tasks mr-1"></i> Tasks ({{ $tasks->count() }})
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link font-weight-bold text-xs text-uppercase" id="projects-tab" data-toggle="tab" href="#projects" role="tab" aria-controls="projects" aria-selected="false">
                <i class="fas fa-folder mr-1"></i> Projects ({{ $projects->count() }})
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link font-weight-bold text-xs text-uppercase" id="companies-tab" data-toggle="tab" href="#companies" role="tab" aria-controls="companies" aria-selected="false">
                <i class="fas fa-building mr-1"></i> Organizations ({{ $companies->count() }})
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link font-weight-bold text-xs text-uppercase" id="members-tab" data-toggle="tab" href="#members" role="tab" aria-controls="members" aria-selected="false">
                <i class="fas fa-users mr-1"></i> Members ({{ $trashedMembers->count() }})
            </a>
        </li>
    </ul>
</div>

<!-- Tab Content Card -->
<div class="card shadow mb-4" style="border-radius: 8px; border: none;">
    <div class="card-body p-4">
        <div class="tab-content" id="trashTabsContent">
            <!-- Tasks Tab -->
            <div class="tab-pane fade show active" id="tasks" role="tabpanel" aria-labelledby="tasks-tab">
                @if($tasks->isEmpty())
                    <div class="text-center py-5 px-3">
                        <div class="d-inline-flex align-items-center justify-center rounded-circle bg-light mb-3 shadow-xs" style="width: 64px; height: 64px;">
                            <i class="fas fa-trash-alt fa-2x text-primary"></i>
                        </div>
                        <h5 class="font-weight-bold mb-1 text-gray-800">No tasks in trash</h5>
                        <p class="text-muted text-sm mb-3" style="max-width: 420px; margin-left: auto; margin-right: auto;">
                            Deleted tasks will appear here and remain safely recoverable for 30 days before automated pruning.
                        </p>
                        <a href="{{ route('tasks.index') }}" class="btn btn-sm btn-primary shadow-sm">
                            <i class="fas fa-arrow-left mr-1"></i> Return to Tasks
                        </a>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Title</th>
                                    <th>Associated Project</th>
                                    <th>Assigned To</th>
                                    <th>Deleted At</th>
                                    <th class="text-center" style="width: 200px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tasks as $task)
                                    <tr>
                                        <td class="align-middle">
                                            <span class="font-weight-bold text-gray-800">{{ $task->title }}</span>
                                        </td>
                                        <td class="align-middle">
                                            @if($task->project)
                                                <span class="badge badge-primary shadow-sm" style="background-color: {{ $task->project->theme ?? '#4e73df' }};">
                                                    {{ $task->project->name }}
                                                </span>
                                            @else
                                                <span class="badge badge-light border text-muted shadow-sm">
                                                    <i class="fas fa-user mr-1"></i> Personal
                                                </span>
                                            @endif
                                        </td>
                                        <td class="align-middle text-xs text-gray-700">
                                            {{ $task->assignedUser->name ?? 'Unassigned' }}
                                        </td>
                                        <td class="align-middle text-xs text-muted">
                                            {{ $task->deleted_at->format('M d, Y H:i') }}
                                        </td>
                                        <td class="text-center align-middle">
                                            <div class="d-flex justify-content-center gap-2">
                                                <form action="{{ route('trash.tasks.restore', $task->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Restore">
                                                        <i class="fas fa-trash-restore mr-1"></i> Restore
                                                    </button>
                                                </form>
                                                <form action="{{ route('trash.tasks.forceDelete', $task->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to permanently delete this task? This action cannot be undone.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Permanently">
                                                        <i class="fas fa-times mr-1"></i> Permanent
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <!-- Projects Tab -->
            <div class="tab-pane fade" id="projects" role="tabpanel" aria-labelledby="projects-tab">
                @if($projects->isEmpty())
                    <div class="text-center py-5 px-3">
                        <div class="d-inline-flex align-items-center justify-center rounded-circle bg-light mb-3 shadow-xs" style="width: 64px; height: 64px;">
                            <i class="fas fa-folder-open fa-2x text-primary"></i>
                        </div>
                        <h5 class="font-weight-bold mb-1 text-gray-800">No projects in trash</h5>
                        <p class="text-muted text-sm mb-3" style="max-width: 420px; margin-left: auto; margin-right: auto;">
                            Deleted projects will appear here and remain safely recoverable for 30 days before automated pruning.
                        </p>
                        <a href="{{ route('projects.index') }}" class="btn btn-sm btn-primary shadow-sm">
                            <i class="fas fa-arrow-left mr-1"></i> Return to Projects
                        </a>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Organization / Space</th>
                                    <th>Deleted At</th>
                                    <th class="text-center" style="width: 200px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($projects as $project)
                                    <tr>
                                        <td class="align-middle">
                                            <div class="d-flex align-items-center">
                                                <div class="mr-2" style="width: 12px; height: 12px; border-radius: 50%; background-color: {{ $project->theme ?? '#4e73df' }};"></div>
                                                <span class="font-weight-bold text-gray-800">{{ $project->name }}</span>
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            @if($project->company)
                                                <span class="badge badge-info shadow-sm">
                                                    <i class="fas fa-building mr-1"></i> {{ $project->company->name }}
                                                </span>
                                            @else
                                                <span class="badge badge-light border text-muted shadow-sm">
                                                    <i class="fas fa-user mr-1"></i> Personal Space
                                                </span>
                                            @endif
                                        </td>
                                        <td class="align-middle text-xs text-muted">
                                            {{ $project->deleted_at->format('M d, Y H:i') }}
                                        </td>
                                        <td class="text-center align-middle">
                                            <div class="d-flex justify-content-center gap-2">
                                                <form action="{{ route('trash.projects.restore', $project->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Restore">
                                                        <i class="fas fa-trash-restore mr-1"></i> Restore
                                                    </button>
                                                </form>
                                                <form action="{{ route('trash.projects.forceDelete', $project->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to permanently delete this project? ALL tasks inside it will also be deleted forever!');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Permanently">
                                                        <i class="fas fa-times mr-1"></i> Permanent
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <!-- Companies Tab -->
            <div class="tab-pane fade" id="companies" role="tabpanel" aria-labelledby="companies-tab">
                @if($companies->isEmpty())
                    <div class="text-center py-5 px-3">
                        <div class="d-inline-flex align-items-center justify-center rounded-circle bg-light mb-3 shadow-xs" style="width: 64px; height: 64px;">
                            <i class="fas fa-sitemap fa-2x text-primary"></i>
                        </div>
                        <h5 class="font-weight-bold mb-1 text-gray-800">No organizations in trash</h5>
                        <p class="text-muted text-sm mb-3" style="max-width: 420px; margin-left: auto; margin-right: auto;">
                            Deleted organizations will appear here and can be restored anytime by an administrator.
                        </p>
                        <a href="{{ route('companies.index') }}" class="btn btn-sm btn-primary shadow-sm">
                            <i class="fas fa-arrow-left mr-1"></i> Return to Organizations
                        </a>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Organization Code</th>
                                    <th>Deleted At</th>
                                    <th class="text-center" style="width: 200px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($companies as $company)
                                    <tr>
                                        <td class="align-middle">
                                            <span class="font-weight-bold text-gray-800">{{ $company->name }}</span>
                                        </td>
                                        <td class="align-middle text-xs text-uppercase font-weight-bold text-primary">
                                            {{ $company->code }}
                                        </td>
                                        <td class="align-middle text-xs text-muted">
                                            {{ $company->deleted_at->format('M d, Y H:i') }}
                                        </td>
                                        <td class="text-center align-middle">
                                            <div class="d-flex justify-content-center gap-2">
                                                <form action="{{ route('trash.companies.restore', $company->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Restore">
                                                        <i class="fas fa-trash-restore mr-1"></i> Restore
                                                    </button>
                                                </form>
                                                <form action="{{ route('trash.companies.forceDelete', $company->id) }}" method="POST" class="d-inline" onsubmit="return confirm('WARNING: Are you sure you want to permanently delete this organization? All projects, tasks, and member history will be permanently wiped!');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Permanently">
                                                        <i class="fas fa-times mr-1"></i> Permanent
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
            
            <!-- Members Tab -->
            <div class="tab-pane fade" id="members" role="tabpanel" aria-labelledby="members-tab">
                @if($trashedMembers->isEmpty())
                    <div class="text-center py-5 px-3">
                        <div class="d-inline-flex align-items-center justify-center rounded-circle bg-light mb-3 shadow-xs" style="width: 64px; height: 64px;">
                            <i class="fas fa-users-slash fa-2x text-primary"></i>
                        </div>
                        <h5 class="font-weight-bold mb-1 text-gray-800">No removed members in trash</h5>
                        <p class="text-muted text-sm mb-3" style="max-width: 420px; margin-left: auto; margin-right: auto;">
                            Removed team members will appear here and can be restored back into the workspace.
                        </p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Organization</th>
                                    <th>Role</th>
                                    <th>Removed At</th>
                                    <th class="text-center" style="width: 200px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($trashedMembers as $membership)
                                    <tr>
                                        <td class="align-middle">
                                            <span class="font-weight-bold text-gray-800">{{ $membership->user->name ?? 'Deleted User' }}</span>
                                        </td>
                                        <td class="align-middle text-xs text-gray-700">
                                            {{ $membership->user->email ?? 'N/A' }}
                                        </td>
                                        <td class="align-middle">
                                            <span class="badge badge-info shadow-sm">
                                                <i class="fas fa-building mr-1"></i> {{ $membership->company->name ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="align-middle text-xs">
                                            @if($membership->role == 1)
                                                <span class="badge badge-danger">Admin</span>
                                            @else
                                                <span class="badge badge-secondary">Member</span>
                                            @endif
                                        </td>
                                        <td class="align-middle text-xs text-muted">
                                            {{ $membership->deleted_at->format('M d, Y H:i') }}
                                        </td>
                                        <td class="text-center align-middle">
                                            <div class="d-flex justify-content-center gap-2">
                                                <form action="{{ route('trash.members.restore', $membership->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Restore">
                                                        <i class="fas fa-trash-restore mr-1"></i> Restore
                                                    </button>
                                                </form>
                                                <form action="{{ route('trash.members.forceDelete', $membership->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to permanently delete this member relation? This action cannot be undone.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Permanently">
                                                        <i class="fas fa-times mr-1"></i> Permanent
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
