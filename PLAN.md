# WorkHub Dual-Engine Architecture Plan & Developer Reference (V1 Classic & V2 Developer)

This document serves as the comprehensive architectural reference, implementation guide, and future roadmap for WorkHub's **Dual-Engine System**: **V1 (Classic Mode)** and **V2 (Developer Mode)**.

---

## 1. System Vision & Architecture

WorkHub operates as a **single application with two tailored user interfaces**:

```
                          ┌─────────────────────────────────────┐
                          │         Incoming HTTP Request       │
                          │   (/dashboard, /tasks, /projects)   │
                          └──────────────────┬──────────────────┘
                                             │
                                             ▼
                          ┌─────────────────────────────────────┐
                          │    ResolveUiVersion Middleware      │
                          │  Checks auth()->user()->ui_version  │
                          └──────────┬───────────────┬──────────┘
                                     │               │
                 ui_version == 'v1'  │               │  ui_version == 'v2'
                                     ▼               ▼
                       ┌───────────────────┐   ┌───────────────────────┐
                       │    V1 (Classic)   │   │     V2 (Developer)    │
                       │   Laravel Blade   │   │   Inertia.js + React  │
                       │    Bootstrap 4    │   │  Tailwind + shadcn/ui │
                       │ Server-Rendered   │   │  Single-Page App (SPA)│
                       └─────────┬─────────┘   └───────────┬───────────┘
                                 │                         │
                                 └───────────┬─────────────┘
                                             │
                                             ▼
                               ┌───────────────────────────┐
                               │   Unified Eloquent Data   │
                               │  - Exact same DB tables   │
                               │  - Models, Services, PBAC │
                               │  - Spatie Activity Logs   │
                               └───────────────────────────┘
```

### The Two Modes

| Feature Aspect | **V1 (Classic Mode)** | **V2 (Developer Mode)** |
| :--- | :--- | :--- |
| **Philosophy** | Lightweight, distraction-free, minimal footprint | Modern, rich, interactive engineering cockpit |
| **Tech Stack** | Laravel Blade, Bootstrap 4, jQuery | Inertia.js, React 18, Tailwind CSS, Lucide icons, Recharts |
| **Rendering** | Pure Server-Side Rendering (SSR) | Client-Side SPA (no full-page reloads) |
| **Key Features** | Notion-like inline AJAX task table, simple cards | Multi-column Kanban, project velocity charts, drawer panels |
| **Ideal For** | Project managers, low-spec devices, fast review | Software engineers, dev leads, sprint planners |

---

## 2. Clean URL Routing Engine

Both V1 and V2 operate on **clean, identical URLs** (`/dashboard`, `/tasks`, `/projects`, `/projects/{id}`, `/notes`, `/team`, `/settings`). The user never sees `/v2/` in the browser address bar during normal use.

### How Clean URL Routing Works

1. **Database Persistence**:
   - The `users` table contains a `ui_version` column (`'v1'` or `'v2'`, default `'v1'`).
   - The `User` model exposes `isV2(): bool`:
     ```php
     public function isV2(): bool
     {
         if (session()->has('ui_version')) {
             return session('ui_version') === 'v2';
         }
         return ($this->ui_version ?? 'v1') === 'v2';
     }
     ```

2. **Middleware Resolution (`ResolveUiVersion.php`)**:
   - Registered globally in `bootstrap/app.php`.
   - Intercepts safe `GET` / `HEAD` requests for authenticated users.
   - If `$user->isV2()` is `true` and the current route name matches `$v2RouteMap`, the request is executed by the corresponding V2 Inertia controller:
     ```php
     protected array $v2RouteMap = [
         'dashboard'     => [\App\Http\Controllers\V2\DashboardController::class, 'index'],
         'dashboard.org' => [\App\Http\Controllers\V2\DashboardController::class, 'index'],
         'tasks.index'   => [\App\Http\Controllers\V2\TaskController::class, 'index'],
         'tasks.show'    => [\App\Http\Controllers\V2\TaskController::class, 'show'],
         'projects.index'=> [\App\Http\Controllers\V2\ProjectController::class, 'index'],
         'projects.show' => [\App\Http\Controllers\V2\ProjectController::class, 'show'],
         'notes.index'   => [\App\Http\Controllers\V2\NoteController::class, 'index'],
         'notes.show'    => [\App\Http\Controllers\V2\NoteController::class, 'show'],
         'team.index'    => [\App\Http\Controllers\V2\TeamController::class, 'index'],
     ];
     ```

