<?php

namespace App\Http\Controllers;

use App\Events\TaskUpdated;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tasks = Task::with(['user', 'project'])
            ->when(! $request->user()->isAdmin(), function ($query) use ($request) {
                $query->where('user_id', $request->user()->id);
            })
            ->get();

        return response()->json($tasks);
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        $task = $request->user()->tasks()->create($request->validated());

        return response()->json($task->load(['user', 'project']), 201);
    }

    public function show(Request $request, Task $task): JsonResponse
    {
        if (! $request->user()->isAdmin() && $task->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($task->load(['user', 'project']));
    }

    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        if (! $request->user()->isAdmin() && $task->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($task->deadline && $task->deadline->isPast() && ! $request->user()->isAdmin()) {
            return response()->json(['message' => 'Cannot update overdue tasks'], 403);
        }

        $oldDeadline = $task->deadline;
        $task->update($request->validated());

        event(new TaskUpdated($task, $oldDeadline));

        return response()->json($task->load(['user', 'project']));
    }

    public function destroy(Request $request, Task $task): JsonResponse
    {
        if (! $request->user()->isAdmin() && $task->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $task->delete();

        return response()->json(null, 204);
    }

    public function overdue(Request $request): JsonResponse
    {
        $tasks = Task::with(['user', 'project'])
            ->where('deadline', '<', now())
            ->when(! $request->user()->isAdmin(), function ($query) use ($request) {
                $query->where('user_id', $request->user()->id);
            })
            ->get();

        return response()->json($tasks);
    }

    public function byUser(Request $request, int $userId): JsonResponse
    {
        if (! $request->user()->isAdmin() && $request->user()->id !== $userId) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $tasks = Task::with(['user', 'project'])
            ->where('user_id', $userId)
            ->get();

        return response()->json($tasks);
    }

    public function byProject(Request $request, int $projectId): JsonResponse
    {
        $tasks = Task::with(['user', 'project'])
            ->where('project_id', $projectId)
            ->when(! $request->user()->isAdmin(), function ($query) use ($request) {
                $query->where('user_id', $request->user()->id);
            })
            ->get();

        return response()->json($tasks);
    }
}
