<?php

namespace App\Policies;

use App\Models\Note;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class NotePolicy
{
    /**
     * Determine whether the user can view the note.
     */
    public function view(User $user, Note $note): bool
    {
        $noteType = (int) $note->note_type;
        $noteTypeId = $note->note_type_id;

        if ($noteType === Note::TYPE_PERSONAL) {
            return $noteTypeId === $user->id;
        }

        if ($noteType === Note::TYPE_PROJECT) {
            $project = Project::find($noteTypeId);
            if (! $project) {
                return false;
            }
            if ($project->company_id) {
                return $user->companies->contains('company_id', $project->company_id);
            }

            return $project->user_id === $user->id;
        }

        if ($noteType === Note::TYPE_TASK) {
            $task = Task::find($noteTypeId);
            if (! $task) {
                return false;
            }
            $project = $task->project;
            if (! $project) {
                // Personal task with no project
                return ($task->user_id === $user->id) || ($task->assigned_to === $user->id);
            }
            if ($project->company_id) {
                return $user->companies->contains('company_id', $project->company_id);
            }

            return $project->user_id === $user->id;
        }

        if ($noteType === Note::TYPE_ORGANIZATION) {
            return $user->companies->contains('company_id', $noteTypeId);
        }

        return false;
    }

    /**
     * Determine whether the user can update the note (Note Owner Only).
     */
    public function update(User $user, Note $note): bool
    {
        return $note->user_id === $user->id ||
               ((int) $note->note_type === Note::TYPE_PERSONAL && (int) $note->note_type_id === $user->id);
    }

    /**
     * Determine whether the user can delete the note (Note Owner Only).
     */
    public function delete(User $user, Note $note): bool
    {
        return $this->update($user, $note);
    }
}
