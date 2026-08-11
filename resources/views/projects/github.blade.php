@extends('layouts.admin')

@section('title', $project->name . ' - GitHub Repositories')

@push('styles')
<style>
    #projectShowTabs .nav-link.active {
        color: {{ $project->theme }} !important;
        border-bottom: 3px solid {{ $project->theme }} !important;
        background: transparent;
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
                <form action="{{ route('projects.destroy', $project) }}" method="POST" class="d-inline ml-1" onclick="return confirm('Are you sure you want to delete this project?');">
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
@include('partials.project_tabs')

<!-- Main Content Area -->
<div class="row">
    <div class="col-lg-9">
        <div class="card shadow mb-4" style="border-left: 4px solid {{ $project->theme }};">
            <div class="card-header py-3 d-flex align-items-center justify-content-between bg-white">
                <h6 class="m-0 font-weight-bold text-primary mb-0">
                    <i class="fab fa-github mr-2"></i>Connected GitHub Repositories
                </h6>
                <button class="btn btn-primary btn-sm shadow-sm font-weight-bold" data-toggle="modal" data-target="#addGithubRepoModal">
                    <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Connect GitHub Repo
                </button>
            </div>
            <div class="card-body">
                <p class="text-sm text-gray-600 mb-4">
                    Store and link GitHub repositories for this project. Stored repositories can be configured with access tokens and webhook secrets for future sync features.
                </p>

                <!-- GitHub Repos Table -->
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle mb-0" id="githubRepoTable">
                        <thead class="thead-light">
                            <tr class="text-xs text-uppercase text-gray-700">
                                <th>Repository (Owner / Name)</th>
                                <th>Connected By</th>
                                <th>Auto Sync Issues</th>
                                <th style="width: 100px;" class="text-center">Status</th>
                                <th style="width: 130px;" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="githubRepoListBody">
                            @forelse($githubRepos as $repo)
                                <tr data-id="{{ $repo->id }}">
                                    <td class="align-middle font-weight-bold text-gray-800">
                                        <a href="https://github.com/{{ $repo->repo_owner }}/{{ $repo->repo_name }}" target="_blank" rel="noopener noreferrer" class="text-primary">
                                            <i class="fab fa-github mr-1"></i> {{ $repo->full_repo_name }}
                                        </a>
                                    </td>
                                    <td class="align-middle">
                                        @if($repo->user)
                                            <span class="badge badge-light border text-gray-800 p-2">
                                                <i class="fas fa-user mr-1"></i> {{ $repo->user->name }}
                                            </span>
                                        @else
                                            <span class="badge badge-light border text-gray-500 p-2">N/A</span>
                                        @endif
                                    </td>
                                    <td class="align-middle">
                                        @if($repo->auto_sync_issues)
                                            <span class="badge badge-info px-2 py-1">
                                                <i class="fas fa-sync-alt mr-1"></i> Enabled
                                            </span>
                                        @else
                                            <span class="badge badge-secondary px-2 py-1">Disabled</span>
                                        @endif
                                    </td>
                                    <td class="align-middle text-center">
                                        @if($repo->is_active)
                                            <span class="badge badge-success px-2 py-1">Active</span>
                                        @else
                                            <span class="badge badge-secondary px-2 py-1">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="align-middle text-center" style="white-space: nowrap;">
                                        <div class="d-inline-flex align-items-center" style="gap: 4px;">
                                            <x-edit-button class="btn-edit-repo"
                                                           data-id="{{ $repo->id }}"
                                                           data-owner="{{ $repo->repo_owner }}"
                                                           data-name="{{ $repo->repo_name }}"
                                                           data-autosync="{{ $repo->auto_sync_issues ? '1' : '0' }}"
                                                           data-active="{{ $repo->is_active ? '1' : '0' }}"
                                                           title="Edit Repo Connection" />
                                            <x-delete-button class="btn-delete-repo" data-id="{{ $repo->id }}" title="Disconnect Repo" />
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr id="emptyRepoRow">
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="fab fa-github fa-2x mb-2 text-gray-400 d-block"></i>
                                        No GitHub repositories connected to this project yet.
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
        <div class="card shadow mb-4 border-left-dark">
            <div class="card-header py-3 bg-dark text-white d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold">
                    <i class="fab fa-github mr-2"></i>GitHub Integration Info
                </h6>
            </div>
            <div class="card-body text-xs text-gray-700">
                <p class="mb-3">
                    Store repository credentials securely for project integrations.
                </p>
                <div class="alert alert-secondary py-2 px-2 text-xs mb-3">
                    <i class="fas fa-lock text-dark mr-1"></i> <strong>Encrypted Credentials:</strong><br>
                    Access tokens and webhook secrets are encrypted at rest using AES-256 before saving in database storage.
                </div>
                <ul class="pl-3 mb-0 text-muted" style="line-height: 1.6;">
                    <li><strong>Repository Owner</strong>: Organization or user account (e.g. <code>laravel</code>)</li>
                    <li><strong>Repository Name</strong>: Repository slug (e.g. <code>framework</code>)</li>
                    <li><strong>Access Token</strong>: Personal Access Token with repo scope (Optional)</li>
                </ul>
            </div>
        </div>
    </div>
</div>

@include('partials.discussion_drawer', ['project' => $project, 'comments' => $comments])

