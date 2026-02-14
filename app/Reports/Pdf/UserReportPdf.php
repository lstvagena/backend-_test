<?php

namespace App\Reports\Pdf;

use App\Reports\Pdf\BaseReportPdf;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class UserReportPdf extends BaseReportPdf
{
    // Initialize report with filters
    public function __construct(array $filters)
    {
        parent::__construct($filters, 'Users Report'); // Set report title
    }

    // Generate and stream PDF
    public function render(array $data)
    {
        $this->pdf->AddPage(); // Add first page

        $this->renderHeader(); // Optional header
        $this->renderFilters(); // Optional filters
        $this->renderData($data); // Render table data

        return $this->streamData(); // Output PDF
    }

    // Render users table
    protected function renderData(array $data): void
    {
        $users = collect($data['users'] ?? []); // Get users collection

        $top = 60; // Initial Y position
        $left = 10; // Initial X position
        $lineHeight = 6; // Row height

        $colWidths = [15, 30, 35, 45, 55, 40]; // Column widths
        $headers = ['ID', 'User Type', 'Username', 'Name', 'Email', 'Created At']; // Table headers

        // Render table header
        $headerLeft = $left; // Header X position
        foreach ($headers as $index => $header) {
            $this->pdf->plotData($headerLeft, $top, $header, 'Arial', 10, 'left', $colWidths[$index]); // Print header
            $headerLeft += $colWidths[$index]; // Move to next column
        }

        $top += $lineHeight; // Move below header
        $this->pdf->addLine($left, $top, 200, $top); // Draw separator line
        $top += $lineHeight; // Move to first row

        // Render rows
        foreach ($users as $user) {

            if ($top > 270) { // Check page overflow
                $this->pdf->AddPage(); // Add new page
                $top = 20; // Reset Y position

                // Re-render header on new page
                $headerLeft = $left; // Reset header X
                foreach ($headers as $index => $header) {
                    $this->pdf->plotData($headerLeft, $top, $header, 'Arial', 10, 'left', $colWidths[$index]); // Print header
                    $headerLeft += $colWidths[$index]; // Move column
                }

                $top += $lineHeight; // Move below header
                $this->pdf->addLine($left, $top, 200, $top); // Draw separator
                $top += $lineHeight; // Move to next row
            }

            $rowLeft = $left; // Reset row X position

            $this->pdf->plotData($rowLeft, $top, $user->id ?? '', 'Arial', 9, 'left', $colWidths[0]); // Print ID
            $rowLeft += $colWidths[0]; // Move column

            $this->pdf->plotData($rowLeft, $top, $user->userType->name ?? '', 'Arial', 9, 'left', $colWidths[1]); // Print username
            $rowLeft += $colWidths[1]; // Move column

            $this->pdf->plotData($rowLeft, $top, $user->username ?? '', 'Arial', 9, 'left', $colWidths[1]); // Print username
            $rowLeft += $colWidths[2]; // Move column

            $this->pdf->plotData($rowLeft, $top, $user->name ?? '', 'Arial', 9, 'left', $colWidths[2]); // Print name
            $rowLeft += $colWidths[3]; // Move column

            $this->pdf->plotData($rowLeft, $top, $user->email ?? '', 'Arial', 9, 'left', $colWidths[3]); // Print email
            $rowLeft += $colWidths[4]; // Move column

            // Format created date
            $createdAt = ''; // Default date
            if (!empty($user->created_at)) {
                $createdAt = Carbon::parse($user->created_at)->format('m/d/Y'); // Format date
            }

            $this->pdf->plotData($rowLeft, $top, $createdAt, 'Arial', 9, 'left', $colWidths[5]); // Print created date

            $top += $lineHeight; // Move to next row
        }
    }

    // Render filter section
    protected function renderFilterSection(int $top, int $left, int $lineHeight): void
    {
        $this->pdf->plotData($left, $top, 'All Users', 'Arial', 10); // Display filter text
    }
}
