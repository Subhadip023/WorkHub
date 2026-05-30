<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Project;
use App\Models\Task;
use App\Models\Company;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class NoteController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        $companyIds = $user->companies->pluck('company_id')->toArray();
        $projectIds = Project::whereNull('company_id')
            ->where('user_id', $user->id)
            ->orWhereIn('company_id', $companyIds)
            ->pluck('id')
            ->toArray();
            
        $taskIds = Task::whereIn('project_id', $projectIds)->pluck('id')->toArray();

        $notes = Note::where(function ($query) use ($user, $projectIds, $taskIds, $companyIds) {
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
        })->latest()->paginate(10);

        return view('notes.index', compact('notes'));
    }

    public function create(Request $request)
    {
        $user = auth()->user();
        $companyIds = $user->companies->pluck('company_id')->toArray();
        
        $projects = Project::where(function($q) use ($user, $companyIds) {
            $q->whereNull('company_id')
              ->where('user_id', $user->id)
              ->orWhereIn('company_id', $companyIds);
        })->get();
            
        $projectIds = $projects->pluck('id')->toArray();
        $tasks = Task::whereIn('project_id', $projectIds)->get();
        $organizations = $user->companies()->with('company')->get()->pluck('company')->filter();

        // Optional query parameters for pre-population
        $defaultType = $request->query('note_type', Note::TYPE_PERSONAL);
        $defaultTypeId = $request->query('note_type_id');

        return view('notes.create', compact('projects', 'tasks', 'organizations', 'defaultType', 'defaultTypeId'));
    }

    public function show(Note $note)
    {
        $user = auth()->user();
        $this->authorizeNoteAccess($note, $user);

        return view('notes.show', compact('note'));
    }

    public function downloadPdf(Note $note)
    {
        $user = auth()->user();
        $this->authorizeNoteAccess($note, $user);

        $joyPixels = new \JoyPixels\Client();
        $noteDescription = $joyPixels->toImage($note->description);

        $pdf = Pdf::loadView('notes.pdf', compact('note', 'noteDescription'))
            ->setOption('isRemoteEnabled', true);
        
        return $pdf->stream(Str::slug($note->title) . '.pdf');
    }

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
            if ($project->company_id) {
                if (!$user->companies->contains('company_id', $project->company_id)) {
                    abort(403);
                }
            } else {
                if ($project->user_id !== $user->id) {
                    abort(403);
                }
            }
        } elseif ($noteType === Note::TYPE_TASK) {
            $task = Task::findOrFail($noteTypeId);
            $project = $task->project;
            if ($project->company_id) {
                if (!$user->companies->contains('company_id', $project->company_id)) {
                    abort(403);
                }
            } else {
                if ($project->user_id !== $user->id) {
                    abort(403);
                }
            }
        } elseif ($noteType === Note::TYPE_ORGANIZATION) {
            if (!$user->companies->contains('company_id', $noteTypeId)) {
                abort(403);
            }
        }

        Note::create([
            'user_id' => $user->id,
            'title' => $request->title,
            'description' => $request->description,
            'note_type' => $noteType,
            'note_type_id' => $noteTypeId,
        ]);

        // If the request came from project show or task show, redirect back
        if ($request->has('redirect_back')) {
            return redirect($request->input('redirect_back'))->with('success', 'Note created successfully.');
        }

        return redirect()->route('notes.index')->with('success', 'Note created successfully.');
    }

    public function edit(Note $note)
    {
        $user = auth()->user();
        $this->authorizeNoteAccess($note, $user);

        $companyIds = $user->companies->pluck('company_id')->toArray();
        $projects = Project::where(function($q) use ($user, $companyIds) {
            $q->whereNull('company_id')
              ->where('user_id', $user->id)
              ->orWhereIn('company_id', $companyIds);
        })->get();
            
        $projectIds = $projects->pluck('id')->toArray();
        $tasks = Task::whereIn('project_id', $projectIds)->get();
        $organizations = $user->companies()->with('company')->get()->pluck('company')->filter();

        return view('notes.edit', compact('note', 'projects', 'tasks', 'organizations'));
    }

    public function update(Request $request, Note $note)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        $user = auth()->user();
        $this->authorizeNoteAccess($note, $user);

        $note->update([
            'title' => $request->title,
            'description' => $request->description,
        ]);

        if ($request->has('redirect_back')) {
            return redirect($request->input('redirect_back'))->with('success', 'Note updated successfully.');
        }

        return redirect()->route('notes.index')->with('success', 'Note updated successfully.');
    }

    public function destroy(Note $note)
    {
        $user = auth()->user();
        $this->authorizeNoteAccess($note, $user);

        $note->delete();

        return redirect()->back()->with('success', 'Note deleted successfully.');
    }

    protected function authorizeNoteAccess(Note $note, $user)
    {
        $noteType = (int) $note->note_type;
        $noteTypeId = $note->note_type_id;

        if ($noteType === Note::TYPE_PERSONAL) {
            if ($noteTypeId !== $user->id) {
                abort(403);
            }
        } elseif ($noteType === Note::TYPE_PROJECT) {
            $project = Project::findOrFail($noteTypeId);
            if ($project->company_id) {
                if (!$user->companies->contains('company_id', $project->company_id)) {
                    abort(403);
                }
            } else {
                if ($project->user_id !== $user->id) {
                    abort(403);
                }
            }
        } elseif ($noteType === Note::TYPE_TASK) {
            $task = Task::findOrFail($noteTypeId);
            $project = $task->project;
            if ($project->company_id) {
                if (!$user->companies->contains('company_id', $project->company_id)) {
                    abort(403);
                }
            } else {
                if ($project->user_id !== $user->id) {
                    abort(403);
                }
            }
        } elseif ($noteType === Note::TYPE_ORGANIZATION) {
            if (!$user->companies->contains('company_id', $noteTypeId)) {
                abort(403);
            }
        }
    }
}
