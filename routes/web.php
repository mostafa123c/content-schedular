<?php

use App\Http\Controllers\Dashboard\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        return redirect('/login');
    });
    Route::get('/login', [DashboardController::class, 'login'])->name('login');
    Route::get('/register', [DashboardController::class, 'register'])->name('register');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::get('/posts/create', [DashboardController::class, 'createPost'])->name('posts.create');
Route::get('/posts/{post}/edit', [DashboardController::class, 'editPost'])->name('posts.edit');
Route::get('/posts', [DashboardController::class, 'posts'])->name('posts.index');

Route::get('/calendar', [DashboardController::class, 'calendar'])->name('calendar');
Route::get('/platforms', [DashboardController::class, 'platforms'])->name('platforms');
Route::get('/activity-logs', [DashboardController::class, 'activityLogs'])->name('activity-logs');
