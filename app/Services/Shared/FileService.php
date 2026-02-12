<?php

namespace App\Services\Shared;

use App\Http\Controllers\Controller;
use App\Helpers\{
    QueryResultHelper,
    ImportHelper,
};
use App\Imports\Shared\TemplateImport;
use Maatwebsite\Excel\Facades\Excel;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as PDF;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Exception;

class FileService extends Controller
{
    public function downloadTemplate($model)
    {
        try {
            $fillable = $model->getFillable();

            // Find the entity field (field ending with _desc)
            $entity = null;
            foreach ($fillable as $item) {
                if (Str::endsWith($item, '_desc')) {
                    $entity = str_replace('_desc', '', $item);
                    break;
                }
            }

            // Exclude system fields
            $excludedColumns = array_filter([
                'record_id',
                $entity ? $entity . '_id' : null,
                'created_at',
                'updated_at',
            ]);

            // Get only importable fillable fields
            $importableFields = array_values(
                array_diff($fillable, $excludedColumns)
            );

            // Format headers: remove _desc suffix and convert to Title Case
            $headers = [];
            foreach ($importableFields as $field) {
                $cleanField = Str::endsWith($field, '_desc')
                    ? Str::replaceLast('_desc', '', $field)
                    : $field;
                $header = Str::of($cleanField)
                    ->replace('_', ' ')
                    ->title()
                    ->toString();
                $headers[] = $header;
            }

            // Generate example data rows (3 examples)
            $exampleData = [];
            $exampleCount = 3;

            // Get entity-specific examples if available
            $entityExamples = $this->getEntityExamples($entity, $exampleCount);

            for ($i = 0; $i < $exampleCount; $i++) {
                $row = [];
                foreach ($importableFields as $field) {
                    // Generate example values based on field type
                    if (Str::endsWith($field, '_desc')) {
                        // Use entity-specific examples if available
                        if (!empty($entityExamples) && isset($entityExamples[$i])) {
                            $row[] = $entityExamples[$i];
                        } else {
                            // Generic examples
                            $entityName = Str::replaceLast('_desc', '', $field);
                            $entityName = Str::of($entityName)->replace('_', ' ')->title()->toString();
                            $row[] = "Example " . ($i + 1);
                        }
                    } elseif (Str::contains($field, ['email'])) {
                        $row[] = "example" . ($i + 1) . "@example.com";
                    } elseif (Str::contains($field, ['phone', 'mobile', 'contact'])) {
                        $row[] = "09" . str_pad($i + 1, 9, '0', STR_PAD_LEFT);
                    } elseif (Str::contains($field, ['date'])) {
                        $row[] = date('Y-m-d', strtotime('+' . ($i + 1) . ' days'));
                    } elseif (Str::contains($field, ['code', 'number', 'id'])) {
                        $row[] = "CODE" . str_pad($i + 1, 3, '0', STR_PAD_LEFT);
                    } else {
                        // For other fields, provide empty values (user will fill)
                        $row[] = '';
                    }
                }
                $exampleData[] = $row;
            }

            // Format file name
            $tableName = $model->getTable();
            $fileTitle = $this->formatFileTitle($tableName);
            $fileName = $this->formatFileName(str_replace(' File', ' Template', $fileTitle), 'xlsx');

            return [
                'headers' => $headers,
                'example_data' => $exampleData,
                'file_name' => $fileName,
            ];
        } catch (Exception $e) {
            return QueryResultHelper::onError($e);
        }
    }

