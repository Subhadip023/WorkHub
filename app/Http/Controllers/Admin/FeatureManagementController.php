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
}
