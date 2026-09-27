<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\Admin\FeatureManagementController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExternalTaskApiController;
use App\Http\Controllers\IssueController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectCredentialsController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TrashController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('home');
});

Route::get('/dashboard', DashboardController::class)->middleware(['auth', 'verified'])->name('dashboard');
Route::get('/dashboard/{company}', DashboardController::class)->middleware(['auth', 'verified'])->name('dashboard.org');

Route::get('/old-dashboard', [DashboardController::class, 'oldDashboard'])->middleware(['auth', 'verified'])->name('old.dashboard');
Route::get('/old-dashboard/{company}', [DashboardController::class, 'oldDashboard'])->middleware(['auth', 'verified'])->name('old.dashboard.org');

Route::get('/analytics', AnalyticsController::class)->middleware(['auth', 'verified'])->name('analytics');
Route::redirect('/new/dashboard', '/dashboard')->name('new.dashboard');
Route::get('/new/analytics', AnalyticsController::class)->middleware(['auth', 'verified'])->name('new.analytics');

Route::redirect('/new/projects', '/projects')->name('new.projects');

Route::get('/new/tasks', function () {
    return Inertia::render('New/Tasks', [
        'initial_tasks' => [
            [
                'id' => 1,
                'title' => 'Implement Inertia.js React layout with shadcn UI Sidebar',
                'description' => 'Migrate navigation header and left panel to official shadcn sidebar primitives.',
                'status' => 'In Progress',
                'priority' => 'High',
                'dueDate' => 'Today',
                'project' => 'Inertia.js Migration',
                'assignee' => ['name' => 'Alex Morgan', 'avatar' => 'AM'],
                'category' => 'Dev',
                'completed' => false,
                'subtasks' => '4/5',
            ],
            [
                'id' => 2,
                'title' => 'Review pull request #142 (WorkHub task API limits)',
                'description' => 'Verify rate-limiting middleware triggers HTTP 429 when threshold exceeded.',
                'status' => 'To Do',
                'priority' => 'High',
                'dueDate' => 'Today',
                'project' => 'WorkHub API',
                'assignee' => ['name' => 'Sarah Chen', 'avatar' => 'SC'],
                'category' => 'Code Review',
                'completed' => false,
                'subtasks' => '1/2',
            ],
            [
                'id' => 3,
                'title' => 'Optimize database queries for TaskRepository dashboard filter',
                'description' => 'Ensure status 4 tasks and on-hold projects are excluded cleanly.',
                'status' => 'Done',
                'priority' => 'Medium',
                'dueDate' => 'Yesterday',
                'project' => 'WorkHub Core',
                'assignee' => ['name' => 'Michael Scott', 'avatar' => 'MS'],
                'category' => 'Backend',
                'completed' => true,
                'subtasks' => '3/3',
            ],
            [
                'id' => 4,
                'title' => 'Design dark mode theme tokens for high-contrast cards',
                'description' => 'Refactor Tailwind color utilities with ambient slate-950 glows.',
                'status' => 'In Progress',
                'priority' => 'Medium',
                'dueDate' => 'Tomorrow',
                'project' => 'shadcn/ui Design',
                'assignee' => ['name' => 'Sarah Chen', 'avatar' => 'SC'],
                'category' => 'UI/UX',
                'completed' => false,
                'subtasks' => '2/4',
            ],
            [
                'id' => 5,
                'title' => 'Setup Pest feature tests for /new/analytics & /projects',
                'description' => 'Write assertions confirming Inertia props resolution.',
                'status' => 'Review',
                'priority' => 'High',
                'dueDate' => 'Aug 24',
                'project' => 'Inertia.js Migration',
                'assignee' => ['name' => 'Emma Watson', 'avatar' => 'EW'],
                'category' => 'QA & Testing',
                'completed' => false,
                'subtasks' => '2/2',
            ],
            [
                'id' => 6,
                'title' => 'Configure GitHub Actions CI pipeline with zero downtime',
                'description' => 'Deploy automated Pest test runner before production staging releases.',
                'status' => 'To Do',
                'priority' => 'Low',
                'dueDate' => 'Aug 29',
                'project' => 'CI/CD Pipeline',
                'assignee' => ['name' => 'David Kim', 'avatar' => 'DK'],
                'category' => 'DevOps',
                'completed' => false,
                'subtasks' => '0/3',
            ],
            [
                'id' => 7,
                'title' => 'Draft sprint retrospective notes & engineering team metrics',
                'description' => 'Compile cycle time and team velocity data into executive report.',
                'status' => 'Done',
                'priority' => 'Low',
                'dueDate' => 'Aug 18',
                'project' => 'WorkHub Management',
                'assignee' => ['name' => 'Alex Morgan', 'avatar' => 'AM'],
                'category' => 'Docs',
                'completed' => true,
                'subtasks' => '2/2',
            ],
        ],
    ]);
})->name('new.tasks');

