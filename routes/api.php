<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController; 


Route::prefix('{company}')->group(function () {
    Route::get('users', [UserController::class, 'index']);      // ✅ GET
    Route::post('users', [UserController::class, 'store']);     // ✅ POST ADDED
});