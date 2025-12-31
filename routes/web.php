<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;

require __DIR__.'/auth.php';

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {

    /*
    |-----------------------------------------
    | Company Setup (NO company middleware here)
    |-----------------------------------------
    */
    Route::get('/company/create', [CompanyController::class, 'create'])
        ->name('company.create');

    Route::post('/company/store', [CompanyController::class, 'store'])
        ->name('company.store');

    Route::post('/company/join', [CompanyController::class, 'join'])
        ->name('company.join');

    /*
    |-----------------------------------------
    | Company-Protected Area
    |-----------------------------------------
    */
    Route::middleware('company.set')->group(function () {

        Route::get('/projects', [ProjectController::class, 'index'])
            ->name('projects.index');

        Route::get('/projects/create', [ProjectController::class, 'create'])
            ->name('projects.create');

        Route::post('/projects', [ProjectController::class, 'store'])
            ->name('projects.store');

        Route::get('/projects/{project}/tasks', [TaskController::class, 'index'])
            ->name('tasks.index');

        Route::post('/projects/{project}/tasks', [TaskController::class, 'store'])
            ->name('tasks.store');
    });
});
