<?php
namespace App\Exports\Shared;

use Maatwebsite\Excel\Concerns\{
    FromCollection,
    WithEvents,
    WithHeadings,
    WithCustomStartCell,
    WithColumnWidths,
    WithStyles
};
use Maatwebsite\Excel\Events\{
    BeforeSheet,
    AfterSheet
};
use PhpOffice\PhpSpreadsheet\{
    Worksheet\Worksheet,
    Style\Alignment,
    Style\Fill,
    Cell\Coordinate
};
use Carbon\Carbon;

class DynamicExport implements WithEvents, WithCustomStartCell, FromCollection, WithHeadings, WithColumnWidths, WithStyles
{
    protected $export;

    public function __construct($export)
    {
        $this->export = $export;
    }

    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event) {
                $row = 1;
                $event->sheet->setCellValue('A' . $row++, 'Lee Systems Technology Ventures, Inc.');
                $event->sheet->setCellValue('A' . $row++, $this->export['file']);
                
                if (isset($this->export['employee_name']) && $this->export['employee_name']) {
                    $event->sheet->setCellValue('A' . $row++, 'Employee Name: ' . $this->export['employee_name']);
                }
                
                if (isset($this->export['printed_by']) && $this->export['printed_by']) {
                    $event->sheet->setCellValue('A' . $row++, 'Printed By: ' . $this->export['printed_by']);
                }
                
                $dateLabel = (isset($this->export['employee_name']) || isset($this->export['printed_by'])) 
                    ? 'Print Date' : 'Date Export';
                $event->sheet->setCellValue('A' . $row++, $dateLabel . ': ' . Carbon::now()->format('F d, Y, h:i A'));
                
                // Style headers
                $event->sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $event->sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
            },
            
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastCol = Coordinate::stringFromColumnIndex(count($this->export['header']));
                
                // Header row calculation (same as startCell)
                $headerRow = 4;
                if (isset($this->export['employee_name']) && $this->export['employee_name']) $headerRow++;
                if (isset($this->export['printed_by']) && $this->export['printed_by']) $headerRow++;
                $headerRow++;
                
                $headerRange = "A{$headerRow}:{$lastCol}{$headerRow}";
                
                // Apply header styling (GRAY BACKGROUND + BOLD)
                $sheet->getStyle($headerRange)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'D3D3D3']
                    ],
                ]);
                // Auto filter on headers
                $sheet->setAutoFilter($headerRange);
                
                // Times New Roman everywhere
                $sheet->getStyle($sheet->calculateWorksheetDimension())->applyFromArray([
                    'font' => ['name' => 'Times New Roman', 'size' => 12]
                ]);
            }
        ];
    }

    public function startCell(): string
    {
        $startRow = 4;
        if (isset($this->export['employee_name']) && $this->export['employee_name']) $startRow++;
        if (isset($this->export['printed_by']) && $this->export['printed_by']) $startRow++;
        $startRow++;
        return 'A' . $startRow;
    }

    public function headings(): array
    {
        return $this->export['header'];
    }

    public function collection()
    {
        return $this->export['data'];
    }

    // NEW: Column widths (configurable)
    public function columnWidths(): array
    {
        return $this->export['columnWidths'] ?? [
            'A' => 10, 'B' => 25, 'C' => 25, 'D' => 35, 'E' => 20
        ];
    }

    // NEW: Header styling (centered + bold)
    public function styles(Worksheet $sheet)
    {
        $headerRow = 4;
        if (isset($this->export['employee_name']) && $this->export['employee_name']) $headerRow++;
        if (isset($this->export['printed_by']) && $this->export['printed_by']) $headerRow++;
        $headerRow++;

        $lastCol = Coordinate::stringFromColumnIndex(count($this->export['header']));
        $headerRange = "A{$headerRow}:{$lastCol}{$headerRow}";

        return [
            $headerRow => [
                'font' => ['bold' => true],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }
}
