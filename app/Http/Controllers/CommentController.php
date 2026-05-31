<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Company;
use App\Models\CompanyUsers;
use App\Models\Note;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * Store a newly created comment in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:5000',
            'commentable_type' => 'required|string|in:task,project,company,note',
            'commentable_id' => 'required|integer',
        ]);

        $this->checkAccess($validated['commentable_type'], $validated['commentable_id']);

        Comment::create([
            'user_id' => auth()->id(),
            'content' => $validated['content'],
            'commentable_type' => $validated['commentable_type'],
            'commentable_id' => $validated['commentable_id'],
        ]);

        return redirect()->back()->with('success', 'Comment added successfully.');
    }

    /**
     * Remove the specified comment from storage.
     */
    public function destroy(Comment $comment)
    {
        if ($comment->user_id !== auth()->id()) {
            abort(403, 'Unauthorized.');
        }

        $comment->delete();

        return redirect()->back()->with('success', 'Comment deleted successfully.');
    }

    /**
     * Perform context authorization checks for the commentable model.
     *
     * @return Task|Project|Company|Note
     */
    protected function checkAccess(string $type, int $id)
    {
        $user = auth()->user();
        if (! $user) {
            abort(401);
        }

        switch ($type) {
            case 'company':
                $company = Company::find($id);
                if (! $company) {
                    abort(404);
                }
                $isMember = CompanyUsers::where('company_id', $company->id)
                    ->where('user_id', $user->id)
                    ->exists();
                if (! $isMember) {
                    abort(403);
                }

                return $company;

            case 'project':
                $project = Project::find($id);
                if (! $project) {
                    abort(404);
                }
                if ($project->company_id === null) {
                    if ($project->user_id !== $user->id) {
                        abort(403);
                    }
                } else {
                    $isMember = CompanyUsers::where('company_id', $project->company_id)
                        ->where('user_id', $user->id)
                        ->exists();
                    if (! $isMember) {
                        abort(403);
                    }
                }

                return $project;

            case 'task':
                $task = Task::find($id);
                if (! $task) {
                    abort(404);
                }
                $this->checkAccess('project', $task->project_id);

                return $task;

            case 'note':
                $note = Note::find($id);
                if (! $note) {
                    abort(404);
                }
                switch ($note->note_type) {
                    case Note::TYPE_PROJECT:
                        $this->checkAccess('project', $note->note_type_id);
                        break;
                    case Note::TYPE_TASK:
                        $this->checkAccess('task', $note->note_type_id);
                        break;
                    case Note::TYPE_ORGANIZATION:
                        $this->checkAccess('company', $note->note_type_id);
                        break;
                    case Note::TYPE_PERSONAL:
                        if ($note->note_type_id !== $user->id) {
                            abort(403);
                        }
                        break;
                    default:
                        abort(403);
                }

                return $note;

            default:
                abort(400);
        }
    }
}
