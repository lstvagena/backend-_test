<?php
namespace App\Services\Reports;

use App\Services\Reports\BaseReportService;
use App\Repositories\Reports\UserReportRepository;
use App\Reports\Pdf\UserReportPdf;
use App\Exports\Reports\UsersReportExport;

class UserReportService extends BaseReportService
{
    // Defines repository source
    protected function getRepositoryClass(): string
    {
        return UserReportRepository::class;
    }

    // Defines PDF renderer
    protected function getPdfRendererClass(): string
    {
        return UserReportPdf::class;
    }

    // Defines Excel export class
    protected function getExportClass(): string
    {
        return UsersReportExport::class;
    }

    // Defines base filename
    protected function getFilename(): string
    {
        return 'users_report';
    }
}
