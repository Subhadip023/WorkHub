<?php

namespace App\Http\Controllers;

use App\Models\CompanyUsers;
use App\Models\ExternalTaskApi;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

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
        $comments = $project->getCachedComments();

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

    /**
     * Download Postman Collection v2.1 pre-configured with HMAC signature pre-request script.
     */
    public function downloadPostmanCollection(Project $project)
    {
        Gate::authorize('view', $project);

        $activeApi = $project->externalApis()->where('is_active', true)->latest()->first();
        $apiKey = $activeApi ? $activeApi->api_key : 'YOUR_API_KEY_HERE';
        $apiSecret = $activeApi ? $activeApi->api_secret : 'YOUR_API_SECRET_HERE';

        $baseUrl = config('app.url', 'http://localhost');

        $collection = [
            'info' => [
                'name' => "WorkHub - External Task API ({$project->name})",
                'description' => "API Collection for managing tasks in project '{$project->name}' using WorkHub External Task API.",
                'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
            ],
            'variable' => [
                ['key' => 'base_url', 'value' => $baseUrl, 'type' => 'string'],
                ['key' => 'api_key', 'value' => $apiKey, 'type' => 'string'],
                ['key' => 'api_secret', 'value' => $apiSecret, 'type' => 'string'],
                ['key' => 'hmac_signature', 'value' => '', 'type' => 'string'],
            ],
            'event' => [
                [
                    'listen' => 'prerequest',
                    'script' => [
                        'type' => 'text/javascript',
                        'exec' => [
                            'const secretKey = pm.variables.get("api_secret") || pm.environment.get("api_secret");',
                            'let bodyToSign = "";',
                            'if (pm.request.body && pm.request.body.mode === "raw") {',
                            '    bodyToSign = pm.request.body.raw || "";',
                            '} else if (pm.request.body && pm.request.body.mode === "formdata") {',
                            '    let formObj = {};',
                            '    if (pm.request.body.formdata) {',
                            '        pm.request.body.formdata.each((item) => {',
                            '            if (!item.disabled && item.type !== "file" && item.key !== "api_key") {',
                            '                formObj[item.key] = item.value;',
                            '            }',
                            '        });',
                            '    }',
                            '    bodyToSign = Object.keys(formObj).length > 0 ? JSON.stringify(formObj) : "";',
                            '}',
                            'const signature = CryptoJS.HmacSHA256(bodyToSign, secretKey).toString(CryptoJS.enc.Hex);',
                            'pm.variables.set("hmac_signature", signature);',
                        ],
                    ],
                ],
            ],
            'item' => [
                [
                    'name' => '1. Get All Tasks',
                    'request' => [
                        'method' => 'GET',
                        'header' => [
                            ['key' => 'X-Api-Key', 'value' => '{{api_key}}', 'type' => 'text'],
                            ['key' => 'X-Api-Signature', 'value' => '{{hmac_signature}}', 'type' => 'text'],
                        ],
                        'url' => [
                            'raw' => '{{base_url}}/api/tasks?status=1',
                            'host' => ['{{base_url}}'],
                            'path' => ['api', 'tasks'],
                            'query' => [
                                ['key' => 'status', 'value' => '1'],
                            ],
                        ],
                    ],
                ],
                [
                    'name' => '2. Create Task (JSON Body)',
                    'request' => [
                        'method' => 'POST',
                        'header' => [
                            ['key' => 'Content-Type', 'value' => 'application/json', 'type' => 'text'],
                            ['key' => 'X-Api-Key', 'value' => '{{api_key}}', 'type' => 'text'],
                            ['key' => 'X-Api-Signature', 'value' => '{{hmac_signature}}', 'type' => 'text'],
                        ],
                        'body' => [
                            'mode' => 'raw',
                            'raw' => json_encode([
                                'title' => 'Bug in Checkout Flow',
                                'description' => 'Payment button failing on mobile',
                                'type' => 2,
                                'priority' => 4,
                                'status' => 1,
                                'image_url' => 'https://via.placeholder.com/600x400.png',
                            ], JSON_PRETTY_PRINT),
                        ],
                        'url' => [
                            'raw' => '{{base_url}}/api/tasks',
                            'host' => ['{{base_url}}'],
                            'path' => ['api', 'tasks'],
                        ],
                    ],
                ],
                [
                    'name' => '3. Create Task (Multipart Form File Upload)',
                    'request' => [
                        'method' => 'POST',
                        'header' => [
                            ['key' => 'X-Api-Key', 'value' => '{{api_key}}', 'type' => 'text'],
                            ['key' => 'X-Api-Signature', 'value' => '{{hmac_signature}}', 'type' => 'text'],
                        ],
                        'body' => [
                            'mode' => 'formdata',
                            'formdata' => [
                                ['key' => 'title', 'value' => 'Task via Form Upload', 'type' => 'text'],
                                ['key' => 'description', 'value' => 'Attached screenshot file', 'type' => 'text'],
                                ['key' => 'image', 'type' => 'file', 'src' => ''],
                            ],
                        ],
                        'url' => [
                            'raw' => '{{base_url}}/api/tasks',
                            'host' => ['{{base_url}}'],
                            'path' => ['api', 'tasks'],
                        ],
                    ],
                ],
                [
                    'name' => '4. Upload Image to Task',
                    'request' => [
                        'method' => 'POST',
                        'header' => [
                            ['key' => 'X-Api-Key', 'value' => '{{api_key}}', 'type' => 'text'],
                            ['key' => 'X-Api-Signature', 'value' => '{{hmac_signature}}', 'type' => 'text'],
                        ],
                        'body' => [
                            'mode' => 'formdata',
                            'formdata' => [
                                ['key' => 'image', 'type' => 'file', 'src' => ''],
                            ],
                        ],
                        'url' => [
                            'raw' => '{{base_url}}/api/tasks/1/images',
                            'host' => ['{{base_url}}'],
                            'path' => ['api', 'tasks', '1', 'images'],
                        ],
                    ],
                ],
            ],
        ];

        $filename = Str::slug($project->name).'-api-postman-collection.json';

        return response()->streamDownload(function () use ($collection) {
            echo json_encode($collection, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }, $filename, [
            'Content-Type' => 'application/json',
        ]);
    }
}
