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
use App\Http\Controllers\RouteExplorerController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TrashController;
use App\Http\Controllers\V2\ActivityLogController as V2ActivityLogController;
use App\Http\Controllers\V2\CompanyController as V2CompanyController;
use App\Http\Controllers\V2\DashboardController as V2DashboardController;
use App\Http\Controllers\V2\IssueController as V2IssueController;
use App\Http\Controllers\V2\NoteController as V2NoteController;
use App\Http\Controllers\V2\PermissionController as V2PermissionController;
use App\Http\Controllers\V2\ProjectController as V2ProjectController;
use App\Http\Controllers\V2\SettingsController as V2SettingsController;
use App\Http\Controllers\V2\TaskController as V2TaskController;
use App\Http\Controllers\V2\TeamController as V2TeamController;
use App\Http\Controllers\V2\TrashController as V2TrashController;
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
Route::get('/routes', RouteExplorerController::class)->middleware(['auth', 'verified'])->name('routes.index');
Route::redirect('/all-routes', '/routes')->name('all.routes');

/*
|--------------------------------------------------------------------------
| Modern WorkHub V2 Routes (Inertia.js + React Suite)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->prefix('v2')->name('v2.')->group(function () {
    Route::get('/dashboard', [V2DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/{company}', [V2DashboardController::class, 'index'])->name('dashboard.org');
    Route::get('/projects', [V2ProjectController::class, 'index'])->name('projects.index');
    Route::get('/tasks', [V2TaskController::class, 'index'])->name('tasks.index');
    Route::get('/tasks/{task}', [V2TaskController::class, 'show'])->name('tasks.show');
    Route::get('/notes', [V2NoteController::class, 'index'])->name('notes.index');
    Route::post('/notes', [V2NoteController::class, 'store'])->name('notes.store');
    Route::match(['put', 'patch'], '/notes/{note}', [V2NoteController::class, 'update'])->name('notes.update');
    Route::delete('/notes/{note}', [V2NoteController::class, 'destroy'])->name('notes.destroy');
    Route::get('/companies', [V2CompanyController::class, 'index'])->name('companies.index');
    Route::post('/companies', [V2CompanyController::class, 'store'])->name('companies.store');
    Route::post('/companies/join', [V2CompanyController::class, 'join'])->name('companies.join');
    Route::get('/companies/personal/switch', [V2CompanyController::class, 'switchToPersonal'])->name('companies.personal.switch');
    Route::get('/companies/{company}', [V2CompanyController::class, 'show'])->name('companies.show');
    Route::match(['put', 'patch'], '/companies/{company}', [V2CompanyController::class, 'update'])->name('companies.update');
    Route::delete('/companies/{company}', [V2CompanyController::class, 'destroy'])->name('companies.destroy');
    Route::get('/companies/{company}/switch', [V2CompanyController::class, 'switch'])->name('companies.switch');
    Route::post('/companies/{company}/leave', [V2CompanyController::class, 'leave'])->name('companies.leave');
    Route::post('/companies/{company}/invite', [V2CompanyController::class, 'invite'])->name('companies.invite');
    Route::post('/companies/{company}/approve/{user}', [V2CompanyController::class, 'approveMember'])->name('companies.approve-member');
    Route::post('/companies/{company}/reject-request/{user}', [V2CompanyController::class, 'rejectMemberRequest'])->name('companies.reject-member-request');
    Route::delete('/companies/{company}/members/{user}', [V2CompanyController::class, 'removeMember'])->name('companies.members.destroy');
    Route::get('/companies/{company}/members/{user}/activity', [V2CompanyController::class, 'memberActivity'])->name('companies.members.activity');

    // Team Members
    Route::get('/team', [V2TeamController::class, 'index'])->name('team.index');
    Route::get('/users', [V2TeamController::class, 'index'])->name('users.index');

    // Issues & Bug Tracker
    Route::get('/issues', [V2IssueController::class, 'index'])->name('issues.index');
    Route::post('/issues', [V2IssueController::class, 'store'])->name('issues.store');
    Route::patch('/issues/{task}/toggle', [V2IssueController::class, 'toggle'])->name('issues.toggle');

    // Activity Logs
    Route::get('/activity-logs', [V2ActivityLogController::class, 'index'])->name('activity-logs.index');

    // Recycle Bin / Trash
    Route::get('/trash', [V2TrashController::class, 'index'])->name('trash.index');
    Route::post('/trash/tasks/{id}/restore', [V2TrashController::class, 'restoreTask'])->name('trash.tasks.restore');
    Route::delete('/trash/tasks/{id}/force', [V2TrashController::class, 'forceDeleteTask'])->name('trash.tasks.forceDelete');
    Route::post('/trash/projects/{id}/restore', [V2TrashController::class, 'restoreProject'])->name('trash.projects.restore');
    Route::delete('/trash/projects/{id}/force', [V2TrashController::class, 'forceDeleteProject'])->name('trash.projects.forceDelete');
    Route::post('/trash/companies/{id}/restore', [V2TrashController::class, 'restoreCompany'])->name('trash.companies.restore');
    Route::delete('/trash/companies/{id}/force', [V2TrashController::class, 'forceDeleteCompany'])->name('trash.companies.forceDelete');

    // System Permissions
    Route::get('/permissions', [V2PermissionController::class, 'index'])->name('permissions.index');

    // Account Settings & Profile
    Route::get('/settings', [V2SettingsController::class, 'index'])->name('settings.index');
    Route::get('/profile', [V2SettingsController::class, 'index'])->name('profile.index');
    Route::post('/settings/profile', [V2SettingsController::class, 'updateProfile'])->name('settings.profile');
    Route::post('/settings/password', [V2SettingsController::class, 'updatePassword'])->name('settings.password');

    Route::get('/analytics', AnalyticsController::class)->name('analytics');
    Route::get('/routes', RouteExplorerController::class)->name('routes');
});

// Top-level aliases
Route::redirect('/users', '/v2/team')->name('users.index');

// Legacy /new aliases
Route::redirect('/new/dashboard', '/dashboard')->name('new.dashboard');
Route::get('/new/analytics', AnalyticsController::class)->middleware(['auth', 'verified'])->name('new.analytics');
Route::redirect('/new/routes', '/routes')->name('new.routes');
Route::redirect('/new/projects', '/projects')->name('new.projects');
Route::redirect('/new/tasks', '/tasks')->name('new.tasks');
Route::redirect('/new/notes', '/v2/notes')->name('new.notes');
Route::get('/new/task-view', function () {
    return Inertia::render('New/TaskView');
})->name('new.task.view');
Route::get('/new/task/{id}', function ($id) {
    return Inertia::render('New/TaskView', [
        'task' => [
            'id' => str_starts_with($id, 'WH-') ? $id : "WH-{$id}",
            'title' => "Inertia.js React layout hydration error on cold start (#{$id})",
            'description' => "When launching the application on a cold browser refresh, React throws a client-side hydration mismatch warning. The DOM attributes generated on the server render differ slightly from the client state.\n\n### Steps to Reproduce\n1. Clear browser cache and navigate to /new/dashboard.\n2. Observe console warning: Hydration failed because the initial UI does not match the server-rendered HTML.\n3. Notice temporary layout flicker during component mounting.\n\n### Expected Behavior\nThe Inertia page wrapper should hydrate seamlessly without layout reflows or console warnings.",
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

Route::redirect('/new/companies', '/v2/companies')->name('new.companies');

Route::redirect('/new/issues', '/v2/issues')->name('new.issues');
Route::redirect('/new/team', '/v2/team')->name('new.team');
Route::redirect('/new/permissions', '/v2/permissions')->name('new.permissions');
Route::redirect('/new/settings', '/v2/settings')->name('new.settings');

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
    });
});

require __DIR__.'/auth.php';
require __DIR__.'/demo.php';
// require __DIR__.'/desing.php';
