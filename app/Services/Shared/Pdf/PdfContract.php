<?php
namespace App\Services\Shared\pdf;

use Illuminate\Support\Facades\Log;
use Mpdf\HTMLParserMode;
use Mpdf\Mpdf;
use App\Services\Shared\pdf\PdfFooter;

class LSTVPDF extends Mpdf
{
    use PdfFooter;

    public string $page_orientation = 'P'; // default portrait
    protected $fileName;

    public function __construct(array $config = [], $fileName = 'test')
    {
        // Default paper size and orientation if not passed
        $config = array_merge([
            'format' => 'A4',
            'orientation' => $this->page_orientation,
            'mode' => 'utf-8',
        ], $config);
        $this->fileName = $fileName;

        parent::__construct($config);
    }

    public static array $paperSizes = [
        'A4'     => [210, 297],
        'Letter' => [216, 279],
        'Legal'  => [216, 356],
        'A5'     => [148, 210],
    ];

    public static function getPaperSize(string $key): array {
        return self::$paperSizes[$key] ?? self::$paperSizes['A4']; // fallback to A4
    }

    public function plotData(int $left, int $top, string $value, string $font_style = 'Arial', int $font_size = 12, string $alignment = 'left', int $width = 0) {
        if($width == 0) {
            $width = $this->GetStringWidth(strip_tags($value));
        }

        $this->addText($left, $top, $value, $font_style, $font_size, $alignment, $width);
    }

    public function ellipsisStringWrap(string $string, int $box_width, string $font_style = "Arial", int $font_size = 12, bool $is_debug = false) {
        $this->SetFont($font_style, '', $font_size);
        $value_width = $this->GetStringWidth($string);
        if($value_width > $box_width) {
            while ($this->GetStringWidth($string . '...') > $box_width && mb_strlen($string) > 0) {
                $string = mb_substr($string, 0, -1);
            }
            $string .= '...';
        }
        return $string;
    }
    public function plotWrappedData( int $left, int $top, string $value, string $font_style = 'Arial', int $font_size = 12, string $alignment = 'left', int $maxWidth = 0, int $lineHeight = 5) {
        $this->SetFont($font_style, '', $font_size);
        $lines = [];
        $words = explode(' ', $value);
        $currentLine = '';

        foreach ($words as $word) {
            $testLine = $currentLine === '' ? $word : $currentLine . ' ' . $word;
            $width = $this->GetStringWidth($testLine);

            if ($width > $maxWidth && $currentLine !== '') {
                $lines[] = $currentLine;
                $currentLine = $word;
            } else {
                $currentLine = $testLine;
            }
        }
        if ($currentLine !== '') {
            $lines[] = $currentLine;
        }

        $currentTop = $top;
        $wrapInstance = 1;
        foreach ($lines as $line) {
            if($wrapInstance > 1) {
                $currentTop += $lineHeight;
            }
            $this->addText($left, $currentTop, $line, $font_style, $font_size, $alignment, $maxWidth);
            $wrapInstance++;
        }
        return $currentTop;
    }


    public function _addText(int $left, int $top, string $value, string $font_style = 'Arial', int $font_size = 12, $alignment = 'left', $width = 0) {
        try {
            $_width = $width > 0 ? "{$width}mm" : "auto";
            $value = "<div style=\"
                        position: absolute;
                        top: {$top}mm;
                        left: {$left}mm;
                        font-family: {$font_style};
                        font-size: {$font_size}pt;
                        width: {$_width};
                        text-align: {$alignment};
                        border: solid 1px silver\">
                        $value
                    </div>";
            $this->WriteHTML($value);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
        }
    }

    public function addText(int $left, int $top, string $value, string $font_style = 'Arial', int $font_size = 12, string $alignment = 'left', int $width = 0) {
        try {
            $_width = $width > 0 ? "{$width}mm" : "auto";

            $html = "
                <div style=\"
                    position: absolute;
                    top: {$top}mm;
                    left: {$left}mm;
                \">
                    <table cellpadding=\"0\" cellspacing=\"0\">
                        <tr>
                            <td style=\"
                                white-space: nowrap;
                                font-family: {$font_style};
                                font-size: {$font_size}pt;
                                width: {$_width};
                                text-align: {$alignment};
                            \">
                                {$value}
                            </td>
                        </tr>
                    </table>
                </div>";

            $this->WriteHTML($html);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
        }
    }

    public function addLine($x1, $y1, $x2, $y2){
        // This is to uniform with export function
        $this->Line($x1, $y1, $x2, $y2);
    }

    public function addImage($file, $left, $top, $width, $height){
        $this->Image($file, $left, $top, $width, $height);
    }

    public function _streamData($fileName = ''){
        try {
            $file_name = $this->fileName.'.pdf';
            $file_path = storage_path('app/public/'.$file_name);
            $URL = asset('storage/'.$file_name);

            if(!file_exists(dirname($file_path))){
                mkdir(dirname($file_path), 0777, true);
            }

            $this->Output($file_path, \Mpdf\Output\Destination::FILE);
            return $URL;
        } catch (\Throwable $th) {
            Log::error($th);
        }
    }

    public function streamData($fileName = ''){
        return response($this->Output($fileName, \Mpdf\Output\Destination::STRING_RETURN))
                ->header('Content-Type', 'application/pdf');
    }
}
