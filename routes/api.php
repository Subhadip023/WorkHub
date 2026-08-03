<?php

use App\Http\Controllers\Api\TaskController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application.
| Routes support authentication via session or X-Api-Key header / api_key parameter.
|
*/

// Add task to a project: POST /api/projects/{project}/tasks
Route::post('/projects/{project}/tasks', [TaskController::class, 'store'])->name('api.projects.tasks.store');

// Add task (general or project): POST /api/tasks
Route::post('/tasks', [TaskController::class, 'storeGeneral'])->name('api.tasks.store');
