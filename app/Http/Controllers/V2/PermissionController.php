<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PermissionController extends Controller
{
    /**
     * Display V2 Roles and System Permissions Matrix.
     */
    public function index(Request $request)
    {
        $authUser = auth()->user();
        if (! $authUser) {
            abort(401);
        }

        $sessionCompanyId = session('current_company_id');
        $activeCompany = is_numeric($sessionCompanyId) ? Company::find($sessionCompanyId) : null;
        $currentWorkspaceName = $activeCompany ? $activeCompany->name : ($sessionCompanyId === 'personal' ? 'Personal Space' : 'All Workspaces');

        // User's memberships
        $userMemberships = $authUser->companies()->with('company')->get()->map(function ($cu) {
            return [
                'company_id' => $cu->company_id,
                'company_name' => $cu->company?->name,
                'role' => (int) $cu->role === 1 ? 'Administrator' : 'Member',
                'is_admin' => (int) $cu->role === 1,
            ];
        })->values()->toArray();

        // Roles definition
        $roles = [
            [
                'name' => 'Super Administrator',
                'badge' => 'Root System',
                'description' => 'Full administrative control over all workspaces, user management, and feature switches.',
                'color' => 'rose',
                'users_count' => 1,
            ],
            [
                'name' => 'Organization Administrator',
                'badge' => 'Workspace Admin',
                'description' => 'Full control within company boundary: invites, workspace deletion, join approvals, and member removal.',
                'color' => 'indigo',
                'users_count' => collect($userMemberships)->where('is_admin', true)->count(),
            ],
            [
                'name' => 'Project Manager',
                'badge' => 'Team Lead',
                'description' => 'Create and modify projects, generate external API credentials, manage task assignments.',
                'color' => 'emerald',
                'users_count' => count($userMemberships),
            ],
            [
                'name' => 'Member / Collaborator',
                'badge' => 'Standard Access',
                'description' => 'Execute assigned tasks, log task points, create subtasks, update comments, and write documentation.',
                'color' => 'blue',
                'users_count' => count($userMemberships),
            ],
        ];

        // Permissions Matrix categories
        $matrix = [
            [
                'category' => 'Workspaces & Organization',
                'permissions' => [
                    ['name' => 'Create New Organization', 'super_admin' => true, 'org_admin' => true, 'manager' => true, 'member' => true],
                    ['name' => 'Delete Workspace', 'super_admin' => true, 'org_admin' => true, 'manager' => false, 'member' => false],
                    ['name' => 'Invite New Members', 'super_admin' => true, 'org_admin' => true, 'manager' => false, 'member' => false],
                    ['name' => 'Approve / Reject Join Requests', 'super_admin' => true, 'org_admin' => true, 'manager' => false, 'member' => false],
                    ['name' => 'Remove Team Members', 'super_admin' => true, 'org_admin' => true, 'manager' => false, 'member' => false],
                ],
            ],
            [
                'category' => 'Projects & Initiatives',
                'permissions' => [
                    ['name' => 'Create Projects', 'super_admin' => true, 'org_admin' => true, 'manager' => true, 'member' => true],
                    ['name' => 'Edit / Archive Projects', 'super_admin' => true, 'org_admin' => true, 'manager' => true, 'member' => false],
                    ['name' => 'Delete Projects to Recycle Bin', 'super_admin' => true, 'org_admin' => true, 'manager' => false, 'member' => false],
                    ['name' => 'Manage Project Credentials', 'super_admin' => true, 'org_admin' => true, 'manager' => true, 'member' => false],
                    ['name' => 'External Task API Key Generation', 'super_admin' => true, 'org_admin' => true, 'manager' => true, 'member' => false],
                ],
            ],
            [
                'category' => 'Tasks & Issues',
                'permissions' => [
                    ['name' => 'Create Tasks & Subtasks', 'super_admin' => true, 'org_admin' => true, 'manager' => true, 'member' => true],
                    ['name' => 'Assign Tasks to Others', 'super_admin' => true, 'org_admin' => true, 'manager' => true, 'member' => true],
                    ['name' => 'Change Task Priority / Status', 'super_admin' => true, 'org_admin' => true, 'manager' => true, 'member' => true],
                    ['name' => 'Report & Triage Issues', 'super_admin' => true, 'org_admin' => true, 'manager' => true, 'member' => true],
                    ['name' => 'Delete Tasks to Trash', 'super_admin' => true, 'org_admin' => true, 'manager' => true, 'member' => true],
                ],
            ],
            [
                'category' => 'Documentation & Auditing',
                'permissions' => [
                    ['name' => 'Create & Export Documentation (PDF)', 'super_admin' => true, 'org_admin' => true, 'manager' => true, 'member' => true],
                    ['name' => 'View System Audit Logs', 'super_admin' => true, 'org_admin' => true, 'manager' => false, 'member' => false],
                    ['name' => 'Restore / Purge Items in Recycle Bin', 'super_admin' => true, 'org_admin' => true, 'manager' => false, 'member' => false],
                ],
            ],
        ];

        return Inertia::render('New/Permissions', [
            'roles' => $roles,
            'matrix' => $matrix,
            'user_memberships' => $userMemberships,
            'current_workspace_name' => $currentWorkspaceName,
        ]);
    }
}
