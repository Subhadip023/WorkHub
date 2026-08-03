@extends('layouts.admin')

@section('title', $project->name . ' - External API')

@push('styles')
<style>
    #projectShowTabs .nav-link.active {
        color: {{ $project->theme }} !important;
        border-bottom: 3px solid {{ $project->theme }} !important;
        background: transparent;
    }
    .cred-val {
        letter-spacing: 1px;
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
                    <button type="submit" class="btn btn-sm btn-danger shadow-sm" onclick="return confirm('Are you sure you want to delete this project?');">
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

<!-- Navigation Tabs Bar -->
<div>
    <ul class="nav nav-tabs mb-4" id="projectShowTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link font-weight-bold" href="{{ route('projects.show', $project) }}">
                <i class="fas fa-tasks mr-2"></i>Tasks
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link font-weight-bold" href="{{ route('projects.notes', $project) }}">
                <i class="fas fa-sticky-note mr-2"></i>Notes
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link font-weight-bold" href="{{ route('projects.credentials', $project) }}">
                <i class="fas fa-key mr-2"></i>Credentials
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link active font-weight-bold" href="{{ route('projects.external-api', $project) }}">
                <i class="fas fa-plug mr-2"></i>External API
            </a>
        </li>
    </ul>
</div>

<!-- Main Content Area -->
<div class="row">
    <div class="col-lg-9">
        <div class="card shadow mb-4" style="border-left: 4px solid {{ $project->theme }};">
            <div class="card-header py-3 d-flex align-items-center justify-content-between bg-white">
                <h6 class="m-0 font-weight-bold text-primary mb-0">
                    <i class="fas fa-plug mr-2"></i>External Task API Credentials & Member Assignment
                </h6>
                <button class="btn btn-primary btn-sm shadow-sm font-weight-bold" data-toggle="modal" data-target="#generateApiModal">
                    <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Generate API Key
                </button>
            </div>
            <div class="card-body">
                <p class="text-sm text-gray-600 mb-4">
                    Generate public API keys and secret keys to automatically ingest tasks into this project via external webhooks or integrations. Select a member to auto-assign incoming API tasks to.
                </p>

                <!-- External API Table -->
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle mb-0" id="externalApiTable">
                        <thead class="thead-light">
                            <tr class="text-xs text-uppercase text-gray-700">
                                <th>Name / Service</th>
                                <th>Public API Key</th>
                                <th>Private Secret (Encrypted)</th>
                                <th>Assigned Member</th>
                                <th>Default Settings (Status, Priority, Type)</th>
                                <th style="width: 80px;" class="text-center">Status</th>
                                <th style="width: 100px;" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="apiListBody">
                            @forelse($externalApis as $api)
                                <tr data-id="{{ $api->id }}">
                                    <td class="align-middle font-weight-bold text-gray-800">
                                        <i class="fas fa-code-branch text-primary mr-1"></i> {{ $api->name }}
                                    </td>
                                    <td class="align-middle">
                                        <div class="input-group input-group-sm" style="max-width: 220px;">
                                            <input type="text" class="form-control form-control-sm font-monospace cred-val" value="{{ $api->api_key }}" readonly>
                                            <div class="input-group-append">
                                                <button class="btn btn-outline-primary btn-copy-text" type="button" title="Copy Public Key"><i class="far fa-copy"></i></button>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <span class="badge badge-light border text-monospace text-gray-600 px-2 py-1">
                                            <i class="fas fa-lock text-muted mr-1"></i> HMAC Secret Hidden
                                        </span>
                                    </td>
                                    <td class="align-middle">
                                        @if($api->assignedUser)
                                            <span class="badge badge-info p-2">
                                                <i class="fas fa-user-tag mr-1"></i> {{ $api->assignedUser->name }}
                                            </span>
                                        @else
                                            <span class="badge badge-light border text-gray-600 p-2">
                                                <i class="fas fa-user-clock mr-1"></i> Creator (Auto)
                                            </span>
                                        @endif
                                    </td>
                                    <td class="align-middle">
                                        @php
                                            $stMap = [1 => ['To Do', 'badge-secondary'], 2 => ['In Progress', 'badge-primary'], 3 => ['Completed', 'badge-success'], 4 => ['On Hold', 'badge-warning']];
                                            $prMap = [1 => ['Low', 'badge-light border text-gray-800'], 2 => ['Medium', 'badge-info'], 3 => ['High', 'badge-warning'], 4 => ['Urgent', 'badge-danger']];
                                            $tpMap = [1 => ['Task', 'badge-light border text-gray-800'], 2 => ['Bug', 'badge-danger'], 3 => ['Feature', 'badge-success'], 4 => ['Improvement', 'badge-info']];
                                            $st = $stMap[$api->default_status ?? 1];
                                            $pr = $prMap[$api->default_priority ?? 2];
                                            $tp = $tpMap[$api->default_type ?? 1];
                                        @endphp
                                        <span class="badge {{ $st[1] }} px-2 py-1 mr-1">{{ $st[0] }}</span>
                                        <span class="badge {{ $pr[1] }} px-2 py-1 mr-1">{{ $pr[0] }}</span>
                                        <span class="badge {{ $tp[1] }} px-2 py-1">{{ $tp[0] }}</span>
                                    </td>
                                    <td class="align-middle text-center">
                                        @if($api->is_active)
                                            <span class="badge badge-success px-2 py-1">Active</span>
                                        @else
                                            <span class="badge badge-secondary px-2 py-1">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="align-middle text-center">
                                        <button class="btn btn-sm btn-outline-warning btn-regenerate-secret mr-1" data-id="{{ $api->id }}" title="Regenerate Secret Key">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger btn-delete-api" data-id="{{ $api->id }}" title="Revoke API Key">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr id="emptyApiRow">
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        <i class="fas fa-plug fa-2x mb-2 text-gray-400 d-block"></i>
                                        No External Task API keys generated for this project yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar Info Column -->
    <div class="col-lg-3">
        <div class="card shadow mb-4 border-left-primary">
            <div class="card-header py-3 bg-gradient-primary text-white d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-terminal mr-2"></i>API Integration Spec
                </h6>
                <span class="badge badge-light text-primary font-weight-bold">v1.0</span>
            </div>
            <div class="card-body text-xs text-gray-700">
                <div class="mb-3">
                    <span class="font-weight-bold text-gray-800 d-block mb-1"><i class="fas fa-link text-primary mr-1"></i> Endpoint URL:</span>
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control font-monospace cred-val" value="{{ route('api.tasks.store') }}" readonly>
                        <div class="input-group-append">
                            <button class="btn btn-outline-primary btn-copy-text" type="button" title="Copy Endpoint"><i class="far fa-copy"></i></button>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <span class="font-weight-bold text-gray-800 d-block mb-1"><i class="fas fa-key text-primary mr-1"></i> Required Headers:</span>
                    <div class="bg-dark text-light p-2 rounded font-monospace" style="font-size: 11px; line-height: 1.5;">
                        <span class="text-info">Content-Type:</span> application/json<br>
                        <span class="text-info">X-Api-Key:</span> &lt;YOUR_PUBLIC_KEY&gt;<br>
                        <span class="text-info">X-Api-Signature:</span> &lt;HMAC_SHA256_HEX&gt;
                    </div>
                </div>

                <div class="alert alert-info py-2 px-2 border-left-info text-xs mb-3">
                    <i class="fas fa-shield-alt text-info mr-1"></i> <strong>HMAC Signature:</strong><br>
                    Compute <code class="text-dark font-weight-bold">hash_hmac('sha256', raw_json_body, secret_key)</code> and send in <code class="text-dark font-weight-bold">X-Api-Signature</code> header.
                </div>

                <div class="mb-3">
                    <span class="font-weight-bold text-gray-800 d-block mb-1"><i class="fas fa-code text-primary mr-1"></i> Sample Request Body:</span>
                    <pre class="p-2 bg-light border rounded text-xs mb-0 text-dark font-monospace" style="font-size: 11px;">{
  "title": "Bug in Checkout Flow",
  "description": "Payment button failing",
  "type": 2,
  "priority": 4,
  "status": 1
}</pre>
                </div>

                <div class="mb-3">
                    <span class="font-weight-bold text-gray-800 d-block mb-1"><i class="fas fa-list-ol text-primary mr-1"></i> Parameter Options:</span>
                    <ul class="pl-3 mb-0 text-muted" style="line-height: 1.6;">
                        <li><strong>type</strong>: 1=Task, 2=Bug, 3=Feature, 4=Improvement</li>
                        <li><strong>priority</strong>: 1=Low, 2=Medium, 3=High, 4=Urgent</li>
                        <li><strong>status</strong>: 1=To Do, 2=In Progress, 3=Completed, 4=On Hold</li>
                    </ul>
                </div>

                <hr class="my-2">
                <p class="mb-0 text-muted" style="font-size: 10px;">
                    <i class="fas fa-info-circle text-primary mr-1"></i> Omitting status, priority, or type automatically uses the defaults pre-configured on your API key.
                </p>
            </div>
        </div>

        @if(isset($comments))
            @include('partials.comments', [
                'comments' => $comments,
                'commentableType' => 'project',
                'commentableId' => $project->id
            ])
        @endif
    </div>
</div>

<!-- Generate API Key Modal -->
<div class="modal fade" id="generateApiModal" tabindex="-1" role="dialog" aria-labelledby="generateApiModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold text-primary" id="generateApiModalLabel">
                    <i class="fas fa-key mr-2"></i>Generate External Task API Key
                </h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <form id="generateApiForm" action="{{ route('projects.external-api.store', $project) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="apiName" class="font-weight-bold text-gray-700">API Name / Label <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="apiName" name="name" placeholder="e.g. GitHub Webhook / Zapier Integration" required>
                    </div>

                    <div class="form-group">
                        <label for="assignedUserId" class="font-weight-bold text-gray-700">Assign Tasks To Member</label>
                        <select class="form-control" id="assignedUserId" name="assigned_user_id">
                            <option value="">-- Select Member (Default / Creator) --</option>
                            @foreach($companyUsers as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">
                            Tasks created via this API key will be automatically assigned to the selected member.
                        </small>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="defaultStatus" class="font-weight-bold text-gray-700">Default Status</label>
                                <select class="form-control" id="defaultStatus" name="default_status">
                                    <option value="1" selected>To Do</option>
                                    <option value="2">In Progress</option>
                                    <option value="3">Completed</option>
                                    <option value="4">On Hold</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="defaultPriority" class="font-weight-bold text-gray-700">Default Priority</label>
                                <select class="form-control" id="defaultPriority" name="default_priority">
                                    <option value="1">Low</option>
                                    <option value="2" selected>Medium</option>
                                    <option value="3">High</option>
                                    <option value="4">Urgent</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="defaultType" class="font-weight-bold text-gray-700">Default Type</label>
                                <select class="form-control" id="defaultType" name="default_type">
                                    <option value="1" selected>Task</option>
                                    <option value="2">Bug</option>
                                    <option value="3">Feature</option>
                                    <option value="4">Improvement</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Generate API Key</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Show Secret Once -->
<div class="modal fade" id="showSecretModal" tabindex="-1" role="dialog" aria-labelledby="showSecretModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-left-warning shadow-lg">
            <div class="modal-header bg-warning text-white py-3">
                <h5 class="modal-title font-weight-bold" id="showSecretModalLabel">
                    <i class="fas fa-key mr-2"></i>HMAC API Secret Key
                </h5>
            </div>
            <div class="modal-body text-gray-800">
                <div class="alert alert-warning border-left-warning shadow-sm mb-3">
                    <i class="fas fa-exclamation-triangle mr-1"></i> <strong>Important Security Warning:</strong><br>
                    Please copy this secret key now. For security reasons, <strong>it will not be displayed again</strong> in the system.
                </div>
                <div class="form-group mb-0">
                    <label class="font-weight-bold text-gray-700">HMAC Secret Key:</label>
                    <div class="input-group">
                        <input type="text" id="modalRawSecret" class="form-control font-monospace font-weight-bold text-primary cred-val" readonly>
                        <div class="input-group-append">
                            <button class="btn btn-primary btn-copy-text" type="button" title="Copy Secret"><i class="far fa-copy mr-1"></i> Copy Secret</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal">I Have Copied & Saved This Key</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        function escapeHtml(text) {
            if (!text) return '';
            return text.toString()
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        const stMap = { 1: ['To Do', 'badge-secondary'], 2: ['In Progress', 'badge-primary'], 3: ['Completed', 'badge-success'], 4: ['On Hold', 'badge-warning'] };
        const prMap = { 1: ['Low', 'badge-light border text-gray-800'], 2: ['Medium', 'badge-info'], 3: ['High', 'badge-warning'], 4: ['Urgent', 'badge-danger'] };
        const tpMap = { 1: ['Task', 'badge-light border text-gray-800'], 2: ['Bug', 'badge-danger'], 3: ['Feature', 'badge-success'], 4: ['Improvement', 'badge-info'] };

        // Copy text
        $(document).on('click', '.btn-copy-text', function() {
            var val = $(this).closest('.input-group').find('.cred-val').val();
            var $btn = $(this);
            navigator.clipboard.writeText(val).then(function() {
                $btn.html('<i class="fas fa-check text-success"></i> Copied!');
                setTimeout(function() {
                    $btn.html('<i class="far fa-copy"></i> Copy');
                }, 2000);
            });
        });

        // Regenerate Secret Key
        $(document).on('click', '.btn-regenerate-secret', function() {
            var id = $(this).data('id');
            var $btn = $(this);

            if (confirm('Are you sure you want to regenerate the HMAC Secret Key? Existing integrations using the old secret key will stop working immediately.')) {
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
                $.ajax({
                    url: "/external-api/" + id + "/regenerate-secret",
                    method: "POST",
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        $btn.prop('disabled', false).html('<i class="fas fa-sync-alt"></i>');
                        $('#modalRawSecret').val(response.raw_secret);
                        $('#showSecretModal').modal('show');
                    },
                    error: function() {
                        $btn.prop('disabled', false).html('<i class="fas fa-sync-alt"></i>');
                        alert('Error regenerating secret key.');
                    }
                });
            }
        });

        // Delete API Key
        $(document).on('click', '.btn-delete-api', function() {
            var $row = $(this).closest('tr');
            var id = $(this).data('id');

            if (confirm('Are you sure you want to revoke this External API key?')) {
                $.ajax({
                    url: "/external-api/" + id,
                    method: "DELETE",
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function() {
                        $row.fadeOut(200, function() {
                            $(this).remove();
                            if ($('#apiListBody tr').length === 0) {
                                $('#apiListBody').append(`
                                    <tr id="emptyApiRow">
                                        <td colspan="7" class="text-center py-4 text-muted">
                                            <i class="fas fa-plug fa-2x mb-2 text-gray-400 d-block"></i>
                                            No External Task API keys generated for this project yet.
                                        </td>
                                    </tr>
                                `);
                            }
                        });
                    },
                    error: function() {
                        alert('Error revoking API key.');
                    }
                });
            }
        });

        // Submit form via AJAX
        $('#generateApiForm').on('submit', function(e) {
            e.preventDefault();
            var $form = $(this);
            var $submitBtn = $form.find('button[type="submit"]');
            $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Generating...');

            $.ajax({
                url: "{{ route('projects.external-api.store', $project) }}",
                method: "POST",
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                data: $form.serialize(),
                success: function(response) {
                    $submitBtn.prop('disabled', false).text('Generate API Key');
                    $('#emptyApiRow').remove();

                    var api = response.api;
                    var secret = response.raw_secret;
                    var assignedName = api.assigned_user ? api.assigned_user.name : 'Creator (Auto)';
                    var st = stMap[api.default_status || 1];
                    var pr = prMap[api.default_priority || 2];
                    var tp = tpMap[api.default_type || 1];

                    var newRow = `
                        <tr data-id="${api.id}">
                            <td class="align-middle font-weight-bold text-gray-800">
                                <i class="fas fa-code-branch text-primary mr-1"></i> ${escapeHtml(api.name)}
                            </td>
                            <td class="align-middle">
                                <div class="input-group input-group-sm" style="max-width: 220px;">
                                    <input type="text" class="form-control form-control-sm font-monospace cred-val" value="${escapeHtml(api.api_key)}" readonly>
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-primary btn-copy-text" type="button" title="Copy Public Key"><i class="far fa-copy"></i></button>
                                    </div>
                                </div>
                            </td>
                            <td class="align-middle">
                                <span class="badge badge-light border text-monospace text-gray-600 px-2 py-1">
                                    <i class="fas fa-lock text-muted mr-1"></i> HMAC Secret Hidden
                                </span>
                            </td>
                            <td class="align-middle">
                                <span class="badge ${api.assigned_user ? 'badge-info' : 'badge-light border text-gray-600'} p-2">
                                    <i class="fas ${api.assigned_user ? 'fa-user-tag' : 'fa-user-clock'} mr-1"></i> ${escapeHtml(assignedName)}
                                </span>
                            </td>
                            <td class="align-middle">
                                <span class="badge ${st[1]} px-2 py-1 mr-1">${st[0]}</span>
                                <span class="badge ${pr[1]} px-2 py-1 mr-1">${pr[0]}</span>
                                <span class="badge ${tp[1]} px-2 py-1">${tp[0]}</span>
                            </td>
                            <td class="align-middle text-center">
                                <span class="badge badge-success px-2 py-1">Active</span>
                            </td>
                            <td class="align-middle text-center">
                                <button class="btn btn-sm btn-outline-warning btn-regenerate-secret mr-1" data-id="${api.id}" title="Regenerate Secret Key">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger btn-delete-api" data-id="${api.id}" title="Revoke API Key">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;

                    $('#apiListBody').prepend(newRow);
                    $('#generateApiModal').modal('hide');
                    $form[0].reset();

                    // Show raw secret modal once
                    if (secret) {
                        $('#modalRawSecret').val(secret);
                        $('#showSecretModal').modal('show');
                    }
                },
                error: function(xhr) {
                    $submitBtn.prop('disabled', false).text('Generate API Key');
                    alert(xhr.responseJSON?.message || 'Error generating API key.');
                }
            });
        });
    });
</script>
@endpush
