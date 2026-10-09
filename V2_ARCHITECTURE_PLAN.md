# WorkHub Dual-Architecture Plan: Classic (v1) & Modern (v2)

This document outlines the architectural plan to cleanly separate the **Classic Blade UI (`/`)** and the **Modern Inertia React UI (`/v2/`)** within WorkHub, ensuring zero regressions for existing workflows while enabling rapid iteration on the new user experience.

---

## 1. Executive Summary & Objective

WorkHub is transitioning from a traditional Laravel Blade + Bootstrap/SB Admin application to an Inertia.js + React + Tailwind CSS single-page experience.

To guarantee complete stability in production:
- **Classic Version (`/`)**: Retains all existing Blade views (`/dashboard`, `/projects`, `/tasks`, `/notes`). Fully functional, reliable, and untouched by new frontend dependencies.
- **Modern Version (`/v2/`)**: Houses the next-generation interface (`/v2/dashboard`, `/v2/projects`, `/v2/tasks`, `/v2/notes`), powered by Inertia.js, React 18, and `DashboardLayout`.
- **Bidirectional UI Switcher**: Users can seamlessly jump between Classic and v2 with a single click.

---

## 2. Route Architecture Matrix

| Feature | Classic Route (`v1`) | Rendering Engine | Modern Route (`v2`) | Rendering Engine |
| :--- | :--- | :--- | :--- | :--- |
| **Dashboard** | `/dashboard` | Blade (`dashboard.blade.php`) | `/v2/dashboard` | Inertia (`New/Dashboard.jsx`) |
| **Workspace Dashboard** | `/dashboard/{company}` | Blade (`dashboard.blade.php`) | `/v2/dashboard/{company}` | Inertia (`New/Dashboard.jsx`) |
| **Projects** | `/projects` | Blade (`projects/index.blade.php`) | `/v2/projects` | Inertia (`New/Projects.jsx`) |
| **Tasks Suite** | `/tasks` | Blade (`tasks/index.blade.php`) | `/v2/tasks` | Inertia (`New/Tasks.jsx`) |
| **Task Detail** | `/tasks/{task}` | Blade (`tasks/show.blade.php`) | `/v2/tasks/{task}` | Inertia (`New/TaskView.jsx`) |
| **Notes & Docs** | `/notes` | Blade (`notes/index.blade.php`) | `/v2/notes` | Inertia (`New/Notes.jsx`) |
| **Note Detail** | `/notes/{note}` | Blade (`notes/show.blade.php`) | `/v2/notes/{note}` | Inertia (`New/Notes.jsx` / Drawer) |
| **Analytics** | `/analytics` | Inertia / Blade legacy | `/v2/analytics` | Inertia (`New/Analytics.jsx`) |
| **Routes Explorer** | `/routes` | Inertia / Blade legacy | `/v2/routes` | Inertia (`New/Routes.jsx`) |

---

## 3. Controller Architecture & Separation of Concerns

### Problem with Current State
Controllers like `ProjectController` and `TaskController` currently contain messy runtime conditionals:
```php
// Anti-pattern currently in place:
if ($request->has('legacy')) {
    return view('projects.index', ...);
}
return Inertia::render('New/Projects', ...);
```
This couples two completely different frontend representations into the same controller methods.

### Target Architecture
Separate V2 controllers into a dedicated namespace: `App\Http\Controllers\V2\`.

```
app/Http/Controllers/
├── DashboardController.php         # Classic Blade: returns view('dashboard')
├── ProjectController.php           # Classic Blade: returns view('projects.*')
├── TaskController.php              # Classic Blade: returns view('tasks.*')
├── NoteController.php              # Classic Blade: returns view('notes.*')
└── V2/
    ├── DashboardController.php     # V2 Inertia: returns Inertia::render('New/Dashboard')
    ├── ProjectController.php       # V2 Inertia: returns Inertia::render('New/Projects')
    ├── TaskController.php          # V2 Inertia: returns Inertia::render('New/Tasks')
    ├── NoteController.php          # V2 Inertia: returns Inertia::render('New/Notes')
    └── AnalyticsController.php     # V2 Inertia: returns Inertia::render('New/Analytics')
