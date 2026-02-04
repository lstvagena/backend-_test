<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;

    // Register a new user  
Route::post('auth/register', [AuthController::class, 'register']);

// Login user and return API token  
Route::post('auth/login', [AuthController::class, 'login']);

// Routes that require a valid Sanctum API token
Route::middleware('auth:sanctum')->group(function () {

    // Return the currently authenticated user
    Route::get('/auth/me', fn() => auth()->user());

    // Logout the user by deleting the current API token
    Route::post('/auth/logout', fn($request) =>
        $request->user()->currentAccessToken()->delete()
    );
});

// Routes that are scoped to a specific company (tenant)
Route::prefix('{company}')

    // Apply tenant database switching + authentication
    ->middleware(['company.db', 'auth:sanctum'])

    // Group all tenant-based routes
    ->group(function () {

        // RESTful API routes for users within a tenant
        Route::apiResource('users', UserController::class);
    });