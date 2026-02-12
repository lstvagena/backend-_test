<?php

namespace App\Reports\Pdf;

use App\Services\Shared\pdf\LSTVPDF;
use Carbon\Carbon;
use Laravel\Sanctum\PersonalAccessToken;

abstract class BaseReportPdf
{
    protected array $filters;
    protected LSTVPDF $pdf;
    protected string $reportTitle;

    public function __construct(array $filters, string $reportTitle = 'Report')
    {
        $this->filters = $filters;
        $this->reportTitle = $reportTitle;
        require_once app_path('Services/Shared/Pdf/PdfFoorter.php');
        require_once app_path('Services/Shared/Pdf/PdfContract.php');
        $this->pdf = new \App\Services\Shared\pdf\LSTVPDF([], $this->getFilename());
    }

    abstract public function render(array $data);

    abstract protected function renderData(array $data): void;

    protected function getFilename(): string
    {
        return strtolower(str_replace(' ', '_', $this->reportTitle)) . '_' . date('Y-m-d_His');
    }

    protected function renderHeader(): void
    {
        $top = 10;
        $left = 10;
        $lineHeight = 7;

        $this->pdf->plotData($left, $top, 'Lee Systems Technology Ventures, Inc.', 'Arial', 14, 'center', 190);
        $top += $lineHeight;

        $this->pdf->plotData($left, $top, $this->reportTitle, 'Arial', 12, 'center', 190);
        $top += $lineHeight * 2;
    }

    protected function renderFilters(): void
    {
        $top = 35;
        $left = 10;
        $lineHeight = 5;

        $this->renderFilterSection($top, $left, $lineHeight);

        $printedBy = '';
        $authToken = request()->cookie('auth_token');
        if ($authToken) {
            $personalAccessToken = PersonalAccessToken::findToken($authToken);
            if ($personalAccessToken && $personalAccessToken->tokenable) {
                $printedBy = $personalAccessToken->tokenable->first_name . ' ' . $personalAccessToken->tokenable->last_name ?? '';
            }
        }

        $top += $lineHeight;
        $this->pdf->plotData($left, $top, "Printed by : $printedBy", 'Arial', 10);
        $top += $lineHeight;

        $this->pdf->plotData($left, $top, "Date Printed: " . Carbon::now()->format('F d, Y, h:i A'), 'Arial', 10);
        $top += $lineHeight * 2;

        $this->pdf->addLine(10, $top, 200, $top);
    }

    abstract protected function renderFilterSection(int $top, int $left, int $lineHeight): void;

    public function streamData()
    {
        return $this->pdf->streamData();
    }
}
