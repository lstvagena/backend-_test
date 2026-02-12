<?php

namespace App\Services\Shared\pdf;

use Carbon\Carbon;

trait PdfFooter
{
    public function getPrintedDateTime(): string {

        return 'Date Printed: ' . Carbon::now()->format('F d, Y, h:i A');
    }

    public function xgetMpdfFooterHtml(): string {

        return '
            <table width="100%" style="font-size: 10px; font-family: helvetica; padding-top: 5px;">
                <tr>
                    <td>' . $this->getPrintedDateTime() . '</td>
                    <td style="text-align: right;">Page {PAGENO} of {nbpg}</td>
                </tr>
            </table>
        ';
    }

    public function getMpdfFooterHtml(): string
    {
        return '
            <table width="100%" style="font-size: 10px; font-family: helvetica;">
                <tr>
                    <td width="50%"> Date Printed: ' . $this->getPrintedDateTime() . '</td>
                    <td width="50%" align="right">Page {PAGENO} of {nbpg}</td>
                </tr>
            </table>
        ';
    }
}
