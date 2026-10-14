<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanyUsers;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TeamController extends Controller
{
    /**
     * Display modern V2 Team Members Directory.
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

        // All user's approved companies
        $userCompanies = $authUser->companies()->with('company')->get()->map(function ($cu) {
            return [
                'id' => $cu->company?->id,
                'name' => $cu->company?->name,
                'is_admin' => (int) $cu->role === 1,
            ];
        })->filter(fn ($c) => ! empty($c['id']))->values()->toArray();

        $companyIds = collect($userCompanies)->pluck('id')->toArray();

        // Admin-managed companies (for sending invites)
        $adminCompanies = collect($userCompanies)->where('is_admin', true)->values()->toArray();

        // Determine target company filter
        $filterCompanyId = $request->query('company');
        if ($filterCompanyId && is_numeric($filterCompanyId) && in_array((int) $filterCompanyId, $companyIds)) {
            $scopedCompanyIds = [(int) $filterCompanyId];
        } elseif ($activeCompany) {
            $scopedCompanyIds = [$activeCompany->id];
        } else {
            $scopedCompanyIds = $companyIds;
        }

        // Fetch team memberships
        $memberships = CompanyUsers::whereIn('company_id', $scopedCompanyIds)
            ->where('is_approved', true)
            ->with(['user', 'company'])
            ->get();

        // Group by user
        $groupedByUser = $memberships->groupBy('user_id');

        $teamMembers = $groupedByUser->map(function ($userMemberships) use ($scopedCompanyIds) {
            $first = $userMemberships->first();
            $user = $first->user;
            if (! $user) {
                return null;
            }

            $isAdmin = $userMemberships->contains(fn ($m) => (int) $m->role === 1);

            $memberCompanies = $userMemberships->map(function ($m) {
                return [
                    'id' => $m->company_id,
                    'name' => $m->company?->name,
                    'role' => (int) $m->role === 1 ? 'Admin' : 'Member',
                ];
            })->values()->toArray();

            // Task counts
            $tasks = Task::where('assigned_to', $user->id)
                ->whereHas('project', function ($q) use ($scopedCompanyIds) {
                    $q->whereIn('company_id', $scopedCompanyIds);
                })
                ->get();

            $totalTasks = $tasks->count();
            $completedTasks = $tasks->where('status', 3)->count();
            $pendingTasks = $totalTasks - $completedTasks;

            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => strtoupper(substr($user->name, 0, 2)),
                'profile_image' => $user->profile_image_url ?? null,
                'is_admin' => $isAdmin,
                'primary_role' => $isAdmin ? 'Admin' : 'Member',
                'companies' => $memberCompanies,
                'tasks_count' => $totalTasks,
                'completed_tasks_count' => $completedTasks,
                'pending_tasks_count' => $pendingTasks,
                'last_login' => $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never',
                'joined_at' => $first->created_at ? $first->created_at->format('M d, Y') : '',
            ];
        })->filter()->values()->toArray();

        // KPI stats
        $totalMembers = count($teamMembers);
        $totalAdmins = collect($teamMembers)->where('is_admin', true)->count();
        $activeMembers = collect($teamMembers)->filter(fn ($m) => $m['last_login'] !== 'Never')->count();

        $stats = [
            'total_members' => $totalMembers,
            'total_admins' => $totalAdmins,
            'active_members' => $activeMembers,
            'total_organizations' => count($companyIds),
            'current_workspace_name' => $currentWorkspaceName,
        ];

        return Inertia::render('New/Team', [
            'team_members' => $teamMembers,
            'stats' => $stats,
            'companies' => $userCompanies,
            'admin_companies' => $adminCompanies,
            'current_workspace_name' => $currentWorkspaceName,
            'filter_company_id' => $filterCompanyId,
        ]);
    }
}
