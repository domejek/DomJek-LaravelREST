<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $projects = Project::with('user')
            ->when(! $request->user()->isAdmin(), function ($query) use ($request) {
                $query->where('user_id', $request->user()->id);
            })
            ->get();

        return response()->json($projects);
    }

    public function store(StoreProjectRequest $request): JsonResponse
    {
        $project = $request->user()->projects()->create($request->validated());

        return response()->json($project->load('user'), 201);
    }

    public function show(Request $request, Project $project): JsonResponse
    {
        if (! $request->user()->isAdmin() && $project->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($project->load(['user', 'tasks']));
    }

    public function update(UpdateProjectRequest $request, Project $project): JsonResponse
    {
        if (! $request->user()->isAdmin() && $project->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $project->update($request->validated());

        return response()->json($project->load('user'));
    }

    public function destroy(Request $request, Project $project): JsonResponse
    {
        if (! $request->user()->isAdmin() && $project->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $project->delete();

        return response()->json(null, 204);
    }
}
