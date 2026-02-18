<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/tasks/overdue', [TaskController::class, 'overdue']);
    Route::get('/users/{id}/tasks', [TaskController::class, 'byUser']);
    Route::get('/projects/{id}/tasks', [TaskController::class, 'byProject']);
    Route::apiResource('tasks', TaskController::class);
    Route::apiResource('projects', ProjectController::class);
});
