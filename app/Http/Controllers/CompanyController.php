<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Mail\InviteMember;
use App\Models\Company;
use App\Models\CompanyInvitation;
use App\Models\CompanyUsers;
use App\Models\Task;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Spatie\Activitylog\Models\Activity;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $companies = auth()->user()->allCompanies()->with('company')->get();

        return view('companies.index', compact('companies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('companies.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCompanyRequest $request)
    {
        $name = $request->input('name');
        do {
            $code = strtoupper(substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 4));
        } while (Company::where('code', $code)->exists());

        $company = Company::create([
            'name' => $name,
            'code' => $code,
        ]);
        $company_id = $company->id;
        $user_id = auth()->user()->id;
        CompanyUsers::create([
            'company_id' => $company_id,
            'user_id' => $user_id,
            'role' => 1,
            'is_approved' => true, // #23: Creator is always an approved admin
        ]);

        session(['current_company_id' => $company_id]);

        return redirect()->route('dashboard')->with('success', 'Company created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Company $company)
    {
        $user = auth()->user();
        if (! $user) {
            abort(401);
        }

        // Verify membership access
        $isMember = CompanyUsers::where('company_id', $company->id)
            ->where('user_id', $user->id)
            ->where('is_approved', true)
            ->exists();

        if (! $isMember) {
            abort(403, 'Unauthorized.');
        }

        // Load members with company-scoped task counts
        $members = CompanyUsers::where('company_id', $company->id)
            ->where('is_approved', true)
            ->with('user')
            ->withCount([
                'pendingTasks as pending_tasks_count',
                'completedTasks as completed_tasks_count',
                'totalTasks as total_tasks_count',
            ])
            ->get();

        // Load comments
        $comments = $company->comments()->with('user')->latest()->get();

        $isAdmin = CompanyUsers::where('company_id', $company->id)
            ->where('user_id', $user->id)
            ->where('role', 1)
            ->exists();

        // Load pending join requests for admins
        $pendingRequests = [];
        if ($isAdmin) {
            $pendingRequests = CompanyUsers::where('company_id', $company->id)
                ->where('is_approved', false)
                ->with('user')
                ->get();
        }

        return view('companies.show', compact('company', 'members', 'comments', 'isAdmin', 'pendingRequests'));
    }

    /**
     * Get member activity log for company details view.
     */
    public function memberActivity(Company $company, User $user): JsonResponse
    {
        $currentUser = auth()->user();

        // Verify current user belongs to this company
        $isMember = CompanyUsers::where('company_id', $company->id)
            ->where('user_id', $currentUser->id)
            ->where('is_approved', true)
            ->exists();

        if (! $isMember) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        // Verify target user is also in this company
        $targetIsMember = CompanyUsers::where('company_id', $company->id)
            ->where('user_id', $user->id)
            ->where('is_approved', true)
            ->exists();

        if (! $targetIsMember) {
            return response()->json(['success' => false, 'message' => 'User is not a member of this organization.'], 404);
        }

        $activities = Activity::where(function ($q) use ($user) {
            $q->where(function ($sub) use ($user) {
                $sub->where('causer_type', 'user')
                    ->where('causer_id', $user->id);
            })->orWhere(function ($sub) use ($user) {
                $sub->where('subject_type', 'user')
                    ->where('subject_id', $user->id);
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
                    'properties' => $activity->properties,
                    'created_at_human' => $activity->created_at ? $activity->created_at->diffForHumans() : '',
                    'created_at_formatted' => $activity->created_at ? $activity->created_at->format('M d, Y h:i A') : '',
                ];
            });

        $lastLoginHuman = $user->last_login_at?->diffForHumans() ?? 'Never';
        $lastLoginFormatted = $user->last_login_at?->format('M d, Y h:i A') ?? 'Never';

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'last_login_at' => $lastLoginHuman,
                'last_login_at_formatted' => $lastLoginFormatted,
            ],
            'activities' => $activities,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Company $company)
    {
        return redirect()->route('companies.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCompanyRequest $request, Company $company)
    {
        $company->update([
            'name' => $request->input('name'),
        ]);

        if (session('current_company_id') == $company->id) {
            session([
                'current_company_data' => $company,
            ]);
        }

        return redirect()->route('companies.index')->with('success', 'Company name updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Company $company)
    {
        $user = auth()->user();
        $is_admin = CompanyUsers::where('company_id', $company->id)
            ->where('user_id', $user->id)
            ->where('role', 1)
            ->exists();

        if (! $is_admin) {
            abort(403, 'Unauthorized action.');
        }

        // Delete association records
        CompanyUsers::where('company_id', $company->id)->delete();

        // Delete the company
        $company->delete();

        // If the deleted company was the active company, switch session
        if (session('current_company_id') == $company->id) {
            session()->forget(['current_company_id', 'current_role', 'current_company_data', 'code']);

            $next_company = CompanyUsers::where('user_id', $user->id)->first();
            if ($next_company) {
                session(['current_company_id' => $next_company->company_id]);
            } else {
                session(['current_company_id' => 'personal']);
            }

            return redirect()->route('companies.index')->with('success', 'Organization deleted successfully');
        }

        return redirect()->route('companies.index')->with('success', 'Organization deleted successfully');
    }

    public function join(Request $request)
    {
        $request->validate(['code' => 'required|string|exists:companies,code']);
        $code = $request->input('code');
        $company = Company::where('code', $code)->first();
        if ($company) {
            $company_id = $company->id;
            $user_id = auth()->user()->id;

            // Prevent double-joining and check approval status
            $membership = CompanyUsers::where('company_id', $company_id)->where('user_id', $user_id)->first();
            if ($membership) {
                if ($membership->is_approved) {
                    session(['current_company_id' => $company_id]);

                    return redirect()->route('dashboard')->with('info', "You are already a member of {$company->name}. Active company switched.");
                } else {
                    return back()->with('error', "Your join request for {$company->name} is already pending approval.");
                }
            }

            $auth_user = auth()->user();
            CompanyUsers::create([
                'company_id' => $company_id,
                'user_id' => $user_id,
                'role' => 0,
                'is_approved' => false,
            ]);

            // Notify admins
            $admins = CompanyUsers::where('company_id', $company_id)
                ->where('role', 1)
                ->where('is_approved', true)
                ->with('user')
                ->get();

            $notificationService = app(NotificationService::class);
            foreach ($admins as $adminMember) {
                if ($adminMember->user) {
                    $notificationService->send(
                        $adminMember->user,
                        'join_request',
                        'New Join Request',
                        "{$auth_user->name} has requested to join {$company->name}.",
                        $company_id,
                        ['user_id' => $auth_user->id, 'url' => route('companies.show', $company->id)]
                    );
                }
            }

            return redirect()->route('companies.index')->with('success', 'Your request to join the organization has been submitted. Please wait for an administrator to approve it.');
        }

        return back()->with('error', 'Company not found');
    }

    /**
     * Switch active company.
     */
    public function switch(Company $company)
    {
        $user_id = auth()->user()->id;
        $belongs = CompanyUsers::where('company_id', $company->id)
            ->where('user_id', $user_id)
            ->where('is_approved', true)
            ->exists();
        if (! $belongs) {
            abort(403);
        }

        session(['current_company_id' => $company->id]);

        return redirect()->route('dashboard')->with('success', "Switched to {$company->name}");
    }

    /**
     * Switch to Personal Space.
     */
    public function switchToPersonal()
    {
        session(['current_company_id' => 'personal']);

        return redirect()->route('dashboard')->with('success', 'Switched to Personal Space');
    }

    /**
     * Remove a member from the company.
     */
    public function removeMember(Company $company, User $user)
    {
        $auth_user = auth()->user();

        Gate::authorize('update', $company);

        if ($auth_user->id == $user->id) {
            return back()->with('error', 'You cannot remove yourself from the organization.');
        }

        $memberRelation = CompanyUsers::where('company_id', $company->id)
            ->where('user_id', $user->id)
            ->first();

        if (! $memberRelation) {
            return back()->with('error', 'Member not found in this organization.');
        }

        // Unassign member's tasks in this company
        Task::where('assigned_to', $user->id)
            ->whereHas('project', function ($query) use ($company) {
                $query->where('company_id', $company->id);
            })
            ->update(['assigned_to' => null]);

        $memberRelation->delete();

        return back()->with('success', 'Member removed successfully.');
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

        $auth_user = auth()->user();

        Gate::authorize('update', $company);

        $email = $request->input('email');

        // Check if the user is already a member of the company
        $existingUser = User::where('email', $email)->first();
        if ($existingUser) {
            $isMember = CompanyUsers::where('company_id', $company->id)
                ->where('user_id', $existingUser->id)
                ->exists();

            if ($isMember) {
                return back()->with('error', "User with email {$email} is already a member of this organization.");
            }
        }

        $customMessage = $request->input('message');
        $expiry = now()->addDays(7)->format('F d, Y h:i A'); // 7 days from now

        try {
            // Store the invitation in the database
            CompanyInvitation::updateOrCreate([
                'company_id' => $company->id,
                'email' => $email,
            ]);

            // Clear cache for the invited user if they exist
            $invitedUser = User::where('email', $email)->first();
            if ($invitedUser) {
                cache()->forget('pending_invitations_'.$invitedUser->id);
            }

            Mail::to($email)->send(new InviteMember(
                $company->name,
                $auth_user->name,
                $expiry,
                route('companies.index', ['code' => $company->code]),
                $customMessage
            ));

            return back()->with('success', "Invitation sent successfully to {$email}.");
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send invitation email: '.$e->getMessage());
        }
    }

    /**
     * Accept a company invitation.
     */
    public function acceptInvitation(Request $request, CompanyInvitation $invitation)
    {
        Gate::authorize('handle', $invitation);

        $company_id = $invitation->company_id;
        $user_id = auth()->user()->id;

        // Add user as an approved member to the company
        CompanyUsers::updateOrCreate([
            'company_id' => $company_id,
            'user_id' => $user_id,
        ], [
            'role' => 0, // Member
            'is_approved' => true,
        ]);

        // Set the active company session
        session(['current_company_id' => $company_id]);

        // Delete the invitation
        $invitation->delete();

        // Clear the cache
        cache()->forget('pending_invitations_'.auth()->id());

        return redirect()->route('dashboard')->with('success', 'You have successfully joined the organization!');
    }

    /**
     * Reject a company invitation.
     */
    public function rejectInvitation(Request $request, CompanyInvitation $invitation)
    {
        Gate::authorize('handle', $invitation);

        // Delete the invitation
        $invitation->delete();

        // Clear the cache
        cache()->forget('pending_invitations_'.auth()->id());

        return back()->with('info', 'Invitation rejected.');
    }

    /**
     * Approve a member's request to join the company.
     */
    public function approveMember(Request $request, Company $company, User $user)
    {
        $auth_user = auth()->user();

        Gate::authorize('update', $company);

        // Approve the member
        CompanyUsers::where('company_id', $company->id)
            ->where('user_id', $user->id)
            ->update(['is_approved' => true]);

        // Notify the user (sent to personal context so it is visible immediately)
        $notificationService = app(NotificationService::class);
        $notificationService->send(
            $user,
            'join_approved',
            'Join Request Approved',
            "Your request to join {$company->name} has been approved.",
            null,
            ['url' => route('companies.show', $company->id)]
        );

        return back()->with('success', "{$user->name} has been approved to join the organization.");
    }

    /**
     * Reject a member's request to join the company.
     */
    public function rejectMemberRequest(Request $request, Company $company, User $user)
    {
        Gate::authorize('update', $company);

        // Delete the pending membership
        CompanyUsers::where('company_id', $company->id)
            ->where('user_id', $user->id)
            ->where('is_approved', false)
            ->delete();

        // Notify the user (sent to personal context so it is visible immediately)
        $notificationService = app(NotificationService::class);
        $notificationService->send(
            $user,
            'join_rejected',
            'Join Request Rejected',
            "Your request to join {$company->name} was rejected.",
            null,
            ['url' => route('companies.index')]
        );

        return back()->with('info', "Join request from {$user->name} was rejected.");
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

        // Unassign all tasks assigned to this user in projects of this company
        $projectIds = $company->projects()->pluck('id')->toArray();
        Task::whereIn('project_id', $projectIds)
            ->where('assigned_to', $user->id)
            ->update(['assigned_to' => null]);

        // Delete membership
        $membership->delete();

        // If left company was active company context, switch back to personal space or next available company
        if (session('current_company_id') == $company->id) {
            $next_company = CompanyUsers::where('user_id', $user->id)
                ->where('is_approved', true)
                ->first();

            if ($next_company) {
                session(['current_company_id' => $next_company->company_id]);
            } else {
                session(['current_company_id' => 'personal']);
            }
        }

        return redirect()->route('companies.index')->with('success', "You have successfully left {$company->name}.");
    }
}
