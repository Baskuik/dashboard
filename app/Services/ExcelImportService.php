<?php

namespace App\Services;

use App\Models\Upload;
use App\Models\Record;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

class ExcelImportService
{
    /**
     * Kolom-detectie mapping - wat zoeken we naar in kolom-headers
     */
    private array $columnPatterns = [
        'date' => ['datum', 'date', 'dag', 'day', 'created', 'created_at'],
        'action' => ['actie', 'action', 'taak', 'task', 'type'],
        'description' => ['omschrijving', 'description', 'detail', 'opmerking', 'remark'],
        'worker' => ['medewerker', 'worker', 'werknemer', 'employee', 'naam', 'name'],
        'time' => ['uren', 'uur', 'hours', 'time', 'duur', 'duration'],
        'costs' => ['kost', 'kosten', 'cost', 'costs', 'bedrag', 'amount'],
    ];

    /**
     * Importeer Excel file en slaan records op
     */
    public function import(Upload $upload): bool
    {
        try {
            $upload->update(['status' => 'processing']);

            // Zoek het uploadbestand
            $filePath = $this->findUploadFile($upload->filename);

            // Excel-bestand lezen
            $sheets = Excel::toArray(null, $filePath);

            if (empty($sheets) || empty($sheets[0])) {
                throw new \Exception('Excel-bestand is leeg');
            }

            $data = $sheets[0]; // Eerste sheet

            if (count($data) < 2) {
                throw new \Exception('Excel-bestand bevat geen data-rijen');
            }

            // Headers detecteren (eerste rij)
            $headers = array_shift($data);
            $columnMap = $this->detectColumns($headers);

            if (empty($columnMap)) {
                throw new \Exception('Kon geen geldige kolommen detecteren in Excel-bestand');
            }

            // Records verwerken
            $recordsCreated = 0;

            foreach ($data as $row) {
                if ($this->isEmptyRow($row)) {
                    continue;
                }

                try {
                    $recordData = $this->extractRowData($row, $columnMap);

                    // Record creëren
                    Record::create([
                        'upload_id' => $upload->bestand_id,
                        'date' => $recordData['date'] ?? null,
                        'action' => $recordData['action'] ?? null,
                        'description' => $recordData['description'] ?? null,
                        'worker' => $recordData['worker'] ?? null,
                        'time' => $recordData['time'] ?? null,
                        'costs' => $recordData['costs'] ?? null,
                        'user_id' => $upload->user_id,
                    ]);

                    $recordsCreated++;
                } catch (\Exception $e) {
                    Log::warning("Fout bij record-verwerking:", ['error' => $e->getMessage()]);
                    // Ga door naar volgende record
                }
            }

            // Upload bijwerken
            $upload->update([
                'status' => $recordsCreated > 0 ? 'completed' : 'declined',
                'processed_rows' => $recordsCreated,
            ]);

            return $recordsCreated > 0;

        } catch (\Exception $e) {
            Log::error('Excel import error:', ['error' => $e->getMessage()]);

            $upload->update([
                'status' => 'failed',
                'processed_rows' => 0,
            ]);

            return false;
        }
    }

    /**
     * Zoek het uploadbestand
     */
    private function findUploadFile(string $filename): string
    {
        // Gebruik storage_path() zoals Laravel configureerd (app/private/uploads)
        $uploadsDir = storage_path('app/private/uploads');

        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0755, true);
        }

        // Probeer exact match
        $filePath = $uploadsDir . DIRECTORY_SEPARATOR . $filename;
        if (file_exists($filePath)) {
            return $filePath;
        }

        // Probeer met basename
        $baseName = basename($filename);
        $filePath = $uploadsDir . DIRECTORY_SEPARATOR . $baseName;
        if (file_exists($filePath)) {
            return $filePath;
        }

        // Probeer de laatste uploadde bestand
        $files = @glob($uploadsDir . DIRECTORY_SEPARATOR . '*');
        if (!empty($files)) {
            usort($files, fn($a, $b) => filemtime($b) - filemtime($a));
            return $files[0];
        }

        throw new \Exception('Upload-bestand niet gevonden in ' . $uploadsDir);
    }

    /**
     * Detecteer welke kolom welke data bevat
     */
    private function detectColumns(array $headers): array
    {
        $columnMap = [];

        foreach ($headers as $index => $header) {
            if ($header === null || $header === '') {
                continue;
            }

            $headerLower = strtolower(trim((string) $header));

            // Voor elke kolom-type, check of deze header eraan voldoet
            foreach ($this->columnPatterns as $columnType => $patterns) {
                foreach ($patterns as $pattern) {
                    if (stripos($headerLower, $pattern) !== false) {
                        if (!isset($columnMap[$columnType])) {
                            $columnMap[$columnType] = $index;
                        }
                        break 2; // Ga naar volgende header
                    }
                }
            }
        }

        // Minstens een kolom moet gevonden worden
        return count($columnMap) > 0 ? $columnMap : [];
    }

    /**
     * Extract data uit een rij
     */
    private function extractRowData(array $row, array $columnMap): array
    {
        $data = [];

        foreach ($columnMap as $field => $columnIndex) {
            $value = $row[$columnIndex] ?? null;

            if ($value === null || $value === '') {
                continue;
            }

            // Type-casting per veld
            switch ($field) {
                case 'date':
                    $data[$field] = $this->parseDate($value);
                    break;
                case 'time':
                case 'costs':
                    $data[$field] = $this->parseNumeric($value);
                    break;
                default:
                    $data[$field] = trim((string) $value);
            }
        }

        return $data;
    }

    /**
     * Parse datum-waarden
     */
    private function parseDate($value): ?string
    {
        if (is_numeric($value)) {
            // Excel datum-nummer
            try {
                $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
                return $date->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }

        try {
            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Parse numerieke waarden
     */
    private function parseNumeric($value): ?float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        // Probeer komma te vervangen
        $value = str_replace(',', '.', trim((string) $value));

        if (is_numeric($value)) {
            return (float) $value;
        }

        return null;
    }

    /**
     * Check of rij leeg is
     */
    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $cell) {
            if ($cell !== null && $cell !== '') {
                return false;
            }
        }
        return true;
    }
}
