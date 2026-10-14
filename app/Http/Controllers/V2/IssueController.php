<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IssueController extends Controller
{
    /**
     * Display modern V2 Issues & Bug Tracker.
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

        $companyIds = $authUser->companies()->pluck('company_id')->toArray();

        // Project filter / workspace scope
        $projectsQuery = Project::select('id', 'name', 'theme', 'company_id');
        if ($activeCompany) {
            $projectsQuery->where('company_id', $activeCompany->id);
        } else {
            $projectsQuery->where(function ($q) use ($authUser, $companyIds) {
                $q->whereIn('company_id', $companyIds)
                    ->orWhere(function ($sub) use ($authUser) {
                        $sub->whereNull('company_id')->where('user_id', $authUser->id);
                    });
            });
        }
        $projects = $projectsQuery->get();
        $projectIds = $projects->pluck('id')->toArray();

        // Query issues (Tasks representing bugs, improvements, features)
        $issuesQuery = Task::where(function ($q) use ($projectIds, $authUser) {
            $q->whereIn('project_id', $projectIds)
                ->orWhere(function ($sub) use ($authUser) {
                    $sub->whereNull('project_id')
                        ->where(function ($owner) use ($authUser) {
                            $owner->where('user_id', $authUser->id)
                                ->orWhere('assigned_to', $authUser->id);
                        });
                });
        })
            ->with(['project:id,name,theme', 'assignedUser:id,name,profile_image', 'user:id,name'])
            ->latest('id');

        $stateFilter = $request->query('state', 'all'); // 'all' | 'open' | 'closed'
        if ($stateFilter === 'open') {
            $issuesQuery->where('status', '!=', 3);
        } elseif ($stateFilter === 'closed') {
            $issuesQuery->where('status', 3);
        }

        $categoryFilter = $request->query('category');
        if ($categoryFilter) {
            $typeMap = [
                'bug' => Task::TYPE_BUG,
                'feature' => Task::TYPE_FEATURE,
                'improvement' => Task::TYPE_IMPROVEMENT,
                'security' => Task::TYPE_BUG,
            ];
            if (isset($typeMap[$categoryFilter])) {
                $issuesQuery->where('type', $typeMap[$categoryFilter]);
            }
        }

        $allTasks = $issuesQuery->get();

        $formattedIssues = $allTasks->map(function (Task $t) {
            $isClosed = (int) $t->status === 3;
            $priorityName = match ((int) $t->priority) {
                4 => 'Critical',
                3 => 'High',
                2 => 'Medium',
                default => 'Low',
            };

            $categoryName = match ((int) $t->type) {
                Task::TYPE_BUG => 'Bug',
                Task::TYPE_FEATURE => 'Feature',
                Task::TYPE_IMPROVEMENT => 'Improvement',
                default => 'Task',
            };

            return [
                'id' => $t->id,
                'code' => 'ISS-'.str_pad((string) $t->id, 4, '0', STR_PAD_LEFT),
                'title' => $t->title,
                'description' => trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($t->description ?: '')))) ?: '',
                'raw_description' => $t->description ?: '',
                'state' => $isClosed ? 'closed' : 'open',
                'status_id' => (int) $t->status,
                'priority' => $priorityName,
                'priority_id' => (int) $t->priority,
                'category' => $categoryName,
                'project_name' => $t->project ? $t->project->name : 'General Tasks',
                'project_id' => $t->project_id,
                'project_theme' => $t->project?->theme ?: '#10b981',
                'assignee' => $t->assignedUser ? [
                    'id' => $t->assignedUser->id,
                    'name' => $t->assignedUser->name,
                    'avatar' => strtoupper(substr($t->assignedUser->name, 0, 2)),
                    'profile_image' => $t->assignedUser->profile_image_url,
                ] : null,
                'reporter' => $t->user ? [
                    'id' => $t->user->id,
                    'name' => $t->user->name,
                ] : null,
                'created_at' => $t->created_at ? $t->created_at->format('M d, Y') : '',
                'created_at_human' => $t->created_at ? $t->created_at->diffForHumans() : '',
            ];
        })->values()->toArray();

        // Calculate KPI stats
        $totalCount = count($formattedIssues);
        $openCount = collect($formattedIssues)->where('state', 'open')->count();
        $closedCount = collect($formattedIssues)->where('state', 'closed')->count();
        $criticalCount = collect($formattedIssues)->where('priority', 'Critical')->where('state', 'open')->count();
        $bugsCount = collect($formattedIssues)->where('category', 'Bug')->where('state', 'open')->count();

        $stats = [
            'total' => $totalCount,
            'open' => $openCount,
            'closed' => $closedCount,
            'critical' => $criticalCount,
            'bugs' => $bugsCount,
            'current_workspace_name' => $currentWorkspaceName,
        ];

        // Format projects for dropdown
        $projectsDropdown = $projects->map(fn ($p) => [
            'id' => $p->id,
            'name' => $p->name,
        ])->values()->toArray();

        return Inertia::render('New/Issues', [
            'issues' => $formattedIssues,
            'stats' => $stats,
            'projects' => $projectsDropdown,
            'current_workspace_name' => $currentWorkspaceName,
            'state_filter' => $stateFilter,
        ]);
    }

    /**
     * Store a newly created issue/bug in V2.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|in:bug,feature,improvement,other',
            'priority' => 'required|string|in:low,medium,high,critical',
            'description' => 'required|string',
            'project_id' => 'nullable|exists:projects,id',
        ]);

        $typeMap = [
            'bug' => Task::TYPE_BUG,
            'feature' => Task::TYPE_FEATURE,
            'improvement' => Task::TYPE_IMPROVEMENT,
            'other' => 1,
        ];

        $priorityMap = [
            'low' => 1,
            'medium' => 2,
            'high' => 3,
            'critical' => 4,
        ];

        $authUser = auth()->user();

        Task::create([
            'title' => trim($request->input('title')),
            'description' => trim($request->input('description')),
            'status' => 1, // To Do / Open
            'priority' => $priorityMap[$request->input('priority')] ?? 2,
            'type' => $typeMap[$request->input('category')] ?? Task::TYPE_BUG,
            'project_id' => $request->input('project_id'),
            'user_id' => $authUser->id,
        ]);

        return redirect()->route('v2.issues.index')
            ->with('success', 'Issue reported successfully and queued for resolution.');
    }

    /**
     * Toggle issue resolution state.
     */
    public function toggle(Task $task)
    {
        $newStatus = (int) $task->status === 3 ? 1 : 3;
        $task->update([
            'status' => $newStatus,
        ]);

        $statusLabel = $newStatus === 3 ? 'resolved' : 're-opened';

        return redirect()->back()->with('success', "Issue {$statusLabel} successfully.");
    }
}
