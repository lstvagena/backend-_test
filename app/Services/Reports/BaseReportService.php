<?php

namespace App\Services\Reports;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

abstract class BaseReportService
{
    abstract protected function getRepositoryClass(): string;
    abstract protected function getPdfRendererClass(): string;
    abstract protected function getExportClass(): string;
    abstract protected function getFilename(): string;

    public function export(Request $request, string $type = 'pdf')
    {
        $filters = $request->all();
        $data = $this->getData($filters);
        $rows = $this->transformData($data, $filters); // .....

        return match ($type) {
            'tab' => $this->exportSpreadsheet($rows),
            default => $this->exportPdf($data, $filters),
        };
    }

    protected function getData(array $filters): array
    {
        $repository = $this->getRepositoryClass();
        return $repository::getData($filters);
    }

    protected function transformData(array $data, array $filters): Collection
    {
        $repository = $this->getRepositoryClass();
        return $repository::transformToRows($data, $filters);
    }

    protected function exportSpreadsheet(Collection $rows)
    {
        $filename = $this->getFilename() . '_' . date('Y-m-d_His') . '.xlsx';
        $exportClass = $this->getExportClass();

        return Excel::download(new $exportClass($rows), $filename);
    }

    protected function exportPdf(array $data, array $filters)
    {
        $pdfClass = $this->getPdfRendererClass();
        $pdf = new $pdfClass($filters);
        return $pdf->render($data);
    }
}
