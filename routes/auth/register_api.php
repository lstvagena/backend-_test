<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\V1\Auth\RegisterController;

Route::post('auth/register', [RegisterController::class, 'register']);

