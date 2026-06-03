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
    @if(session('current_company_id') == $company->id)
        <span class="badge badge-primary p-2"><i class="fas fa-check-circle mr-1"></i> Active Workspace</span>
    @else
        <a href="{{ route('companies.switch', $company) }}" class="btn btn-primary btn-sm">
            <i class="fas fa-exchange-alt mr-1"></i> Switch to this Workspace
        </a>
    @endif
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
                                @if($isAdmin)
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
    });
</script>
@endpush
