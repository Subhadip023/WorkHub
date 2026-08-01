<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class FeatureManagementController extends Controller
{
    /**
     * Display the list of users and their feature flag permissions.
     */
    public function index(Request $request)
    {
        abort_unless(Gate::allows('manage-features'), 403);

        $search = $request->query('q');

        $users = User::query()
            ->with('roles')
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->orderBy('id', 'asc')
            ->paginate(5)
            ->withQueryString();

        // Attach feature state for each user
        $users->getCollection()->transform(function ($user) {
            $user->beta_access = $user->hasDirectPermission('access-beta');
            $user->issues_access = $user->hasDirectPermission('access-issues');

            return $user;
        });

        return view('admin.features.index', compact('users', 'search'));
    }

    /**
     * Toggle a feature for a specific user.
     */
    public function toggleFeature(Request $request, User $user)
    {
        abort_unless(Gate::allows('manage-features'), 403);

        $request->validate([
            'feature' => 'required|string',
        ]);

        $feature = $request->input('feature');
        Permission::findOrCreate($feature);

        $isActive = $user->hasDirectPermission($feature);

        if ($isActive) {
            $user->revokePermissionTo($feature);
            $newStatus = false;
        } else {
            $user->givePermissionTo($feature);
            $newStatus = true;
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'active' => $newStatus,
                'message' => "Feature '{$feature}' ".($newStatus ? 'activated' : 'deactivated')." for {$user->name}.",
            ]);
        }

        return redirect()->back()->with('success', "Feature '{$feature}' status updated for {$user->name}.");
    }

    /**
     * Toggle user role for a specific user.
     */
    public function toggleRole(Request $request, User $user)
    {
        abort_unless(Gate::allows('manage-features'), 403);

        $request->validate([
            'role' => 'required',
        ]);

        $roleInput = $request->input('role');
        $targetRole = match ((string) $roleInput) {
            '0', User::ROLE_SUPER_ADMIN => User::ROLE_SUPER_ADMIN,
            '1', User::ROLE_ADMIN => User::ROLE_ADMIN,
            default => User::ROLE_USER,
        };

        // Super Admin role cannot be assigned or revoked via web UI
        if ($targetRole === User::ROLE_SUPER_ADMIN && ! $user->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Super Admin role can only be assigned via console command: php artisan user:super-admin '.$user->id,
            ], 422);
        }

        if ($user->isSuperAdmin() && $targetRole !== User::ROLE_SUPER_ADMIN) {
            return response()->json([
                'success' => false,
                'message' => 'Super Admin role cannot be changed via web UI. Use CLI command: php artisan user:super-admin '.$user->id.' --revoke',
            ], 422);
        }

        Role::findOrCreate($targetRole);
        $user->syncRoles([$targetRole]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'role' => $targetRole,
                'role_label' => $targetRole,
                'message' => "User role updated to {$targetRole} for {$user->name}.",
            ]);
        }

        return redirect()->back()->with('success', "User role updated to {$targetRole} for {$user->name}.");
    }
}
