<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;


Route::prefix('{company}')->group(function () {
    Route::get('users', [UserController::class, 'index']);      // ✅ GET
    Route::post('users', [UserController::class, 'store']);     // ✅ POST ADDED
});

// PUBLIC AUTH (no company prefix)
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// PROTECTED ROUTES (after auth)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', fn() => auth()->user());
    Route::post('/auth/logout', fn(Request $request) => $request->user()->currentAccessToken()->delete());
});

// COMPANY ROUTES (tenant-specific data)
Route::prefix('{company}')->middleware(['auth:sanctum', 'company.db'])->group(function () {
    Route::apiResource('users', UserController::class);
});
