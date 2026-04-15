<?php

namespace App\Services;

use App\Models\Record;
use App\Models\Upload;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Facades\Excel;

class ExcelImportService
{
    public function import(Upload $upload): bool
    {
        try {
            $fileName = $upload->filename;
            $filePath = $this->findUploadFile($fileName);

            if (!file_exists($filePath)) {
                Log::error('ExcelImportService: Bestand niet gevonden', ['filePath' => $filePath]);
                return false;
            }

            Log::debug('ExcelImportService: Bestand gevonden', ['filePath' => $filePath]);

            // Read the Excel file
            $sheets = Excel::toArray([], $filePath);
            if (empty($sheets)) {
                Log::error('ExcelImportService: Geen sheets gevonden in Excel');
                return false;
            }

            $data = $sheets[0]; // First sheet
            if (empty($data)) {
                Log::error('ExcelImportService: Sheet is leeg');
                return false;
            }

            // First row is headers
            $headers = array_map(fn($header) => $this->normalizeHeader($header), $data[0]);
            Log::debug('ExcelImportService: Excel headers gevonden', ['headers' => $headers]);

            // Detect columns
            $columns = $this->detectColumns($headers);
            Log::debug('ExcelImportService: Kolommen gemapped', ['columns' => $columns]);

            $count = 0;
            $userId = $upload->user_id;

            // Process data rows (skip header row)
            for ($i = 1; $i < count($data); $i++) {
                $row = $data[$i];

                // Convert to associative array using headers
                $rowData = [];
                foreach ($headers as $idx => $header) {
                    $rowData[$header] = $row[$idx] ?? null;
                }

                // Skip rows without action or date
                $action = trim((string) ($rowData[$columns['action']] ?? ''));
                $date = $rowData[$columns['date']] ?? null;

                if (empty($action) && empty($date)) {
                    continue;
                }

                // Skip total row
                if (strtolower($action) === 'totaal') {
                    continue;
                }

                Record::create([
                    'upload_id' => $upload->bestand_id,
                    'user_id' => $userId,
                    'date' => $this->parseDate($date),
                    'action' => $action,
                    'description' => trim((string) ($rowData[$columns['description']] ?? '')),
                    'worker' => trim((string) ($rowData[$columns['worker']] ?? '')),
                    'time' => $this->parseNumeric($rowData[$columns['time']] ?? null),
                    'costs' => $this->parseNumeric($rowData[$columns['costs']] ?? null),
                ]);

                $count++;
            }

            $upload->update([
                'processed_rows' => $count,
                'status' => 'completed',
            ]);

            Log::info('ExcelImportService: Import voltooid', ['count' => $count, 'upload_id' => $upload->bestand_id]);
            return true;
        } catch (\Exception $e) {
            Log::error('ExcelImportService: Fout bij import', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return false;
        }
    }

    private function detectColumns(array $headers): array
    {
        $patterns = [
            'action' => ['actie', 'action'],
            'date' => ['datum', 'date'],
            'description' => ['omschrijving', 'beschrijving', 'description', 'toelichting', 'details'],
            'worker' => ['medewerker', 'worker', 'werknemer', 'employee'],
            'time' => ['uren', 'time', 'hours', 'duur', 'tijd'],
            'costs' => ['kosten', 'costs', 'bedrag', 'amount', 'prijs', 'tarief'],
        ];

        $columns = [];
        $normalizedHeaders = array_map(fn($header) => $this->normalizeHeader($header), $headers);

        Log::debug('ExcelImportService: Detecteer kolommen', [
            'headers' => $normalizedHeaders,
        ]);

        foreach ($patterns as $field => $possibleNames) {
            $columns[$field] = null;
            foreach ($normalizedHeaders as $idx => $header) {
                foreach ($possibleNames as $name) {
                    if (strpos($header, $name) !== false) {
                        $columns[$field] = $normalizedHeaders[$idx];
                        Log::debug('ExcelImportService: Kolom gevonden', [
                            'field' => $field,
                            'header' => $columns[$field],
                            'matched_pattern' => $name,
                        ]);
                        break 2;
                    }
                }
            }

            if ($columns[$field] === null) {
                Log::warning('ExcelImportService: Kolom niet gevonden', [
                    'field' => $field,
                    'expected_patterns' => $possibleNames,
                    'headers' => $normalizedHeaders,
                ]);
            }
        }

        return $columns;
    }

    private function normalizeHeader(mixed $value): string
    {
        $header = (string) ($value ?? '');
        $header = preg_replace('/^\xEF\xBB\xBF/', '', $header) ?? $header; // UTF-8 BOM
        $header = str_replace("\xC2\xA0", ' ', $header); // NBSP
        $header = mb_strtolower(trim($header));
        $header = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $header) ?? $header;
        $header = preg_replace('/\s+/u', ' ', $header) ?? $header;

        return trim($header);
    }

    private function findUploadFile(string $fileName): string
    {
        $fileName = basename($fileName);
        $basePath = storage_path('app' . DIRECTORY_SEPARATOR . 'private' . DIRECTORY_SEPARATOR . 'uploads');
        return $basePath . DIRECTORY_SEPARATOR . $fileName;
    }

    private function parseDate(mixed $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        if (is_numeric($value)) {
            try {
                return Date::excelToDateTimeObject((float) $value)->format('Y-m-d');
            } catch (\Exception) {
                return null;
            }
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception) {
            return null;
        }
    }

    private function parseNumeric(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $value = trim((string) $value);
        $value = preg_replace('/[€$£\s]/u', '', $value) ?? $value;

        if (strpos($value, ',') !== false && strpos($value, '.') !== false) {
            if (strrpos($value, ',') > strrpos($value, '.')) {
                $value = str_replace('.', '', $value);
                $value = str_replace(',', '.', $value);
            } else {
                $value = str_replace(',', '', $value);
            }
        } elseif (strpos($value, ',') !== false) {
            $value = str_replace(',', '.', $value);
        }

        return is_numeric($value) ? (float) $value : null;
    }
}
