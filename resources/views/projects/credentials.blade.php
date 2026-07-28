@extends('layouts.admin')

@section('title', $project->name . ' - Credentials')

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

<!-- Navigation Tabs Bar -->
<div>
    <ul class="nav nav-tabs mb-4" id="projectShowTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link font-weight-bold" href="{{ route('projects.show', $project) }}">
                <i class="fas fa-tasks mr-2"></i>Tasks
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link font-weight-bold" href="{{ route('projects.show', $project) }}#notes-content">
                <i class="fas fa-sticky-note mr-2"></i>Notes
            </a>
        </li>
        @feature('access-beta')
            <li class="nav-item">
                <a class="nav-link active font-weight-bold" href="{{ route('projects.credentials', $project) }}">
                    <i class="fas fa-key mr-2"></i>Credentials
                </a>
            </li>
        @endfeature
    </ul>
</div>

<!-- Main Content Area -->
<div class="row">
    <div class="col-lg-9">
        <div class="card shadow mb-4" style="border-left: 4px solid {{ $project->theme }};">
            <div class="card-header py-3 d-flex align-items-center justify-content-between bg-white">
                <h6 class="m-0 font-weight-bold text-primary mb-0">
                    <i class="fas fa-key mr-2"></i>Project Environment Keys & API Secret Vault
                </h6>
                <button class="btn btn-primary btn-sm shadow-sm font-weight-bold" data-toggle="modal" data-target="#addCredentialModal">
                    <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Add Credential
                </button>
            </div>
            <div class="card-body">
                <!-- Filter Pills -->
                <div class="d-flex align-items-center flex-wrap mb-3">
                    <span class="small font-weight-bold text-gray-600 mr-2">Filter:</span>
                    <button class="btn btn-xs btn-primary active cred-filter-btn mr-1 shadow-sm px-3" data-env="all">All (<span id="credCountAll">{{ $credentials->count() }}</span>)</button>
                    <button class="btn btn-xs btn-outline-danger cred-filter-btn mr-1 px-3" data-env="production">Production</button>
                    <button class="btn btn-xs btn-outline-warning cred-filter-btn mr-1 px-3 text-dark" data-env="staging">Staging</button>
                    <button class="btn btn-xs btn-outline-info cred-filter-btn mr-1 px-3" data-env="development">Development</button>
                    <button class="btn btn-xs btn-outline-success cred-filter-btn mr-1 px-3" data-env="api_key">API Keys</button>
                </div>

                <!-- Credentials Table -->
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle mb-0" id="credentialsTable">
                        <thead class="thead-light">
                            <tr class="text-xs text-uppercase text-gray-700">
                                <th style="width: 140px;">Environment</th>
                                <th>Service / Title</th>
                                <th>Host / Identifier</th>
                                <th>Password / Secret Value</th>
                                <th style="width: 90px;" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="credentialsListBody">
                            @forelse($credentials as $credential)
                                <tr data-env="{{ $credential->type_slug }}" data-id="{{ $credential->id }}">
                                    <td class="align-middle">
                                        @if($credential->type === 0)
                                            <span class="badge badge-danger font-weight-bold p-2"><i class="fas fa-server mr-1"></i>Production</span>
                                        @elseif($credential->type === 1)
                                            <span class="badge badge-warning font-weight-bold p-2 text-dark"><i class="fas fa-vial mr-1"></i>Staging</span>
                                        @elseif($credential->type === 2)
                                            <span class="badge badge-success font-weight-bold p-2"><i class="fas fa-key mr-1"></i>API Key</span>
                                        @else
                                            <span class="badge badge-info font-weight-bold p-2"><i class="fas fa-code mr-1"></i>Development</span>
                                        @endif
                                    </td>
                                    <td class="align-middle font-weight-bold text-gray-800">{{ $credential->name }}</td>
                                    <td class="align-middle"><code>{{ $credential->host_or_identifier }}</code></td>
                                    <td class="align-middle">
                                        <div class="input-group input-group-sm" style="max-width: 260px;">
                                            <input type="password" class="form-control form-control-sm cred-val font-monospace" value="{{ $credential->password_or_secret }}" readonly>
                                            <div class="input-group-append">
                                                <button class="btn btn-outline-secondary btn-toggle-mask" type="button" title="Show/Hide"><i class="far fa-eye"></i></button>
                                                <button class="btn btn-outline-primary btn-copy-cred" type="button" title="Copy Secret"><i class="far fa-copy"></i></button>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="align-middle text-center">
                                        <button class="btn btn-sm btn-outline-danger btn-delete-cred" data-id="{{ $credential->id }}" title="Delete"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                            @empty
                                <tr id="emptyCredRow">
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="fas fa-key fa-2x mb-2 text-gray-400 d-block"></i>
                                        No credentials stored for this project yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar Info & Comments Column -->
    <div class="col-lg-3">
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-light">
                <h6 class="m-0 font-weight-bold text-gray-800">
                    <i class="fas fa-shield-alt mr-2 text-primary"></i>Security Guidelines
                </h6>
            </div>
            <div class="card-body text-xs text-gray-600">
                <p class="mb-2"><i class="fas fa-check-circle text-success mr-1"></i> Use environment variables for API tokens & credentials.</p>
                <p class="mb-2"><i class="fas fa-check-circle text-success mr-1"></i> Secrets are automatically encrypted in storage.</p>
                <p class="mb-0"><i class="fas fa-info-circle text-info mr-1"></i> Route: <code>/projects/{{ $project->id }}/credentials</code></p>
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

