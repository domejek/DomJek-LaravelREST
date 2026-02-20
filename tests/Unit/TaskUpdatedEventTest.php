<?php

namespace Tests\Unit;

use App\Events\TaskUpdated;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskUpdatedEventTest extends TestCase
{
    use RefreshDatabase;

    public function test_event_wird_mit_task_erstellt(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);

        $event = new TaskUpdated($task);

        $this->assertSame($task, $event->task);
        $this->assertNull($event->oldDeadline);
    }

    public function test_event_wird_mit_task_und_alter_deadline_erstellt(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);
        $oldDeadline = now()->addDays(5);

        $event = new TaskUpdated($task, $oldDeadline);

        $this->assertSame($task, $event->task);
        $this->assertEquals($oldDeadline, $event->oldDeadline);
    }

    public function test_event_ist_dispatchable(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);

        TaskUpdated::dispatch($task);

        $this->assertTrue(true);
    }
}
