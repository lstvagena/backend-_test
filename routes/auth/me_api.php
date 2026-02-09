<?php

use App\Http\Controllers\Api\V1\Auth\MeController;
use Illuminate\Support\Facades\Route;

Route::controller(MeController::class)->group(function () {
    Route::get('me', 'me');
});