```

#### Advantages:
1. **Single Responsibility**: Classic controllers only worry about Blade variables and paginated Eloquent collections. V2 controllers only worry about structured JSON serialization and Inertia props.
2. **Zero Risk to Production**: Bugfixes or changes in V2 cannot break Classic Blade views.
3. **Clean Code**: No more `?legacy=1` workarounds.

---

## 4. Notes & Documentation Module (V2 Specification)

The user requested: *"make the note and docs according to new UI"*.

### V2 Notes Component Features (`New/Notes.jsx`)
- **Category Tabs**: Instant switching across *All Notes*, *Personal Space*, *Project Notes*, *Task Notes*, and *Workspace / Organization*.
- **Live Search & Tags**: Filter notes in real-time by title, content, author, and associated project/task.
- **Markdown / Rich Document Viewer**: Clean, distraction-free reading pane with syntax highlighting, task checkboxes, and typography hierarchy.
- **Integrated Creation & Editing Modal**: Modal with markdown toolbar, category dropdowns (dynamically loading user's projects/tasks), and immediate reactivity.
- **Export & Actions**: Download as PDF (`/notes/{note}/pdf`), copy link, edit, and delete with confirmation dialog.
- **Discussion Thread**: Side drawer or tab displaying nested comments powered by the existing `Comment` model.

---

## 5. UI Version Switcher Design

### In Classic UI (Blade Topbar)
Add a highlighted button in `resources/views/partials/topbar.blade.php`:
```html
<a href="{{ route('v2.dashboard') }}" class="btn btn-sm btn-outline-primary font-weight-bold ml-2 shadow-sm d-flex align-items-center">
    <i class="fas fa-sparkles mr-1 text-warning"></i> Switch to WorkHub v2
</a>
```

### In Modern UI (V2 `DashboardLayout.jsx`)
Add a clean switcher item in the sidebar footer and user menu:
```jsx
<Button asChild variant="ghost" size="sm" className="w-full justify-start text-xs text-zinc-400 hover:text-zinc-100 hover:bg-zinc-900">
  <a href="/dashboard">
    <ArrowLeft className="w-3.5 h-3.5 mr-2" />
    Return to Classic WorkHub
  </a>
</Button>
```

---

## 6. Detailed Implementation Roadmap

### Phase 1: Controller & Route Decoupling
1. Create `App\Http\Controllers\V2\` namespace.
2. Move Inertia serialization logic from `ProjectController`, `TaskController`, and `DashboardController` into their respective `V2` controllers.
3. Restore classic controllers to clean, pure Blade responses.
4. Define route group in `routes/web.php`:
   ```php
   Route::middleware(['auth', 'verified'])->prefix('v2')->name('v2.')->group(function () {
       Route::get('/dashboard', [V2\DashboardController::class, 'index'])->name('dashboard');
       Route::get('/dashboard/{company}', [V2\DashboardController::class, 'index'])->name('dashboard.org');
       Route::get('/projects', [V2\ProjectController::class, 'index'])->name('projects.index');
       Route::get('/tasks', [V2\TaskController::class, 'index'])->name('tasks.index');
       Route::get('/tasks/{task}', [V2\TaskController::class, 'show'])->name('tasks.show');
       Route::get('/notes', [V2\NoteController::class, 'index'])->name('notes.index');
       Route::get('/analytics', [V2\AnalyticsController::class, 'index'])->name('analytics');
   });
   ```

### Phase 2: Build V2 Notes & Documentation
1. Create `resources/js/Pages/New/Notes.jsx` incorporating `DashboardLayout`.
2. Implement `App\Http\Controllers\V2\NoteController@index` to pass formatted notes, available projects, tasks, and statistics.
3. Wire up CRUD actions (store, update, destroy) through Inertia form helpers.

### Phase 3: Bidirectional Navigation & Testing
1. Add version switcher badges to Blade topbar and V2 sidebar.
2. Update tests in `tests/Feature/` so:
   - Classic route tests assert status 200 and Blade views.
   - V2 route tests assert status 200 and `assertInertia`.
3. Verify test suite passes 100%.

---

## 7. Compliance with Coding Standards

All changes must strictly adhere to [`CODING_STANDARDS.md`](file:///home/subhadip/Herd/WorkHub/CODING_STANDARDS.md):
- **Facade Imports**: Always import `Gate`, `Inertia`, `Storage`, `Route` at the top of PHP files.
- **PBAC**: Use Policy gates (`$this->authorize()` or `Gate::authorize()`) in V2 controllers identically to classic controllers.
- **Storage Resolution**: Always resolve uploaded images through `asset('storage/...')` or `$user->profile_image_url`.
- **Soft Deletes**: Respect soft delete cascading and `withTrashed()` conventions.
