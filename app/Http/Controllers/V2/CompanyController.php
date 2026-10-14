<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Mail\InviteMember;
use App\Models\Company;
use App\Models\CompanyInvitation;
use App\Models\CompanyUsers;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Spatie\Activitylog\Models\Activity;

class CompanyController extends Controller
{
    public function __construct(
        protected readonly NotificationService $notificationService
    ) {}

    /**
     * Display modern V2 Companies and Workspaces Suite.
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

        // Fetch all memberships of current user
        $memberships = $authUser->allCompanies()
            ->with([
                'company' => function ($q) {
                    $q->withCount(['projects', 'members as approved_members_count' => function ($m) {
                        $m->where('is_approved', true);
                    }]);
                },
                'company.members' => function ($m) {
                    $m->where('is_approved', true)->with('user')->limit(5);
                },
            ])
            ->latest('id')
            ->get();

        $selectedCompanyId = $request->query('company') ?? $request->query('selected');
        $selectedCompanyData = null;

        $companies = $memberships->map(function (CompanyUsers $cu) use ($sessionCompanyId, $authUser, $selectedCompanyId, &$selectedCompanyData) {
            $company = $cu->company;
            if (! $company) {
                return null;
            }

            $isActive = (string) $sessionCompanyId === (string) $company->id;
            $isAdmin = (int) $cu->role === 1;
            $isApproved = (bool) $cu->is_approved;

            // Tasks count in this workspace
            $tasksCount = Task::whereHas('project', function ($q) use ($company) {
                $q->where('company_id', $company->id);
            })->count();

            // Pending join requests count for admins
            $pendingRequestsCount = 0;
            if ($isAdmin && $isApproved) {
                $pendingRequestsCount = CompanyUsers::where('company_id', $company->id)
                    ->where('is_approved', false)
                    ->count();
            }

            // Member avatars preview
            $membersPreview = $company->members->map(function ($m) {
                $u = $m->user;
                if (! $u) {
                    return null;
                }

                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'avatar' => strtoupper(substr($u->name, 0, 2)),
                    'profile_image' => $u->profile_image_url ?? null,
                ];
            })->filter()->values()->toArray();

            $item = [
                'id' => $company->id,
                'name' => $company->name,
                'code' => $company->code,
                'role' => $isAdmin ? 'Admin' : 'Member',
                'role_id' => (int) $cu->role,
                'is_approved' => $isApproved,
                'is_active' => $isActive,
                'members_count' => $company->approved_members_count ?? $company->members()->where('is_approved', true)->count(),
                'pending_requests_count' => $pendingRequestsCount,
                'projects_count' => $company->projects_count ?? $company->projects()->count(),
                'tasks_count' => $tasksCount,
                'created_at' => $company->created_at ? $company->created_at->format('M d, Y') : '',
                'created_at_human' => $company->created_at ? $company->created_at->diffForHumans() : '',
                'members_preview' => $membersPreview,
                'can_manage' => $isAdmin && $isApproved,
                'can_leave' => ! $isAdmin && $isApproved,
            ];

            if ($selectedCompanyId && (string) $company->id === (string) $selectedCompanyId && $isApproved) {
                $selectedCompanyData = $this->loadFullCompanyDetails($company, $authUser, $isAdmin);
            }

            return $item;
        })->filter()->values()->toArray();

        // If no selected company is explicitly requested via query, but activeCompany exists, prepare its details
        if (! $selectedCompanyData && $activeCompany) {
            $activeMembership = $authUser->companies()->where('company_id', $activeCompany->id)->first();
            if ($activeMembership) {
                $isAdmin = (int) $activeMembership->role === 1;
                $selectedCompanyData = $this->loadFullCompanyDetails($activeCompany, $authUser, $isAdmin);
            }
        }

        // Summary KPI stats
        $totalCompanies = count($companies);
        $adminCompaniesCount = collect($companies)->where('role_id', 1)->where('is_approved', true)->count();
        $totalPendingRequests = collect($companies)->sum('pending_requests_count');

        $approvedCompanyIds = $authUser->companies()->pluck('company_id')->toArray();
        $uniqueTeammatesCount = CompanyUsers::whereIn('company_id', $approvedCompanyIds)
            ->where('is_approved', true)
            ->distinct('user_id')
            ->count('user_id');

        $totalProjectsCount = Project::whereIn('company_id', $approvedCompanyIds)->count();

        $stats = [
            'total_organizations' => $totalCompanies,
            'admin_organizations_count' => $adminCompaniesCount,
            'pending_requests_total' => $totalPendingRequests,
            'total_teammates_count' => $uniqueTeammatesCount,
            'total_projects_count' => $totalProjectsCount,
            'active_workspace_name' => $currentWorkspaceName,
            'active_workspace_id' => $sessionCompanyId,
        ];

        return Inertia::render('New/Companies', [
            'companies' => $companies,
            'stats' => $stats,
            'selected_company' => $selectedCompanyData,
            'current_workspace_name' => $currentWorkspaceName,
            'current_company_id' => $sessionCompanyId,
        ]);
    }

    /**
     * Show detailed view of a single company in V2.
     */
    public function show(Company $company)
    {
        $authUser = auth()->user();
        if (! $authUser) {
            abort(401);
        }

        $membership = CompanyUsers::where('company_id', $company->id)
            ->where('user_id', $authUser->id)
            ->where('is_approved', true)
            ->first();

        if (! $membership) {
            abort(403, 'Unauthorized. You are not a member of this workspace.');
        }

        return redirect()->route('v2.companies.index', ['company' => $company->id]);
    }

