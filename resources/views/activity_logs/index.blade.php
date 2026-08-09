@extends('layouts.admin')

@section('title', 'Activity Logs')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-history text-primary mr-2"></i>My Activity Logs</h1>
        @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
            <div class="btn-group" role="group">
                <a href="{{ route('activity-logs.index', array_merge(request()->except('scope'), ['scope' => 'mine'])) }}" class="btn btn-sm {{ ($scope ?? 'mine') === 'mine' ? 'btn-primary font-weight-bold' : 'btn-outline-primary' }}">
                    <i class="fas fa-user mr-1"></i>My Activity
                </a>
                <a href="{{ route('activity-logs.index', array_merge(request()->except('scope'), ['scope' => 'all'])) }}" class="btn btn-sm {{ ($scope ?? 'mine') === 'all' ? 'btn-primary font-weight-bold' : 'btn-outline-primary' }}">
                    <i class="fas fa-users-cog mr-1"></i>All System Activity
                </a>
            </div>
        @endif
    </div>

    <!-- Filter Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-white d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-filter mr-1"></i>Filter Logs</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('activity-logs.index') }}" class="form-inline">
                @if(request()->filled('scope'))
                    <input type="hidden" name="scope" value="{{ request('scope') }}">
                @endif
                <label class="mr-2 mb-2 font-weight-bold" for="event">Event:</label>
                <select name="event" id="event" class="form-control mr-sm-3 mb-2 custom-select">
                    <option value="">All Events</option>
                    <option value="login" {{ request('event') == 'login' ? 'selected' : '' }}>Login</option>
                    <option value="created" {{ request('event') == 'created' ? 'selected' : '' }}>Created</option>
                    <option value="updated" {{ request('event') == 'updated' ? 'selected' : '' }}>Updated</option>
                    <option value="deleted" {{ request('event') == 'deleted' ? 'selected' : '' }}>Deleted</option>
                </select>

                <label class="mr-2 mb-2 font-weight-bold" for="subject_type">Resource Type:</label>
                <select name="subject_type" id="subject_type" class="form-control mr-sm-3 mb-2 custom-select">
                    <option value="">All Resources</option>
                    <option value="user" {{ request('subject_type') == 'user' ? 'selected' : '' }}>User Account</option>
                    <option value="task" {{ request('subject_type') == 'task' ? 'selected' : '' }}>Task</option>
                    <option value="project" {{ request('subject_type') == 'project' ? 'selected' : '' }}>Project</option>
                </select>

                <button type="submit" class="btn btn-primary mb-2 mr-2"><i class="fas fa-search mr-1"></i>Filter</button>
                <a href="{{ route('activity-logs.index', ['scope' => request('scope', 'mine')]) }}" class="btn btn-secondary mb-2"><i class="fas fa-undo mr-1"></i>Reset</a>
            </form>
        </div>
    </div>

    <!-- Activity Log Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-white">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-list mr-1"></i>Audit Trail</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-items-center mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>ID</th>
                            <th>User / Causer</th>
                            <th>Event</th>
                            <th>Resource</th>
                            <th>Changes (Before / After)</th>
                            <th>Date & Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activities as $activity)
                            <tr>
                                <td><span class="font-weight-bold">#{{ $activity->id }}</span></td>
                                <td>
                                    @if($activity->causer)
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle mr-2 bg-primary text-white d-flex align-items-center justify-content-center rounded-circle" style="width: 28px; height: 28px; font-size: 0.75rem;">
                                                {{ strtoupper(substr($activity->causer->name, 0, 1)) }}
                                            </div>
                                            <span>{{ $activity->causer->name }}</span>
                                        </div>
                                    @else
                                        <span class="text-muted"><i class="fas fa-robot mr-1"></i>System</span>
                                    @endif
                                </td>
                                <td>
                                    @if($activity->event == 'login')
                                        <span class="badge badge-warning px-2 py-1 text-dark"><i class="fas fa-key mr-1"></i>Login</span>
                                    @elseif($activity->event == 'created')
                                        <span class="badge badge-success px-2 py-1"><i class="fas fa-plus-circle mr-1"></i>Created</span>
                                    @elseif($activity->event == 'updated')
                                        <span class="badge badge-info px-2 py-1"><i class="fas fa-edit mr-1"></i>Updated</span>
                                    @elseif($activity->event == 'deleted')
                                        <span class="badge badge-danger px-2 py-1"><i class="fas fa-trash-alt mr-1"></i>Deleted</span>
                                    @else
                                        <span class="badge badge-secondary px-2 py-1">{{ ucfirst($activity->event ?? 'action') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-light border text-dark font-weight-bold">
                                        {{ ucfirst($activity->subject_type ?? 'resource') }} #{{ $activity->subject_id }}
                                    </span>
                                </td>
                                <td style="max-width: 350px;">
                                    @php
                                        $old = $activity->properties['old'] ?? [];
                                        $attributes = $activity->properties['attributes'] ?? [];
                                    @endphp

                                    @if(!empty($attributes))
                                        <div class="small">
                                            @foreach($attributes as $key => $newVal)
                                                @if(is_scalar($newVal))
                                                    <div class="text-truncate">
                                                        <strong class="text-dark">{{ $key }}:</strong>
                                                        @if(isset($old[$key]))
                                                            <span class="text-danger strike mr-1" style="text-decoration: line-through;">{{ Str::limit((string)$old[$key], 30) }}</span>
                                                        @endif
                                                        <span class="text-success font-weight-bold">{{ Str::limit((string)$newVal, 30) }}</span>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    @elseif($activity->description)
                                        <span class="small font-weight-bold text-dark">{{ $activity->description }}</span>
                                    @else
                                        <span class="text-muted small">No attribute diff</span>
                                    @endif
                                </td>
                                <td class="small text-muted">
                                    <i class="far fa-clock mr-1"></i>{{ $activity->created_at ? $activity->created_at->diffForHumans() : 'N/A' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="fas fa-info-circle mr-1"></i>No activity logs recorded yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($activities->hasPages())
            <div class="card-footer py-3 bg-white">
                {{ $activities->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
