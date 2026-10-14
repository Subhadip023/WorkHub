<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class SettingsController extends Controller
{
    /**
     * Display V2 Account Settings & Profile.
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

        $userCompanies = $authUser->companies()->with('company')->get()->map(function ($cu) {
            return [
                'id' => $cu->company?->id,
                'name' => $cu->company?->name,
                'code' => $cu->company?->code,
                'role' => (int) $cu->role === 1 ? 'Admin' : 'Member',
            ];
        })->filter(fn ($c) => ! empty($c['id']))->values()->toArray();

        $userData = [
            'id' => $authUser->id,
            'name' => $authUser->name,
            'email' => $authUser->email,
            'profile_image' => $authUser->profile_image_url,
            'avatar' => strtoupper(substr($authUser->name, 0, 2)),
            'created_at' => $authUser->created_at ? $authUser->created_at->format('M d, Y') : '',
            'last_login_at' => $authUser->last_login_at ? $authUser->last_login_at->format('M d, Y h:i A') : 'Never',
        ];

        return Inertia::render('New/Settings', [
            'user' => $userData,
            'companies' => $userCompanies,
            'current_workspace_name' => $currentWorkspaceName,
        ]);
    }

    /**
     * Update user profile information in V2.
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
        ]);

        $user->fill([
            'name' => trim($request->input('name')),
            'email' => trim($request->input('email')),
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return redirect()->back()->with('success', 'Profile details updated successfully.');
    }

    /**
     * Update user password in V2.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $user = auth()->user();
        $user->update([
            'password' => Hash::make($request->input('password')),
        ]);

        return redirect()->back()->with('success', 'Password updated successfully.');
    }
}