{{-- Add Credential Modal --}}
<div class="modal fade" id="addCredentialModal" tabindex="-1" role="dialog" aria-labelledby="addCredentialModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold text-primary" id="addCredentialModalLabel">
                    <i class="fas fa-key mr-2"></i>Add Project Credential
                </h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <form id="addCredentialForm" action="{{ route('projects.credentials.store', $project) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="credEnv" class="font-weight-bold text-gray-700">Environment <span class="text-danger">*</span></label>
                        <select class="form-control" id="credEnv" name="type" required>
                            <option value="production">Production</option>
                            <option value="staging">Staging</option>
                            <option value="development">Development</option>
                            <option value="api_key">API Key</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="credTitle" class="font-weight-bold text-gray-700">Service / Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="credTitle" name="name" placeholder="e.g. AWS S3 Access Key" required>
                    </div>
                    <div class="form-group">
                        <label for="credHost" class="font-weight-bold text-gray-700">Host / Username / Identifier</label>
                        <input type="text" class="form-control" id="credHost" name="host_or_identifier" placeholder="e.g. s3-user@aws.com">
                    </div>
                    <div class="form-group">
                        <label for="credSecret" class="font-weight-bold text-gray-700">Password / Secret Value <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="credSecret" name="password_or_secret" placeholder="Enter secret password or token" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Credential</button>
                </div>
            </form>
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

        // 1. Password Mask Toggle
        $(document).on('click', '.btn-toggle-mask', function() {
            var $input = $(this).closest('.input-group').find('.cred-val');
            var $icon = $(this).find('i');
            if ($input.attr('type') === 'password') {
                $input.attr('type', 'text');
                $icon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                $input.attr('type', 'password');
                $icon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });

        // 2. One-Click Copy to Clipboard
        $(document).on('click', '.btn-copy-cred', function() {
            var val = $(this).closest('.input-group').find('.cred-val').val();
            var $btn = $(this);
            navigator.clipboard.writeText(val).then(function() {
                $btn.html('<i class="fas fa-check text-success"></i>');
                setTimeout(function() {
                    $btn.html('<i class="far fa-copy"></i>');
                }, 1500);
            });
        });

        // 3. Delete Credential Row
        $(document).on('click', '.btn-delete-cred', function() {
            var $row = $(this).closest('tr');
            var id = $(this).data('id');

            if (!id) {
                $row.fadeOut(200, function() {
                    $(this).remove();
                    updateCredCounts();
                });
                return;
            }

            if (confirm('Are you sure you want to delete this credential?')) {
                $.ajax({
                    url: "/projects/{{ $project->id }}/credentials/" + id,
                    method: "DELETE",
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function() {
                        $row.fadeOut(200, function() {
                            $(this).remove();
                            updateCredCounts();
                        });
                    },
                    error: function() {
                        alert('Error deleting credential.');
                    }
                });
            }
        });

        // 4. Add Credential Form Submission via AJAX
        $('#addCredentialForm').on('submit', function(e) {
            e.preventDefault();
            var $form = $(this);
            var $submitBtn = $form.find('button[type="submit"]');
            $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');

            var env = $('#credEnv').val();
            var title = $('#credTitle').val();
            var host = $('#credHost').val() || 'N/A';
            var secret = $('#credSecret').val();

            $.ajax({
                url: "{{ route('projects.credentials.store', $project) }}",
                method: "POST",
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                data: {
                    type: env,
                    name: title,
                    host_or_identifier: host,
                    password_or_secret: secret
                },
                success: function(response) {
                    $submitBtn.prop('disabled', false).text('Save Credential');
                    $('#emptyCredRow').remove();

                    var envBadges = {
                        'production': '<span class="badge badge-danger font-weight-bold p-2"><i class="fas fa-server mr-1"></i>Production</span>',
                        'staging': '<span class="badge badge-warning font-weight-bold p-2 text-dark"><i class="fas fa-vial mr-1"></i>Staging</span>',
                        'development': '<span class="badge badge-info font-weight-bold p-2"><i class="fas fa-code mr-1"></i>Development</span>',
                        'api_key': '<span class="badge badge-success font-weight-bold p-2"><i class="fas fa-key mr-1"></i>API Key</span>'
                    };

                    var cred = response.credential;
                    var typeSlug = cred.type_slug || env;

                    var newRow = `
                        <tr data-env="${typeSlug}" data-id="${cred.id}">
                            <td class="align-middle">${envBadges[typeSlug] || envBadges[env] || env}</td>
                            <td class="align-middle font-weight-bold text-gray-800">${escapeHtml(cred.name)}</td>
                            <td class="align-middle"><code>${escapeHtml(cred.host_or_identifier)}</code></td>
                            <td class="align-middle">
                                <div class="input-group input-group-sm" style="max-width: 260px;">
                                    <input type="password" class="form-control form-control-sm cred-val font-monospace" value="${escapeHtml(cred.password_or_secret)}" readonly>
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary btn-toggle-mask" type="button" title="Show/Hide"><i class="far fa-eye"></i></button>
                                        <button class="btn btn-outline-primary btn-copy-cred" type="button" title="Copy Secret"><i class="far fa-copy"></i></button>
                                    </div>
                                </div>
                            </td>
                            <td class="align-middle text-center">
                                <button class="btn btn-sm btn-outline-danger btn-delete-cred" data-id="${cred.id}" title="Delete"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    `;

                    $('#credentialsListBody').prepend(newRow);
                    $('#addCredentialModal').modal('hide');
                    $form[0].reset();
                    updateCredCounts();
                },
                error: function(xhr) {
                    $submitBtn.prop('disabled', false).text('Save Credential');
                    alert(xhr.responseJSON?.message || 'Error saving credential.');
                }
            });
        });

        // 5. Environment Filter Buttons
        $('.cred-filter-btn').on('click', function() {
            var env = $(this).data('env');
            $('.cred-filter-btn').removeClass('active btn-primary').addClass('btn-outline-secondary');
            $(this).addClass('active btn-primary').removeClass('btn-outline-secondary');

            if (env === 'all') {
                $('#credentialsListBody tr').show();
            } else {
                $('#credentialsListBody tr').each(function() {
                    if ($(this).attr('id') === 'emptyCredRow') return;
                    if ($(this).data('env') === env) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            }
        });

        function updateCredCounts() {
            var count = $('#credentialsListBody tr').not('#emptyCredRow').length;
            $('#credCountAll').text(count);
            if (count === 0 && $('#emptyCredRow').length === 0) {
                $('#credentialsListBody').append(`
                    <tr id="emptyCredRow">
                        <td colspan="5" class="text-center py-4 text-muted">
                            <i class="fas fa-key fa-2x mb-2 text-gray-400 d-block"></i>
                            No credentials stored for this project yet.
                        </td>
                    </tr>
                `);
            }
        }
    });
</script>
@endpush
