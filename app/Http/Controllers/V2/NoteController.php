<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Note;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class NoteController extends Controller
{
    /**
     * Display the Notes & Documentation suite in V2 Inertia UI.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $companyId = session('current_company_id');
        $company = is_numeric($companyId) ? Company::find($companyId) : null;
        $companyIds = $user ? $user->companies()->pluck('company_id')->toArray() : [];

        $projectsQuery = Project::select('id', 'name')->where(function ($q) use ($user, $companyIds) {
            $q->whereNull('company_id')
                ->where('user_id', $user->id)
                ->orWhereIn('company_id', $companyIds);
        });

        if ($company) {
            $projectsQuery = Project::select('id', 'name')->where('company_id', $company->id);
            $currentWorkspaceName = $company->name;
        } else {
            $currentWorkspaceName = 'All Workspaces';
        }

        $projects = $projectsQuery->get();
        $projectIds = $projects->pluck('id')->toArray();

        $tasks = Task::select('id', 'title', 'project_id')->whereIn('project_id', $projectIds)->get();
        $taskIds = $tasks->pluck('id')->toArray();

        $organizations = $user->companies()->with(['company' => function ($query) {
            $query->select('id', 'name');
        }])->get()->pluck('company')->filter()->values();

        $notesQuery = Note::where(function ($query) use ($user, $projectIds, $taskIds, $companyIds, $company) {
            if ($company) {
                $query->where(function ($q) use ($projectIds) {
                    $q->where('note_type', Note::TYPE_PROJECT)
                        ->whereIn('note_type_id', $projectIds);
                })->orWhere(function ($q) use ($taskIds) {
                    $q->where('note_type', Note::TYPE_TASK)
                        ->whereIn('note_type_id', $taskIds);
                })->orWhere(function ($q) use ($company) {
                    $q->where('note_type', Note::TYPE_ORGANIZATION)
                        ->where('note_type_id', $company->id);
                });
            } else {
                $query->where(function ($q) use ($projectIds) {
                    $q->where('note_type', Note::TYPE_PROJECT)
                        ->whereIn('note_type_id', $projectIds);
                })->orWhere(function ($q) use ($taskIds) {
                    $q->where('note_type', Note::TYPE_TASK)
                        ->whereIn('note_type_id', $taskIds);
                })->orWhere(function ($q) use ($companyIds) {
                    $q->where('note_type', Note::TYPE_ORGANIZATION)
                        ->whereIn('note_type_id', $companyIds);
                })->orWhere(function ($q) use ($user) {
                    $q->where('note_type', Note::TYPE_PERSONAL)
                        ->where('note_type_id', $user->id);
                });
            }
        })->with(['user', 'comments.user'])->latest();

        $notes = $notesQuery->get();

        $personalCount = 0;
        $projectCount = 0;
        $taskCount = 0;
        $orgCount = 0;

        $formattedNotes = $notes->map(function (Note $n) use ($user, &$personalCount, &$projectCount, &$taskCount, &$orgCount) {
            $typeLabel = match ((int) $n->note_type) {
                Note::TYPE_PERSONAL => 'Personal',
                Note::TYPE_PROJECT => 'Project',
                Note::TYPE_TASK => 'Task',
                Note::TYPE_ORGANIZATION => 'Workspace',
                default => 'Note',
            };

            match ((int) $n->note_type) {
                Note::TYPE_PERSONAL => $personalCount++,
                Note::TYPE_PROJECT => $projectCount++,
                Note::TYPE_TASK => $taskCount++,
                Note::TYPE_ORGANIZATION => $orgCount++,
                default => null,
            };

            $noteableData = null;
            $noteable = $n->noteable;
            if ($noteable) {
                if ($n->note_type === Note::TYPE_PROJECT) {
                    $noteableData = [
                        'id' => $noteable->id,
                        'name' => $noteable->name,
                        'type' => 'Project',
                        'url' => route('v2.projects.index'),
                    ];
                } elseif ($n->note_type === Note::TYPE_TASK) {
                    $noteableData = [
                        'id' => $noteable->id,
                        'name' => $noteable->title,
                        'type' => 'Task',
                        'url' => route('v2.tasks.show', $noteable->id),
                    ];
                } elseif ($n->note_type === Note::TYPE_ORGANIZATION) {
                    $noteableData = [
                        'id' => $noteable->id,
                        'name' => $noteable->name,
                        'type' => 'Workspace',
                        'url' => route('v2.dashboard.org', $noteable->id),
                    ];
                } elseif ($n->note_type === Note::TYPE_PERSONAL) {
                    $noteableData = [
                        'id' => $user->id,
                        'name' => 'Personal Space',
                        'type' => 'Private',
                        'url' => '#',
                    ];
                }
            }

            $commentsFormatted = $n->comments->map(function ($c) use ($user) {
                return [
                    'id' => $c->id,
                    'user' => [
                        'id' => $c->user->id,
                        'name' => $c->user->name,
                        'avatar' => strtoupper(substr($c->user->name, 0, 2)),
                        'profile_image' => $c->user->profile_image_url,
                    ],
                    'time' => $c->created_at ? $c->created_at->diffForHumans() : '',
                    'created_at' => $c->created_at?->format('M d, Y H:i'),
                    'text' => $c->content,
                    'can_delete' => $user ? Gate::allows('delete', $c) : false,
                ];
            })->values()->toArray();

            return [
                'id' => $n->id,
                'title' => $n->title,
                'description' => $n->description ?: '',
                'preview_text' => trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($n->description ?: '')))) ?: '',
                'note_type' => (int) $n->note_type,
                'note_type_name' => $typeLabel,
                'note_type_id' => $n->note_type_id,
                'noteable' => $noteableData,
                'author' => [
                    'id' => $n->user?->id,
                    'name' => $n->user?->name ?? 'Unknown',
                    'avatar' => strtoupper(substr($n->user?->name ?? 'UN', 0, 2)),
                    'profile_image' => $n->user?->profile_image_url,
                    'email' => $n->user?->email,
                ],
                'comments_count' => $n->comments->count(),
                'comments' => $commentsFormatted,
                'created_at' => $n->created_at?->format('M d, Y'),
                'created_at_human' => $n->created_at?->diffForHumans(),
                'updated_at' => $n->updated_at?->format('M d, Y'),
                'updated_at_human' => $n->updated_at?->diffForHumans(),
                'can_edit' => $user ? Gate::allows('update', $n) : false,
                'can_delete' => $user ? Gate::allows('delete', $n) : false,
                'pdf_url' => route('notes.pdf', $n),
            ];
        })->values()->toArray();

        $stats = [
            'total' => count($formattedNotes),
            'personal' => $personalCount,
            'projects' => $projectCount,
            'tasks' => $taskCount,
            'workspace' => $orgCount,
        ];

        return Inertia::render('New/Notes', [
            'initial_notes' => $formattedNotes,
            'stats' => $stats,
            'projects' => $projects->map(fn ($p) => ['id' => $p->id, 'name' => $p->name])->values()->toArray(),
            'tasks' => $tasks->map(fn ($t) => ['id' => $t->id, 'name' => $t->title, 'project_id' => $t->project_id])->values()->toArray(),
            'organizations' => $organizations->map(fn ($o) => ['id' => $o->id, 'name' => $o->name])->values()->toArray(),
            'current_workspace_name' => $currentWorkspaceName,
        ]);
    }

    /**
     * Store a new note via V2 API / Inertia form.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'note_type' => 'required|integer|in:1,2,3,4',
            'note_type_id' => 'nullable|integer',
        ]);

        $user = auth()->user();
        $noteType = (int) $request->note_type;
        $noteTypeId = $request->note_type_id;

        if ($noteType === Note::TYPE_PERSONAL) {
            $noteTypeId = $user->id;
        } elseif ($noteType === Note::TYPE_PROJECT) {
            $project = Project::findOrFail($noteTypeId);
            Gate::authorize('view', $project);
        } elseif ($noteType === Note::TYPE_TASK) {
            $task = Task::findOrFail($noteTypeId);
            Gate::authorize('view', $task);
        } elseif ($noteType === Note::TYPE_ORGANIZATION) {
            $company = Company::findOrFail($noteTypeId);
            Gate::authorize('view', $company);
        }

        Note::create([
            'user_id' => $user->id,
            'title' => $request->title,
            'description' => $request->description,
            'note_type' => $noteType,
            'note_type_id' => $noteTypeId,
        ]);

        return redirect()->route('v2.notes.index')->with('success', 'Note created successfully.');
    }

    /**
     * Update an existing note via V2 API / Inertia form.
     */
    public function update(Request $request, Note $note)
    {
        Gate::authorize('update', $note);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        $note->update([
            'title' => $request->title,
            'description' => $request->description,
        ]);

        return redirect()->route('v2.notes.index')->with('success', 'Note updated successfully.');
    }

    /**
     * Remove the specified note.
     */
    public function destroy(Note $note)
    {
        Gate::authorize('delete', $note);

        $note->delete();

        return redirect()->route('v2.notes.index')->with('success', 'Note deleted successfully.');
    }
}
