<?php

namespace Tests\Unit;

use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskDeadlineNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskDeadlineNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_verwendet_mail_kanal(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);
        $notification = new TaskDeadlineNotification($task);

        $channels = $notification->via($user);

        $this->assertContains('mail', $channels);
    }

    public function test_notification_mail_enthaelt_task_titel(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create([
            'user_id' => $user->id,
            'title' => 'Wichtige Aufgabe',
        ]);
        $notification = new TaskDeadlineNotification($task);

        $mail = $notification->toMail($user);

        $this->assertEquals('Aufgaben-Deadline überschritten', $mail->subject);
        $this->assertStringContainsString('Wichtige Aufgabe', $mail->render());
    }

    public function test_notification_to_array_enthaelt_task_daten(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create([
            'user_id' => $user->id,
            'title' => 'Test Aufgabe',
        ]);
        $notification = new TaskDeadlineNotification($task);

        $array = $notification->toArray($user);

        $this->assertEquals($task->id, $array['task_id']);
        $this->assertEquals('Test Aufgabe', $array['title']);
        $this->assertEquals($task->deadline, $array['deadline']);
        $this->assertArrayHasKey('message', $array);
    }

    public function test_notification_implements_should_queue(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);
        $notification = new TaskDeadlineNotification($task);

        $this->assertInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class, $notification);
    }
}
