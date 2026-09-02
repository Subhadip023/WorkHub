<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Demo & Guest Dashboard Routes
|--------------------------------------------------------------------------
|
| Independent routes for previewing the Inertia React Dashboard with mock /
| static demo data for guest view and UI testing.
|
*/

Route::get('/demo/dashboard', function () {
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
})->name('demo.dashboard');
