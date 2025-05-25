<?php

use App\Http\Controllers\API\ActivityLogController;
use App\Http\Controllers\API\AnalyticsController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\PlatformController;
use App\Http\Controllers\API\PostController;
use App\Http\Controllers\API\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::get('platforms', [PlatformController::class, 'index']);

    Route::middleware('auth:sanctum')->group(function () {

        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);

        Route::get('profile', [ProfileController::class, 'profile']);
        Route::put('profile', [ProfileController::class, 'updateProfile']);
        Route::put('profile/password', [ProfileController::class, 'updatePassword']);

        Route::get('/analytics', [AnalyticsController::class, 'analytics']);

        Route::apiResource('posts', PostController::class);
        Route::get('/user/posts', [PostController::class, 'getUsersPosts']);
        Route::post('/posts/{originalPost}/repost', [PostController::class, 'repost']);

        Route::get('/user/platforms', [PlatformController::class, 'userPlatforms']);
        Route::post('/platforms/{platform}/toggle', [PlatformController::class, 'togglePlatform']);
        Route::get('/platforms/{platform}/settings', [PlatformController::class, 'getSettings']);
        Route::put('/platforms/{platform}/settings', [PlatformController::class, 'updateSettings']);

        Route::get('/activity-logs', [ActivityLogController::class, 'index']);
        Route::get('/activity-logs/types', [ActivityLogController::class, 'getActionTypes']);
    });
});
