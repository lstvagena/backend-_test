<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\FileExport\UserExportController;

/*{
Route::prefix('v1/auth')         
    ->name('api.v1.auth.')
    ->group(function () {
        foreach (glob(__DIR__ . '/auth/*_api.php') as $file) {
            require $file;
    }
});
} */

/*
|--------------------------------------------------------------------------
| PUBLIC AUTH ROUTES
|--------------------------------------------------------------------------
*/
Route::prefix('v1/auth')
    ->name('api.v1.auth.')
    ->group(function () {
        require __DIR__ . '/auth/login_api.php';
        require __DIR__ . '/auth/register_api.php';
    });

/*
|--------------------------------------------------------------------------
| PROTECTED ROUTES
|--------------------------------------------------------------------------
*/
Route::prefix('v1')
    ->middleware(['api'])
    ->group(function () {

        // protected auth
        Route::prefix('auth')
            ->name('api.v1.auth.')
            ->group(function () {
                require __DIR__ . '/auth/logout_api.php';
                require __DIR__ . '/auth/me_api.php';
            });

        // utilities
        foreach (glob(__DIR__ . '/../routes/utilities/*_api.php') as $file) {
            require $file;
        }

        foreach (glob(__DIR__ . '/../routes/reports/*_api.php') as $file) {
            require $file;
        }



    });

