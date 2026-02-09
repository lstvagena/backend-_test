<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\V1\Auth\LogoutController;

Route::post('logout', [LogoutController::class, 'logout']);