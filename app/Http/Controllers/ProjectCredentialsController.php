<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectCredentialsRequest;
use App\Models\Project;
use App\Models\ProjectCredentials;
use Illuminate\Support\Facades\Gate;

class ProjectCredentialsController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProjectCredentialsRequest $request, Project $project)
    {
        Gate::authorize('update', $project);

        $typeInput = $request->input('type');
        $type = is_numeric($typeInput)
            ? (int) $typeInput
            : (ProjectCredentials::$typeMap[$typeInput] ?? ProjectCredentials::TYPE_DEVELOPMENT);

        $credential = $project->credentials()->create([
            'type' => $type,
            'name' => $request->input('name'),
            'host_or_identifier' => $request->input('host_or_identifier') ?: 'N/A',
            'password_or_secret' => $request->input('password_or_secret'),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Credential added successfully.',
                'credential' => [
                    'id' => $credential->id,
                    'type' => $credential->type,
                    'type_slug' => $credential->type_slug,
                    'name' => $credential->name,
                    'host_or_identifier' => $credential->host_or_identifier,
                    'password_or_secret' => $credential->password_or_secret,
                ],
            ]);
        }

        return redirect()->route('projects.credentials', $project)->with('success', 'Credential added successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, ProjectCredentials $credential)
    {
        Gate::authorize('update', $project);

        if ($credential->project_id !== $project->id) {
            abort(404);
        }

        $credential->delete();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Credential deleted successfully.',
            ]);
        }

        return redirect()->route('projects.credentials', $project)->with('success', 'Credential deleted successfully.');
    }
}
