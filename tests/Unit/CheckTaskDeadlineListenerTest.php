<?php

namespace Tests\Unit;

use App\Events\TaskUpdated;
use App\Listeners\CheckTaskDeadline;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskDeadlineNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CheckTaskDeadlineListenerTest extends TestCase
{
    use RefreshDatabase;

    public function test_listener_sendet_benachrichtigung_bei_ueberfaelliger_aufgabe(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $task = Task::factory()->create([
            'user_id' => $user->id,
            'deadline' => now()->subDay(),
        ]);

        $event = new TaskUpdated($task);
        $listener = new CheckTaskDeadline;

        $listener->handle($event);

        Notification::assertSentTo(
            $user,
            TaskDeadlineNotification::class,
            function ($notification) use ($task) {
                return $notification->task->id === $task->id;
            }
        );
    }

    public function test_listener_sendet_keine_benachrichtigung_bei_zukuenftiger_deadline(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $task = Task::factory()->create([
            'user_id' => $user->id,
            'deadline' => now()->addDays(7),
        ]);

        $event = new TaskUpdated($task);
        $listener = new CheckTaskDeadline;

        $listener->handle($event);

        Notification::assertNotSentTo($user, TaskDeadlineNotification::class);
    }

    public function test_listener_sendet_keine_benachrichtigung_ohne_deadline(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $task = Task::factory()->create([
            'user_id' => $user->id,
            'deadline' => null,
        ]);

        $event = new TaskUpdated($task);
        $listener = new CheckTaskDeadline;

        $listener->handle($event);

        Notification::assertNotSentTo($user, TaskDeadlineNotification::class);
    }
}
