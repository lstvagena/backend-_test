<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\V1\Utilities\UserController;

Route::put('update-user/{id}', [UserController::class, 'update']);

