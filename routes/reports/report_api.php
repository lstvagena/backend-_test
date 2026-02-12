<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\V1\Reports\UserReportController;


Route::prefix('reports')
    ->controller(UserReportController::class)
    ->group(function () {
        Route::get('/users/export', 'export');
        Route::get('/users/download-pdf', 'downloadPdf');
    });


