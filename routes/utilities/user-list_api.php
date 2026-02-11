<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\V1\Utilities\UserController;

Route::get('list-users', [UserController::class, 'index']);