    /**
     * Store a newly created company in storage.
     */
    public function store(StoreCompanyRequest $request)
    {
        $name = trim($request->input('name'));

        do {
            $code = strtoupper(substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 4));
        } while (Company::where('code', $code)->exists());

        $company = Company::create([
            'name' => $name,
            'code' => $code,
        ]);

        $user = auth()->user();
        CompanyUsers::create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'role' => 1,
            'is_approved' => true,
        ]);

        session([
            'current_company_id' => $company->id,
            'current_company_data' => $company,
        ]);

        return redirect()->route('v2.companies.index', ['company' => $company->id])
            ->with('success', "Workspace '{$company->name}' created successfully! Join Code: {$company->code}");
    }

    /**
     * Join an existing company with 4-letter code.
     */
    public function join(Request $request)
    {
        $request->validate([
            'code' => 'required|string|min:4|max:10|exists:companies,code',
        ], [
            'code.exists' => 'No organization found with this code. Please check and try again.',
        ]);

        $code = strtoupper(trim($request->input('code')));
        $company = Company::where('code', $code)->first();

        if (! $company) {
            return back()->with('error', 'Organization not found.');
        }

        $user = auth()->user();
        $membership = CompanyUsers::where('company_id', $company->id)
            ->where('user_id', $user->id)
            ->first();

        if ($membership) {
            if ($membership->is_approved) {
                session([
                    'current_company_id' => $company->id,
                    'current_company_data' => $company,
                ]);

                return redirect()->route('v2.companies.index', ['company' => $company->id])
                    ->with('info', "You are already a member of {$company->name}. Active workspace switched.");
            }

            return back()->with('error', "Your request to join {$company->name} is already pending administrator approval.");
        }

        CompanyUsers::create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'role' => 0,
            'is_approved' => false,
        ]);

        // Notify organization administrators
        $admins = CompanyUsers::where('company_id', $company->id)
            ->where('role', 1)
            ->where('is_approved', true)
            ->with('user')
            ->get();

        foreach ($admins as $adminMember) {
            if ($adminMember->user) {
                $this->notificationService->send(
                    $adminMember->user,
                    'join_request',
                    'New Join Request',
                    "{$user->name} has requested to join {$company->name}.",
                    $company->id,
                    ['user_id' => $user->id, 'url' => route('v2.companies.index', ['company' => $company->id])]
                );
            }
        }

        return redirect()->route('v2.companies.index')
            ->with('success', "Your request to join '{$company->name}' has been submitted. Awaiting administrator approval.");
    }

    /**
     * Switch active company context.
     */
    public function switch(Company $company)
    {
        $user = auth()->user();
        $belongs = CompanyUsers::where('company_id', $company->id)
            ->where('user_id', $user->id)
            ->where('is_approved', true)
            ->exists();

        if (! $belongs) {
            abort(403, 'You are not an approved member of this organization.');
        }

        session([
            'current_company_id' => $company->id,
            'current_company_data' => $company,
        ]);

        return redirect()->route('v2.companies.index', ['company' => $company->id])
            ->with('success', "Active workspace switched to {$company->name}.");
    }

    /**
     * Switch to Personal Space.
     */
    public function switchToPersonal()
    {
        session(['current_company_id' => 'personal']);
        session()->forget(['current_company_data', 'code']);

        return redirect()->route('v2.companies.index')
            ->with('success', 'Switched to Personal Space.');
    }

    /**
     * Update the specified company name.
     */
    public function update(UpdateCompanyRequest $request, Company $company)
    {
        $company->update([
            'name' => trim($request->input('name')),
        ]);

        if (session('current_company_id') == $company->id) {
            session(['current_company_data' => $company]);
        }

        return redirect()->route('v2.companies.index', ['company' => $company->id])
            ->with('success', "Workspace name updated to '{$company->name}'.");
    }

    /**
     * Remove the specified company.
     */
    public function destroy(Company $company)
    {
        $user = auth()->user();
        $isAdmin = CompanyUsers::where('company_id', $company->id)
            ->where('user_id', $user->id)
            ->where('role', 1)
            ->exists();

        if (! $isAdmin) {
            abort(403, 'Unauthorized action. Only administrators can delete this organization.');
        }

        $companyName = $company->name;

        // Delete associations & company
        CompanyUsers::where('company_id', $company->id)->delete();
        $company->delete();

        if (session('current_company_id') == $company->id) {
            session()->forget(['current_company_id', 'current_role', 'current_company_data', 'code']);
            $nextCompany = CompanyUsers::where('user_id', $user->id)->where('is_approved', true)->first();
            if ($nextCompany) {
                session(['current_company_id' => $nextCompany->company_id]);
            } else {
                session(['current_company_id' => 'personal']);
            }
        }

        return redirect()->route('v2.companies.index')
            ->with('success', "Organization '{$companyName}' has been deleted.");
    }

    /**
     * Leave the specified company.
     */
    public function leave(Company $company)
    {
        $user = auth()->user();

        Gate::authorize('leave', $company);

        $membership = CompanyUsers::where('company_id', $company->id)
            ->where('user_id', $user->id)
            ->first();

        if (! $membership) {
            return back()->with('error', 'Membership record not found.');
        }

        // Unassign member's tasks in this company's projects
        $projectIds = $company->projects()->pluck('id')->toArray();
        Task::whereIn('project_id', $projectIds)
            ->where('assigned_to', $user->id)
            ->update(['assigned_to' => null]);

        $membership->delete();

        if (session('current_company_id') == $company->id) {
            $nextCompany = CompanyUsers::where('user_id', $user->id)
                ->where('is_approved', true)
                ->first();

            if ($nextCompany) {
                session(['current_company_id' => $nextCompany->company_id]);
            } else {
                session(['current_company_id' => 'personal']);
            }
        }

        return redirect()->route('v2.companies.index')
            ->with('success', "You have left {$company->name}.");
    }

    /**
     * Invite a new member to the company.
     */
    public function invite(Request $request, Company $company)
    {
        $request->validate([
            'email' => 'required|email',
            'message' => 'nullable|string|max:1000',
        ]);

        $authUser = auth()->user();
        Gate::authorize('update', $company);

        $email = strtolower(trim($request->input('email')));
        $existingUser = User::where('email', $email)->first();

        if ($existingUser) {
            $isMember = CompanyUsers::where('company_id', $company->id)
                ->where('user_id', $existingUser->id)
                ->exists();

            if ($isMember) {
                return back()->with('error', "{$email} is already a member of this workspace.");
            }
        }

        $customMessage = $request->input('message');
        $expiry = now()->addDays(7)->format('F d, Y h:i A');

        try {
            CompanyInvitation::updateOrCreate([
                'company_id' => $company->id,
                'email' => $email,
            ]);

            if ($existingUser) {
                cache()->forget('pending_invitations_'.$existingUser->id);
            }

            $joinLink = $existingUser
                ? route('login', ['email' => $email])
                : route('register', ['email' => $email]);

            Mail::to($email)->send(new InviteMember(
                $company->name,
                $authUser->name,
                $expiry,
                $joinLink,
                $customMessage
            ));

            return back()->with('success', "Invitation sent successfully to {$email}.");
        } catch (\Exception $e) {
            return back()->with('info', "Invitation record saved for {$email}. (Mail sending note: {$e->getMessage()})");
        }
    }

    /**
     * Approve a member's pending join request.
     */
    public function approveMember(Request $request, Company $company, User $user)
    {
        Gate::authorize('update', $company);

        CompanyUsers::where('company_id', $company->id)
            ->where('user_id', $user->id)
            ->update(['is_approved' => true]);

        $this->notificationService->send(
            $user,
            'join_approved',
            'Join Request Approved',
            "Your request to join {$company->name} has been approved.",
            $company->id,
            ['url' => route('v2.companies.index', ['company' => $company->id])]
        );

        return back()->with('success', "{$user->name} has been approved to join {$company->name}.");
    }

    /**
     * Reject a member's pending join request.
     */
    public function rejectMemberRequest(Request $request, Company $company, User $user)
    {
        Gate::authorize('update', $company);

        CompanyUsers::where('company_id', $company->id)
            ->where('user_id', $user->id)
            ->where('is_approved', false)
            ->delete();

        $this->notificationService->send(
            $user,
            'join_rejected',
            'Join Request Rejected',
            "Your request to join {$company->name} was rejected.",
            null,
            ['url' => route('v2.companies.index')]
        );

        return back()->with('info', "Join request from {$user->name} was rejected.");
    }

    /**
     * Remove a member from the company.
     */
    public function removeMember(Company $company, User $user)
    {
        $authUser = auth()->user();
        Gate::authorize('update', $company);

        if ($authUser->id === $user->id) {
            return back()->with('error', 'You cannot remove yourself. Please use the Leave option.');
        }

        $membership = CompanyUsers::where('company_id', $company->id)
            ->where('user_id', $user->id)
            ->first();

        if (! $membership) {
            return back()->with('error', 'Member not found in this organization.');
        }

        // Unassign member's tasks
        Task::where('assigned_to', $user->id)
            ->whereHas('project', function ($query) use ($company) {
                $query->where('company_id', $company->id);
            })
            ->update(['assigned_to' => null]);

        $membership->delete();

        return back()->with('success', "{$user->name} was removed from the organization.");
    }

    /**
     * Get member activity log for modal / drawer.
     */
    public function memberActivity(Company $company, User $user): JsonResponse
    {
        $currentUser = auth()->user();

        $isMember = CompanyUsers::where('company_id', $company->id)
            ->where('user_id', $currentUser->id)
            ->where('is_approved', true)
            ->exists();

        if (! $isMember) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $activities = Activity::where(function ($q) use ($user) {
            $q->where(function ($sub) use ($user) {
                $sub->where('causer_type', 'user')->where('causer_id', $user->id);
            })->orWhere(function ($sub) use ($user) {
                $sub->where('subject_type', 'user')->where('subject_id', $user->id);
            });
        })
            ->with(['causer', 'subject'])
            ->latest('id')
            ->take(30)
            ->get()
            ->map(function (Activity $activity) {
                $subject = $activity->subject;
                $title = null;
                if ($subject) {
                    $title = $subject->getAttribute('title') ?? $subject->getAttribute('name');
                }

                return [
                    'id' => $activity->id,
                    'description' => $activity->description,
                    'event' => $activity->event,
                    'subject_type' => class_basename($activity->subject_type ?? ''),
                    'subject_title' => is_string($title) ? $title : null,
                    'created_at_human' => $activity->created_at ? $activity->created_at->diffForHumans() : '',
                    'created_at_formatted' => $activity->created_at ? $activity->created_at->format('M d, Y h:i A') : '',
                ];
            });

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'last_login_at' => $user->last_login_at?->diffForHumans() ?? 'Never',
                'last_login_at_formatted' => $user->last_login_at?->format('M d, Y h:i A') ?? 'Never',
            ],
            'activities' => $activities,
        ]);
    }

    /**
     * Helper to load comprehensive details for a selected workspace.
     */
    protected function loadFullCompanyDetails(Company $company, User $authUser, bool $isAdmin): array
    {
        $members = CompanyUsers::where('company_id', $company->id)
            ->where('is_approved', true)
            ->with('user')
            ->withCount([
                'pendingTasks as pending_tasks_count',
                'completedTasks as completed_tasks_count',
                'totalTasks as total_tasks_count',
            ])
            ->get()
            ->map(function (CompanyUsers $m) use ($isAdmin, $authUser) {
                $u = $m->user;
                if (! $u) {
                    return null;
                }

                return [
                    'id' => $u->id,
                    'membership_id' => $m->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'role' => (int) $m->role === 1 ? 'Admin' : 'Member',
                    'role_id' => (int) $m->role,
                    'avatar' => strtoupper(substr($u->name, 0, 2)),
                    'profile_image' => $u->profile_image_url ?? null,
                    'pending_tasks_count' => $m->pending_tasks_count ?? 0,
                    'completed_tasks_count' => $m->completed_tasks_count ?? 0,
                    'total_tasks_count' => $m->total_tasks_count ?? 0,
                    'joined_at' => $m->created_at ? $m->created_at->format('M d, Y') : '',
                    'last_login' => $u->last_login_at ? $u->last_login_at->diffForHumans() : 'Never',
                    'can_remove' => $isAdmin && $u->id !== $authUser->id,
                ];
            })->filter()->values()->toArray();

        $pendingRequests = [];
        if ($isAdmin) {
            $pendingRequests = CompanyUsers::where('company_id', $company->id)
                ->where('is_approved', false)
                ->with('user')
                ->latest()
                ->get()
                ->map(function ($req) {
                    $u = $req->user;
                    if (! $u) {
                        return null;
                    }

                    return [
                        'id' => $u->id,
                        'name' => $u->name,
                        'email' => $u->email,
                        'avatar' => strtoupper(substr($u->name, 0, 2)),
                        'profile_image' => $u->profile_image_url ?? null,
                        'requested_at' => $req->created_at ? $req->created_at->diffForHumans() : '',
                    ];
                })->filter()->values()->toArray();
        }

        $projects = $company->projects()
            ->withCount([
                'tasks as total_tasks_count',
                'tasks as completed_tasks_count' => function ($q) {
                    $q->where('status', 3);
                },
            ])
            ->latest()
            ->get()
            ->map(function (Project $p) {
                $total = $p->total_tasks_count ?? 0;
                $completed = $p->completed_tasks_count ?? 0;
                $progress = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'theme' => $p->theme ?: '#10b981',
                    'total_tasks' => $total,
                    'completed_tasks' => $completed,
                    'progress' => $progress,
                    'status' => match ((int) $p->status) {
                        3 => 'Completed',
                        2 => 'In Progress',
                        4 => 'On Hold',
                        default => 'To Do',
                    },
                ];
            })->toArray();

        return [
            'id' => $company->id,
            'name' => $company->name,
            'code' => $company->code,
            'created_at' => $company->created_at ? $company->created_at->format('M d, Y') : '',
            'created_at_human' => $company->created_at ? $company->created_at->diffForHumans() : '',
            'is_admin' => $isAdmin,
            'members' => $members,
            'pending_requests' => $pendingRequests,
            'projects' => $projects,
        ];
    }
}
