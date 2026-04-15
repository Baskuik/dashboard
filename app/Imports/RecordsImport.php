<?php

namespace App\Imports;

use App\Models\Record;
use App\Models\Upload;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Throwable;

class RecordsImport
{
    public function __construct(
        private Upload $upload,
        private int $userId
    ) {}

    public function import(string $filePath): int
    {
        Log::info('RecordsImport: import gestart', [
            'filePath' => $filePath,
            'file_exists' => file_exists($filePath),
        ]);

        if (! file_exists($filePath)) {
            throw new \RuntimeException("Importbestand niet gevonden: {$filePath}");
        }

        try {
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
        } catch (Throwable $exception) {
            Log::error('RecordsImport: bestand kon niet worden gelezen', [
                'filePath' => $filePath,
                'error' => $exception->getMessage(),
            ]);
            throw new \RuntimeException('Excelbestand kon niet worden gelezen.', 0, $exception);
        }

        if (empty($rows)) {
            Log::warning('RecordsImport: bestand is leeg', ['filePath' => $filePath]);

            return 0;
        }

        $rawHeaders = $rows[0];
        $headers = array_map(fn ($header) => $this->normalizeHeader($header), $rawHeaders);
        $columns = $this->detectColumns($headers);

        Log::info('RecordsImport: headers en kolomposities gedetecteerd', [
            'raw_headers' => $rawHeaders,
            'normalized_headers' => $headers,
            'column_positions' => $columns,
        ]);

        $requiredColumns = ['worker', 'action', 'costs', 'time', 'date'];
        $missingRequiredColumns = array_values(array_filter(
            $requiredColumns,
            fn ($column) => $columns[$column] === null
        ));

        if ($missingRequiredColumns !== []) {
            Log::error('RecordsImport: verplichte kolommen ontbreken', [
                'missing_columns' => $missingRequiredColumns,
                'column_positions' => $columns,
                'headers' => $headers,
            ]);

            throw new \RuntimeException(
                'Verplichte kolommen ontbreken in Excel: '.implode(', ', $missingRequiredColumns)
            );
        }

        $count = 0;
        $skipped = 0;

        for ($index = 1; $index < count($rows); $index++) {
            $row = $rows[$index];
            $rowNumber = $index + 1;

            if (! $this->rowHasContent($row)) {
                continue;
            }

            if (count($row) !== count($headers)) {
                Log::debug('RecordsImport: rij/header lengte mismatch', [
                    'row_number' => $rowNumber,
                    'header_count' => count($headers),
                    'row_count' => count($row),
                    'row' => $row,
                ]);
            }

            $action = trim((string) $this->valueAt($row, $columns['action']));
            $date = $this->valueAt($row, $columns['date']);
            $worker = trim((string) $this->valueAt($row, $columns['worker']));
            $description = trim((string) $this->valueAt($row, $columns['description']));
            $time = $this->parseNumeric($this->valueAt($row, $columns['time']));
            $costs = $this->parseNumeric($this->valueAt($row, $columns['costs']));
            $parsedDate = $this->parseDate($date);

            Log::debug('RecordsImport: rijwaarden geëxtraheerd', [
                'row_number' => $rowNumber,
                'worker' => $worker,
                'action' => $action,
                'description' => $description,
                'time' => $time,
                'costs' => $costs,
                'date' => $parsedDate,
            ]);

            if (empty($action) && empty($date)) {
                continue;
            }

            if (strtolower($action) === 'totaal') {
                continue;
            }

            $recordData = [
                'upload_id' => $this->upload->bestand_id,
                'user_id' => $this->userId,
                'date' => $parsedDate,
                'action' => $action,
                'description' => $description,
                'worker' => $worker,
                'time' => $time,
                'costs' => $costs,
            ];

            try {
                $validated = Record::validateRecord($recordData);
                Record::create($validated);
                $count++;
            } catch (ValidationException $exception) {
                $skipped++;
                Log::warning('RecordsImport: rij skipped vanwege validatie fouten', [
                    'row_number' => $rowNumber,
                    'errors' => $exception->errors(),
                    'data' => $recordData,
                ]);
            } catch (Throwable $exception) {
                $skipped++;
                Log::error('RecordsImport: rij skipped vanwege error', [
                    'row_number' => $rowNumber,
                    'error' => $exception->getMessage(),
                    'data' => $recordData,
                ]);
            }
        }

        Log::info('RecordsImport klaar', [
            'count' => $count,
            'skipped' => $skipped,
            'total_processed' => $count + $skipped,
        ]);

        return $count;
    }

