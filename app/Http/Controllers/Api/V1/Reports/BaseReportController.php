<?php

namespace App\Http\Controllers\Api\V1\Reports;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

abstract class BaseReportController extends Controller
{
    abstract protected function getServiceClass(): string;
    abstract protected function getPdfFilename(): string;

    public function export(Request $request)
    {
        $service = app($this->getServiceClass());
        return $service->export($request, 'tab');
    }

    public function downloadPdf(Request $request)
    {
        $service = app($this->getServiceClass());
        $response = $service->export($request, 'pdf');
        
        return $response->header('Content-Disposition', 'inline; filename="' . $this->getPdfFilename() . '.pdf"');
    }
    
}
