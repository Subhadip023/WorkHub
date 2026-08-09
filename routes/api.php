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

Route::middleware(['api.key', 'throttle:api'])->group(function () {
    // Get tasks: GET /api/tasks
    Route::get('/tasks', [TaskController::class, 'index'])->name('api.tasks.index');

    // Add task: POST /api/tasks
    Route::post('/tasks', [TaskController::class, 'store'])->name('api.tasks.store');

    // Upload images to an existing task: POST /api/tasks/{task}/images
    Route::post('/tasks/{task}/images', [TaskController::class, 'uploadImage'])->name('api.tasks.images.store');
});
