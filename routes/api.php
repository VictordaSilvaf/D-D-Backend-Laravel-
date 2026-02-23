<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CharacterSheetController;
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

        // Character Sheets (wizard + import)
        Route::prefix('character-sheets')->group(function (): void {
            Route::get('/', [CharacterSheetController::class, 'index']);
            Route::post('/', [CharacterSheetController::class, 'store']);
            Route::post('/import-pdf', [CharacterSheetController::class, 'importPdf']);
            Route::get('/{characterSheet}', [CharacterSheetController::class, 'show']);
            Route::put('/{characterSheet}', [CharacterSheetController::class, 'update']);
            Route::patch('/{characterSheet}/step/{step}', [CharacterSheetController::class, 'updateStep']);
            Route::post('/{characterSheet}/complete', [CharacterSheetController::class, 'complete']);
            Route::delete('/{characterSheet}', [CharacterSheetController::class, 'destroy']);
        });

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
