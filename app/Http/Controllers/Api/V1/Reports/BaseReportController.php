<?php

namespace App\Http\Controllers\Api\V1\Reports;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

abstract class BaseReportController extends Controller
{
    // Child must return service class
    abstract protected function getServiceClass(): string;

    // Child must return PDF filename
    abstract protected function getPdfFilename(): string;

    public function export(Request $request)
    {
        // Resolve service dynamically
        $service = app($this->getServiceClass());

        // Export as table format
        return $service->export($request, 'tab');
    }

    public function downloadPdf(Request $request)
    {
        // Resolve service dynamically
        $service = app($this->getServiceClass());

        // Export as PDF
        $response = $service->export($request, 'pdf');
        
        // Set inline PDF filename
        return $response->header('Content-Disposition', 'inline; filename="' . $this->getPdfFilename() . '.pdf"');
    }
    
}
