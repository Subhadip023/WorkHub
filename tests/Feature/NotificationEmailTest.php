<?php

namespace Tests\Feature;

use App\Mail\WorkHubNotificationMail;
use App\Models\Company;
use App\Models\CompanyUsers;
use App\Models\User;
use App\Services\TaskService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class NotificationEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_sends_email_notification_when_task_is_assigned_to_user(): void
    {
        Mail::fake();

        $creator = User::factory()->create();
        $assignee = User::factory()->create(['email_notifications' => true]);

        $taskService = app(TaskService::class);
        $task = $taskService->createTask([
            'title' => 'Implement OAuth Provider',
            'assigned_to' => $assignee->id,
            'status' => 1,
            'priority' => 3,
        ], null, $creator);

        Mail::assertSent(WorkHubNotificationMail::class, function ($mail) use ($assignee) {
            return $mail->hasTo($assignee->email)
                && str_contains($mail->envelope()->subject, 'Task Assigned')
                && $mail->notification->type === 'task_created';
        });
    }

    public function test_it_does_not_send_email_notification_if_user_has_disabled_email_notifications(): void
    {
        Mail::fake();

        $creator = User::factory()->create();
        $assignee = User::factory()->create(['email_notifications' => false]);

        $taskService = app(TaskService::class);
        $taskService->createTask([
            'title' => 'Silent Task',
            'assigned_to' => $assignee->id,
            'status' => 1,
            'priority' => 2,
        ], null, $creator);

        Mail::assertNotSent(WorkHubNotificationMail::class);
    }

    public function test_it_sends_email_notification_on_task_deadline_update(): void
    {
        Mail::fake();

        $creator = User::factory()->create();
        $assignee = User::factory()->create(['email_notifications' => true]);

        $taskService = app(TaskService::class);
        $task = $taskService->createTask([
            'title' => 'Deadline Task',
            'assigned_to' => $assignee->id,
            'status' => 1,
            'priority' => 2,
            'due_date' => '2026-11-10',
        ], null, $creator);

        Mail::fake(); // Reset fake to only capture update

        $taskService->updateTask($task, [
            'due_date' => '2026-11-20',
        ], $creator);

        Mail::assertSent(WorkHubNotificationMail::class, function ($mail) use ($assignee) {
            return $mail->hasTo($assignee->email)
                && str_contains($mail->envelope()->subject, 'Task Deadline Updated')
                && $mail->notification->type === 'task_deadline_updated';
        });
    }

    public function test_it_sends_email_notification_on_task_status_update(): void
    {
        Mail::fake();

        $creator = User::factory()->create();
        $assignee = User::factory()->create(['email_notifications' => true]);

        $taskService = app(TaskService::class);
        $task = $taskService->createTask([
            'title' => 'Review Task',
            'assigned_to' => $assignee->id,
            'status' => 1,
            'priority' => 2,
        ], null, $creator);

        Mail::fake();

        $taskService->updateTask($task, [
            'status' => 3, // Completed
        ], $creator);

        Mail::assertSent(WorkHubNotificationMail::class, function ($mail) use ($assignee) {
            return $mail->hasTo($assignee->email)
                && str_contains($mail->envelope()->subject, 'Task Status Updated')
                && $mail->notification->type === 'task_status_updated';
        });
    }

    public function test_it_sends_email_notification_when_organization_project_is_created(): void
    {
        Mail::fake();

        $company = Company::factory()->create();
        $creator = User::factory()->create();
        $member = User::factory()->create(['email_notifications' => true]);

        CompanyUsers::create([
            'company_id' => $company->id,
            'user_id' => $creator->id,
            'role' => 1, // Admin
            'is_approved' => true,
        ]);

        CompanyUsers::create([
            'company_id' => $company->id,
            'user_id' => $member->id,
            'role' => 0, // Member
            'is_approved' => true,
        ]);

        $response = $this->actingAs($creator)->post('/projects', [
            'name' => 'Q4 Marketing Campaign',
            'company_id' => $company->id,
            'description' => 'Global launch plan',
            'theme' => '#3b82f6',
            'status' => 1,
            'priority' => 2,
        ]);

        $response->assertSessionHasNoErrors();

        Mail::assertSent(WorkHubNotificationMail::class, function ($mail) use ($member, $company) {
            return $mail->hasTo($member->email)
                && str_contains($mail->envelope()->subject, $company->name)
                && $mail->notification->type === 'project_created';
        });
    }

    public function test_user_can_toggle_email_notifications_via_settings_endpoint(): void
    {
        $user = User::factory()->create(['email_notifications' => true]);

        $response = $this->actingAs($user)->post('/settings/notifications', [
            'email_notifications' => false,
        ]);

        $response->assertRedirect();
        $this->assertFalse($user->fresh()->email_notifications);

        $response2 = $this->actingAs($user)->post('/settings/notifications', [
            'email_notifications' => true,
        ]);

        $response2->assertRedirect();
        $this->assertTrue($user->fresh()->email_notifications);
    }
}
