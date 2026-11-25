<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\MediaAssetController;
use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\PublicPageController;
use App\Http\Controllers\Api\V1\TemplateController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['throttle:api'])->group(function (): void {
    // Public Authentication Routes (with stricter auth rate limiting)
    Route::prefix('auth')->middleware(['throttle:auth'])->group(function (): void {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    });

    // Public Templates Catalog & Public Published Pages
    Route::get('/templates', [TemplateController::class, 'index']);
    Route::get('/pages/{slug}', [PublicPageController::class, 'show']);

    // Protected Authenticated SaaS Workspace Routes
    Route::middleware(['auth:sanctum'])->group(function (): void {
        Route::prefix('auth')->group(function (): void {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/me', [AuthController::class, 'me']);
            Route::put('/profile', [AuthController::class, 'updateProfile']);
            Route::put('/password', [AuthController::class, 'updatePassword']);
            Route::post('/email/verify', [AuthController::class, 'verifyEmail']);
        });

        Route::get('/dashboard', [DashboardController::class, 'index']);

        // Projects & Landing Page Editor
        Route::get('/projects', [ProjectController::class, 'index']);
        Route::post('/projects', [ProjectController::class, 'store']);
        Route::get('/projects/{project}', [ProjectController::class, 'show']);
        Route::put('/projects/{project}', [ProjectController::class, 'update']);
        Route::delete('/projects/{project}', [ProjectController::class, 'destroy']);
        Route::post('/projects/{project}/duplicate', [ProjectController::class, 'duplicate']);
        Route::put('/projects/{project}/content', [ProjectController::class, 'updateContent']);
        Route::post('/projects/{project}/apply-template', [ProjectController::class, 'applyTemplate']);
        Route::post('/projects/{project}/publish', [ProjectController::class, 'publish']);

        // AI Generation with dedicated per-user rate limit
        Route::post('/projects/{project}/generate', [ProjectController::class, 'generate'])
            ->middleware(['throttle:ai-generation']);

        // Secure Media Uploads
        Route::get('/media', [MediaAssetController::class, 'index']);
        Route::post('/media', [MediaAssetController::class, 'store']);
        Route::delete('/media/{mediaAsset}', [MediaAssetController::class, 'destroy']);
    });
});
