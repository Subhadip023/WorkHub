<?php

namespace App\Http\Controllers;

use App\Models\CompanyUsers;
use App\Models\ExternalTaskApi;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ExternalTaskApiController extends Controller
{
    /**
     * Display external task API management for the specified project.
     */
    public function index(Project $project)
    {
        Gate::authorize('view', $project);

        $user_id = auth()->id();

        if ($project->company_id === null) {
            $companyUsers = collect([auth()->user()]);
            $user_role = 1;
        } else {
            $membership = CompanyUsers::where('company_id', $project->company_id)
                ->where('user_id', $user_id)
                ->first();

            $companyUsers = CompanyUsers::where('company_id', $project->company_id)
                ->with('user')
                ->get()
                ->map(function ($cu) {
                    return $cu->user;
                })
                ->filter()
                ->values();

            $user_role = $membership ? $membership->role : 2;
        }

        $externalApis = $project->externalApis()->with(['assignedUser', 'user'])->latest()->get();
        $comments = $project->comments()->with('user')->latest()->get();

        return view('projects.external_api', compact('project', 'companyUsers', 'user_role', 'externalApis', 'comments'));
    }

    /**
     * Store a newly generated external task API key for the project.
     */
    public function store(Request $request, Project $project)
    {
        Gate::authorize('update', $project);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'assigned_user_id' => 'nullable|exists:users,id',
            'default_status' => 'nullable|integer|in:1,2,3,4',
            'default_priority' => 'nullable|integer|in:1,2,3,4',
            'default_type' => 'nullable|integer|in:1,2,3,4',
        ]);

        $credentials = ExternalTaskApi::generateCredentials();

        $apiConfig = $project->externalApis()->create([
            'project_id' => $project->id,
            'user_id' => auth()->id(),
            'assigned_user_id' => $validated['assigned_user_id'] ?? null,
            'default_status' => $validated['default_status'] ?? 1,
            'default_priority' => $validated['default_priority'] ?? 2,
            'default_type' => $validated['default_type'] ?? 1,
            'name' => $validated['name'],
            'api_key' => $credentials['api_key'],
            'api_secret' => $credentials['api_secret'], // Automatically encrypted by model cast
            'is_active' => true,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'External Task API generated successfully',
                'api' => $apiConfig->load(['assignedUser', 'user']),
                'raw_secret' => $credentials['api_secret'], // Return raw secret once upon creation
            ], 201);
        }

        return redirect()->back()->with('success', 'External Task API key generated successfully');
    }

    /**
     * Update member assignment or active state of external task API key.
     */
    public function update(Request $request, ExternalTaskApi $externalTaskApi)
    {
        $project = $externalTaskApi->project;
        if ($project) {
            Gate::authorize('update', $project);
        } else {
            if ($externalTaskApi->user_id !== auth()->id()) {
                abort(403);
            }
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'assigned_user_id' => 'nullable|exists:users,id',
            'default_status' => 'nullable|integer|in:1,2,3,4',
            'default_priority' => 'nullable|integer|in:1,2,3,4',
            'default_type' => 'nullable|integer|in:1,2,3,4',
            'is_active' => 'nullable|boolean',
        ]);

        $externalTaskApi->update($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'External Task API updated successfully',
                'api' => $externalTaskApi->fresh(['assignedUser', 'user']),
            ]);
        }

        return redirect()->back()->with('success', 'External Task API updated successfully');
    }

    /**
     * Regenerate secret key for external task API.
     */
    public function regenerateSecret(ExternalTaskApi $externalTaskApi)
    {
        $project = $externalTaskApi->project;
        if ($project) {
            Gate::authorize('update', $project);
        } else {
            if ($externalTaskApi->user_id !== auth()->id()) {
                abort(403);
            }
        }

        $credentials = ExternalTaskApi::generateCredentials();
        $newSecret = $credentials['api_secret'];

        $externalTaskApi->update([
            'api_secret' => $newSecret,
        ]);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'HMAC API Secret Key regenerated successfully.',
                'raw_secret' => $newSecret,
            ]);
        }

        return redirect()->back()->with('success', 'HMAC API Secret Key regenerated successfully.');
    }

    /**
     * Remove / revoke external task API key.
     */
    public function destroy(ExternalTaskApi $externalTaskApi)
    {
        $project = $externalTaskApi->project;
        if ($project) {
            Gate::authorize('update', $project);
        } else {
            if ($externalTaskApi->user_id !== auth()->id()) {
                abort(403);
            }
        }

        $externalTaskApi->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'External Task API key revoked successfully',
            ]);
        }

        return redirect()->back()->with('success', 'External Task API key revoked successfully');
    }
}
