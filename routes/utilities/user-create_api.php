<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\V1\Utilities\UserController;

Route::post('create-user', [UserController::class, 'store']);
 

