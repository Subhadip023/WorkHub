<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\CompanyUsers;
use App\Models\Project;
use App\Services\NotificationService;

class ProjectController extends Controller
{
    public function __construct(private readonly NotificationService $notificationService)
    {
    }


    public function index()
    {
        $user = auth()->user();
        $companyIds = $user->companies()->pluck('company_id')->toArray();

        $projects = Project::select('id', 'name', 'slug', 'theme', 'status', 'priority', 'user_id', 'company_id')
            ->whereIn('company_id', $companyIds)
            ->orWhere(function ($query) use ($user) {
                $query->whereNull('company_id')->where('user_id', $user->id);
            })
            ->with([
                'company' => function ($query) {
                    $query->select('id', 'name');
                },
                'tasks' => function ($query) {
                    $query->select('id', 'project_id', 'status');
                },
            ])
            ->paginate(10);

        return view('projects.index')->with('projects', $projects);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $companies = auth()->user()->companies()->with('company')->get()->map(function ($cu) {
            return $cu->company;
        })->filter();

        return view('projects.create', compact('companies'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProjectRequest $request)
    {
        $data = $request->validated();
        $data['slug'] = str_replace(' ', '-', strtolower($data['name']));

        $company_id = $request->input('company_id');
        if ($company_id === 'personal' || empty($company_id)) {
            $data['company_id'] = null;
        } else {
            // Verify user belongs to this company
            $belongs = CompanyUsers::where('company_id', $company_id)
                ->where('user_id', auth()->id())
                ->exists();
            if (! $belongs) {
                abort(403);
            }
            $data['company_id'] = $company_id;
        }
        $data['user_id'] = auth()->id();

        $project = Project::create($data);

        // Send notification
        if ($project->company_id) {
            $members = CompanyUsers::where('company_id', $project->company_id)
                ->where('user_id', '!=', auth()->id())
                ->with('user')
                ->get();
            foreach ($members as $member) {
                if ($member->user) {
                    $this->notificationService->send(
                        $member->user,
                        'project_created',
                        'New Project Created',
                        "A new project '{$project->name}' has been created in your organization.",
                        $project->company_id,
                        ['project_id' => $project->id]
                    );
                }
            }
        } else {
            $this->notificationService->send(
                auth()->user(),
                'project_created',
                'New Personal Project',
                "You created a new personal project '{$project->name}'.",
                null,
                ['project_id' => $project->id]
            );
        }

        return redirect()->route('projects.index')->with('success', 'Project created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        $user_id = auth()->id();

        if ($project->company_id === null) {
            if ($project->user_id !== $user_id) {
                abort(403);
            }

            $project->load(['tasks' => function ($query) {
                $query->orderByRaw('due_date IS NULL, due_date ASC')->orderBy('priority', 'desc');
            }, 'tasks.assignedUser']);
            $companyUsers = collect([auth()->user()]);
            $user_role = 1; // Admin of their personal space
        } else {
            $membership = CompanyUsers::where('company_id', $project->company_id)
                ->where('user_id', $user_id)
                ->first();

            if (! $membership) {
                abort(403);
            }

            $project->load(['tasks' => function ($query) {
                $query->orderByRaw('due_date IS NULL, due_date ASC')->orderBy('priority', 'desc');
            }, 'tasks.assignedUser']);

            $companyUsers = CompanyUsers::where('company_id', $project->company_id)
                ->with('user')
                ->get()
                ->map(function ($cu) {
                    return $cu->user;
                })
                ->filter()
                ->values();

            $user_role = $membership->role;
        }

        return view('projects.show', compact('project', 'companyUsers', 'user_role'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project)
    {
        $user_id = auth()->id();
        if ($project->company_id === null) {
            if ($project->user_id !== $user_id) {
                abort(403);
            }
        } else {
            $membership = CompanyUsers::where('company_id', $project->company_id)
                ->where('user_id', $user_id)
                ->exists();
            if (! $membership) {
                abort(403);
            }
        }

        $companies = auth()->user()->companies()->with('company')->get()->map(function ($cu) {
            return $cu->company;
        })->filter();

        return view('projects.edit', compact('project', 'companies'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProjectRequest $request, Project $project)
    {
        $user_id = auth()->id();
        if ($project->company_id === null) {
            if ($project->user_id !== $user_id) {
                abort(403);
            }
        } else {
            $membership = CompanyUsers::where('company_id', $project->company_id)
                ->where('user_id', $user_id)
                ->exists();
            if (! $membership) {
                abort(403);
            }
        }

        $data = $request->validated();
        $data['slug'] = str_replace(' ', '-', strtolower($data['name']));

        $company_id = $request->input('company_id');
        if ($company_id === 'personal' || empty($company_id)) {
            $data['company_id'] = null;
        } else {
            // Verify user belongs to this company
            $belongs = CompanyUsers::where('company_id', $company_id)
                ->where('user_id', auth()->id())
                ->exists();
            if (! $belongs) {
                abort(403);
            }
            $data['company_id'] = $company_id;
        }

        $project->update($data);

        return redirect()->route('projects.index')->with('success', 'Project updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        $user_id = auth()->id();
        if ($project->company_id === null) {
            if ($project->user_id !== $user_id) {
                abort(403);
            }
        } else {
            $membership = CompanyUsers::where('company_id', $project->company_id)
                ->where('user_id', $user_id)
                ->first();
            if (! $membership || $membership->role !== 1) {
                abort(403, 'Only organization admins can delete projects.');
            }
        }

        $project->delete();

        return redirect()->route('projects.index')->with('success', 'Project deleted successfully');
    }
}
