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
    protected Collection $rows;

    public function __construct(Collection $rows)
    {
        $this->rows = $rows;
    }

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function map($row): array
    {
        return [
            $row['ID'] ?? '',
            $row['Username'] ?? '',
            $row['Name'] ?? '',
            $row['Email'] ?? '',
            $row['Created At'] ?? '',
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Username',
            'Name',
            'Email',
            'Created At',
        ];
    }

    public function startCell(): string
    {
        return 'A5';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10,
            'B' => 25,
            'C' => 25,
            'D' => 35,
            'E' => 20,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            5 => [
                'font' => ['bold' => true],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event) {

                $sheet = $event->sheet->getDelegate();

                $sheet->setCellValue('A1', 'Lee Systems Technology Ventures, Inc.');
                $sheet->setCellValue('A2', 'Users Report');
                $sheet->setCellValue('A3', 'Date Printed: ' . Carbon::now()->format('F d, Y, h:i A'));

                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
            },

            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                $lastColumn = Coordinate::stringFromColumnIndex(count($this->headings()));
                $headerRange = 'A5:' . $lastColumn . '5';

                $sheet->getStyle($headerRange)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'D3D3D3'],
                    ],
                ]);

                $sheet->setAutoFilter($headerRange);
            },
        ];
    }
}
