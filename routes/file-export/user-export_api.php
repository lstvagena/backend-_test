<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\V1\FileExport\UserExportController;


Route::prefix('file-export')
    ->controller(UserExportController::class)
    ->group(function () {
        Route::get('/user-export/excel/export', 'export')
            ->name('user-export.export');

        Route::get('/user-export/pdf/export', 'downloadPdf')
            ->name('user-export.download-pdf');
    });

