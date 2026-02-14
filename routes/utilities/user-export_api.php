<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\V1\Utilities\UserController;


Route::prefix('user-export')
    ->controller(UserController::class)
    ->group(function () {

        Route::get('/excel/export', 'excel')
            ;

        Route::get('/pdf/download-pdf', 'pdf')
            ;
            
    });

