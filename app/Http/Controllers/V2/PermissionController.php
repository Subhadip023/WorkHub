<?php

namespace App\Http\Controllers\V2;

use App\Authorization\CompanyAbility;
use App\Enums\CompanyRole;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanyUsers;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Roles & permissions page. Everything shown here is generated from
 * App\Enums\CompanyRole and App\Authorization\CompanyAbility — the same rules
 * the policies enforce — so the page cannot drift from real behaviour.
 */
class PermissionController extends Controller
{
    public function index(Request $request)
    {
        $authUser = auth()->user();
        if (! $authUser) {
            abort(401);
        }

        $sessionCompanyId = session('current_company_id');
        $activeCompany = is_numeric($sessionCompanyId) ? Company::find($sessionCompanyId) : null;
        $currentWorkspaceName = $activeCompany ? $activeCompany->name : ($sessionCompanyId === 'personal' ? 'Personal Space' : 'All Workspaces');

        $memberships = $authUser->companies()->with('company')->get();

        $userMemberships = $memberships->map(function (CompanyUsers $cu) {
            $role = $cu->roleEnum();

            return [
                'company_id' => $cu->company_id,
                'company_name' => $cu->company?->name,
                'role' => $role->label(),
                'is_admin' => $role->atLeast(CompanyRole::Admin),
            ];
        })->values()->toArray();

        // Real member counts per role: the active workspace, or all of the viewer's workspaces.
        $countCompanyIds = $activeCompany ? [$activeCompany->id] : $memberships->pluck('company_id')->all();
        $counts = CompanyUsers::whereIn('company_id', $countCompanyIds)
            ->where('is_approved', true)
            ->get(['role'])
            ->countBy(fn (CompanyUsers $m) => $m->roleEnum()->value);

        $roles = array_map(fn (CompanyRole $role) => [
            'key' => strtolower($role->name),
            'name' => $role->label(),
            'badge' => 'Rank '.($role->rank() + 1),
            'description' => $role->description(),
            'users_count' => (int) ($counts[$role->value] ?? 0),
        ], CompanyRole::ordered());

        $roleColumns = array_map(fn (CompanyRole $role) => [
            'key' => strtolower($role->name),
            'label' => $role->label(),
        ], CompanyRole::ordered());

        return Inertia::render('New/Permissions', [
            'roles' => $roles,
            'role_columns' => $roleColumns,
            'matrix' => CompanyAbility::matrix(),
            'user_memberships' => $userMemberships,
            'current_workspace_name' => $currentWorkspaceName,
        ]);
    }
}
