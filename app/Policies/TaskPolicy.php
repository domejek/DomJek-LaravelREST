<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Task $task): bool
    {
        return $user->isAdmin() || $task->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Task $task): bool
    {
        if (! $user->isAdmin() && $task->user_id !== $user->id) {
            return false;
        }

        if ($task->deadline && $task->deadline->isPast() && ! $user->isAdmin()) {
            return false;
        }

        return true;
    }

    public function delete(User $user, Task $task): bool
    {
        return $user->isAdmin() || $task->user_id === $user->id;
    }
}
