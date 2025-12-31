<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;

Route::get('/', fn () => redirect('/login'));

Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
Route::post('/register', [RegisteredUserController::class, 'store']);

Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::post('/login', [AuthenticatedSessionController::class, 'store']);
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

Route::middleware('auth')->group(function () {

    Route::get('/company', [CompanyController::class, 'index'])
    ->name('company.index');

Route::post('/company/create', [CompanyController::class, 'store'])
    ->name('company.store');

Route::post('/company/join', [CompanyController::class, 'join'])
    ->name('company.join');

    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');

    Route::middleware('admin')->group(function () {
        Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create');
        Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    });

    Route::get('/projects/{project}/tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::middleware('admin')->group(function () {
        Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    });
});
