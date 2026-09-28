<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Company;
use App\Models\CompanyUsers;
use App\Models\Project;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ProjectController extends Controller
{
    public function __construct(private readonly NotificationService $notificationService) {}

    public function index(Request $request)
    {
        $user = auth()->user();
        $companyId = session('current_company_id');
        $company = is_numeric($companyId) ? Company::find($companyId) : null;
        $companyIds = $user ? $user->companies()->pluck('company_id')->toArray() : [];

        if ($company) {
            $projectsQuery = Project::where('company_id', $company->id);
            $currentWorkspaceName = $company->name;
        } else {
            $projectsQuery = Project::where(function ($q) use ($user, $companyIds) {
                $q->whereIn('company_id', $companyIds);
                if ($user) {
                    $q->orWhere(function ($sub) use ($user) {
                        $sub->whereNull('company_id')->where('user_id', $user->id);
                    });
                }
            });
            $currentWorkspaceName = 'All Workspaces';
        }

        // Support legacy blade view if explicitly requested
        if ($request->has('legacy')) {
            $projects = (clone $projectsQuery)->with([
                'company:id,name',
                'tasks:id,project_id,status',
            ])->paginate(10);

            return view('projects.index')->with('projects', $projects);
        }

        $projects = $projectsQuery->with([
            'company:id,name',
            'user:id,name,profile_image',
            'tasks' => function ($query) {
                $query->select('id', 'title', 'project_id', 'status', 'priority', 'due_date', 'assigned_to')
                    ->with('assignedUser:id,name,profile_image');
            },
        ])->latest()->get();

        $userCompanies = $user ? $user->companies()->with('company')->get()->map(function ($cu) {
            return [
                'id' => $cu->company?->id,
                'name' => $cu->company->name ?? '',
            ];
        })->filter(fn ($item) => ! empty($item['id']))->values()->toArray() : [];

        $totalTasksCount = 0;
        $completedTasksCount = 0;
        $inProgressProjectsCount = 0;
        $completedProjectsCount = 0;
        $onHoldProjectsCount = 0;

        $formattedProjects = $projects->map(function (Project $p) use ($user, &$totalTasksCount, &$completedTasksCount, &$inProgressProjectsCount, &$completedProjectsCount, &$onHoldProjectsCount) {
            $tasks = $p->tasks;
            $pTotal = $tasks->count();
            $pCompleted = $tasks->where('status', 3)->count();
            $totalTasksCount += $pTotal;
            $completedTasksCount += $pCompleted;

            $progress = $pTotal > 0 ? (int) round(($pCompleted / $pTotal) * 100) : 0;

            $statusName = match ((int) $p->status) {
                3 => 'Completed',
                2 => 'In Progress',
                4 => 'On Hold',
                default => 'To Do',
            };

            if ((int) $p->status === 3) {
                $completedProjectsCount++;
            } elseif ((int) $p->status === 4) {
                $onHoldProjectsCount++;
            } else {
                $inProgressProjectsCount++;
            }

            $priorityName = match ((int) $p->priority) {
                4 => 'Urgent',
                3 => 'High',
                2 => 'Medium',
                default => 'Low',
            };

            $assignedUsers = $tasks->pluck('assignedUser')->filter()->unique('id');
            $teamMembers = $assignedUsers->map(function ($u) {
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'avatar' => strtoupper(substr($u->name, 0, 2)),
                    'profile_image' => $u->profile_image_url,
                ];
            })->values()->toArray();

            $earliestDueDate = $tasks->where('status', '!=', 3)
                ->whereNotNull('due_date')
                ->sortBy('due_date')
                ->first()?->due_date;

            $formattedDueDate = $earliestDueDate
                ? Carbon::parse($earliestDueDate)->format('M d, Y')
                : ($p->created_at ? $p->created_at->format('M d, Y') : 'No deadline');

            $canEdit = $user && ($p->company_id === null ? $p->user_id === $user->id : $user->companies->contains('company_id', $p->company_id));
            $canDelete = false;
            if ($user) {
                if ($p->company_id === null) {
                    $canDelete = $p->user_id === $user->id;
                } else {
                    $membership = $user->companies()->where('company_id', $p->company_id)->first();
                    $canDelete = $membership && (int) $membership->role === 1;
                }
            }

            return [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'description' => trim(strip_tags($p->description ?: 'No description provided.')),
                'raw_description' => $p->description ?: '',
                'theme' => $p->theme ?: '#10b981',
                'status' => $statusName,
                'status_id' => (int) $p->status,
                'priority' => $priorityName,
                'priority_id' => (int) $p->priority,
                'progress' => $progress,
                'dueDate' => $formattedDueDate,
                'completedTasks' => $pCompleted,
                'totalTasks' => $pTotal,
                'tag' => $p->theme ?: ($p->company ? $p->company->name : 'Personal'),
                'category' => $p->company ? $p->company->name : 'Personal Space',
                'company_id' => $p->company_id,
                'company_name' => $p->company?->name,
                'teamMembers' => $teamMembers,
                'can_edit' => $canEdit,
                'can_delete' => $canDelete,
                'created_at' => $p->created_at?->format('M d, Y'),
            ];
        })->toArray();

        $stats = [
            'total_projects' => count($projects),
            'in_progress' => $inProgressProjectsCount,
            'completed' => $completedProjectsCount,
            'on_hold' => $onHoldProjectsCount,
            'total_tasks' => $totalTasksCount,
            'completed_tasks' => $completedTasksCount,
            'completion_rate' => $totalTasksCount > 0 ? (int) round(($completedTasksCount / $totalTasksCount) * 100) : 100,
        ];

        return Inertia::render('New/Projects', [
            'initial_projects' => $formattedProjects,
            'companies' => $userCompanies,
            'stats' => $stats,
            'current_workspace_name' => $currentWorkspaceName,
        ]);
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
        $baseSlug = Str::slug($data['name']) ?: 'project';
        $slug = $baseSlug;
        $counter = 1;
        while (Project::where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter++;
        }
        $data['slug'] = $slug;

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

        $perPage = (int) $request->input('per_page', 5);
        $tasksQuery->orderByRaw('due_date IS NULL, due_date ASC')->orderBy('id', 'desc')->orderBy('priority', 'desc');
        $tasks = $tasksQuery->paginate($perPage)->withQueryString();

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
        $baseSlug = Str::slug($data['name']) ?: 'project';
        $slug = $baseSlug;
        $counter = 1;
        while (Project::where('slug', $slug)->where('id', '!=', $project->id)->exists()) {
            $slug = $baseSlug.'-'.$counter++;
        }
        $data['slug'] = $slug;

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
