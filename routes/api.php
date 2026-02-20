<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\GameSessionController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\TurnController;
use Illuminate\Support\Facades\Route;

// API v1 routes
Route::prefix('v1')->group(function (): void {
    // Public auth routes
    Route::prefix('auth')->group(function (): void {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
    });

    // Protected routes
    Route::middleware('auth:sanctum')->group(function (): void {
        // Auth routes
        Route::prefix('auth')->group(function (): void {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/me', [AuthController::class, 'me']);
        });

        // Health check
        Route::get('/health', [HealthController::class, 'index']);

        // Game Sessions
        Route::prefix('game-sessions')->group(function (): void {
            Route::post('/', [GameSessionController::class, 'store']);
            Route::get('/{gameSession}', [GameSessionController::class, 'show']);
            Route::delete('/{gameSession}', [GameSessionController::class, 'destroy']);
            Route::get('/{gameSession}/logs', [GameSessionController::class, 'logs']);
            Route::post('/{gameSession}/turn', [TurnController::class, 'process']);
        });
    });
});
