<?php

namespace App\Http\Controllers\Api\V1\Reports;

use App\Http\Controllers\Api\V1\Reports\BaseReportController;
use App\Services\Reports\UserReportService;

class UserReportController extends BaseReportController
{
    protected function getServiceClass(): string
    {
        return UserReportService::class;
    }

    protected function getPdfFilename(): string
    {
        return 'users_report';
    }
}
