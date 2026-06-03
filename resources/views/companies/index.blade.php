@extends('layouts.admin')

@section('title', 'Organizations')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Organization Management</h1>
</div>

<div class="row">
@if($companies->isEmpty())
    <div class="col-lg-12">
        <div class="row justify-content-center">
            <!-- Join Organization Card -->
            <div class="col-md-5 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Join an Organization</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('companies.join') }}">
                            @csrf
                            <div class="form-group">
                                <label for="joinCode" class="text-xs font-weight-bold text-gray-600 uppercase">Organization Code</label>
                                <input type="text" name="code" id="joinCode" class="form-control" placeholder="Enter Code (e.g. ABCD)" value="{{ request('code') }}" required>
                                @error('code')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-sign-in-alt mr-1"></i>Join Organization
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Create Organization Card -->
            <div class="col-md-5 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Create an Organization</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('companies.store') }}">
                            @csrf
                            <div class="form-group">
                                <label for="createName" class="text-xs font-weight-bold text-gray-600 uppercase">Organization Name</label>
                                <input type="text" name="name" id="createName" class="form-control" placeholder="Enter Organization Name" required>
                                @error('name')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-success btn-block">
                                <i class="fas fa-plus mr-1"></i>Create Organization
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@else
    <!-- Left column: list of organizations -->
    <div class="col-lg-8 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Your Organizations</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="companiesTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Invitation Code</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($companies as $cu)
                                @if($cu->company)
                                    <tr>
                                        <td class="font-weight-bold align-middle">
                                            <a href="{{ route('companies.show', $cu->company) }}" class="text-primary">
                                                {{ $cu->company->name }}
                                            </a>
                                        </td>
                                        <td class="align-middle">
                                            <div class="d-flex align-items-center">
                                                <span class="badge badge-light p-2 font-weight-bold text-monospace mr-2" style="font-size: 0.9rem;">
                                                    {{ $cu->company->code }}
                                                </span>
                                                <button class="btn btn-sm btn-link text-primary p-0 copy-code" data-code="{{ $cu->company->code }}" title="Copy Code">
                                                    <i class="far fa-copy text-secondary"></i>
                                                </button>
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            @if($cu->role == 1)
                                                <span class="badge badge-success"><i class="fas fa-user-shield mr-1"></i>Admin</span>
                                            @else
                                                <span class="badge badge-secondary"><i class="fas fa-user mr-1"></i>Member</span>
                                            @endif
                                        </td>
                                        <td class="align-middle">
                                            @if($cu->company_id == session('current_company_id'))
                                                <span class="badge badge-primary p-2"><i class="fas fa-check-circle mr-1"></i>Active</span>
                                            @else
                                                <a href="{{ route('companies.switch', $cu->company) }}" class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-exchange-alt mr-1"></i>Switch
                                                </a>
                                            @endif
                                        </td>
                                        <td class="align-middle">
                                            @if($cu->role == 1)
                                                <button type="button" 
                                                        class="btn btn-info btn-sm btn-circle edit-company-btn" 
                                                        data-toggle="modal" 
                                                        data-target="#editCompanyModal" 
                                                        data-id="{{ $cu->company->id }}" 
                                                        data-name="{{ $cu->company->name }}"
                                                        data-url="{{ route('companies.update', $cu->company) }}"
                                                        title="Edit Name">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                
                                                <form action="{{ route('companies.destroy', $cu->company) }}" method="POST" class="d-inline delete-company-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="btn btn-danger btn-sm btn-circle" 
                                                            title="Delete Organization"
                                                            onclick="return confirm('Warning: Deleting this organization will permanently remove all of its projects and tasks. This action cannot be undone. Are you sure you want to proceed?');">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-muted small">No management actions</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Right column: Create and Join forms -->
    <div class="col-lg-4">
        <!-- Join Organization Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Join an Organization</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('companies.join') }}">
                    @csrf
                    <div class="form-group">
                        <label for="joinCode" class="text-xs font-weight-bold text-gray-600 uppercase">Organization Code</label>
                        <input type="text" name="code" id="joinCode" class="form-control" placeholder="Enter Code (e.g. ABCD)" value="{{ request('code') }}" required>
                        @error('code')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-sign-in-alt mr-1"></i>Join Organization
                    </button>
                </form>
            </div>
        </div>

        <!-- Create Organization Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Create an Organization</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('companies.store') }}">
                    @csrf
                    <div class="form-group">
                        <label for="createName" class="text-xs font-weight-bold text-gray-600 uppercase">Organization Name</label>
                        <input type="text" name="name" id="createName" class="form-control" placeholder="Enter Organization Name" required>
                        @error('name')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-success btn-block">
                        <i class="fas fa-plus mr-1"></i>Create Organization
                    </button>
                </form>
            </div>
        </div>
    </div>
@endif
</div>

<!-- Edit Organization Modal -->
<div class="modal fade" id="editCompanyModal" tabindex="-1" role="dialog" aria-labelledby="editCompanyModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="editCompanyForm" method="POST" action="">
                @csrf
                @method('PATCH')
                <div class="modal-header">
                    <h5 class="modal-title text-primary font-weight-bold" id="editCompanyModalLabel">Edit Organization Name</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="editCompanyName" class="text-xs font-weight-bold text-gray-600 uppercase">Organization Name</label>
                        <input type="text" name="name" id="editCompanyName" class="form-control" required>
                        @error('name')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Handle edit organization button click
        $('.edit-company-btn').click(function() {
            var url = $(this).data('url');
            var name = $(this).data('name');
            
            $('#editCompanyForm').attr('action', url);
            $('#editCompanyName').val(name);
        });

        // Copy invitation code function
        $('.copy-code').click(function(e) {
            e.preventDefault();
            var code = $(this).data('code');
            
            // Create temporary element to copy from
            var tempInput = document.createElement("input");
            tempInput.value = code;
            document.body.appendChild(tempInput);
            tempInput.select();
            document.execCommand("copy");
            document.body.removeChild(tempInput);

            // Change icon temporarily to indicate success
            var icon = $(this).find('i');
            icon.removeClass('far fa-copy text-secondary').addClass('fas fa-check text-success');
            setTimeout(function() {
                icon.removeClass('fas fa-check text-success').addClass('far fa-copy text-secondary');
            }, 2000);
        });
    });
</script>
@endpush
