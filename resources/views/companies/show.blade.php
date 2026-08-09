@extends('layouts.admin')

@section('title', 'Organization - ' . $company->name)

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center">
        <a href="{{ route('companies.index') }}" class="btn btn-outline-primary btn-sm mr-3">
            <i class="fas fa-arrow-left"></i> Back to Organizations
        </a>
        <h1 class="h3 mb-0 text-gray-800">{{ $company->name }}</h1>
    </div>
    <div class="d-flex align-items-center">
        @if(session('current_company_id') == $company->id)
            <span class="badge badge-primary p-2 mr-3"><i class="fas fa-check-circle mr-1"></i> Active Workspace</span>
        @else
            <a href="{{ route('companies.switch', $company) }}" class="btn btn-primary btn-sm mr-3">
                <i class="fas fa-exchange-alt mr-1"></i> Switch to this Workspace
            </a>
        @endif

        @if(!$isAdmin)
            <form action="{{ route('companies.leave', $company) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to leave this organization? All your assigned tasks in this workspace will be unassigned.');">
                @csrf
                <button type="submit" class="btn btn-danger btn-sm">
                    <i class="fas fa-sign-out-alt mr-1"></i> Leave Organization
                </button>
            </form>
        @endif
    </div>
</div>

<div class="row">
    <!-- Left column: Info and Team Members -->
    <div class="col-lg-8 mb-4">
        <!-- Organization Overview -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Workspace Overview</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <span class="text-xs font-weight-bold text-gray-500 uppercase d-block">Organization Name</span>
                        <span class="font-weight-bold text-gray-800" style="font-size: 1.1rem;">{{ $company->name }}</span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <span class="text-xs font-weight-bold text-gray-500 uppercase d-block">Invitation Code</span>
                        <div class="d-flex align-items-center mt-1">
                            <span class="badge badge-light p-2 font-weight-bold text-monospace mr-2" style="font-size: 0.95rem; border: 1px solid #d1d3e2;">
                                {{ $company->code }}
                            </span>
                            <button class="btn btn-sm btn-outline-secondary copy-code" data-code="{{ $company->code }}" title="Copy Code">
                                <i class="far fa-copy"></i> Copy
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <span class="text-xs font-weight-bold text-gray-500 uppercase d-block">Created At</span>
                        <span class="text-gray-800 font-weight-bold">{{ $company->created_at->format('M d, Y') }}</span>
                    </div>
                    <div class="col-md-6">
                        <span class="text-xs font-weight-bold text-gray-500 uppercase d-block">Total Members</span>
                        <span class="text-gray-800 font-weight-bold">{{ $members->count() }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Team Members Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Team Members</h6>
                @if($isAdmin)
                    <button type="button" class="btn btn-primary btn-sm shadow-sm" data-toggle="modal" data-target="#inviteMemberModal">
                        <i class="fas fa-user-plus fa-sm text-white-50 mr-1"></i> Invite Member
                    </button>
                @endif
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Tasks</th>
                                <th>Joined</th>
                                <th>Last Login</th>
                                @if($isAdmin)
                                    <th class="text-center">Activity</th>
                                    <th>Actions</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($members as $member)
                                <tr>
                                    <td class="font-weight-bold align-middle">
                                        <div class="d-flex align-items-center">
                                            <div class="mr-2 rounded-circle bg-primary text-white d-flex align-items-center justify-content-center font-weight-bold" 
                                                 style="width: 32px; height: 32px; font-size: 0.85rem;">
                                                {{ strtoupper(substr($member->user->name ?? 'M', 0, 1)) }}
                                            </div>
                                            {{ $member->user->name ?? 'Unknown Member' }} 
                                        </div>
                                    </td>
                                    <td class="align-middle">{{ $member->user->email ?? 'N/A' }}</td>
                                    <td class="align-middle">
                                        @if($member->role == 1)
                                            <span class="badge badge-success"><i class="fas fa-user-shield mr-1"></i>Admin</span>
                                        @else
                                            <span class="badge badge-secondary"><i class="fas fa-user mr-1"></i>Member</span>
                                        @endif
                                    </td>
                                    <td class="align-middle">
                                        <div class="d-flex align-items-center" style="min-width: 150px;">
                                            <span class="mr-2 font-weight-bold text-gray-800" style="font-size: 0.85rem;">{{ $member->completed_tasks_count }}/{{ $member->total_tasks_count }}</span>
                                            @if($member->total_tasks_count > 0)
                                                @php
                                                    $percentage = round(($member->completed_tasks_count / $member->total_tasks_count) * 100);
                                                    $barClass = 'bg-danger';
                                                    if ($percentage >= 80) {
                                                        $barClass = 'bg-success';
                                                     } elseif ($percentage >= 50) {
                                                        $barClass = 'bg-primary';
                                                     } elseif ($percentage >= 20) {
                                                        $barClass = 'bg-info';
                                                     }
                                                @endphp
                                                <div class="progress progress-sm flex-grow-1 mr-2" style="height: 6px;">
                                                    <div class="progress-bar {{ $barClass }}" role="progressbar" 
                                                         style="width: {{ $percentage }}%" aria-valuenow="{{ $percentage }}" 
                                                         aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                                <span class="text-xs text-gray-600 font-weight-bold">{{ $percentage }}%</span>
                                            @else
                                                <div class="progress progress-sm flex-grow-1 mr-2" style="height: 6px; background-color: #eaecf4;">
                                                    <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                                                </div>
                                                <span class="text-xs text-gray-400 font-weight-bold">0%</span>
                                            @endif
                                        </div>
                                    </td>
    
                                    <td class="align-middle text-muted small">{{ $member->created_at->diffForHumans() }}</td>
                                    <td class="align-middle text-muted small">
                                        @if($member->user?->last_login_at)
                                            <span title="{{ $member->user->last_login_at->format('M d, Y h:i A') }}">
                                                <i class="far fa-clock mr-1 text-primary"></i>{{ $member->user->last_login_at->diffForHumans() }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">Never</span>
                                        @endif
                                    </td>
                                    <td class="align-middle text-center">
                                        @if($member->user)
                                            <button type="button" class="btn btn-sm btn-outline-info view-member-activity-btn shadow-sm" 
                                                    data-user-id="{{ $member->user->id }}" 
                                                    data-user-name="{{ e($member->user->name ?? 'Member') }}" 
                                                    data-url="{{ route('companies.members.activity', [$company, $member->user]) }}" 
                                                    title="View Member Activity">
                                                <i class="fas fa-history mr-1"></i> Activity
                                            </button>
                                        @else
                                            <span class="text-muted small">N/A</span>
                                        @endif
                                    </td>
                                    @if($isAdmin)
                                        <td class="align-middle text-center">
                                            @if($member->user_id !== auth()->id())
                                                <form action="{{ route('companies.members.destroy', [$company, $member->user]) }}" method="POST" class="d-inline remove-member-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="btn btn-danger btn-sm btn-circle" 
                                                            title="Remove Member"
                                                            onclick="return confirm('Are you sure you want to remove this member from the organization? All their assigned tasks in this workspace will be unassigned.');">
                                                        <i class="fas fa-user-minus"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-muted small">N/A</span>
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @if($isAdmin && count($pendingRequests) > 0)
            <!-- Pending Join Requests Card -->
            <div class="card shadow mb-4 border-left-warning">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning d-flex align-items-center">
                        <i class="fas fa-user-clock mr-2 fa-lg"></i>
                        Pending Join Requests
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Requested</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingRequests as $req)
                                    <tr>
                                        <td class="font-weight-bold align-middle">
                                            <div class="d-flex align-items-center">
                                                <div class="mr-2 rounded-circle bg-warning text-white d-flex align-items-center justify-content-center font-weight-bold" 
                                                     style="width: 32px; height: 32px; font-size: 0.85rem;">
                                                    {{ strtoupper(substr($req->user->name ?? 'P', 0, 1)) }}
                                                </div>
                                                {{ $req->user->name ?? 'Unknown User' }}
                                            </div>
                                        </td>
                                        <td class="align-middle">{{ $req->user->email ?? 'N/A' }}</td>
                                        <td class="align-middle text-muted small">{{ $req->created_at->diffForHumans() }}</td>
                                        <td class="align-middle text-center">
                                            <form action="{{ route('companies.reject-member-request', [$company, $req->user]) }}" method="POST" class="d-inline mr-2">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-danger btn-sm font-weight-bold px-3">
                                                    <i class="fas fa-times-circle mr-1"></i> Reject
                                                </button>
                                            </form>
                                            <form action="{{ route('companies.approve-member', [$company, $req->user]) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-sm font-weight-bold px-3">
                                                    <i class="fas fa-check-circle mr-1"></i> Approve
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Right column: Discussion Board -->
    <div class="col-lg-4">
        @include('partials.comments', [
            'comments' => $comments,
            'commentableType' => 'company',
            'commentableId' => $company->id
        ])
    </div>
</div>

<!-- Invite Member Modal -->
@if($isAdmin)
<div class="modal fade" id="inviteMemberModal" tabindex="-1" role="dialog" aria-labelledby="inviteMemberModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('companies.invite', $company) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title text-primary font-weight-bold" id="inviteMemberModalLabel">Invite Team Member</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label for="inviteEmail" class="text-xs font-weight-bold text-gray-600 uppercase">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="inviteEmail" class="form-control" placeholder="member@example.com" required>
                    </div>
                    <div class="form-group">
                        <label for="inviteMessage" class="text-xs font-weight-bold text-gray-600 uppercase">Personal Message (Optional)</label>
                        <textarea name="message" id="inviteMessage" class="form-control" rows="4" placeholder="Say hello and invite them to join your workspace..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane mr-1"></i> Send Invitation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
<!-- Member Activity Modal -->
<div class="modal fade" id="memberActivityModal" tabindex="-1" role="dialog" aria-labelledby="memberActivityModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-gradient-primary text-white">
                <h5 class="modal-title font-weight-bold" id="memberActivityModalLabel">
                    <i class="fas fa-history mr-2"></i><span id="activityModalUserName">Member Activity</span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4" style="max-height: 500px; overflow-y: auto;">
                <div id="activityModalLoading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading activity logs...</span>
                    </div>
                    <p class="text-muted small mt-2">Loading member activity...</p>
                </div>
                <div id="activityModalContent" class="d-none">
                    <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded mb-3 border">
                        <div>
                            <span class="text-xs text-uppercase font-weight-bold text-gray-600 d-block">Member Email</span>
                            <span id="activityModalUserEmail" class="font-weight-bold text-dark"></span>
                        </div>
                        <div class="text-right">
                            <span class="text-xs text-uppercase font-weight-bold text-gray-600 d-block">Last Login</span>
                            <span id="activityModalLastLogin" class="badge badge-info px-2 py-1"></span>
                        </div>
                    </div>
                    
                    <h6 class="font-weight-bold text-gray-800 mb-3"><i class="fas fa-list-alt mr-1"></i> Recent Activity Log</h6>
                    <div id="activityTimeline" class="timeline-history"></div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <a id="activityModalFullLogLink" href="#" class="btn btn-primary btn-sm shadow-sm">
                    <i class="fas fa-external-link-alt mr-1"></i> View Full Activity Log
                </a>
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('.copy-code').click(function(e) {
            e.preventDefault();
            var code = $(this).data('code');
            var $temp = $("<input>");
            $("body").append($temp);
            $temp.val(code).select();
            document.execCommand("copy");
            $temp.remove();

            var $btn = $(this);
            var originalText = $btn.html();
            $btn.html('<i class="fas fa-check"></i> Copied!').addClass('btn-success').removeClass('btn-outline-secondary');
            setTimeout(function() {
                $btn.html(originalText).addClass('btn-outline-secondary').removeClass('btn-success');
            }, 2000);
        });

        // View Member Activity Modal Handler
        $('.view-member-activity-btn').on('click', function() {
            var userId = $(this).data('user-id');
            var userName = $(this).data('user-name');
            var fetchUrl = $(this).data('url');

            $('#activityModalUserName').text(userName + "'s Activity");
            $('#activityModalLoading').removeClass('d-none');
            $('#activityModalContent').addClass('d-none');
            $('#memberActivityModal').modal('show');

            var fullLogUrl = "{{ route('activity-logs.index') }}?user_id=" + userId + "&scope=all";
            $('#activityModalFullLogLink').attr('href', fullLogUrl);

            $.ajax({
                url: fetchUrl,
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        $('#activityModalUserEmail').text(response.user.email);
                        $('#activityModalLastLogin').text(response.user.last_login_at);
                        if (response.user.last_login_at_formatted !== 'Never') {
                            $('#activityModalLastLogin').attr('title', response.user.last_login_at_formatted);
                        }

                        var timelineHtml = '';
                        if (response.activities && response.activities.length > 0) {
                            $.each(response.activities, function(index, act) {
                                var icon = 'fa-info-circle text-info';
                                if (act.event === 'login') icon = 'fa-sign-in-alt text-success';
                                else if (act.event === 'created' || act.event === 'created_via_api') icon = 'fa-plus-circle text-primary';
                                else if (act.event === 'updated') icon = 'fa-pen text-warning';
                                else if (act.event === 'deleted') icon = 'fa-trash text-danger';
                                else if (act.event === 'viewed' || act.event === 'viewed_dashboard') icon = 'fa-eye text-info';

                                timelineHtml += '<div class="mb-3 pl-3 py-2 bg-white shadow-xs rounded border-left-primary" style="border-left: 3px solid #4e73df !important;">';
                                timelineHtml += '<div class="d-flex align-items-center justify-content-between">';
                                timelineHtml += '<span class="font-weight-bold text-gray-800 text-sm"><i class="fas ' + icon + ' mr-1"></i> ' + escapeHtml(act.description) + '</span>';
                                timelineHtml += '<span class="text-xs text-gray-500 font-weight-bold ml-2">' + act.created_at_human + '</span>';
                                timelineHtml += '</div>';
                                if (act.subject_title) {
                                    timelineHtml += '<div class="text-xs text-muted mt-1">' + escapeHtml(act.subject_type) + ': ' + escapeHtml(act.subject_title) + '</div>';
                                }
                                if (act.properties && (act.properties.ip || act.properties.workspace)) {
                                    var obs = [];
                                    if (act.properties.workspace) obs.push('<i class="fas fa-building mr-1"></i>' + escapeHtml(act.properties.workspace));
                                    if (act.properties.ip) obs.push('<i class="fas fa-network-wired mr-1"></i>IP: ' + escapeHtml(act.properties.ip));
                                    timelineHtml += '<div class="text-xs text-muted mt-1">' + obs.join(' &bull; ') + '</div>';
                                }
                                timelineHtml += '</div>';
                            });
                        } else {
                            timelineHtml = '<div class="text-center py-4 text-muted"><i class="fas fa-history fa-2x mb-2 text-gray-300 d-block"></i><p class="mb-0">No recent activity logged for this member.</p></div>';
                        }

                        $('#activityTimeline').html(timelineHtml);
                        $('#activityModalLoading').addClass('d-none');
                        $('#activityModalContent').removeClass('d-none');
                    }
                },
                error: function() {
                    $('#activityTimeline').html('<div class="alert alert-danger">Failed to load member activity.</div>');
                    $('#activityModalLoading').addClass('d-none');
                    $('#activityModalContent').removeClass('d-none');
                }
            });
        });

        function escapeHtml(text) {
            if (!text) return '';
            return text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
        }
    });
</script>
@endpush
