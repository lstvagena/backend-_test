<?php

namespace App\Exports\Reports;

use Maatwebsite\Excel\Concerns\{
    FromCollection,
    WithHeadings,
    WithMapping,
    WithEvents,
    WithCustomStartCell,
    WithColumnWidths,
    WithStyles
};
use Maatwebsite\Excel\Events\{
    BeforeSheet,
    AfterSheet
};
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class UsersReportExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithEvents,
    WithCustomStartCell,    
    WithColumnWidths,
    WithStyles
{
    // Holds export rows
    protected Collection $rows;

    // Inject rows collection
    public function __construct(Collection $rows)
    {
        $this->rows = $rows; // Assign rows
    }

    // Return collection for export
    public function collection(): Collection
    {
        return $this->rows; // Provide data
    }

    // Map each row to Excel columns
    public function map($row): array
    {
        return [
            $row['ID'] ?? '',          // User ID
            $row['User Type'] ?? '', 
            $row['Username'] ?? '',    // Username
            $row['Name'] ?? '',        // Full name
            $row['Email'] ?? '',       // Email address
            $row['Created At'] ?? '',  // Creation date
        ];
    }

    // Define table headers
    public function headings(): array
    {
        return [
            'ID',          // Column A
            'User Type', 
            'Username',    // Column B
            'Name',        // Column C
            'Email',       // Column D
            'Created At',  // Column E
        ];
    }

    // Set starting cell for table
    public function startCell(): string
    {
        return 'A5'; // Data begins at row 5
    }

    // Set column widths
    public function columnWidths(): array
    {
        return [
            'A' => 10, // ID width
            'B' => 20,
            'C' => 25, // Username width
            'D' => 25, // Name width
            'E' => 35, // Email width
            'F' => 20, // Created At width
        ];
    }

    // Apply sheet styles
    public function styles(Worksheet $sheet)
    {
        return [
            5 => [ // Header row
                'font' => ['bold' => true], // Bold text
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER, // Center horizontally
                    'vertical' => Alignment::VERTICAL_CENTER,     // Center vertically
                ],
            ],
        ];
    }

    // Register sheet events
    public function registerEvents(): array
    {
        return [

            // Before sheet is generated
            BeforeSheet::class => function (BeforeSheet $event) {

                $sheet = $event->sheet->getDelegate(); // Get worksheet

                $sheet->setCellValue('A1', 'Lee Systems Technology Ventures, Inc.'); // Company name
                $sheet->setCellValue('A2', 'Users Report'); // Report title
                $sheet->setCellValue('A3', 'Date Printed: ' . Carbon::now()->format('F d, Y, h:i A')); // Print date

                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14); // Style company
                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12); // Style title
            },

            // After sheet is generated
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate(); // Get worksheet

                $lastColumn = Coordinate::stringFromColumnIndex(count($this->headings())); // Last column
                $headerRange = 'A5:' . $lastColumn . '5'; // Header range

                $sheet->getStyle($headerRange)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11], // Header font
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID, // Solid fill
                        'startColor' => ['rgb' => 'D3D3D3'], // Gray background
                    ],
                ]);

                $sheet->setAutoFilter($headerRange); // Enable filter
            },
        ];
    }
}