    public function import($model, UploadedFile $file, $errorFormat = 'txt')
    {
        try {
            // Import Excel file
            $import = new TemplateImport();

            try {
                Excel::import($import, $file);
            } catch (\Exception $excelException) {
                // Check if it's a Windows file locking/unlink error during cleanup
                // This happens after the import succeeds, so we can safely ignore it
                if (
                    str_contains($excelException->getMessage(), 'unlink') &&
                    (str_contains($excelException->getMessage(), 'Resource temporarily unavailable') ||
                        str_contains($excelException->getMessage(), 'No such file'))
                ) {
                    // Windows file locking issue during cleanup - import already succeeded
                    // The data is already in $import, so we can continue
                    // This is a known Windows issue with Laravel Excel temporary file cleanup
                } else {
                    // Re-throw if it's a different error
                    throw $excelException;
                }
            }

            $excelHeaders = $import->headers;
            $excelRows = $import->rows;

            // Get model fillable fields and create header mapping
            $fillable = $model->getFillable();
            $entity = null;
            foreach ($fillable as $item) {
                if (Str::endsWith($item, '_desc')) {
                    $entity = str_replace('_desc', '', $item);
                    break;
                }
            }

            // Exclude system fields
            $excludedColumns = array_filter([
                'record_id',
                $entity ? $entity . '_id' : null,
                'created_at',
                'updated_at',
            ]);

            $importableFields = array_values(
                array_diff($fillable, $excludedColumns)
            );

            // Create header to field mapping and column position mapping
            // Laravel Excel normalizes headers: lowercase, spaces/underscores to single underscore
            // Example: "Area" -> "area", "Region Code" -> "region_code"
            $headerMapping = [];
            $headerToColumn = [];

            // Build expected normalized headers from importable fields
            $expectedHeaders = [];
            foreach ($importableFields as $field) {
                $cleanField = Str::endsWith($field, '_desc')
                    ? Str::replaceLast('_desc', '', $field)
                    : $field;

                // Convert to title case (as generated in template): "area" -> "Area"
                $templateHeader = Str::of($cleanField)
                    ->replace('_', ' ')
                    ->title()
                    ->toString();

                // Normalize the same way Laravel Excel does: "Area" -> "area"
                $normalizedHeader = Str::of($templateHeader)
                    ->lower()
                    ->replace(' ', '_')
                    ->replace('_', '_') // Ensure single underscores
                    ->toString();

                $expectedHeaders[$normalizedHeader] = [
                    'field' => $field,
                    'template_header' => $templateHeader,
                    'normalized' => $normalizedHeader
                ];
            }

            // Map Excel headers (already normalized by Laravel Excel) to DB fields
            foreach ($excelHeaders as $index => $excelHeader) {
                $columnNumber = $index + 1;

                // Try exact match first
                if (isset($expectedHeaders[$excelHeader])) {
                    $headerMapping[$excelHeader] = $expectedHeaders[$excelHeader]['field'];
                    $headerToColumn[$excelHeader] = $columnNumber;
                } else {
                    // Try case-insensitive match
                    $excelHeaderLower = Str::lower($excelHeader);
                    foreach ($expectedHeaders as $normalized => $info) {
                        if ($excelHeaderLower === $normalized) {
                            $headerMapping[$excelHeader] = $info['field'];
                            $headerToColumn[$excelHeader] = $columnNumber;
                            break;
                        }
                    }
                }
            }

            // Check if any headers were matched
            if (empty($headerMapping)) {
                return [
                    'status' => false,
                    'message' => 'No matching headers found. Expected headers: ' . implode(', ', array_column($expectedHeaders, 'template_header')) . '. Found headers: ' . implode(', ', $excelHeaders),
                    'expected_headers' => array_column($expectedHeaders, 'template_header'),
                    'found_headers' => $excelHeaders
                ];
            }

            // Validate all rows and collect errors
            $errors = [];
            $validRows = [];

            foreach ($excelRows as $rowIndex => $row) {
                $excelRowNumber = $rowIndex + 2; // Excel row number (header is row 1, data starts at row 2)

                // Map Excel row to database fields
                $mappedRow = [];
                $isEmptyRow = true;

                // Iterate through mapped headers (only those that matched)
                foreach ($headerMapping as $excelHeader => $dbField) {
                    $value = $row[$excelHeader] ?? null;

                    // Handle different value types
                    if ($value !== null) {
                        $value = is_string($value) ? trim($value) : $value;
                        if ($value !== '' && $value !== null) {
                            $isEmptyRow = false;
                        }
                    }

                    $mappedRow[$dbField] = $value;
                }

                // If no mapping was found, skip this row
                if (empty($headerMapping)) {
                    continue;
                }

                // Skip completely empty rows
                if ($isEmptyRow) {
                    continue;
                }

                // Validate required fields
                foreach ($headerMapping as $excelHeader => $dbField) {
                    $value = $mappedRow[$dbField] ?? null;
                    $columnNumber = $headerToColumn[$excelHeader];

                    // Check if field is required (not empty)
                    if (empty($value) || (is_string($value) && trim($value) === '')) {
                        $errors[] = [
                            'row' => $excelRowNumber,
                            'column' => $columnNumber,
                            'message' => 'IT CANNOT BE BLANK (IF THIS IS NOT THE LAST VALUE IN A SPECIFIC ROW)'
                        ];
                    }
                }

                // Check for duplicates (only if no blank field errors for this row)
                if (!$this->hasErrorForRow($errors, $excelRowNumber)) {
                    $uniqueKeyField = null;
                    foreach ($mappedRow as $field => $value) {
                        if (Str::endsWith($field, '_desc') && !empty($value)) {
                            $uniqueKeyField = $field;
                            break;
                        }
                    }

                    if ($uniqueKeyField) {
                        $exists = $model::where($uniqueKeyField, $mappedRow[$uniqueKeyField])->exists();
                        if ($exists) {
                            // Find the Excel header for this field
                            $excelHeader = array_search($uniqueKeyField, $headerMapping);
                            $columnNumber = $headerToColumn[$excelHeader];

                            $errors[] = [
                                'row' => $excelRowNumber,
                                'column' => $columnNumber,
                                'message' => 'VALUE IS ALREADY EXIST'
                            ];
                        }
                    }

                    // Run custom validation if exists
                    if (method_exists($model, 'pagerCustomValidation')) {
                        $validationResult = $model::pagerCustomValidation($mappedRow);
                        if (!empty($validationResult['errors'])) {
                            foreach ($validationResult['errors'] as $field => $fieldErrors) {
                                if (isset($headerMapping[$field]) || in_array($field, $headerMapping)) {
                                    // Find the Excel header for this field
                                    $excelHeader = array_search($field, $headerMapping) ?: $field;
                                    if (isset($headerToColumn[$excelHeader])) {
                                        $columnNumber = $headerToColumn[$excelHeader];
                                    } else {
                                        // Try to find by db field name
                                        $excelHeader = array_search($field, $headerMapping);
                                        $columnNumber = $excelHeader ? $headerToColumn[$excelHeader] : 1;
                                    }

                                    foreach ($fieldErrors as $errorMessage) {
                                        $errors[] = [
                                            'row' => $excelRowNumber,
                                            'column' => $columnNumber,
                                            'message' => $errorMessage
                                        ];
                                    }
                                }
                            }
                        }
                    }
                }

                // Store valid row for insertion (only if no errors for this row)
                if (!$this->hasErrorForRow($errors, $excelRowNumber)) {
                    $validRows[] = $mappedRow;
                }
            }

            // If there are errors, generate error file and return
            if (!empty($errors)) {
                $errorFile = $this->generateErrorFile($errors, $model, $errorFormat);
                return [
                    'status' => false,
                    'message' => 'Import failed. Please check the error file.',
                    'error_file' => $errorFile
                ];
            }

            // All rows are valid, insert all data
            DB::beginTransaction();
            try {
                foreach ($validRows as $row) {
                    $model::create($row);
                }
                DB::commit();

                return [
                    'status' => true,
                    'message' => 'Import completed successfully. ' . count($validRows) . ' record(s) imported.',
                    'imported_count' => count($validRows)
                ];
            } catch (Exception $e) {
                DB::rollBack();
                return QueryResultHelper::onError($e);
            }
        } catch (Exception $e) {
            return QueryResultHelper::onError($e);
        }
    }

