<?php
namespace App\Services\Reports;

use App\Services\Reports\BaseReportService;
use App\Repositories\Reports\UserReportRepository;
use App\Reports\Pdf\UserReportPdf;
use App\Exports\Reports\UsersReportExport;

class UserReportService extends BaseReportService
{
    protected function getRepositoryClass(): string
    {
        return UserReportRepository::class;
    }

    protected function getPdfRendererClass(): string
    {
        return UserReportPdf::class;
    }

    protected function getExportClass(): string
    {
        return UsersReportExport::class;
    }

    protected function getFilename(): string
    {
        return 'users_report'; //filename
    }
}