    private function parseDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
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

        // Controleer voor tijd-formaten (H:MM of HH:MM:SS of H;MM:SS of H;MM)
        // Ondersteunt: "1:30", "15:00", "29;15:00", "1;30" etc.
        if (preg_match('/^(\d+)[;:](\d+)(?::(\d+))?$/', $value, $matches)) {
            $hours = (int) $matches[1];
            $minutes = (int) $matches[2];
            $seconds = isset($matches[3]) ? (int) $matches[3] : 0;

            // Zet om naar decimale uren
            // Bijv: 1 uur + 30 minuten = 1 + (30/60) = 1.5 uur
            return $hours + ($minutes / 60) + ($seconds / 3600);
        }

        // Originele numerieke parsing logica
        // Verwijder valutasymbolen
        $value = preg_replace('/[€$£\s]/', '', $value);

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

    private function normalizeHeader(mixed $value): string
    {
        $header = (string) ($value ?? '');
        $header = preg_replace('/^\xEF\xBB\xBF/', '', $header) ?? $header;
        $header = str_replace("\xC2\xA0", ' ', $header);
        $header = mb_strtolower(trim($header));
        $header = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $header) ?? $header;
        $header = preg_replace('/\s+/u', ' ', $header) ?? $header;

        return trim($header);
    }

    private function detectColumns(array $headers): array
    {
        $patterns = [
            'action' => ['actie', 'action', 'type'],
            'date' => ['datum', 'date'],
            'description' => ['omschrijving', 'beschrijving', 'description', 'toelichting', 'info', 'opmerking', 'details', 'detail', 'note', 'notes', 'notities', 'notitie'],
            'worker' => ['medewerker', 'medeweker', 'worker', 'werknemer', 'employee', 'naam', 'name'],
            'time' => ['uren', 'time', 'hours', 'duur', 'tijd'],
            'costs' => ['kosten', 'costs', 'bedrag', 'amount', 'prijs', 'tarief'],
        ];

        $columns = [
            'worker' => null,
            'action' => null,
            'description' => null,
            'time' => null,
            'costs' => null,
            'date' => null,
        ];

        foreach ($patterns as $field => $possibleNames) {
            foreach ($headers as $index => $header) {
                foreach ($possibleNames as $name) {
                    if ($header !== '' && strpos($header, $name) !== false) {
                        $columns[$field] = $index;
                        break 2;
                    }
                }
            }
        }

        $fallbackPositions = count($headers) >= 8
            ? [
                'worker' => 1,
                'action' => 2,
                'costs' => 3,
                'time' => 4,
                'description' => 5,
                'date' => 7,
            ]
            : [
                'worker' => 0,
                'action' => 1,
                'description' => 2,
                'date' => 3,
                'costs' => 4,
                'time' => 5,
            ];

        foreach ($fallbackPositions as $field => $position) {
            if ($columns[$field] === null && array_key_exists($position, $headers)) {
                $columns[$field] = $position;
            }
        }

        return $columns;
    }

    private function valueAt(array $row, ?int $index): mixed
    {
        if ($index === null) {
            return null;
        }

        return $row[$index] ?? null;
    }

    private function rowHasContent(array $row): bool
    {
        return ! empty(array_filter(
            $row,
            fn ($value) => $value !== null && trim((string) $value) !== ''
        ));
    }
}
