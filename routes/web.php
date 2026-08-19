<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\Admin\FeatureManagementController;
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

Route::get('/', function () {
    return view('home');
});

Route::get('/dashboard', DashboardController::class)->middleware(['auth', 'verified'])->name('dashboard');
Route::get('/dashboard/{company}', DashboardController::class)->middleware(['auth', 'verified'])->name('dashboard.org');

Route::get('/new/dashboard', function () {
    return Inertia::render('New/Dashboard', [
        'user' => auth()->user() ?? [
            'name' => 'Demo Administrator',
            'email' => 'admin@workhub.io',
        ],
        'stats' => [
            'total_projects' => 12,
            'active_tasks' => 48,
            'completed_tasks' => 156,
            'team_members' => 18,
            'productivity_rate' => 94.2,
            'revenue' => 42850,
        ],
        'recent_activity' => [
            ['id' => 1, 'user' => 'Alex Morgan', 'action' => 'completed task', 'target' => 'API Authentication Refactor', 'time' => '10 mins ago', 'avatar' => 'AM', 'badge' => 'Completed'],
            ['id' => 2, 'user' => 'Sarah Chen', 'action' => 'created issue', 'target' => 'Hydration mismatch on dashboard', 'time' => '32 mins ago', 'avatar' => 'SC', 'badge' => 'Issue'],
            ['id' => 3, 'user' => 'Michael Scott', 'action' => 'updated project', 'target' => 'Q3 WorkHub Redesign', 'time' => '1 hour ago', 'avatar' => 'MS', 'badge' => 'Project'],
            ['id' => 4, 'user' => 'Emma Watson', 'action' => 'pushed commit', 'target' => 'feat: Add Inertia React dashboard', 'time' => '2 hours ago', 'avatar' => 'EW', 'badge' => 'Code'],
            ['id' => 5, 'user' => 'David Kim', 'action' => 'joined company', 'target' => 'Product Team', 'time' => '4 hours ago', 'avatar' => 'DK', 'badge' => 'Team'],
        ],
        'chart_data' => [
            ['name' => 'Mon', 'tasks' => 14, 'completed' => 12, 'revenue' => 2400],
            ['name' => 'Tue', 'tasks' => 22, 'completed' => 18, 'revenue' => 3800],
            ['name' => 'Wed', 'tasks' => 28, 'completed' => 25, 'revenue' => 5100],
            ['name' => 'Thu', 'tasks' => 24, 'completed' => 22, 'revenue' => 4600],
            ['name' => 'Fri', 'tasks' => 35, 'completed' => 31, 'revenue' => 6800],
            ['name' => 'Sat', 'tasks' => 18, 'completed' => 16, 'revenue' => 3200],
            ['name' => 'Sun', 'tasks' => 12, 'completed' => 11, 'revenue' => 2100],
        ],
        'projects' => [
            ['id' => 1, 'name' => 'WorkHub Mobile App', 'progress' => 78, 'status' => 'In Progress', 'dueDate' => 'Aug 28', 'team' => 6, 'tag' => 'React Native'],
            ['id' => 2, 'name' => 'Inertia.js Migration', 'progress' => 92, 'status' => 'Near Completion', 'dueDate' => 'Aug 22', 'team' => 4, 'tag' => 'Laravel + React'],
            ['id' => 3, 'name' => 'shadcn/ui Design System', 'progress' => 100, 'status' => 'Completed', 'dueDate' => 'Aug 18', 'team' => 5, 'tag' => 'Tailwind CSS'],
            ['id' => 4, 'name' => 'Customer Portal v2', 'progress' => 45, 'status' => 'In Progress', 'dueDate' => 'Sep 15', 'team' => 8, 'tag' => 'Next.js'],
        ],
    ]);
})->name('new.dashboard');

Route::get('/new/analytics', function () {
    return Inertia::render('New/Analytics', [
        'analytics_data' => [
            'throughput' => [
                ['period' => 'Week 1', 'created' => 45, 'completed' => 38, 'backlog' => 7],
                ['period' => 'Week 2', 'created' => 52, 'completed' => 48, 'backlog' => 11],
                ['period' => 'Week 3', 'created' => 61, 'completed' => 59, 'backlog' => 13],
                ['period' => 'Week 4', 'created' => 48, 'completed' => 54, 'backlog' => 7],
                ['period' => 'Week 5', 'created' => 70, 'completed' => 66, 'backlog' => 11],
                ['period' => 'Week 6', 'created' => 58, 'completed' => 62, 'backlog' => 7],
            ],
            'categories' => [
                ['category' => 'Frontend Dev', 'tasks' => 42, 'hours' => 168],
                ['category' => 'Backend API', 'tasks' => 36, 'hours' => 144],
                ['category' => 'UI/UX Design', 'tasks' => 24, 'hours' => 96],
                ['category' => 'QA & Testing', 'tasks' => 18, 'hours' => 72],
                ['category' => 'DevOps / Infra', 'tasks' => 12, 'hours' => 48],
            ],
        ],
        'team_members' => [
            ['name' => 'Alex Morgan', 'role' => 'Fullstack Engineer', 'avatar' => 'AM', 'assigned' => 18, 'completed' => 16, 'rate' => 88, 'velocity' => '3.2/day'],
            ['name' => 'Sarah Chen', 'role' => 'UI/UX Lead', 'avatar' => 'SC', 'assigned' => 14, 'completed' => 14, 'rate' => 100, 'velocity' => '2.8/day'],
            ['name' => 'Michael Scott', 'role' => 'Backend Developer', 'avatar' => 'MS', 'assigned' => 22, 'completed' => 19, 'rate' => 86, 'velocity' => '3.8/day'],
            ['name' => 'Emma Watson', 'role' => 'QA Engineer', 'avatar' => 'EW', 'assigned' => 15, 'completed' => 13, 'rate' => 86, 'velocity' => '2.6/day'],
            ['name' => 'David Kim', 'role' => 'DevOps Engineer', 'avatar' => 'DK', 'assigned' => 10, 'completed' => 9, 'rate' => 90, 'velocity' => '1.8/day'],
        ],
    ]);
})->name('new.analytics');