3. **Inertia Responsable Handling**:
   - If the V2 controller returns an `Inertia\Response`, the middleware converts it via `$response->toResponse($request)` to ensure Symfony response compatibility.

---

## 3. Route & Component Parity Matrix

| Clean URL | Route Name | V1 (Classic) Controller & View | V2 (Developer) Controller & Component | Status |
| :--- | :--- | :--- | :--- | :--- |
| `/` | `home` | Closure -> `view('home')` | Shared Landing Page (Blade + Tailwind) | In Progress |
| `/login` | `login` | `AuthenticatedSessionController` -> `auth.login` | Shared Auth (Blade + Tailwind) | Planned |
| `/register` | `register` | `RegisteredUserController` -> `auth.register` | Shared Auth with Mode Selector | Planned |
| `/dashboard` | `dashboard` | `DashboardController` -> `welcome.blade.php` | `V2\DashboardController` -> `New/Dashboard.jsx` | Completed |
| `/dashboard/{company}` | `dashboard.org` | `DashboardController` -> `welcome.blade.php` | `V2\DashboardController` -> `New/Dashboard.jsx` | Completed |
| `/tasks` | `tasks.index` | `TaskController@index` -> `tasks/index.blade.php` | `V2\TaskController@index` -> `New/Tasks.jsx` | Completed |
| `/tasks/{task}` | `tasks.show` | `TaskController@show` -> `tasks/show.blade.php` | `V2\TaskController@show` -> `New/TaskView.jsx` | Completed |
| `/projects` | `projects.index` | `ProjectController@index` -> `projects/index.blade.php` | `V2\ProjectController@index` -> `New/Projects.jsx` | Completed |
| `/projects/{project}` | `projects.show` | `ProjectController@show` -> `projects/show.blade.php` | `V2\ProjectController@show` -> `New/ProjectView.jsx` | Completed |
| `/notes` | `notes.index` | `NoteController@index` -> `notes/index.blade.php` | `V2\NoteController@index` -> `New/Notes.jsx` | Completed |
| `/notes/{note}` | `notes.show` | `NoteController@show` -> `notes/show.blade.php` | `V2\NoteController@show` -> `New/Notes.jsx` | Completed |
| `/team` | `team.index` | Redirects to `/users` | `V2\TeamController@index` -> `New/Team.jsx` | Completed |
| `/settings` | `settings.index` | `profile.edit` (Blade) | `V2\SettingsController@index` -> `New/Settings.jsx` | Completed |
| `/analytics` | `analytics` | N/A (Redirect) | `V2\AnalyticsController` -> `New/Analytics.jsx` | Completed |
| `/routes` | `routes.index` | `routes/index.blade.php` (Blade) | `V2\RouteExplorerController` -> `New/Routes.jsx` | Completed |
| `/trash` | `v2.trash.index` | `TrashController@index` -> `trash/index.blade.php` | `V2\TrashController@index` -> `New/Trash.jsx` | Completed |
| `/issues` | `v2.issues.index`| Classic Modal / `IssueController` | `V2\IssueController@index` -> `New/Issues.jsx` | Completed |
| `/permissions` | `v2.permissions.index` | N/A | `V2\PermissionsController@index` -> `New/Permissions.jsx` | Completed |

---

## 4. UI Switcher Policy & Rules

### Core Rules
1. **No Sidebar/Topbar Clutter**: Version switcher buttons are **never** placed in sidebars, navigation headers, or floating badges.
2. **Exclusively in Settings**: Switching is strictly performed in **Account Settings / Profile**:
   - In **V2**: Under `Settings > UI Version` (`/settings` tab).
   - In **V1**: Inside the "Interface Version" card in `resources/views/profile/edit.blade.php`.
3. **Switch Endpoint**:
   - Route: `/ui/switch/{version}` (`name('ui.switch')`)
   - Supported versions: `'v1'` and `'v2'`.
   - Action: Updates `users.ui_version`, writes `session(['ui_version' => $version])`, and redirects to `route('dashboard')`.

---

## 5. Inertia vs. AJAX vs. Blade Form Rules

When mutating data (e.g. toggling tasks, creating notes, deleting credentials), actions may be called from both Classic Blade (via jQuery/vanilla AJAX or standard POST) and Modern V2 (via Inertia `router.patch` / `router.post`).