Route::get('/new/task-view', function () {
    return Inertia::render('New/TaskView');
})->name('new.task.view');

Route::get('/new/task/{id}', function ($id) {
    return Inertia::render('New/TaskView', [
        'task' => [
            'id' => str_starts_with($id, 'WH-') ? $id : "WH-{$id}",
            'title' => "Inertia.js React layout hydration error on cold start (#{$id})",
            'description' => "When launching the application on a cold browser refresh, React throws a client-side hydration mismatch warning. The DOM attributes generated on the server render differ slightly from the client state.\n\n### Steps to Reproduce\n1. Clear browser cache and navigate to /dashboard.\n2. Observe console warning: Hydration failed because the initial UI does not match the server-rendered HTML.\n3. Notice temporary layout flicker during component mounting.\n\n### Expected Behavior\nThe Inertia page wrapper should hydrate seamlessly without layout reflows or console warnings.",
            'status' => 'In Progress',
            'priority' => 'Urgent',
            'dueDate' => 'Today, 5:00 PM',
            'project' => 'Inertia.js Migration',
            'branch' => 'fix/inertia-hydration',
            'category' => 'Frontend Bug',
            'created' => '2 hours ago by Alex Morgan',
            'assignee' => ['name' => 'Alex Morgan', 'avatar' => 'AM', 'email' => 'alex@workhub.io'],
            'reporter' => ['name' => 'Sarah Chen', 'avatar' => 'SC'],
            'completed' => false,
            'pr' => ['id' => '#148', 'title' => 'fix(layout): resolve hydration mismatch', 'status' => 'Merged'],
            'tags' => ['Bug', 'Frontend', 'Inertia.js'],
        ],
    ]);
})->name('new.task.show');

Route::get('/new/tasks/{id}', function ($id) {
    return redirect()->route('new.task.show', ['id' => $id]);
})->name('new.tasks.show');

Route::get('/new/companies', function () {
    return Inertia::render('New/ComingSoon', ['feature' => 'Companies Workspace', 'activeItem' => 'companies']);
})->name('new.companies');

Route::get('/new/issues', function () {
    return Inertia::render('New/ComingSoon', ['feature' => 'Issues & Bug Tracker', 'activeItem' => 'issues']);
})->name('new.issues');

Route::get('/new/notes', function () {
    return Inertia::render('New/ComingSoon', ['feature' => 'Notes & Documentation', 'activeItem' => 'notes']);
})->name('new.notes');

Route::get('/new/team', function () {
    return Inertia::render('New/ComingSoon', ['feature' => 'Team Members Directory', 'activeItem' => 'team']);
})->name('new.team');

Route::get('/new/permissions', function () {
    return Inertia::render('New/ComingSoon', ['feature' => 'System Permissions', 'activeItem' => 'permissions']);
})->name('new.permissions');

