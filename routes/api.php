<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\UserController;

// 🔥 ADD V1 ROUTES HERE (10 lines)
/*
Route::prefix('v1')->group(function() {
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::post('auth/register', [AuthController::class, 'register']);
    
    Route::prefix('{company}')
        ->middleware(['company.db', 'auth:sanctum'])
        ->group(function() {
            Route::apiResource('users', UserController::class);
            Route::post('auth/logout', [AuthController::class, 'logout']);
        });
});

// 🔥 YOUR ORIGINAL CODE BELOW (UNCHANGED)
Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', fn() => auth()->user());
    Route::post('/auth/logout', fn($request) => $request->user()->currentAccessToken()->delete());
});
 
Route::prefix('{company}')
    ->middleware(['company.db', 'auth:sanctum'])
    ->group(function () {
        Route::apiResource('users', UserController::class);
    });
*/


Route::prefix('v1')->name('api.v1.')->group(function () {
    // Public auth routes (NO company prefix needed)
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::post('auth/register', [AuthController::class, 'register']);
    
    // Protected tenant routes
    Route::prefix('{company}')
        ->middleware(['company.db', 'auth:sanctum'])
        ->name('tenant.')
        ->group(function () {
            Route::post('auth/logout', [AuthController::class, 'logout']);
            Route::apiResource('users', UserController::class);
        });
});