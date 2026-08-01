@extends('layouts.admin')

@section('title', 'Feature Access Control - Super Admin')

@section('content')
<!-- Page Header -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800 font-weight-bold">
            <i class="fas fa-user-shield text-warning mr-2"></i>Feature Access & Role Management
        </h1>
        <p class="text-muted text-xs mb-0">Super Admin Portal: Dynamically assign or revoke feature flags and system roles for any user in real-time.</p>
    </div>
</div>

<div class="alert alert-info text-xs border-left-info shadow-sm mb-3">
    <i class="fas fa-info-circle mr-2 text-info"></i>
    <strong>Super Admin Access Control:</strong> Super Admin role can only be assigned via console command: <code>php artisan user:super-admin {user}</code>. Only one Super Admin exists in the database at a time.
</div>

<div class="card shadow mb-4 border-left-warning">
    <div class="card-header py-3 bg-white d-flex flex-column flex-md-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-warning mb-2 mb-md-0">
            <i class="fas fa-users-cog mr-2"></i>User Roles & Spatie Permissions
        </h6>
        
        <!-- Search Form -->
        <form action="{{ route('admin.features.index') }}" method="GET" class="form-inline">
            <div class="input-group input-group-sm">
                <input type="text" name="q" class="form-control bg-light border-0 small" placeholder="Search user name or email..." value="{{ $search ?? '' }}">
                <div class="input-group-append">
                    <button class="btn btn-warning text-white" type="submit">
                        <i class="fas fa-search fa-sm"></i>
                    </button>
                    @if($search)
                        <a href="{{ route('admin.features.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times fa-sm"></i>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-xs text-uppercase text-gray-700">
                    <tr>
                        <th style="width: 60px;" class="text-center">ID</th>
                        <th>User Profile</th>
                        <th>System Role</th>
                        <th class="text-center">Beta Access (<code>access-beta</code>)</th>
                        <th class="text-center">Issue Access (<code>access-issues</code>)</th>
                        <th>Registered Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr id="userRow-{{ $user->id }}">
                            <td class="text-center font-weight-bold text-gray-600">#{{ $user->id }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($user->profile_image)
                                        <img src="{{ $user->profile_image }}" class="rounded-circle mr-3" style="width: 38px; height: 38px; object-fit: cover;">
                                    @else
                                        <div class="rounded-circle bg-gradient-primary text-white mr-3 d-flex align-items-center justify-content-center font-weight-bold text-sm" style="width: 38px; height: 38px;">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="font-weight-bold text-gray-800">{{ $user->name }}</div>
                                        <div class="text-muted text-xs">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($user->isSuperAdmin())
                                    <span class="badge badge-warning px-3 py-2 font-weight-bold"><i class="fas fa-crown mr-1"></i> Super Admin</span>
                                @else
                                    <select class="form-control form-control-sm role-select font-weight-bold" data-user-id="{{ $user->id }}" style="max-width: 130px;">
                                        <option value="1" {{ $user->role === 1 ? 'selected' : '' }}>⭐ Admin</option>
                                        <option value="2" {{ $user->role === 2 ? 'selected' : '' }}>👤 User</option>
                                    </select>
                                @endif
                            </td>
                            <td class="text-center align-middle">
                                <button type="button" 
                                        class="btn btn-sm btn-toggle-feature {{ $user->beta_access ? 'btn-success' : 'btn-outline-secondary' }} px-3 font-weight-bold" 
                                        data-user-id="{{ $user->id }}" 
                                        data-feature="access-beta">
                                    <i class="fas {{ $user->beta_access ? 'fa-check-circle' : 'fa-times-circle' }} mr-1"></i>
                                    <span class="feature-status-text">{{ $user->beta_access ? 'Active' : 'Inactive' }}</span>
                                </button>
                            </td>
                            <td class="text-center align-middle">
                                <button type="button" 
                                        class="btn btn-sm btn-toggle-feature {{ $user->issues_access ? 'btn-info' : 'btn-outline-secondary' }} px-3 font-weight-bold" 
                                        data-user-id="{{ $user->id }}" 
                                        data-feature="access-issues">
                                    <i class="fas {{ $user->issues_access ? 'fa-check-circle' : 'fa-times-circle' }} mr-1"></i>
                                    <span class="feature-status-text">{{ $user->issues_access ? 'Active' : 'Inactive' }}</span>
                                </button>
                            </td>
                            <td class="text-muted text-xs align-middle">
                                {{ $user->created_at ? $user->created_at->format('M d, Y') : 'N/A' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="fas fa-users-slash fa-3x mb-3 text-gray-300"></i>
                                <div>No users found matching your query.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($users->hasPages())
        <div class="card-footer bg-white py-2">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div class="text-xs text-muted mb-1 mb-md-0">
                    Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() }} users
                </div>
                <div class="pagination-sm mb-0">
                    {{ $users->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // 1. Toggle Feature Flag via AJAX
    $('.btn-toggle-feature').on('click', function() {
        const $btn = $(this);
        const userId = $btn.data('user-id');
        const feature = $btn.data('feature');
        
        $btn.prop('disabled', true);

        $.ajax({
            url: `/admin/features/${userId}/toggle-feature`,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            data: { feature: feature },
            success: function(res) {
                $btn.prop('disabled', false);
                if (res.active) {
                    $btn.removeClass('btn-outline-secondary').addClass('btn-success');
                    $btn.find('i').removeClass('fa-times-circle').addClass('fa-check-circle');
                    $btn.find('.feature-status-text').text('Active');
                } else {
                    $btn.removeClass('btn-success').addClass('btn-outline-secondary');
                    $btn.find('i').removeClass('fa-check-circle').addClass('fa-times-circle');
                    $btn.find('.feature-status-text').text('Inactive');
                }
                showToast(res.message, 'success');
            },
            error: function(xhr) {
                $btn.prop('disabled', false);
                const msg = xhr.responseJSON?.message || 'Failed to update feature flag.';
                showToast(msg, 'error');
            }
        });
    });

    // 2. Change Role via AJAX Dropdown
    $('.role-select').on('change', function() {
        const $select = $(this);
        const userId = $select.data('user-id');
        const newRole = $select.val();
        const prevRole = $select.data('prev-val') || $select.val();

        $.ajax({
            url: `/admin/features/${userId}/toggle-role`,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            data: { role: newRole },
            success: function(res) {
                $select.data('prev-val', newRole);
                showToast(res.message, 'success');
                // If promoted to Super Admin, feature is automatically active
                if (parseInt(newRole) === 0) {
                    const $featureBtn = $select.closest('tr').find('.btn-toggle-feature');
                    $featureBtn.removeClass('btn-outline-secondary').addClass('btn-success');
                    $featureBtn.find('i').removeClass('fa-times-circle').addClass('fa-check-circle');
                    $featureBtn.find('.feature-status-text').text('Active');
                }
            },
            error: function(xhr) {
                $select.val(prevRole);
                const msg = xhr.responseJSON?.message || 'Failed to update user role.';
                showToast(msg, 'error');
            }
        });
    });
});
</script>
@endpush
