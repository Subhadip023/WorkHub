<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Company;
use App\Models\Note;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

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
        Gate::authorize('delete', $comment);

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
                $company = Company::findOrFail($id);
                Gate::authorize('view', $company);

                return $company;

            case 'project':
                $project = Project::findOrFail($id);
                Gate::authorize('view', $project);

                return $project;

            case 'task':
                $task = Task::findOrFail($id);
                Gate::authorize('view', $task);

                return $task;

            case 'note':
                $note = Note::findOrFail($id);
                Gate::authorize('view', $note);

                return $note;

            default:
                abort(400);
        }
    }
}
