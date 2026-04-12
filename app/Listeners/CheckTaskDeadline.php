<?php

namespace App\Listeners;

use App\Events\TaskUpdated;
use App\Notifications\TaskDeadlineNotification;

class CheckTaskDeadline
{
    public function handle(TaskUpdated $event): void
    {
        $task = $event->task;
        $oldDeadline = $event->oldDeadline;

        $wasNotOverdue = is_null($oldDeadline) || ! $oldDeadline->isPast();
        $isNowOverdue = $task->deadline && $task->deadline->isPast();

        if ($wasNotOverdue && $isNowOverdue) {
            $task->user->notify(new TaskDeadlineNotification($task));
        }
    }
}
