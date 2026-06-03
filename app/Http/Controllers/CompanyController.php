<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Models\Company;
use App\Models\CompanyUsers;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $companies = auth()->user()->companies()->with('company')->get();

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
            ->exists();

        if (! $isMember) {
            abort(403, 'Unauthorized.');
        }

        // Load members with company-scoped task counts
        $members = CompanyUsers::where('company_id', $company->id)
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

        return view('companies.show', compact('company', 'members', 'comments', 'isAdmin'));
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

            // Prevent double-joining and gracefully switch company
            $exists = CompanyUsers::where('company_id', $company_id)->where('user_id', $user_id)->exists();
            if ($exists) {
                session(['current_company_id' => $company_id]);

                return redirect()->route('dashboard')->with('info', "You are already a member of {$company->name}. Active company switched.");
            }

            CompanyUsers::create(['company_id' => $company_id, 'user_id' => $user_id, 'role' => 0]);
            session(['current_company_id' => $company_id]);

            return redirect()->route('dashboard')->with('success', 'Company joined successfully');
        }

        return back()->with('error', 'Company not found');
    }

    /**
     * Switch active company.
     */
    public function switch(Company $company)
    {
        $user_id = auth()->user()->id;
        $belongs = CompanyUsers::where('company_id', $company->id)->where('user_id', $user_id)->exists();
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
    public function removeMember(Company $company, \App\Models\User $user)
    {
        $auth_user = auth()->user();
        
        $isAdmin = CompanyUsers::where('company_id', $company->id)
            ->where('user_id', $auth_user->id)
            ->where('role', 1)
            ->exists();

        if (!$isAdmin) {
            abort(403, 'Unauthorized action.');
        }

        if ($auth_user->id == $user->id) {
            return back()->with('error', 'You cannot remove yourself from the organization.');
        }

        $memberRelation = CompanyUsers::where('company_id', $company->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$memberRelation) {
            return back()->with('error', 'Member not found in this organization.');
        }

        // Unassign member's tasks in this company
        \App\Models\Task::where('assigned_to', $user->id)
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

        if (!$isAdmin) {
            abort(403, 'Unauthorized action.');
        }

        $email = $request->input('email');
        $customMessage = $request->input('message');
        $expiry = now()->addDays(7)->format('F d, Y h:i A'); // 7 days from now

        try {
            \Illuminate\Support\Facades\Mail::to($email)->send(new \App\Mail\InviteMember(
                $company->name,
                $auth_user->name,
                $expiry,
                route('companies.index', ['code' => $company->code]),
                $customMessage
            ));

            return back()->with('success', "Invitation sent successfully to {$email}.");
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send invitation email: ' . $e->getMessage());
        }
    }
}
