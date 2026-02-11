<?php

use Illuminate\Support\Facades\Route;

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
        foreach (glob(__DIR__ . '/utilities/*_api.php') as $file) {
        require $file;
    }
    });
