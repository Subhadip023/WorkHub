<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;


Route::get('/tasks', [TaskController::class, 'index']);
Route::post('/tasks', [TaskController::class, 'store']);
Route::delete('/tasks/{id}', [TaskController::class, 'destroy']);

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Dashboard
Route::get('/', function () {
    return view('welcome');
})->name('dashboard');

// Components Routes
Route::get('/buttons', function () {
    return view('pages.buttons');
})->name('buttons');

Route::get('/cards', function () {
    return view('pages.cards');
})->name('cards');

// Utilities Routes
Route::get('/utilities/colors', function () {
    return view('pages.utilities.colors');
})->name('utilities.colors');

Route::get('/utilities/borders', function () {
    return view('pages.utilities.borders');
})->name('utilities.borders');

Route::get('/utilities/animations', function () {
    return view('pages.utilities.animations');
})->name('utilities.animations');

Route::get('/utilities/other', function () {
    return view('pages.utilities.other');
})->name('utilities.other');

// Auth Routes
Route::get('/login', function () {
    return view('pages.login');
})->name('login');

Route::get('/register', function () {
    return view('pages.register');
})->name('register');

Route::get('/forgot-password', function () {
    return view('pages.forgot-password');
})->name('forgot-password');

// Other Pages Routes
Route::get('/404', function () {
    return view('pages.404');
})->name('404');

Route::get('/blank', function () {
    return view('pages.blank');
})->name('blank');

// Data Visualization Routes
Route::get('/charts', function () {
    return view('pages.charts');
})->name('charts');

Route::get('/tables', function () {
    return view('pages.tables');
})->name('tables');
