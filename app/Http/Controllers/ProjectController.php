<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Company;
use App\Models\CompanyUsers;
use App\Models\Project;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    public function __construct(private readonly NotificationService $notificationService) {}

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
        $data['slug'] = Str::slug($data['name']); // #13: Handles special chars correctly

        $company_id = $request->input('company_id');
        if ($company_id === 'personal' || empty($company_id)) {
            $data['company_id'] = null;
        } else {
            $company = Company::findOrFail($company_id);
            Gate::authorize('view', $company);
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
                        ['project_id' => $project->id, 'url' => route('projects.show', $project->id)]
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
                ['project_id' => $project->id, 'url' => route('projects.show', $project->id)]
            );
        }

        return redirect()->route('projects.index')->with('success', 'Project created successfully');
    }

    /**
     * Display notes for the specified project.
     */
    public function notes(Project $project)
    {
        Gate::authorize('view', $project);

        $notes = $project->notes()->with('user')->latest()->get();
        $comments = $project->getCachedComments();

        return view('projects.notes', compact('project', 'notes', 'comments'));
    }

    /**
     * Display credentials for the specified project.
     */
    public function credentials(Project $project)
    {
        Gate::authorize('view', $project);

        $credentials = $project->credentials()->latest()->get();
        $comments = $project->getCachedComments();

        return view('projects.credentials', compact('project', 'credentials', 'comments'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, Request $request)
    {
        $user_id = auth()->id();

        Gate::authorize('view', $project);

        log_activity(
            description: "Viewed project '{$project->name}'",
            event: 'viewed',
            subject: $project
        );

        if ($project->company_id === null) {
            $companyUsers = collect([auth()->user()]);
            $user_role = 1; // Admin of their personal space
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

        // Build tasks query with filters
        $tasksQuery = $project->tasks()
            ->select('id', 'title', 'status', 'points', 'priority', 'type', 'due_date', 'project_id', 'assigned_to', 'user_id', 'created_at', 'updated_at')
            ->with('assignedUser');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $tasksQuery->where('title', 'like', '%'.$search.'%');
        }

        if ($request->filled('priority')) {
            $tasksQuery->where('priority', $request->input('priority'));
        }

        if ($request->filled('status')) {
            $tasksQuery->where('status', $request->input('status'));
        }

        if ($request->filled('type')) {
            $tasksQuery->where('type', $request->input('type'));
        }

        $showCompleted = $request->input('show_completed') === 'true';
        if (! $showCompleted && $request->input('status') != 3) {
            $tasksQuery->where('status', '!=', 3);
        }

        $tasksQuery->orderByRaw('due_date IS NULL, due_date ASC')->orderBy('priority', 'desc');
        $tasks = $tasksQuery->get();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('projects.partials.tasks_table', compact('project', 'tasks', 'companyUsers'))->render(),
            ]);
        }

        $comments = $project->getCachedComments();

        $totalTasks = $project->tasks()->count();
        $completedTasks = $project->tasks()->where('status', 3)->count();
        $percentage = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

        return view('projects.show', compact('project', 'companyUsers', 'user_role', 'comments', 'tasks', 'totalTasks', 'completedTasks', 'percentage'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project)
    {
        Gate::authorize('update', $project);

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
        Gate::authorize('update', $project);

        $data = $request->validated();
        $data['slug'] = Str::slug($data['name']); // #13: Handles special chars correctly

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
        Gate::authorize('delete', $project);

        $project->delete();

        return redirect()->route('projects.index')->with('success', 'Project deleted successfully');
    }
}
