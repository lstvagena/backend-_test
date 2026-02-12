<?php

namespace App\Reports\Pdf;

use App\Reports\Pdf\BaseReportPdf;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class UserReportPdf extends BaseReportPdf
{
    public function __construct(array $filters)
    {
        parent::__construct($filters, 'Users Report');
    }

    public function render(array $data)
    {
        $this->pdf->AddPage();

        $this->renderHeader();
        $this->renderFilters();
        $this->renderData($data);

        return $this->streamData();
    }

    protected function renderData(array $data): void
    {
        $users = collect($data['users'] ?? []);

        $top = 60;
        $left = 10;
        $lineHeight = 6;

        $colWidths = [15, 35, 45, 55, 40];
        $headers = ['ID', 'Username', 'Name', 'Email', 'Created At'];


        // Render table header
        $headerLeft = $left;
        foreach ($headers as $index => $header) {
            $this->pdf->plotData($headerLeft, $top, $header, 'Arial', 10, 'left', $colWidths[$index]);
            $headerLeft += $colWidths[$index];
        }

        $top += $lineHeight;
        $this->pdf->addLine($left, $top, 200, $top);
        $top += $lineHeight;

        // Render rows
        foreach ($users as $user) {

            if ($top > 270) {
                $this->pdf->AddPage();
                $top = 20;

                // Re-render header on new page
                $headerLeft = $left;
                foreach ($headers as $index => $header) {
                    $this->pdf->plotData($headerLeft, $top, $header, 'Arial', 10, 'left', $colWidths[$index]);
                    $headerLeft += $colWidths[$index];
                }

                $top += $lineHeight;
                $this->pdf->addLine($left, $top, 200, $top);
                $top += $lineHeight;
            }

            $rowLeft = $left;

            $this->pdf->plotData($rowLeft, $top, $user->id ?? '', 'Arial', 9, 'left', $colWidths[0]);
            $rowLeft += $colWidths[0];

            $this->pdf->plotData($rowLeft, $top, $user->username ?? '', 'Arial', 9, 'left', $colWidths[1]);
            $rowLeft += $colWidths[1];

            $this->pdf->plotData($rowLeft, $top, $user->name ?? '', 'Arial', 9, 'left', $colWidths[2]);
            $rowLeft += $colWidths[2];

            $this->pdf->plotData($rowLeft, $top, $user->email ?? '', 'Arial', 9, 'left', $colWidths[3]);
            $rowLeft += $colWidths[3];

            // Created At (formatted)
            $createdAt = '';
            if (!empty($user->created_at)) {
                $createdAt = Carbon::parse($user->created_at)->format('m/d/Y');
            }

            $this->pdf->plotData($rowLeft, $top, $createdAt, 'Arial', 9, 'left', $colWidths[4]);

            $top += $lineHeight;
        }
    }

    protected function renderFilterSection(int $top, int $left, int $lineHeight): void
    {
        $this->pdf->plotData($left, $top, 'All Users', 'Arial', 10);
    }
}