Route::get('/new/projects', function () {
    return Inertia::render('New/Projects', [
        'initial_projects' => [
            [
                'id' => 1,
                'name' => 'WorkHub Mobile App',
                'description' => 'Cross-platform mobile application for real-time task management and team synchronization.',
                'progress' => 78,
                'status' => 'In Progress',
                'dueDate' => 'Aug 28, 2026',
                'teamMembers' => [
                    ['name' => 'Alex Morgan', 'avatar' => 'AM'],
                    ['name' => 'Sarah Chen', 'avatar' => 'SC'],
                    ['name' => 'David Kim', 'avatar' => 'DK'],
                ],
                'completedTasks' => 42,
                'totalTasks' => 54,
                'tag' => 'React Native',
                'category' => 'Mobile',
                'priority' => 'High',
            ],
            [
                'id' => 2,
                'name' => 'Inertia.js Migration',
                'description' => 'Upgrading legacy Laravel blade views to Inertia.js + React JS SPA stack with shadcn UI.',
                'progress' => 92,
                'status' => 'Near Completion',
                'dueDate' => 'Aug 22, 2026',
                'teamMembers' => [
                    ['name' => 'Michael Scott', 'avatar' => 'MS'],
                    ['name' => 'Emma Watson', 'avatar' => 'EW'],
                ],
                'completedTasks' => 38,
                'totalTasks' => 41,
                'tag' => 'Laravel + React',
                'category' => 'Web App',
                'priority' => 'High',
            ],
            [
                'id' => 3,
                'name' => 'shadcn/ui Design System',
                'description' => 'Unified design component token library built with Tailwind CSS and Radix primitives.',
                'progress' => 100,
                'status' => 'Completed',
                'dueDate' => 'Aug 18, 2026',
                'teamMembers' => [
                    ['name' => 'Sarah Chen', 'avatar' => 'SC'],
                    ['name' => 'Alex Morgan', 'avatar' => 'AM'],
                    ['name' => 'Emma Watson', 'avatar' => 'EW'],
                    ['name' => 'David Kim', 'avatar' => 'DK'],
                ],
                'completedTasks' => 30,
                'totalTasks' => 30,
                'tag' => 'Tailwind CSS',
                'category' => 'Design',
                'priority' => 'Medium',
            ],
            [
                'id' => 4,
                'name' => 'Customer Portal v2',
                'description' => 'Self-service analytics and billing management portal for corporate clients.',
                'progress' => 45,
                'status' => 'In Progress',
                'dueDate' => 'Sep 15, 2026',
                'teamMembers' => [
                    ['name' => 'Michael Scott', 'avatar' => 'MS'],
                    ['name' => 'David Kim', 'avatar' => 'DK'],
                ],
                'completedTasks' => 18,
                'totalTasks' => 40,
                'tag' => 'Next.js',
                'category' => 'Frontend',
                'priority' => 'Medium',
            ],
            [
                'id' => 5,
                'name' => 'Automated CI/CD Pipeline',
                'description' => 'GitHub Actions workflow setup with Pest test runner and zero-downtime deployment.',
                'progress' => 30,
                'status' => 'In Progress',
                'dueDate' => 'Sep 30, 2026',
                'teamMembers' => [
                    ['name' => 'David Kim', 'avatar' => 'DK'],
                ],
                'completedTasks' => 6,
                'totalTasks' => 20,
                'tag' => 'DevOps',
                'category' => 'Infra',
                'priority' => 'Low',
            ],
            [
                'id' => 6,
                'name' => 'Real-time Notification Engine',
                'description' => 'WebSockets & Redis pub/sub integration for instant task deadline triggers.',
                'progress' => 15,
                'status' => 'On Hold',
                'dueDate' => 'Oct 10, 2026',
                'teamMembers' => [
                    ['name' => 'Alex Morgan', 'avatar' => 'AM'],
                ],
                'completedTasks' => 3,
                'totalTasks' => 22,
                'tag' => 'Redis + Laravel',
                'category' => 'Backend',
                'priority' => 'Low',
            ],
        ],
    ]);
})->name('new.projects');

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
                'title' => 'Setup Pest feature tests for /new/analytics & /new/projects',
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
// require __DIR__.'/desing.php';