    private function hasErrorForRow($errors, $rowNumber)
    {
        foreach ($errors as $error) {
            if ($error['row'] === $rowNumber) {
                return true;
            }
        }
        return false;
    }

    private function generateErrorFile($errors, $model, $format = 'txt')
    {
        $tableName = $model->getTable();
        $fileTitle = $this->formatFileTitle($tableName);
        $fileName = 'import_error_' . strtolower(str_replace(' ', '_', $fileTitle)) . '_' . date('Y-m-d_His');

        if ($format === 'pdf') {
            return $this->generateErrorPdf($errors, $fileName);
        } else {
            return $this->generateErrorTxt($errors, $fileName);
        }
    }

    private function generateErrorTxt($errors, $fileName)
    {
        $content = "ERROR LOG:\n\n";

        foreach ($errors as $error) {
            $content .= "ROW {$error['row']} - COLUMN {$error['column']}: {$error['message']}\n";
        }

        $filePath = storage_path('app/public/' . $fileName . '.txt');
        $directory = dirname($filePath);

        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents($filePath, $content);

        return [
            'file_path' => $filePath,
            'file_name' => $fileName . '.txt',
            'download_url' => asset('storage/' . $fileName . '.txt')
        ];
    }

    private function generateErrorPdf($errors, $fileName)
    {
        $html = view('pdf.import-error', ['errors' => $errors])->render();

        $pdf = PDF::loadView('pdf.import-error', ['errors' => $errors], [], [
            'margin_bottom' => 20
        ]);

        $filePath = storage_path('app/public/' . $fileName . '.pdf');
        $directory = dirname($filePath);

        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents($filePath, $pdf->output());

        return [
            'file_path' => $filePath,
            'file_name' => $fileName . '.pdf',
            'download_url' => asset('storage/' . $fileName . '.pdf')
        ];
    }

    public function exportPrint($query, $extension = 'xlsx', $explicitFileTitle = null, $explicitFileName = null)
    {
        $tableName = $query->getModel()->getTable();
        $fileTitle = $explicitFileTitle ?? $this->formatFileTitle($tableName);
        $headerQuery = clone $query;
        $headers = $headerQuery->first() ? array_keys($headerQuery->first()->getAttributes()) : [];
        $collection = $query->get();
        $fileName = $explicitFileName ?? $this->formatFileName($fileTitle, $extension);
        return [
            'data' => [
                'file' => $fileTitle,
                'header' => $headers,
                'data' => $collection,
            ],
            'file_name' => $fileName,
        ];
    }

    private function formatFileTitle($table)
    {
        $tableName = preg_replace('/^(mf_|trn_|sys_|setup_|def_)/', '', $table);
        $tableName = rtrim($tableName, 's');
        return ucfirst($tableName) . ' File';
    }

    private function formatFileName($fileTitle, $extension = 'xlsx')
    {
        return strtolower(str_replace(' ', '_', $fileTitle)) . '.' . $extension;
    }

    private function getEntityExamples($entity, $count = 3)
    {
        // Provide entity-specific examples
        $examples = [
            'area' => ['NCR', 'REGION I', 'REGION II'],
            'department' => ['IT Department', 'HR Department', 'Finance Department'],
            'position' => ['Manager', 'Supervisor', 'Staff'],
            'employee_status' => ['Active', 'On Leave', 'Resigned'],
        ];

        if ($entity && isset($examples[$entity])) {
            return array_slice($examples[$entity], 0, $count);
        }

        return [];
    }
}