### Critical Rule for Controllers Returning JSON
Inertia requests send `X-Inertia: true` and `X-Requested-With: XMLHttpRequest`. If a controller returns a raw `response()->json(...)` to an Inertia request, Inertia client throws:
> *"All Inertia requests must receive a valid Inertia response, however a plain JSON response was received."*

#### Standard Controller Pattern:
```php
// app/Http/Controllers/TaskController.php
public function toggle(Task $task)
{
    $this->checkTaskOwnership($task);
    $task = $this->taskService->toggleTaskStatus($task);

    // Only return JSON to non-Inertia AJAX callers (e.g., Blade jQuery)
    if (! request()->header('X-Inertia') && (request()->ajax() || request()->wantsJson())) {
        return response()->json([
            'success' => true,
            'message' => 'Task status updated',
            'status' => $task->status,
            'task' => $task->fresh(['project', 'assignedUser']),
        ]);
    }

    // Inertia requests receive a redirect back with session flash
    return redirect()->back()->with('success', 'Task status updated');
}
```

---

## 6. How to Add a New Page to Both Modes in the Future

Follow this step-by-step checklist whenever building a new feature:

### Step 1: Controller Separation
- Create Classic Blade action in `app/Http/Controllers/YourController.php`:
  ```php
  public function index() {
      return view('your-feature.index', compact(...));
  }
  ```
- Create Modern Inertia action in `app/Http/Controllers/V2/YourController.php`:
  ```php
  public function index() {
      return Inertia::render('New/YourFeature', [...]);
  }
  ```

### Step 2: Route Registration
- In `routes/web.php`:
  ```php
  // Clean URL default (Blade)
  Route::get('/your-feature', [YourController::class, 'index'])->name('your-feature.index');

  // Modern V2 named alias
  Route::middleware(['auth', 'verified'])->prefix('v2')->name('v2.')->group(function () {
      Route::get('/your-feature', [V2YourController::class, 'index'])->name('your-feature.index');
  });
  ```

### Step 3: Register in Clean URL Middleware
- Add to `$v2RouteMap` in `app/Http/Middleware/ResolveUiVersion.php`:
  ```php
  'your-feature.index' => [\App\Http\Controllers\V2\YourController::class, 'index'],
  ```

### Step 4: Write Feature Tests
- In `tests/Feature/UiVersionCleanUrlTest.php`:
  - Test that a user with `ui_version = 'v2'` gets status 200 and `assertInertia(fn ($page) => $page->component('New/YourFeature'))`.
  - Test that a user with `ui_version = 'v1'` gets status 200 and `assertViewIs('your-feature.index')`.

### Step 5: Verification Suite
Run the 3 verification steps before committing:
```bash
# 1. Static Analysis
./vendor/bin/phpstan analyse

# 2. Code Styling
./vendor/bin/pint --test

# 3. Test Suite
php artisan test
```

---

## 7. Dual-Engine Public UI & Topbar Controls

1. **Theme Controls in Every Mode**:
   - **Classic Mode (V1)** Topbar ([`partials/topbar.blade.php`](file:///home/subhadip/Herd/WorkHub/resources/views/partials/topbar.blade.php)): Dedicated Theme button (`#classicThemeToggleBtn`) toggling Light and Dark theme with persistent `localStorage` and full CSS coverage in [`admin-custom.css`](file:///home/subhadip/Herd/WorkHub/public/asset/css/admin-custom.css).
   - **Developer Mode (V2)** Header ([`DashboardLayout.jsx`](file:///home/subhadip/Herd/WorkHub/resources/js/Layouts/DashboardLayout.jsx)): Interactive Sun/Moon theme button toggling Tailwind `.dark` / `.light` modes.
   - **Public Landing Page** ([`home.blade.php`](file:///home/subhadip/Herd/WorkHub/resources/views/home.blade.php)): Topbar theme button toggling Light and Dark visual styles.

2. **Public Experience & Registration**:
   - Landing page features interactive workflow previews for both Developer (V2 React) and Classic (V1 Blade).
   - Register view lets users choose their engine preference upfront (`users.ui_version`).
   - Clean URLs (`/dashboard`, `/tasks`, `/projects`, `/settings`, `/routes`) resolve dynamically based on the active mode. Mode switching is managed through account Settings (`/settings`).

