<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('q', '');
        $user = auth()->user();

        // If query is empty, return empty results
        if (trim($query) === '') {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'projects' => [],
                    'tasks' => [],
                    'notes' => [],
                    'users' => [],
                ]);
            }

            return view('search.results', [
                'query' => $query,
                'projects' => collect(),
                'tasks' => collect(),
                'notes' => collect(),
                'users' => collect(),
            ]);
        }

        // Get user companies, projects, tasks for access control
        $companyIds = $user->companies->pluck('company_id')->toArray();

        $projectIds = Project::whereNull('company_id')
            ->where('user_id', $user->id)
            ->orWhereIn('company_id', $companyIds)
            ->pluck('id')
            ->toArray();

        $taskIds = Task::whereIn('project_id', $projectIds)->pluck('id')->toArray();

        $isAjax = $request->expectsJson() || $request->ajax();
        $limit = $isAjax ? 5 : 15;

        // 1. Projects search
        $projects = Project::whereIn('id', $projectIds)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            })
            ->limit($limit)
            ->get();

        // 2. Tasks search
        $tasks = Task::where(function ($q) use ($projectIds, $user) {
            $q->whereIn('project_id', $projectIds)
                ->orWhere(function ($sub) use ($user) {
                    $sub->whereNull('project_id')
                        ->where(function ($inner) use ($user) {
                            $inner->where('user_id', $user->id)
                                ->orWhere('assigned_to', $user->id);
                        });
                });
        })
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            })
            ->limit($limit)
            ->get();

        // 3. Notes search
        $notes = Note::where(function ($q) use ($user, $projectIds, $taskIds, $companyIds) {
            $q->where(function ($sub) use ($projectIds) {
                $sub->where('note_type', Note::TYPE_PROJECT)
                    ->whereIn('note_type_id', $projectIds);
            })->orWhere(function ($sub) use ($taskIds) {
                $sub->where('note_type', Note::TYPE_TASK)
                    ->whereIn('note_type_id', $taskIds);
            })->orWhere(function ($sub) use ($companyIds) {
                $sub->where('note_type', Note::TYPE_ORGANIZATION)
                    ->whereIn('note_type_id', $companyIds);
            })->orWhere(function ($sub) use ($user) {
                $sub->where('note_type', Note::TYPE_PERSONAL)
                    ->where('note_type_id', $user->id);
            });
        })
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            })
            ->limit($limit)
            ->get();

        // 4. Users (Team Members) search
        $users = User::where(function ($q) use ($companyIds, $user) {
            // Users from the same companies
            $q->whereHas('companies', function ($sub) use ($companyIds) {
                $sub->whereIn('company_id', $companyIds);
            })
            // Or user themselves
                ->orWhere('id', $user->id);
        })
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%");
            })
            ->limit($limit)
            ->get();

        // If AJAX / JSON request, format output for the autocomplete dropdown
        if ($isAjax) {
            return response()->json([
                'projects' => $projects->map(function ($project) {
                    return [
                        'id' => $project->id,
                        'title' => $project->name,
                        'url' => route('projects.show', $project),
                        'theme' => $project->theme,
                    ];
                }),
                'tasks' => $tasks->map(function ($task) {
                    return [
                        'id' => $task->id,
                        'title' => $task->title,
                        'url' => route('tasks.show', $task),
                        'status' => $task->status,
                    ];
                }),
                'notes' => $notes->map(function ($note) {
                    return [
                        'id' => $note->id,
                        'title' => $note->title,
                        'url' => route('notes.show', $note),
                    ];
                }),
                'users' => $users->map(function ($u) {
                    return [
                        'id' => $u->id,
                        'title' => $u->name,
                        'email' => $u->email,
                        'profile_image' => $u->profile_image ? asset('storage/'.$u->profile_image) : null,
                    ];
                }),
            ]);
        }

        // Otherwise return the full HTML results page
        return view('search.results', compact('query', 'projects', 'tasks', 'notes', 'users'));
    }
}
