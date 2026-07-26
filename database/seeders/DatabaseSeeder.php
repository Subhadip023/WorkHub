<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Company;
use App\Models\CompanyUsers;
use App\Models\Note;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create / Update Core Test Users
        $admin = User::updateOrCreate(
            ['email' => 'admin@email.com'],
            [
                'name' => 'Test admin',
                'password' => bcrypt('12345678'),
                'email_verified_at' => now(),
            ]
        );

        $user = User::updateOrCreate(
            ['email' => 'user@email.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('12345678'),
                'email_verified_at' => now(),
            ]
        );

        // Additional Team Members
        $emily = User::updateOrCreate(
            ['email' => 'emily@workhub.com'],
            [
                'name' => 'Emily Fowler',
                'password' => bcrypt('12345678'),
                'email_verified_at' => now(),
            ]
        );

        $jae = User::updateOrCreate(
            ['email' => 'jae@workhub.com'],
            [
                'name' => 'Jae Chun',
                'password' => bcrypt('12345678'),
                'email_verified_at' => now(),
            ]
        );

        $morgan = User::updateOrCreate(
            ['email' => 'morgan@workhub.com'],
            [
                'name' => 'Morgan Alvarez',
                'password' => bcrypt('12345678'),
                'email_verified_at' => now(),
            ]
        );

        // 2. Create Companies / Organizations
        $companyAcme = Company::firstOrCreate(
            ['code' => 'ACME-2026'],
            ['name' => 'Acme Innovations']
        );

        $companyNexus = Company::firstOrCreate(
            ['code' => 'NEXUS-2026'],
            ['name' => 'Nexus Tech Solutions']
        );

        // 3. Attach Users to Companies
        // Acme Innovations
        CompanyUsers::firstOrCreate([
            'company_id' => $companyAcme->id,
            'user_id' => $admin->id,
        ], [
            'role' => 1, // Admin
            'is_approved' => 1,
        ]);

        CompanyUsers::firstOrCreate([
            'company_id' => $companyAcme->id,
            'user_id' => $user->id,
        ], [
            'role' => 2, // Member
            'is_approved' => 1,
        ]);

        CompanyUsers::firstOrCreate([
            'company_id' => $companyAcme->id,
            'user_id' => $emily->id,
        ], [
            'role' => 2, // Member
            'is_approved' => 1,
        ]);

        // Nexus Tech
        CompanyUsers::firstOrCreate([
            'company_id' => $companyNexus->id,
            'user_id' => $user->id,
        ], [
            'role' => 1, // Admin
            'is_approved' => 1,
        ]);

        CompanyUsers::firstOrCreate([
            'company_id' => $companyNexus->id,
            'user_id' => $jae->id,
        ], [
            'role' => 2, // Member
            'is_approved' => 1,
        ]);

        CompanyUsers::firstOrCreate([
            'company_id' => $companyNexus->id,
            'user_id' => $morgan->id,
        ], [
            'role' => 2, // Member
            'is_approved' => 1,
        ]);

        // 4. Create Projects
        $projectMobile = Project::firstOrCreate(
            ['slug' => 'mobile-app-redesign'],
            [
                'name' => 'Mobile App Redesign',
                'description' => '<p>Overhaul of the iOS and Android applications with modern dark mode UI, smooth micro-animations, and fast REST API sync.</p>',
                'theme' => '#4e73df',
                'status' => 2, // In Progress
                'priority' => 3, // High
                'user_id' => $admin->id,
                'company_id' => $companyAcme->id,
            ]
        );

        $projectApi = Project::firstOrCreate(
            ['slug' => 'api-infrastructure-v2'],
            [
                'name' => 'API Infrastructure v2',
                'description' => '<p>Refactoring core controllers, implementing JWT authentication, and optimizing database queries for high throughput.</p>',
                'theme' => '#1cc88a',
                'status' => 2, // In Progress
                'priority' => 4, // Urgent
                'user_id' => $admin->id,
                'company_id' => $companyAcme->id,
            ]
        );

        $projectPortal = Project::firstOrCreate(
            ['slug' => 'customer-portal-dashboard'],
            [
                'name' => 'Customer Portal Dashboard',
                'description' => '<p>Client self-service portal for managing billing, team invitations, and task progress reports.</p>',
                'theme' => '#36b9cc',
                'status' => 1, // To Do
                'priority' => 2, // Medium
                'user_id' => $user->id,
                'company_id' => $companyNexus->id,
            ]
        );

        $projectPersonalAdmin = Project::firstOrCreate(
            ['slug' => 'personal-workspace-research'],
            [
                'name' => 'Personal Workspace & Research',
                'description' => '<p>Private sandbox for testing new Laravel packages and prototype ideas.</p>',
                'theme' => '#f6c23e',
                'status' => 1, // To Do
                'priority' => 1, // Low
                'user_id' => $admin->id,
                'company_id' => null, // Personal
            ]
        );

        // 5. Create Tasks
        $tasksData = [
            // Mobile App Redesign Tasks
            [
                'title' => 'Design Figma wireframes for user profile modal',
                'description' => 'Create high-fidelity responsive component specs for mobile screens.',
                'due_date' => now()->addDays(2)->format('Y-m-d'),
                'status' => 3, // Completed
                'priority' => 2, // Medium
                'type' => Task::TYPE_FEATURE,
                'project_id' => $projectMobile->id,
                'user_id' => $admin->id,
                'assigned_to' => $emily->id,
            ],
            [
                'title' => 'Fix avatar broken link fallback on navigation topbar',
                'description' => 'Replace hardcoded external Unsplash URL with local SVG assets or dynamic UI-avatars endpoint.',
                'due_date' => now()->subDay()->format('Y-m-d'), // Overdue
                'status' => 1, // To Do
                'priority' => 4, // Urgent
                'type' => Task::TYPE_BUG,
                'project_id' => $projectMobile->id,
                'user_id' => $admin->id,
                'assigned_to' => $admin->id,
            ],
            [
                'title' => 'Implement push notification triggers for task assignments',
                'description' => 'Send real-time alerts when a team member is assigned or mentioned in a comment.',
                'due_date' => now()->addDays(5)->format('Y-m-d'),
                'status' => 2, // In Progress
                'priority' => 3, // High
                'type' => Task::TYPE_FEATURE,
                'project_id' => $projectMobile->id,
                'user_id' => $admin->id,
                'assigned_to' => $user->id,
            ],
            [
                'title' => 'Optimize mobile touch gestures for task drag-and-drop',
                'description' => 'Ensure smooth touch interactions on iOS Safari and Chrome mobile.',
                'due_date' => now()->addDays(8)->format('Y-m-d'),
                'status' => 1, // To Do
                'priority' => 2, // Medium
                'type' => Task::TYPE_IMPROVEMENT,
                'project_id' => $projectMobile->id,
                'user_id' => $admin->id,
                'assigned_to' => null,
            ],

            // API Infrastructure v2 Tasks
            [
                'title' => 'Migrate database queries to eager load relationships',
                'description' => 'Eliminate N+1 query bottlenecks in ProjectController and TaskController.',
                'due_date' => now()->addDays(3)->format('Y-m-d'),
                'status' => 2, // In Progress
                'priority' => 4, // Urgent
                'type' => Task::TYPE_IMPROVEMENT,
                'project_id' => $projectApi->id,
                'user_id' => $admin->id,
                'assigned_to' => $admin->id,
            ],
            [
                'title' => 'Set up automated Pest test suite for API endpoints',
                'description' => 'Write feature tests covering authentication, policy checks, and JSON response schemas.',
                'due_date' => now()->addDays(6)->format('Y-m-d'),
                'status' => 1, // To Do
                'priority' => 3, // High
                'type' => Task::TYPE_TASK,
                'project_id' => $projectApi->id,
                'user_id' => $admin->id,
                'assigned_to' => $user->id,
            ],
            [
                'title' => 'Fix CORS headers issue on webhooks endpoint',
                'description' => 'Allow trusted cross-origin requests for third-party integrations.',
                'due_date' => now()->subDays(2)->format('Y-m-d'), // Overdue
                'status' => 1, // To Do
                'priority' => 4, // Urgent
                'type' => Task::TYPE_BUG,
                'project_id' => $projectApi->id,
                'user_id' => $admin->id,
                'assigned_to' => $admin->id,
            ],

            // Customer Portal Dashboard Tasks
            [
                'title' => 'Integrate Stripe billing webhooks for subscription upgrades',
                'description' => 'Handle customer invoice paid, payment failed, and plan cancellation events.',
                'due_date' => now()->addDays(12)->format('Y-m-d'),
                'status' => 1, // To Do
                'priority' => 3, // High
                'type' => Task::TYPE_FEATURE,
                'project_id' => $projectPortal->id,
                'user_id' => $user->id,
                'assigned_to' => $jae->id,
            ],
            [
                'title' => 'Add export project report to PDF functionality',
                'description' => 'Generate downloadable summary reports of completed tasks and notes.',
                'due_date' => now()->addDays(4)->format('Y-m-d'),
                'status' => 3, // Completed
                'priority' => 2, // Medium
                'type' => Task::TYPE_FEATURE,
                'project_id' => $projectPortal->id,
                'user_id' => $user->id,
                'assigned_to' => $morgan->id,
            ],

            // Personal / Standalone Tasks
            [
                'title' => 'Review upcoming Q3 feature roadmap',
                'description' => 'Outline milestones, tech requirements, and resource estimates for next quarter.',
                'due_date' => now()->addDays(7)->format('Y-m-d'),
                'status' => 1, // To Do
                'priority' => 2, // Medium
                'type' => Task::TYPE_TASK,
                'project_id' => $projectPersonalAdmin->id,
                'user_id' => $admin->id,
                'assigned_to' => $admin->id,
            ],
            [
                'title' => 'Organize desktop workspace & clean temporary logs',
                'description' => 'Tidy up local development environment and run garbage collection scripts.',
                'due_date' => now()->format('Y-m-d'),
                'status' => 3, // Completed
                'priority' => 1, // Low
                'type' => Task::TYPE_TASK,
                'project_id' => null, // Personal projectless task
                'user_id' => $admin->id,
                'assigned_to' => $admin->id,
            ],
        ];

        foreach ($tasksData as $data) {
            Task::firstOrCreate(
                [
                    'title' => $data['title'],
                    'user_id' => $data['user_id'],
                ],
                $data
            );
        }

        // 6. Create Sample Notes
        Note::firstOrCreate(
            [
                'title' => 'Architecture & Design System Guidelines',
                'user_id' => $admin->id,
            ],
            [
                'description' => '<p>Use consistent Bootstrap 4 cards, custom badge utilities, and SVG icons. Ensure all views utilize shared Blade partials for tables and modals.</p>',
                'note_type' => Note::TYPE_PROJECT,
                'note_type_id' => $projectMobile->id,
            ]
        );

        Note::firstOrCreate(
            [
                'title' => 'API Rate Limiting & Caching Strategy',
                'user_id' => $admin->id,
            ],
            [
                'description' => '<p>Implement Redis caching for user permissions and dynamic translation helpers to maintain sub-50ms response times under load.</p>',
                'note_type' => Note::TYPE_PROJECT,
                'note_type_id' => $projectApi->id,
            ]
        );

        Note::firstOrCreate(
            [
                'title' => 'Weekly Sprint Retrospective',
                'user_id' => $admin->id,
            ],
            [
                'description' => '<p>Key takeaways: AJAX filtering is working smoothly across project views, Pest test suite coverage is excellent, and CSS refactoring is complete.</p>',
                'note_type' => Note::TYPE_PERSONAL,
                'note_type_id' => null,
            ]
        );

        // 7. Create Sample Comments
        Comment::firstOrCreate([
            'content' => 'Mobile wireframes look awesome! Let us move forward with the dark theme variant.',
            'user_id' => $admin->id,
            'commentable_type' => Project::class,
            'commentable_id' => $projectMobile->id,
        ]);

        Comment::firstOrCreate([
            'content' => 'Started working on the CORS headers fix, will post a update once tests pass.',
            'user_id' => $user->id,
            'commentable_type' => Project::class,
            'commentable_id' => $projectMobile->id,
        ]);
    }
}
