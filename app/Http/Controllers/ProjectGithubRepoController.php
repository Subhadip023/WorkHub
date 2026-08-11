<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectGithubRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProjectGithubRepoController extends Controller
{
    /**
     * Display GitHub repositories for the specified project.
     */
    public function index(Project $project)
    {
        Gate::authorize('view', $project);

        $githubRepos = $project->githubRepos()->with('user')->latest()->get();
        $comments = $project->getCachedComments();

        return view('projects.github', compact('project', 'githubRepos', 'comments'));
    }

    /**
     * Store a newly created GitHub repository in storage.
     */
    public function store(Request $request, Project $project)
    {
        Gate::authorize('update', $project);

        $validated = $request->validate([
            'repo_owner' => 'required|string|max:255',
            'repo_name' => 'required|string|max:255',
            'access_token' => 'nullable|string|max:1000',
            'webhook_secret' => 'nullable|string|max:255',
            'auto_sync_issues' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $githubRepo = $project->githubRepos()->create([
            'user_id' => auth()->id(),
            'repo_owner' => trim($validated['repo_owner']),
            'repo_name' => trim($validated['repo_name']),
            'access_token' => $validated['access_token'] ?? null,
            'webhook_secret' => $validated['webhook_secret'] ?? null,
            'auto_sync_issues' => $request->has('auto_sync_issues') ? (bool) $request->input('auto_sync_issues') : true,
            'is_active' => $request->has('is_active') ? (bool) $request->input('is_active') : true,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'GitHub repository stored successfully.',
                'github_repo' => $githubRepo,
            ]);
        }

        return redirect()->route('projects.github', $project)->with('success', 'GitHub repository stored successfully.');
    }

    /**
     * Update the specified GitHub repository in storage.
     */
    public function update(Request $request, Project $project, ProjectGithubRepo $githubRepo)
    {
        Gate::authorize('update', $project);

        if ($githubRepo->project_id !== $project->id) {
            abort(404);
        }

        $validated = $request->validate([
            'repo_owner' => 'required|string|max:255',
            'repo_name' => 'required|string|max:255',
            'access_token' => 'nullable|string|max:1000',
            'webhook_secret' => 'nullable|string|max:255',
            'auto_sync_issues' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $updateData = [
            'repo_owner' => trim($validated['repo_owner']),
            'repo_name' => trim($validated['repo_name']),
            'auto_sync_issues' => $request->has('auto_sync_issues') ? (bool) $request->input('auto_sync_issues') : false,
            'is_active' => $request->has('is_active') ? (bool) $request->input('is_active') : false,
        ];

        if ($request->filled('access_token')) {
            $updateData['access_token'] = $validated['access_token'];
        }

        if ($request->filled('webhook_secret')) {
            $updateData['webhook_secret'] = $validated['webhook_secret'];
        }

        $githubRepo->update($updateData);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'GitHub repository updated successfully.',
                'github_repo' => $githubRepo,
            ]);
        }

        return redirect()->route('projects.github', $project)->with('success', 'GitHub repository updated successfully.');
    }

    /**
     * Remove the specified GitHub repository from storage.
     */
    public function destroy(Project $project, ProjectGithubRepo $githubRepo)
    {
        Gate::authorize('update', $project);

        if ($githubRepo->project_id !== $project->id) {
            abort(404);
        }

        $githubRepo->delete();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'GitHub repository disconnected successfully.',
            ]);
        }

        return redirect()->route('projects.github', $project)->with('success', 'GitHub repository disconnected successfully.');
    }
}
