<?php

namespace App\Services\Reports;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

abstract class BaseReportService
{
    // Child class must specify which repository handles data retrieval
    abstract protected function getRepositoryClass(): string;

    // Child class must specify which class generates the PDF
    abstract protected function getPdfRendererClass(): string;

    // Child class must specify which class handles Excel export
    abstract protected function getExportClass(): string;

    // Child class must define the base filename for exports
    abstract protected function getFilename(): string;

    /*
      Ask Repository to fetch data from database
    | Transform data (mainly for Excel format)
    | Decide whether to generate Excel or PDF
    */
    public function export(Request $request, string $type = 'pdf')
    {
       $filters = $request->all(); 
        // Extract all filtering parameters from request

        $data = $this->getData($filters); 
        // Fetch raw dataset from repository
        // It calls like UserReportRepository::getData()

        $rows = $this->transformData($data, $filters); 
        // Convert raw data into flat rows for Excel
        // It calls like  UserReportRepository::transformToRows()

        return match ($type) {
            'tab' => $this->exportSpreadsheet($rows),  // If the request is tab then generate this class
           
            default => $this->exportPdf($data, $filters), // if not run teh default class
            
        };
    }


    /*
    This method retrieves the repository class defined in the child service (e.g., UserReportService).
    Then it calls that repository's getData() method.
    */
    protected function getData(array $filters): array
    {
        $repository = $this->getRepositoryClass(); 
        // Get repository class defined by child
        // Example: returns UserReportRepository::class

        return $repository::getData($filters); 
        // Call repository to retrieve report data
    }

    /*
    Convert raw database data into structured rows.
    Used for Excel exports.
    */
    protected function transformData(array $data, array $filters): Collection
    {
        $repository = $this->getRepositoryClass(); 
        // Get repository class defined by child

        return $repository::transformToRows($data, $filters); 
        // Transform dataset into exportable rows
    }

    /*
    Generate and return an Excel (.xlsx) file.
    Build filename
    Get export class defined in child service
    Pass rows into export class
    Return download response
    */
    protected function exportSpreadsheet(Collection $rows)
    {
        $filename = $this->getFilename() . '_' . date('Y-m-d_His') . '.xlsx'; 
        // Build timestamped Excel filename

        $exportClass = $this->getExportClass(); 
        // Get Excel export class defined by child

        return Excel::download(new $exportClass($rows), $filename); 
        // Trigger Excel file download
    }

    /*
    Generate and return a PDF file.
    Get PDF renderer class from child service
    Create PDF object
    Pass original dataset into PDF
    PDF class renders content
    Return streamed PDF response
    */
    protected function exportPdf(array $data, array $filters)
    {
        $pdfClass = $this->getPdfRendererClass(); 
        // Get PDF renderer class defined by child

        $pdf = new $pdfClass($filters); 
        // Instantiate PDF with filters
        // Filters are used to display filter info in header

        return $pdf->render($data); 
        // Generate and return PDF response
        // PDF class formats and prints data into document
    }
}
