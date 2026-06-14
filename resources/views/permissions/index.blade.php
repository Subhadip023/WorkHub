@extends('layouts.admin')

@section('title', 'Permissions Guide')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800 font-weight-bold">Permissions Guide</h1>
    <span class="text-muted text-xs">Overview of roles and access privileges across WorkHub</span>
</div>

<div class="row">
    <!-- Left Column: Core Concepts -->
    <div class="col-lg-8">
        <!-- Scope Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-white">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-cubes mr-2"></i> Task Context & Scope</h6>
            </div>
            <div class="card-body">
                <p class="text-gray-700">
                    In WorkHub, permissions are determined dynamically based on the **context** of the task (whether it is a personal task or belongs to an organization) and your **role** within that organization.
                </p>

                <div class="row mt-4">
                    <!-- Personal Task -->
                    <div class="col-md-6 mb-4">
                        <div class="card border-left-primary h-100 py-2 bg-light">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Personal Scope
                                        </div>
                                        <div class="h6 mb-2 font-weight-bold text-gray-800">Personal Tasks & Projects</div>
                                        <p class="text-xs text-muted mb-0">
                                            Tasks created in your personal space, or projects not associated with any organization.
                                        </p>
                                        <div class="mt-2 text-xs font-weight-bold text-dark">
                                            <i class="fas fa-lock text-primary mr-1"></i> Access restricted to Creator & Assignee.
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-user-shield fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Organization Task -->
                    <div class="col-md-6 mb-4">
                        <div class="card border-left-success h-100 py-2 bg-light">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Organization Scope
                                        </div>
                                        <div class="h6 mb-2 font-weight-bold text-gray-800">Company Projects & Tasks</div>
                                        <p class="text-xs text-muted mb-0">
                                            Tasks and projects belonging to a Company/Organization.
                                        </p>
                                        <div class="mt-2 text-xs font-weight-bold text-dark">
                                            <i class="fas fa-users text-success mr-1"></i> Access controlled by Company Roles.
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-sitemap fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Matrix Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-white">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-table mr-2"></i> Permissions Matrix</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover text-sm">
                        <thead class="bg-gradient-primary text-white font-weight-bold">
                            <tr>
                                <th>Action / Capability</th>
                                <th class="text-center">Personal Tasks</th>
                                <th class="text-center">Company Admin</th>
                                <th class="text-center">Company Member</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="font-weight-bold text-gray-800">View Task Details & History</td>
                                <td class="text-center text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i> Yes</td>
                                <td class="text-center text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i> Yes</td>
                                <td class="text-center text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i> Yes</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold text-gray-800">Update Metadata (Status, Priority, Due Date)</td>
                                <td class="text-center text-primary text-xs">Creator & Assignee Only</td>
                                <td class="text-center text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i> Yes</td>
                                <td class="text-center text-info text-xs"><i class="fas fa-info-circle mr-1"></i> Assigned Tasks Only</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold text-gray-800">Edit Task Description (Quill Editor)</td>
                                <td class="text-center text-primary text-xs">Creator & Assignee Only</td>
                                <td class="text-center text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i> Yes</td>
                                <td class="text-center text-info text-xs"><i class="fas fa-info-circle mr-1"></i> Assigned Tasks Only</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold text-gray-800">Upload / Delete Image Attachments</td>
                                <td class="text-center text-primary text-xs">Creator & Assignee Only</td>
                                <td class="text-center text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i> Yes</td>
                                <td class="text-center text-info text-xs"><i class="fas fa-info-circle mr-1"></i> Assigned Tasks Only</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold text-gray-800">Add Notes</td>
                                <td class="text-center text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i> Yes</td>
                                <td class="text-center text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i> Yes</td>
                                <td class="text-center text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i> Yes</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold text-gray-800">Edit / Delete Notes</td>
                                <td class="text-center text-primary text-xs">Note Owner Only</td>
                                <td class="text-center text-primary text-xs">Note Owner Only</td>
                                <td class="text-center text-primary text-xs">Note Owner Only</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold text-gray-800">Post Comments & Discussions</td>
                                <td class="text-center text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i> Yes</td>
                                <td class="text-center text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i> Yes</td>
                                <td class="text-center text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i> Yes</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Role Profiles -->
    <div class="col-lg-4">
        <!-- Admin Profile -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-success text-white">
                <h6 class="m-0 font-weight-bold"><i class="fas fa-user-cog mr-2"></i> Company Admin Role</h6>
            </div>
            <div class="card-body">
                <span class="badge badge-success mb-3 p-2">Full Write Access</span>
                <p class="text-xs text-gray-700 mb-3" style="line-height: 1.5;">
                    Admins are the managers of the organization. They have administrative privileges to manage all tasks within their company projects, regardless of who the task is assigned to.
                </p>
                <div class="text-xs font-weight-bold text-success">
                    <i class="fas fa-check-circle mr-1"></i> Manage all company tasks<br>
                    <i class="fas fa-check-circle mr-1"></i> Delete task images/attachments<br>
                    <i class="fas fa-check-circle mr-1"></i> Modify due dates & status
                </div>
            </div>
        </div>

        <!-- Member Profile -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-info text-white">
                <h6 class="m-0 font-weight-bold"><i class="fas fa-user mr-2"></i> Company Member Role</h6>
            </div>
            <div class="card-body">
                <span class="badge badge-info mb-3 p-2">Collaborator / Limited Write</span>
                <p class="text-xs text-gray-700 mb-3" style="line-height: 1.5;">
                    Members are individual contributors in the organization. They have full read access to all tasks in their company projects but can only perform modification actions on tasks that are **explicitly assigned to them**.
                </p>
                <div class="text-xs font-weight-bold text-info">
                    <i class="fas fa-check-circle mr-1"></i> View all organization tasks<br>
                    <i class="fas fa-check-circle mr-1"></i> Modify assigned tasks<br>
                    <i class="fas fa-check-circle mr-1"></i> Post comments & add notes anywhere
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
