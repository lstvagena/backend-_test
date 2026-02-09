<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\V1\Auth\LoginController;

Route::post('login', [LoginController::class, 'login']);