<!-- Connect GitHub Repo Modal -->
<div class="modal fade" id="addGithubRepoModal" tabindex="-1" role="dialog" aria-labelledby="addGithubRepoModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title font-weight-bold" id="addGithubRepoModalLabel">
                    <i class="fab fa-github mr-2"></i>Connect GitHub Repository
                </h5>
                <button class="close text-white" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <form id="addGithubRepoForm" action="{{ route('projects.github-repos.store', $project) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="repoOwner" class="font-weight-bold text-gray-700">Repository Owner / Org <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="repoOwner" name="repo_owner" placeholder="e.g. octocat or company-org" required>
                    </div>

                    <div class="form-group">
                        <label for="repoName" class="font-weight-bold text-gray-700">Repository Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="repoName" name="repo_name" placeholder="e.g. Hello-World" required>
                    </div>

                    <div class="form-group">
                        <label for="accessToken" class="font-weight-bold text-gray-700">GitHub Access Token (Optional)</label>
                        <input type="password" class="form-control" id="accessToken" name="access_token" placeholder="ghp_xxxxxxxxxxxxxxxxxxxx">
                        <small class="form-text text-muted">Encrypted securely. Required for private repositories.</small>
                    </div>

                    <div class="form-group">
                        <label for="webhookSecret" class="font-weight-bold text-gray-700">Webhook Secret Key (Optional)</label>
                        <input type="password" class="form-control" id="webhookSecret" name="webhook_secret" placeholder="Secret key used for GitHub webhook verification">
                    </div>

                    <div class="form-group mb-2">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="autoSyncIssues" name="auto_sync_issues" value="1" checked>
                            <label class="custom-control-label font-weight-bold text-gray-700" for="autoSyncIssues" style="cursor: pointer;">Enable Auto-Sync Issues</label>
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="isActive" name="is_active" value="1" checked>
                            <label class="custom-control-label font-weight-bold text-gray-700" for="isActive" style="cursor: pointer;">Active Connection</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark"><i class="fab fa-github mr-1"></i> Save Repository</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit GitHub Repo Modal -->
<div class="modal fade" id="editGithubRepoModal" tabindex="-1" role="dialog" aria-labelledby="editGithubRepoModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title font-weight-bold" id="editGithubRepoModalLabel">
                    <i class="fas fa-edit mr-2"></i>Edit GitHub Repository Connection
                </h5>
                <button class="close text-white" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <form id="editGithubRepoForm" method="POST">
                @csrf
                @method('PATCH')
                <input type="hidden" id="editRepoId" name="repo_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="editRepoOwner" class="font-weight-bold text-gray-700">Repository Owner / Org <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editRepoOwner" name="repo_owner" required>
                    </div>

                    <div class="form-group">
                        <label for="editRepoName" class="font-weight-bold text-gray-700">Repository Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editRepoName" name="repo_name" required>
                    </div>

                    <div class="form-group">
                        <label for="editAccessToken" class="font-weight-bold text-gray-700">GitHub Access Token (Leave blank to keep existing)</label>
                        <input type="password" class="form-control" id="editAccessToken" name="access_token" placeholder="Leave blank to keep existing token">
                    </div>

                    <div class="form-group">
                        <label for="editWebhookSecret" class="font-weight-bold text-gray-700">Webhook Secret Key (Leave blank to keep existing)</label>
                        <input type="password" class="form-control" id="editWebhookSecret" name="webhook_secret" placeholder="Leave blank to keep existing secret">
                    </div>

                    <div class="form-group mb-2">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="editAutoSyncIssues" name="auto_sync_issues" value="1">
                            <label class="custom-control-label font-weight-bold text-gray-700" for="editAutoSyncIssues" style="cursor: pointer;">Enable Auto-Sync Issues</label>
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="editIsActive" name="is_active" value="1">
                            <label class="custom-control-label font-weight-bold text-gray-700" for="editIsActive" style="cursor: pointer;">Active Connection</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark">Update Repository</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Edit Repo Modal Populate
        $(document).on('click', '.btn-edit-repo', function() {
            var id = $(this).data('id');
            var owner = $(this).data('owner');
            var name = $(this).data('name');
            var autosync = $(this).data('autosync') == '1';
            var active = $(this).data('active') == '1';

            $('#editRepoId').val(id);
            $('#editRepoOwner').val(owner);
            $('#editRepoName').val(name);
            $('#editAutoSyncIssues').prop('checked', autosync);
            $('#editIsActive').prop('checked', active);

            $('#editGithubRepoForm').attr('action', '/projects/{{ $project->id }}/github-repos/' + id);
            $('#editGithubRepoModal').modal('show');
        });

        // Delete Repo
        $(document).on('click', '.btn-delete-repo', function() {
            var $row = $(this).closest('tr');
            var id = $(this).data('id');

            if (confirm('Are you sure you want to disconnect this GitHub repository?')) {
                $.ajax({
                    url: '/projects/{{ $project->id }}/github-repos/' + id,
                    method: "DELETE",
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function() {
                        $row.fadeOut(200, function() {
                            $(this).remove();
                            if ($('#githubRepoListBody tr').length === 0) {
                                $('#githubRepoListBody').append(`
                                    <tr id="emptyRepoRow">
                                        <td colspan="5" class="text-center py-4 text-muted">
                                            <i class="fab fa-github fa-2x mb-2 text-gray-400 d-block"></i>
                                            No GitHub repositories connected to this project yet.
                                        </td>
                                    </tr>
                                `);
                            }
                        });
                    },
                    error: function() {
                        alert('Error disconnecting repository.');
                    }
                });
            }
        });
    });
</script>
@endpush