Route::get('/new/settings', function () {
    return Inertia::render('New/ComingSoon', ['feature' => 'Account Settings', 'activeItem' => 'settings']);
})->name('new.settings');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/invitations/{invitation}/accept', [CompanyController::class, 'acceptInvitation'])->name('invitations.accept');
    Route::post('/invitations/{invitation}/reject', [CompanyController::class, 'rejectInvitation'])->name('invitations.reject');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('companies', CompanyController::class);
    Route::post('/companies/join', [CompanyController::class, 'join'])->name('companies.join');
    Route::get('/companies/{company}/switch', [CompanyController::class, 'switch'])->name('companies.switch');
    Route::post('/companies/{company}/leave', [CompanyController::class, 'leave'])->name('companies.leave');
    Route::delete('/companies/{company}/members/{user}', [CompanyController::class, 'removeMember'])->name('companies.members.destroy');
    Route::get('/companies/{company}/members/{user}/activity', [CompanyController::class, 'memberActivity'])->name('companies.members.activity');
    Route::post('/companies/{company}/approve/{user}', [CompanyController::class, 'approveMember'])->name('companies.approve-member');
    Route::post('/companies/{company}/reject-request/{user}', [CompanyController::class, 'rejectMemberRequest'])->name('companies.reject-member-request');
    Route::post('/companies/{company}/invite', [CompanyController::class, 'invite'])->name('companies.invite');
    Route::get('/personal/switch', [CompanyController::class, 'switchToPersonal'])->name('personal.switch');

    Route::resource('projects', ProjectController::class);
    Route::get('/projects/{project}/notes', [ProjectController::class, 'notes'])->name('projects.notes');
    Route::get('/projects/{project}/note', [ProjectController::class, 'notes']);
    Route::get('/projects/{project}/credentials', [ProjectController::class, 'credentials'])->name('projects.credentials');
    Route::post('/projects/{project}/credentials', [ProjectCredentialsController::class, 'store'])->name('projects.credentials.store');
    Route::delete('/projects/{project}/credentials/{credential}', [ProjectCredentialsController::class, 'destroy'])->name('projects.credentials.destroy');

    Route::get('/projects/{project}/external-api', [ExternalTaskApiController::class, 'index'])->name('projects.external-api');
    Route::get('/projects/{project}/external-api/postman', [ExternalTaskApiController::class, 'downloadPostmanCollection'])->name('projects.external-api.postman');
    Route::post('/projects/{project}/external-api', [ExternalTaskApiController::class, 'store'])->name('projects.external-api.store');
    Route::patch('/external-api/{externalTaskApi}', [ExternalTaskApiController::class, 'update'])->name('external-api.update');
    Route::post('/external-api/{externalTaskApi}/regenerate-secret', [ExternalTaskApiController::class, 'regenerateSecret'])->name('external-api.regenerate-secret');
    Route::delete('/external-api/{externalTaskApi}', [ExternalTaskApiController::class, 'destroy'])->name('external-api.destroy');
    Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::post('/tasks', [TaskController::class, 'storeGeneral'])->name('tasks.store');
    Route::post('/projects/{project}/tasks', [TaskController::class, 'store'])->name('projects.tasks.store');
    Route::post('/projects/{project}/tasks/import', [TaskController::class, 'import'])->name('projects.tasks.import');
    Route::patch('/tasks/{task}/toggle', [TaskController::class, 'toggle'])->name('tasks.toggle');
    Route::patch('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
    Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
    Route::post('/tasks/{task}/subtasks', [TaskController::class, 'storeSubtask'])->name('tasks.subtasks.store');
    Route::post('/tasks/{task}/copy', [TaskController::class, 'copy'])->name('tasks.copy');
    Route::post('/tasks/{task}/images', [TaskController::class, 'uploadImage'])->name('tasks.images.store');
    Route::delete('/tasks/images/{image}', [TaskController::class, 'deleteImage'])->name('tasks.images.destroy');

    Route::get('/notes/{note}/pdf', [NoteController::class, 'downloadPdf'])->name('notes.pdf');
    Route::resource('notes', NoteController::class);

    Route::post('/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

    Route::get('/trash', [TrashController::class, 'index'])->name('trash.index');
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    Route::post('/trash/tasks/{id}/restore', [TrashController::class, 'restoreTask'])->name('trash.tasks.restore');
    Route::delete('/trash/tasks/{id}/force', [TrashController::class, 'forceDeleteTask'])->name('trash.tasks.forceDelete');
    Route::post('/trash/projects/{id}/restore', [TrashController::class, 'restoreProject'])->name('trash.projects.restore');
    Route::delete('/trash/projects/{id}/force', [TrashController::class, 'forceDeleteProject'])->name('trash.projects.forceDelete');
    Route::post('/trash/companies/{id}/restore', [TrashController::class, 'restoreCompany'])->name('trash.companies.restore');
    Route::delete('/trash/companies/{id}/force', [TrashController::class, 'forceDeleteCompany'])->name('trash.companies.forceDelete');
    Route::post('/trash/members/{id}/restore', [TrashController::class, 'restoreMember'])->name('trash.members.restore');
    Route::delete('/trash/members/{id}/force', [TrashController::class, 'forceDeleteMember'])->name('trash.members.forceDelete');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');

    Route::get('/permissions', function () {
        return view('permissions.index');
    })->name('permissions.index');

    Route::get('/issues', [IssueController::class, 'index'])->name('issues.index');
    Route::post('/issues', [IssueController::class, 'store'])->name('issues.store');

    Route::get('/search', [SearchController::class, 'index'])->name('search.index');

    Route::middleware('can:manage-features')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/features', [FeatureManagementController::class, 'index'])->name('features.index');
        Route::post('/features/{user}/toggle-feature', [FeatureManagementController::class, 'toggleFeature'])->name('features.toggle-feature');
        Route::post('/features/{user}/toggle-role', [FeatureManagementController::class, 'toggleRole'])->name('features.toggle-role');
    });
});

require __DIR__.'/auth.php';
require __DIR__.'/demo.php';
// require __DIR__.'/desing.php';
