<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Mail\InviteMember;
use App\Models\Company;
use App\Models\CompanyUsers;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

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
        CompanyUsers::create(['company_id' => $company_id, 'user_id' => $user_id, 'role' => 1]);

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
                'is_approved' => false
            ]);

            // Notify admins
            $admins = CompanyUsers::where('company_id', $company_id)
                ->where('role', 1)
                ->where('is_approved', true)
                ->with('user')
                ->get();

            $notificationService = app(\App\Services\NotificationService::class);
            foreach ($admins as $adminMember) {
                if ($adminMember->user) {
                    $notificationService->send(
                        $adminMember->user,
                        'join_request',
                        'New Join Request',
                        "{$auth_user->name} has requested to join {$company->name}.",
                        $company_id,
                        ['user_id' => $auth_user->id]
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

        $isAdmin = CompanyUsers::where('company_id', $company->id)
            ->where('user_id', $auth_user->id)
            ->where('role', 1)
            ->exists();

        if (! $isAdmin) {
            abort(403, 'Unauthorized action.');
        }

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

        // Verify current user is admin of this company
        $isAdmin = CompanyUsers::where('company_id', $company->id)
            ->where('user_id', $auth_user->id)
            ->where('role', 1)
            ->exists();

        if (! $isAdmin) {
            abort(403, 'Unauthorized action.');
        }

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
            \App\Models\CompanyInvitation::updateOrCreate([
                'company_id' => $company->id,
                'email' => $email,
            ]);

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
    public function acceptInvitation(Request $request, \App\Models\CompanyInvitation $invitation)
    {
        if ($invitation->email !== auth()->user()->email) {
            abort(403, 'Unauthorized action.');
        }

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

        return redirect()->route('dashboard')->with('success', 'You have successfully joined the organization!');
    }

    /**
     * Reject a company invitation.
     */
    public function rejectInvitation(Request $request, \App\Models\CompanyInvitation $invitation)
    {
        if ($invitation->email !== auth()->user()->email) {
            abort(403, 'Unauthorized action.');
        }

        // Delete the invitation
        $invitation->delete();

        return back()->with('info', 'Invitation rejected.');
    }

    /**
     * Approve a member's request to join the company.
     */
    public function approveMember(Request $request, Company $company, User $user)
    {
        $auth_user = auth()->user();

        // Verify current user is admin of this company
        $isAdmin = CompanyUsers::where('company_id', $company->id)
            ->where('user_id', $auth_user->id)
            ->where('role', 1)
            ->exists();

        if (! $isAdmin) {
            abort(403, 'Unauthorized action.');
        }

        // Approve the member
        CompanyUsers::where('company_id', $company->id)
            ->where('user_id', $user->id)
            ->update(['is_approved' => true]);

        // Notify the user (sent to personal context so it is visible immediately)
        $notificationService = app(\App\Services\NotificationService::class);
        $notificationService->send(
            $user,
            'join_approved',
            'Join Request Approved',
            "Your request to join {$company->name} has been approved.",
            null
        );

        return back()->with('success', "{$user->name} has been approved to join the organization.");
    }

    /**
     * Reject a member's request to join the company.
     */
    public function rejectMemberRequest(Request $request, Company $company, User $user)
    {
        $auth_user = auth()->user();

        // Verify current user is admin of this company
        $isAdmin = CompanyUsers::where('company_id', $company->id)
            ->where('user_id', $auth_user->id)
            ->where('role', 1)
            ->exists();

        if (! $isAdmin) {
            abort(403, 'Unauthorized action.');
        }

        // Delete the pending membership
        CompanyUsers::where('company_id', $company->id)
            ->where('user_id', $user->id)
            ->where('is_approved', false)
            ->delete();

        // Notify the user (sent to personal context so it is visible immediately)
        $notificationService = app(\App\Services\NotificationService::class);
        $notificationService->send(
            $user,
            'join_rejected',
            'Join Request Rejected',
            "Your request to join {$company->name} was rejected.",
            null
        );

        return back()->with('info', "Join request from {$user->name} was rejected.");
    }

    /**
     * Leave the specified company.
     */
    public function leave(Company $company)
    {
        $user = auth()->user();

        // Verify membership access
        $membership = CompanyUsers::where('company_id', $company->id)
            ->where('user_id', $user->id)
            ->first();

        if (! $membership || ! $membership->is_approved) {
            abort(403, 'Unauthorized action.');
        }

        // If admin, they cannot leave
        if ($membership->role == 1) {
            abort(403, 'Administrators cannot leave the organization.');
        }

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
