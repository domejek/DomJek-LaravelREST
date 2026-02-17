<?php

namespace App\Listeners;

use App\Events\TaskUpdated;
use App\Notifications\TaskDeadlineNotification;

class CheckTaskDeadline
{
    public function handle(TaskUpdated $event): void
    {
        $task = $event->task;

        if ($task->deadline && $task->deadline->isPast()) {
            $task->user->notify(new TaskDeadlineNotification($task));
        }
    }
}
